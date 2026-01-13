<?php

namespace Application\quakercall\services;

use Application\quakercall\db\repository\QcallEndorsementsRepository;
use Tops\services\TServiceCommand;

class GetIndividualEndorsersListCommand extends TServiceCommand
{

    protected function run()
    {
        $repo = new QcallEndorsementsRepository();
        $result = new \stdClass();
        $result->list = $repo->getEndorsementList();
        $result->count = count($result->list);
        $result->lastDate = $repo->getLastEndorsementDate();
        $this->setReturnValue($result);
    }
}