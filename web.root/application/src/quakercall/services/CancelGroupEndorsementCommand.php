<?php

namespace Application\quakercall\services;

use Application\quakercall\db\repository\QcallGroupendorsementsRepository;
use Tops\services\TServiceCommand;

class CancelGroupEndorsementCommand extends TServiceCommand
{

    protected function run()
    {
        $id = $this->getRequest();
        if (empty($id)) {
            $this->addErrorMessage('Id not received');
            return;
        }
        $repository = new QcallGroupendorsementsRepository();
        $endorsement = $repository->cancelEndorsement($id);
        $repository = new QcallGroupendorsementsRepository();
        $groupEndorsements = $repository->getGroupEndorsementsForApproval();
        $msg = sprintf('Endorsement cancelled for %s', $endorsement->organizationName);
        $this->addInfoMessage($msg);
        $this->setReturnValue($groupEndorsements);

    }
}