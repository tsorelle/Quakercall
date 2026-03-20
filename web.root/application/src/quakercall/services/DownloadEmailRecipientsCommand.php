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
        $repo = new QcallContactsRepository();
        switch ($filter) {
            case 'unposted':
                $list = $repo->getUnPostedEmailRecipients();
                break;
            case 'full':
                $list = $repo->getEmailRecipients();
                break;
            default:
                exit('Invalid filter: ' . $filter);
        }
        $repo->setPostedDate();

        $csv = TCsvFormatter::ToCsv($list);

        $response = new \stdClass();
        $response->data = $csv;
        $response->filename =
            sprintf('%s-%s-%s',
                date('Y-m-d'),$filter, $type);
        $this->setReturnValue($response);
    }
}