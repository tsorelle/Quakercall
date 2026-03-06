<?php

namespace Application\quakercall\services;

use Application\quakercall\db\QcallDataManager;
use Tops\mail\TEmailValidator;
use Tops\services\TServiceCommand;

/**
 * Service contract
 * Request:
 * interface IRegistrationRequest {
 * meetingId : any;
 * name : string;
 * email : string;
 * city : string;
 * state: string;
 * country: string;
 * postalCode: string;
 * phone: string;
 * organization: string;
 * religion: string;
 * }
 *
 * Response:
 * interface IRegistrationResponse {
 * fullname : string;
 * phone : string;
 * location : string;
 * email : string;
 * organization : string;
 * submissionId : string;
 * registrationId : any;
 * contactId : any;
 * }
 */
class PostMeetingRegistrationCommand extends TServiceCommand
{

    protected function run()
    {
        $request = $this->getRequest();
        if (empty($request)) {
            $this->addErrorMessage('No request received');
            return;
        }
        if (empty($request->meetingId)) {
            $this->addErrorMessage('No email received');
            return;
        }
        if (empty($request->name)){
            $this->addErrorMessage('No name received');
            return;
        }

        $validation = TEmailValidator::CheckEmailAddress($request->email);
        if ($validation->valid === false) {
            $this->addErrorMessage('Invalid email address');
            return;
        }
        if ($validation->changed == true && !empty($validation->email)) {
            $request->email = $validation->email;
        }

        $manager = new QcallDataManager();
        $result = $manager->PostMeetingRegistration($request);
        if (!empty($result->error)) {
            $this->addErrorMessage($result->error);
            return;
        }
        $this->setReturnValue($result);
    }
}