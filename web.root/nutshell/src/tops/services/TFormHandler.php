<?php

namespace Tops\services;

use Tops\sys\TWebSite;

abstract class TFormHandler
{
    protected $formType = 'jotform';

    // Simple function to check if an IP is in a CIDR range
    function ipInRange($ip, $cidr) {
        list($subnet, $mask) = explode('/', $cidr);
        return (ip2long($ip) & ~((1 << (32 - $mask)) - 1)) === ip2long($subnet);
    }
    private function validateContentType(array $validTypes): bool
    {
        $contentType = strtolower($_SERVER['CONTENT_TYPE']);

        foreach ($validTypes as $type) {
            if (strpos($contentType, $type) !== false) {
                return true;
            }
        }
        return false;
    }
    private function checkContentType($formtype): bool
    {
        if ($formtype === 'local') {
            return true;
        }
        if (!isset($_SERVER['CONTENT_TYPE'])) {
            return false;
        }
        if ($formtype == 'jotform') {
            return $this->validateContentType(['application/x-www-form-urlencoded', 'multipart/form-data']);
        }
        return false;
    }

    protected function ValidatePost($formType='jotform')
    {
        $response = new \stdClass();
        $response->errors = [];
        if (TWebSite::GetEnvironmentName() !== 'local' && (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') ){
            $response->errors[] = "The web site is not secure";
            return $response;
        }
        // Only allow POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $response->errors[] = "The request method must be POST";
            return $response;
        }

        if (!$this->checkContentType($formType)) {
            $response->errors[] = "Invalid content type";
            return $response;
        }

        // todo: check for banned ip addresses
        // $clientIp = $_SERVER['REMOTE_ADDR'] ?? '';

        //---------------------------------------------------------------------

        // 3. CSRF-style protection: secret token
        //---------------------------------------------------------------------
        // In your form, add a hidden field named "auth_token"
        // with a strong, random value that only you know.

        define('FORM_SECRET_TOKEN', '0980ad9f80ad9f80a0ad8f08a0f8e0e9809');

        $authToken = $_POST['auth_token'] ?? '';

        if (!hash_equals(FORM_SECRET_TOKEN, $authToken)) {
            $response->errors[] = "Invalid form token";
        }

        return $response;
    }

    protected function getFormValues(array $names,$response) {
        $response->data = [];
        foreach ($names as $name) {
            $raw =  $_POST[$name]  ?? '';
            if (mb_strlen($raw) > 5000) {
                $response->errors[] = "$name value is too long";
                return false;
            }
            $response->data[$name] = $raw;
        }
    }

    protected function processForm($formType = 'local') : void
    {
        $response = $this->ValidatePost($formType);
        if (empty($response->errors)) {
            $this->getFormValues($formType, $response);
            $this->doProcessForm($response);
        }
    }

    abstract function doProcessForm($formData);
}