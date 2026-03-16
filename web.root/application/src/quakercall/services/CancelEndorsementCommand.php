<?php

namespace Application\quakercall\services;

use Application\quakercall\db\repository\QcallEndorsementsRepository;
use Tops\services\TServiceCommand;

class CancelEndorsementCommand extends TServiceCommand
{

    protected function run()
    {
        $id = $this->getRequest();
        if (empty($id)) {
            $this->addErrorMessage('Id not received');
            return;
        }
        $repository = new QcallEndorsementsRepository();
        $endorsement = $repository->cancelEndorsement($id);
        $repository =new QcallEndorsementsRepository();
        $endorsements = $repository->getEndorsementsForApproval();
        $msg = sprintf('Endorsement cancelled for %s', $endorsement->name);
        $this->addInfoMessage($msg);
        $this->setReturnValue($endorsements);
    }
}