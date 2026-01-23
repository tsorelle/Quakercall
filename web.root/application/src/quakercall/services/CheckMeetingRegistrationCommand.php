<?php

namespace Application\quakercall\services;

use Application\quakercall\db\entity\QcallMeeting;
use Application\quakercall\db\QcallDataManager;
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
        meetingAvailable: boolean;
        registered : boolean;
        message : string;
        nameError: boolean;
        emailError: boolean;
        denied : boolean;
        zoomId : string;
        zoomHref :string;
        zoomPwd: string;
        subscribed: boolean;
    }

 */



class CheckMeetingRegistrationCommand extends TServiceCommand
{
    private QcallDataManager $dataManager;
    private function getDataManager()
    {
        if (!isset($this->dataManager)) {
            $this->dataManager = new QcallDataManager();
        }
        return $this->dataManager;
    }

    private $meeting;
    private string $email;

/*    private function checkRegistration()
    {
        return $this->getDataManager()->isRegistered($this->email);
    }
    private function registerParticipant($name) {
        return $this->getDataManager()->registerParticipant($name,$this->email,$this->meeting->id,true);
    }*/

    private function checkEmail() {
        if (empty($this->email)) {
            return false;
        }
        $validation = TEmailValidator::Validate($this->email);
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
        $response->subscribed = false;

        $request = $this->getRequest();
        $this->email = $request->email ?? '';
        $meetingCode = $request->meetingCode ?? '';
        $subscribe  = $request->subscribe ?? false;
        if (empty($meetingCode)) {
            $meeting = $this->getDataManager()->getCurrentMeeting();
            if (empty($meeting)) {
                $this->addErrorMessage('No meeting is current.');
                return;
            }
            $this->meeting = $meeting;
        }
        else {
            // todo: implement multiple meeting support.  This may not work for the purpose
            $this->meeting = $this->getDataManager()->getMeetingByCode($meetingCode);
        }

        if ($this->checkEmail()) {
            switch ($request->action) {
                case 'register':
                    $name = trim($request->name ?? '');
                    if (empty($name)) {
                        $response->nameError = true;
                    } else {
                        $response->registered = $this->getDataManager()->registerParticipant($name,$this->email,$this->meeting->id,$subscribe);
                    }
                    break;
                default:
                    $isRegistered = $this->getDataManager()->isRegistered($this->meeting->id, $this->email);
                    if ($isRegistered) {
                        $this->getDataManager()->confirmRegistration($this->meeting->id, $this->email);
                    }
                    $response->registered = $isRegistered;
                    $response->subscribed = $this->getDataManager()->isSubscribed($this->email);
                    break;
            }
        } else {
            $response->emailError = true;
        }
        if ($response->registered) {
            $response->zoomId = $this->meeting->zoomMeetingId;
            $response->zoomHref = $this->meeting->zoomUrl;
            $response->zoomPwd = $this->meeting->zoomPasscode;
        }

        $this->setReturnValue($response);
    }



}