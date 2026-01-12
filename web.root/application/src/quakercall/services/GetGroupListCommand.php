<?php

namespace Application\quakercall\services;

use Application\quakercall\db\repository\QcallGroupendorsementsRepository;
use Tops\services\TServiceCommand;

/*
 * Service Contract:
 *
 * Response:
    interface IGroupListItem {
        organizationName : string;
        city: string;
        state: string;
    }
    interface IGetGroupListResponse {
        list: IGroupListItem[];
        count: string;
        lastDate: string;
    }
 */

class GetGroupListCommand extends TServiceCommand
{

    protected function run()
    {
        $repo = new QcallGroupendorsementsRepository();
        $result = new \stdClass();
        $result->list = $repo->getGroupendorsementList();
        $result->count = $repo->getEndorsementCount();
        $result->lastDate = $repo->getLastEndorsementDate();
        $this->setReturnValue($result);

    }
}