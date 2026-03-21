<?php

namespace Application\quakercall;

use Tops\services\MessageType;
use Tops\services\TServiceContext;
use Tops\services\TUploadHelper;
use Tops\sys\TConfiguration;
use Tops\sys\TPath;

class UploadManager
{
    private TServiceContext $service;
    private $uploadPath;

    public function __construct(TServiceContext $serviceContext) {
        $this->service = $serviceContext;
    }
    public function getUploadPath($subDir='csv',$expectedExtension = 'csv') {
        if (!isset($this->upploadPath)) {
            $uploadPath = TConfiguration::getValue('uploads', 'location',
                'application/uploads');
            if (str_starts_with($uploadPath, '/')) {
                $uploadPath = substr($uploadPath, 1);
            }
            if (!str_ends_with($uploadPath, '/')) {
                $uploadPath .= '/';
            }
            $uploadPath = TPath::fromFileRoot($uploadPath.$subDir);
            if (!is_dir($uploadPath) && !mkdir($uploadPath, 0777, true) && !is_dir($uploadPath)) {
                throw new \RuntimeException('Failed to create directory: ' . $uploadPath);
            }

            $this->uploadPath = $uploadPath;
        }
        return $this->uploadPath;
    }

    private function hasErrors() {
        $response = $this->service->GetResponse();
        foreach ($response->Messages as $message) {
            if ($message->MessageType == MessageType::Error) {
                return true;
            }
        }
        return false;
    }
    public function saveFiles($subDir='csv',$expectedExtension = 'csv') : string | false
    {
        $fileNames = TUploadHelper::filesReady($this->service);
        if ($this->hasErrors()) {
            return false;
        }
        $fileCount = count($fileNames);
        if ($fileCount) {
            $fileName = TPath::normalizeFileName($fileNames[0]);
            $ext = strtolower( pathinfo($fileName, PATHINFO_EXTENSION));
            if ($ext !== $expectedExtension) {
                $this->service->addErrorMessage('Sorry, your file must be in '.
                    strtoupper($expectedExtension).' format.');
                return false;
            }
        }
        else {
            $this->service->addErrorMessage('No files were uploaded');
            return false;
        }

        $uploadPath = $this->getUploadPath();
        if ($uploadPath == false) {
            $this->service->addErrorMessage('SYSTEM ERROR: Cannot get upload path');
            return false;
        }
        // place file in expected location
        $uploadedFiles = TUploadHelper::upload($this->service, $uploadPath);
        if ($this->hasErrors()) {
            return false;
        }
        if (empty($uploadedFiles)) {
            $this->service->addErrorMessage('Cannot get uploaded file');
            return false;
        }
        return $uploadPath.'/'.$fileName;
    }


}