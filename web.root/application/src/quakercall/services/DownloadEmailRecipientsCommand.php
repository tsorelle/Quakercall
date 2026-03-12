<?php

namespace Application\quakercall\services;

use Application\quakercall\db\repository\QcallContactsRepository;
use Tops\services\TServiceCommand;
use Tops\sys\TCsvFormatter;
use Tops\sys\TDates;

class DownloadEmailRecipientsCommand extends TServiceCommand
{

    protected function run()
    {
        $type = $_POST['downloadtype'] ?? 'contacts';
        if ($type == 'godaddy') {
            $type = 'mailing-upload';
        }
        $filter = $_POST['filter'] ?? 'full';
       $list = (new QcallContactsRepository())->getEmailRecipients();
        $csv = TCsvFormatter::ToCsv($list);

        $response = new \stdClass();
        $response->data = $csv;
        $response->filename =
            sprintf('%s-%s-%s.csv',
                date('Y-m-d'),$filter, $type);
        $this->setReturnValue($response);
    }
}