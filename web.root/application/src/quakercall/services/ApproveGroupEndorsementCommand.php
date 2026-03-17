<?php

namespace Application\quakercall\services;

use Application\quakercall\db\entity\QcallGroupendorsement;
use Application\quakercall\db\repository\QcallGroupendorsementsRepository;
use Tops\services\TServiceCommand;

class ApproveGroupEndorsementCommand extends TServiceCommand
{

    protected function run()
    {
        $id = $this->getRequest();
        if (empty($id)) {
            $this->addErrorMessage('Id not received');
            return;
        }
        $repository = new QcallGroupendorsementsRepository();
        /**
         * @var QcallGroupendorsement $endorsement
         */
        $endorsement = $repository->approve($id);
        if (!$endorsement) {
            $this->addErrorMessage('Endorsement not found for id ='.$id);
            return;
        }
        $response = new \stdClass();
        $response->endorsements = $repository->getGroupEndorsementsForApproval();
        $this->addInfoMessage("Endorsement approved for $endorsement->organizationName");
        $emailManager = new QCallEmailManager();
        $response->messageText = $emailManager->getGroupAcknowlegementText($endorsement);
        $this->setReturnValue($response);
    }

}