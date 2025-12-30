<?php

namespace Application\quakercall\services;

use Application\quakercall\db\repository\QcallMeetingsRepository;
use Tops\mail\TEmailValidator;
use Tops\services\TServiceCommand;

/*
     interface IJoinMeetingRequest {

        registered : boolean;
        message : string;
        nameError: boolean;
        emailError: boolean;
        denied: boolean;
    }

    interface IJoinMeetingResponse {
        registered : boolean;
        error : string;
        zoomId : string;
        zoomHref :string;
        zoomPwd: string;
    }

 */

class CheckMeetingRegistrationCommand extends TServiceCommand
{

    private function checkRegistration($meetingId,$email)
    {
        return false;
    }
    private function registerParticipant($meetingId,$email, $name) {
        return true;
    }
    private function checkEmail($email) {
        if (empty($email)) {
            return false;
        }
        $validation = TEmailValidator::Validate($email);
        return ($validation->isValid);
    }

    private function sanitizeName($value) {
        $stripped = strip_tags($value);
        if ($stripped != $value) {
            return false;
        }
        return preg_replace('/[^\p{L}\p{M}0-9\'"\.\(\):;&\+ -]/u', '', $value);
    }

    protected function run()
    {
        $response = new \stdClass();
        $response->registered = false;
        $response->emailError = false;
        $response->nameError = false;
        $response->denied = false;

        $request = $this->getRequest();
        $email = $request->email ?? '';
        $meetingId = $request->meetingId ?? '';


        if ($this->checkEmail($email)) {
            switch ($request->action) {
                case 'register':
                    $name = trim($request->name ?? '');
                    if (empty($name)) {
                        $response->nameError = true;
                    } else {
                        $response->registered = $this->registerParticipant($meetingId, $request->email, $name);
                        if ($response->registered) {
                            $invite = $this->getMeetingInvitation($meetingId);
                            if ($invite) {
                                $response->zoomId = $invite->meetingId;
                                $response->zoomHref = $invite->url;
                                $response->zoomPwd = $invite->passCode;
                            }
                        } else {
                            $this->addErrorMessage('Unable to get meeting invitation');
                            return;
                        }
                    }
                    break;
                default:
                    $isRegistered = $this->checkRegistration($meetingId, $email);
                    break;
            }
        } else {
            $response->emailError = true;
        }


        $this->setReturnValue($response);
    }

    private function getMeetingInvitation(string $meetingId)
    {
        $result = new \stdClass();
        $result->meetingId =  '861 4198 4369';
        $result->url 		= 'https://us02web.zoom.us/j/86141984369?pwd=UmhhLzEwZnVxS2RLbmRQb2YycXU1Zz09';
        $result->passCode  = 'banjo';
        return $result;
    }
}