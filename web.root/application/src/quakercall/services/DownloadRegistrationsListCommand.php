<?php
namespace Application\quakercall\services;

use Application\quakercall\db\repository\QcallMeetingsRepository;
use Application\quakercall\db\repository\QcallRegistrationsRepository;
use Tops\services\TServiceCommand;
use Tops\sys\TCsvFormatter;
use Tops\sys\TDates;

class DownloadRegistrationsListCommand extends TServiceCommand
{

    protected function run()
    {
        $meetingsRepository = new QcallMeetingsRepository();
        $meetingId = $meetingsRepository->getLatestMeetingId();
        $repository = new QcallRegistrationsRepository();
        $list = $repository->getRegistrationListForDownload($meetingId);
        $csv = TCsvFormatter::ToCsv($list);
        $response = new \stdClass();
        $response->data = $csv;
        $response->filename = 'registrations-list'.'-'.TDates::now(TDates::FilenameTimeFormat);
        $this->setReturnValue($response);
    }
}