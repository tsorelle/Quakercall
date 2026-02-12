<?php

namespace Application\quakercall\services;

use Application\quakercall\db\repository\QcallEndorsementsRepository;
use Tops\services\TServiceCommand;

class ApproveEndorsementCommand extends TServiceCommand
{

    protected function run()
    {
        $id = $this->getRequest();
        if (empty($id)) {
            $this->addErrorMessage('Id not received');
            return;
        }
        $repository = new QcallEndorsementsRepository();
        $result = $repository->approve($id);
        if (!$result) {
            $this->addErrorMessage('Endorsement not found for id ='.$id);
        }
        $this->addInfoMessage("Endorsement approved for $result->name");
        $response = $repository->getEndorsementsForApproval();
        $this->setReturnValue($response);

    }
}