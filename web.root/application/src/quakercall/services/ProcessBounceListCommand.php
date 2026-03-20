<?php

namespace Application\quakercall\services;

use Application\quakercall\db\repository\QcallContactsRepository;
use Tops\services\TServiceCommand;
use Tops\services\TUploadHelper;
use Tops\sys\TConfiguration;
use Tops\sys\TPath;

class ProcessBounceListCommand extends TServiceCommand
{
    private $uploadPath;
    private function getUploadPath() {
        if (!isset($this->upploadPath)) {
            $uploadPath = TConfiguration::getValue('uploads', 'location',
                'application/uploads');
            if (str_starts_with($uploadPath, '/')) {
                $uploadPath = substr($uploadPath, 1);
            }
            if (!str_ends_with($uploadPath, '/')) {
                $uploadPath .= '/';
            }
            $uploadPath = TPath::fromFileRoot($uploadPath.'csv');
            if (!is_dir($uploadPath) && !mkdir($uploadPath, 0777, true) && !is_dir($uploadPath)) {
                throw new \RuntimeException('Failed to create directory: ' . $uploadPath);
            }

            $this->uploadPath = $uploadPath;
        }
        return $this->uploadPath;
    }

    private function saveFiles() : string | false
    {
        $fileNames = TUploadHelper::filesReady($this->getMessages());
        if ($this->hasErrors()) {
            return false;
        }
        $fileCount = count($fileNames);
        if ($fileCount) {
            $fileName = TPath::normalizeFileName($fileNames[0]);
            $ext = strtolower( pathinfo($fileName, PATHINFO_EXTENSION));
            if ($ext !== 'csv') {
                $this->addErrorMessage('Sorry, your file must be in CSV format.');
                return false;
            }
        }
        else {
            $this->addErrorMessage('No files were uploaded');
            return false;
        }

        $uploadPath = $this->getUploadPath();
        if ($uploadPath == false) {
            $this->addErrorMessage('SYSTEM ERROR: Cannot get upload path');
            return false;
        }
        // place file in expected location
        $uploadedFiles = TUploadHelper::upload($this->getMessages(), $uploadPath);
        if ($this->hasErrors()) {
            return false;
        }
        if (empty($uploadedFiles)) {
            $this->addErrorMessage('Cannot get uploaded file');
            return false;
        }
        return $uploadPath.'/'.$fileName;
    }


    protected function run()
    {
        $filePath = $this->saveFiles();
        if ($filePath === false) {
            return;
        }

        if (($handle = fopen($filePath, 'r')) !== false) {
            $headers = fgetcsv($handle, 0, ',');
            if (empty($headers)) {
                $this->addInfoMessage('File is empty');
            }
            $test = $headers[1] ?? '';
            if ($test != 'email') {
                $this->addErrorMessage('File is not in expected format. Email column not found.');
                return;
            }
            $repo = new QcallContactsRepository();
            $processed = 0;
            while (($data = fgetcsv($handle, 0, ',')) !== false) {
                $email = $data[1] ?? null;
                if (!empty($email)) {
                    $repo->setBounced($email);
                    $processed++;
                }
            }

            fclose($handle);
        }
        $this->addInfoMessage('Processed '.$processed.' bounces.');

    }
}