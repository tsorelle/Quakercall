<?php

namespace Application\quakercall\services;



use Application\quakercall\db\entity\QcallRegistration;

class JotFormManager
{
    public static function processForm() {
        global $_POST;
        $result = new QcallRegistration();

        // todo: update for new data model
/*        $result->submissionDate = 	DATE('Y-m-d');
        $result->submissionId = $_POST['submission_id']  ?? '';
        $result->formId =     $_POST['formID']  ?? '';
        $result->ipAddress =  $_POST['ip']  ?? '';
        $result->firstName =    $_POST['name']['first']  ?? '';
        $result->lastName =  	$_POST['name']['last']  ?? '';
        $result->email =    $_POST['email']  ?? '';
        $result->phone =  $_POST['phonenumber']  ?? '';
        $result->location = $_POST['yourlocation']  ?? '';
        $result->affiliation = $_POST['affiliation'][0]  ?? '';
        $result->meeting = $_POST['friendsmeeting']  ?? '';*/


        return $result;

    }

}