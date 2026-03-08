<?php

namespace Application\quakercall\services;

use Application\quakercall\db\QcallDataManager;
use Application\quakercall\db\repository\QcallMeetingsRepository;
use Tops\services\TServiceCommand;
use Tops\sys\TPath;

class GetCurrentMeetingCommand extends TServiceCommand
{

    protected function run()
    {
        $upcoming = $this->getRequest() ?? false;

        $repository = new QcallMeetingsRepository();
        if ($upcoming) {
            $meeting = $repository->getUpcomingMeeting();
        }
        else {
            $meeting = $repository->getCurrentMeeting('2 HOUR');
        }

        if(!empty($meeting->image)) {
            $path = '/application/assets/img/meeting/'.$meeting->image;
            $filepath = TPath::fromFileRoot($path);
            if (file_exists($filepath)) {
                $meeting->image = $path;
            }
            else {
                $meeting->image = '';
            }
        }
        $titleParts = explode(':', $meeting->theme);
        $meeting->theme = trim( $titleParts[0]);
        $meeting->subtitle = count($titleParts) > 1 ? trim($titleParts[1]) : '';
        $this->setReturnValue($meeting);
    }
}