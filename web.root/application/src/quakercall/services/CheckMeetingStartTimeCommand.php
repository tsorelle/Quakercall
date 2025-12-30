<?php

namespace Application\quakercall\services;

use Application\quakercall\db\repository\QcallMeetingsRepository;
use Tops\services\TServiceCommand;

class CheckMeetingStartTimeCommand extends TServiceCommand
{

    protected function run()
    {
        $meetingId = $this->getRequest();
        $meetingsRepository = new QcallMeetingsRepository();
        $timeForMeeting = $meetingsRepository->meetingReady($meetingId);
        $this->setReturnValue($timeForMeeting);
    }
}