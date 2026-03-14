<?php

namespace Application\quakercall\services;

use Application\quakercall\db\QcallDataManager;
use Tops\mail\TPostOffice;
use Tops\services\TServiceCommand;
use Tops\services\TUploadHelper;
use Tops\sys\TConfiguration;
use Tops\sys\TPath;
use Tops\sys\TWebSite;

class PostGroupEndorsementCommand extends TServiceCommand
{

    private $documentPath;
    private function getDocumentPath() {
        if (!isset($this->documentPath)) {
            $this->documentPath = TConfiguration::getValue('documents', 'location', 'application/documents');
            if (str_starts_with($this->documentPath, '/')) {
                $this->documentPath = substr($this->documentPath, 1);
            }
            if (!str_ends_with($this->documentPath, '/')) {
                $this->documentPath .= '/';
            }
            $this->documentPath .= 'endorsements';
        }
        return $this->documentPath;
    }

    private function getDocumentFilePath() {
        return TPath::fromFileRoot($this->getDocumentPath());
    }

    private function getDocumentLocalURL($fileName) {
        return TWebSite::GetLocalUrl($this->getDocumentPath(),$fileName);
    }

    private function saveFiles() : string | false
    {
        $fileNames = TUploadHelper::filesReady($this->getMessages());
        if ($this->hasErrors()) {
            return false;
        }
        $fileCount = count($fileNames);
        if ($fileCount) {
            $documentName = TPath::normalizeFileName($fileNames[0]);
            $ext = strtolower( pathinfo($documentName, PATHINFO_EXTENSION));
            if ($ext !== 'pdf') {
                $this->addErrorMessage('Sorry, your file must be in PDF format.');
                return false;
            }
        }
        else {
            $this->addErrorMessage('No files were uploaded');
            return false;
        }

        $documentDir = $this->getDocumentFilePath();

        if (!is_dir($documentDir)) {
            if (!@mkdir($documentDir, 0777, true)) {
                $this->addErrorMessage('document-error-mkdir-failed');
                return false;
            };
        }
        // place file in expected location
        $uploadedFiles = TUploadHelper::upload($this->getMessages(), $documentDir);
        if ($this->hasErrors()) {
            return false;
        }
        if (empty($uploadedFiles)) {
            $this->addErrorMessage('SYSTEM ERROR: Cannot get uploaded file');
            return false;
        }
        return implode(',', $fileNames);
    }
    protected function run()
    {
        $request = $this->getRequest();
        if (empty($request)) {
            $this->addErrorMessage('No request received');
            return;
        }
        $documentationType = $request->documentationType ?? null;
        if (empty($documentationType)) {
            $this->addErrorMessage('No documentation type received');
            return;
        }
        if ($documentationType == 'upload') {
            $request->document = $this->saveFiles();
            if ($request->document === false) {
                return;
            }
        }
        $manager = new QcallDataManager();
        $endorsement = $manager->SubmitGroupEndorsement($request);
        if ($endorsement === false) {
            $this->addErrorMessage('Unexpected error: Cannot insert endorsement.');
            return;
        }
        $response = new \stdClass();

        $response->organizationName = $endorsement->organizationName;
        $response->contactName = $endorsement->contactName;
        $response->location = $endorsement->city . ', ' . $endorsement->state;
        $response->phone = $endorsement->phone;
        $response->email = $endorsement->email;
        $response->submissionId = $endorsement->submissionId;

        $date = date('Y-m-d');
        $senderAddress = TPostOffice::GetMailboxAddress('admin');
        $approvalPage =  TConfiguration::getValue('approvals','pages','/admin/approvals');
        $approvalPage = TWebSite::ExpandUrl($approvalPage);
        TPostOffice::SendMessageToUs($senderAddress,
            "New group endorsement received on $date",
            "<p>A new organizational endorsement from $endorsement->organizationName has been submitted.  ".
            "<a href='$approvalPage'>Please review for approval</a>.</p>."
        );

        $this->setReturnValue($response);
    }
}