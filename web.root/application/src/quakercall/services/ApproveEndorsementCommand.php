<?php

namespace Application\quakercall\services;

use Application\quakercall\db\entity\QcallContact;
use Application\quakercall\db\repository\QcallContactsRepository;
use Application\quakercall\db\repository\QcallEndorsementsRepository;
use Tops\services\TServiceCommand;
use Application\quakercall\db\entity\QcallEndorsement;

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
        /**
         * @var QcallEndorsement $endorsement
         */
        $endorsement = $repository->approve($id);
        if (!$endorsement) {
            $this->addErrorMessage('Endorsement not found for id ='.$id);
            return;
        }
        $response = new \stdClass();
        $response->endorsements = $repository->getEndorsementsForApproval();
        $this->addInfoMessage("Endorsement approved for $endorsement->name");

        $sendMessage = true;
        $contactsRepository = new QcallContactsRepository();
        /**
         * @var QcallContact $contact
         */
        $contact = $contactsRepository->get($endorsement->contactId);
        if ($contact) {
            $email = $contact->email ?? null;
            if (empty($email)) {
                $this->addErrorMessage('Cannot compose message: contact email not found.');
                $sendMessage = false;
            }
        }
        else {
            $this->addInfoMessage('Cannot compose message: contact not found.');
            $sendMessage = false;
        }
        if ($sendMessage) {
            $emailManager = new QCallEmailManager();
            $response->messageText = $emailManager->getAcknowlegementText($contact);
        }
        $this->setReturnValue($response);
    }
}