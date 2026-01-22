<?php

namespace Application\quakercall\services;

use Application\quakercall\db\QcallDataManager;
use Application\quakercall\db\repository\QcallMeetingsRepository;
use Tops\services\TServiceCommand;

class GetCurrentMeetingCommand extends TServiceCommand
{

    protected function run()
    {
        $meetingId = $this->getRequest();
        $repository = new QcallMeetingsRepository();
        if ($meetingId === '696a585072a29') {
            $current = $repository->getTestMeeting();
        }
        else {
            $current = $repository->getCurrentMeeting('2 HOUR');
        }
        $this->setReturnValue($current);
    }
}