<?php

namespace Application\quakercall\services;

use Application\quakercall\db\entity\QcallSuppression;
use Application\quakercall\db\repository\QcallContactsRepository;
use Application\quakercall\db\repository\QcallSuppressionsRepository;
use Application\quakercall\UploadManager;
use Mailgun\Api\Suppression;
use Tops\services\TServiceCommand;
use Tops\services\TUploadHelper;
use Tops\sys\TPath;

class ProcessSuppressionListCommand extends TServiceCommand
{

   private $responseList = [];

   private function addToResponseList(QcallSuppression $suppression,$firstName,$lastName)
   {
       $response = new \stdClass();
       $response->email = $suppression->email;
       $response->reason = $suppression->reason;
       $response->firstName = $firstName;
       $response->lastName = $lastName;
       $this->responseList[] = $response;

   }
    protected function run()
    {
        $uploadManager = new UploadManager($this->getMessages());
        $filePath = $uploadManager->saveFiles('tmp','csv');
        if ($filePath === false) {
            return;
        }
        $bounceCount = 0;
        $unsubCount = 0;
        $noneCount = 0;
        $processedCount = 0;
        $emailIdx = -1;
        $reasonIdx = -1;
        $firstIdx = -1;
        $lastIdx = -1;
        $this->responseList = [];

        if (($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle, 0, ',');
            if (empty($header)) {
                $this->addInfoMessage('File is empty');
            }
            for ($idx = 0; $idx < count($header); $idx++) {
                switch ($header[$idx]) {
                    case 'Email':
                        $emailIdx = $idx;
                        break;
                    case 'Suppression reason':
                        $reasonIdx = $idx;
                        break;
                    case 'First name':
                        $firstIdx = $idx;
                        break;
                    case 'Last name':
                        $lastIdx = $idx;
                        break;
                }
            }
            if ($emailIdx === -1 || $reasonIdx === -1) {
                $this->addErrorMessage('File is not in expected format. Email or Suppression reason column not found.');
                return;
            }
            $repo = new QcallContactsRepository();
            $suppressionsRepo = new QcallSuppressionsRepository();
            $processedDate = date('Y-m-d H:i:s');
            while (($values = fgetcsv($handle, 0, ',')) !== false) {
                $suppression = new QcallSuppression();
                $suppression->processedDate = $processedDate;
                $suppression->email = $values[$emailIdx];
                $suppression->reason = $values[$reasonIdx];
                $suppression->active = 1;
                switch ($suppression->reason) {
                    case 'email_blocked' :
                    case 'email_bounced_hard' :
                        $suppression->disposition = 'bounced';
                        $repo->setBounced($suppression->email);
                        $bounceCount++;
                        break;
                    // case 'subscription_requested' :
                    case 'email_marketing_unsubscribed' :
                        $suppression->disposition = 'unsubscribed';
                        $repo->unsubscribe($suppression->email);
                        $unsubCount++;
                        break;
                    default:
                        $suppression->disposition = 'none';
                        $this->addToResponseList($suppression,$values[$firstIdx],$values[$lastIdx]);
                        $noneCount++;
                        break;
                }
                $suppressionsRepo->insert($suppression);
                $processedCount++;
            }

            fclose($handle);
            unlink($filePath);
        }

        if ($processedCount == 0) {
            $this->addWarningMessage('No suppressions found to process.');
        }
        else {
            if ($bounceCount) {
                $this->addInfoMessage('Processed ' . $bounceCount . ' bounced or invalid email addresses. ');
            }
            if ($unsubCount) {
                $this->addInfoMessage('Processed ' . $unsubCount . ' unsubscribed email addresses. ');
            }
            if ($noneCount) {
                $this->addInfoMessage('Logged ' . $noneCount . ' email addresses for further review. See list below.');
            }
        }
        $this->setReturnValue($this->responseList);
    }
}