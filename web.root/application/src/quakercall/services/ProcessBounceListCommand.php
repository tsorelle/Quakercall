<?php

namespace Application\quakercall\services;

use Application\quakercall\db\repository\QcallContactsRepository;
use Application\quakercall\UploadManager;
use Tops\services\TServiceCommand;
use Tops\services\TUploadHelper;
use Tops\sys\TConfiguration;
use Tops\sys\TPath;

class ProcessBounceListCommand extends TServiceCommand
{
    protected function run()
    {
        $uploadManager = new UploadManager($this->getMessages());

        $filePath = $uploadManager->saveFiles('tmp','csv');
        // $filePath = $this->saveFiles();
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
            unlink($filePath);
            $this->addInfoMessage('Processed '.$processed.' bounces.');
        }
        else {
            $this->addErrorMessage('Could not open file.');
        }
    }
}