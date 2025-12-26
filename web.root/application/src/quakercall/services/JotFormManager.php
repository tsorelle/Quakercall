<?php

namespace Application\quakercall\services;

class QcRegistration {
    public $submissionDate = '';
    public $firstName = '';
    public $lastName = '';
    public $email = '';
    public $phone = '';
    public $location = '';
    public $submissionId = '';
    public $formId = '';
    public $ipAddress = '';
    public $affiliation = '';
    public $meeting = '';
// "Submission Date","Register NOW!",Name,Email,"Phone Number","State in which you reside",Affiliation
}

class JotFormManager
{
    public static function processForm() {
        global $_POST;
        $result = new QcRegistration();

        $result->submissionDate = 	DATE('Y-m-d');
        $result->submissionId = $_POST['submission_id']  ?? '';
        $result->formId =     $_POST['formID']  ?? '';
        $result->ipAddress =  $_POST['ip']  ?? '';
        $result->firstName =    $_POST['name']['first']  ?? '';
        $result->lastName =  	$_POST['name']['last']  ?? '';
        $result->email =    $_POST['email']  ?? '';
        $result->phone =  $_POST['phonenumber']  ?? '';
        $result->location = $_POST['yourlocation']  ?? '';
        $result->affiliation = $_POST['affiliation'][0]  ?? '';
        $result->meeting = $_POST['friendsmeeting']  ?? '';


        return $result;

    }

}