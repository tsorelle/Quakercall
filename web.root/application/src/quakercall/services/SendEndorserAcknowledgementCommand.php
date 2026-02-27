<?php

namespace Application\quakercall\services;

use Application\quakercall\db\entity\QcallContact;
use Application\quakercall\db\repository\QcallContactsRepository;
use Peanut\PeanutMailings\db\EmailManager;
use Tops\mail\TEmailAddress;
use Tops\mail\TEMailMessage;
use Tops\mail\TEmailValidator;
use Tops\mail\TPostOffice;
use Tops\services\TServiceCommand;

class SendEndorserAcknowledgementCommand extends TServiceCommand
{

    protected function run()
    {
        $request = $this->getRequest();
        $id = $request->contactId ?? null;
        if (!$id) {
            $this->addErrorMessage("Contact ID is required");
            return;
        }
        $messageText = $request->messageText ?? null;
        if (!$messageText) {
            $this->addErrorMessage("Message text is required");
            return;
        }
        /**
         * @var QcallContact $contact
         */
        $contact = (new QcallContactsRepository())->get($id);
        if (!$contact) {
            $this->addErrorMessage("Contact not found");
            return;
        }

        $emailManager = new QCallEmailManager();

        $email = trim( $contact->email ?? '');
        if (!$email) {
            $this->addErrorMessage("Email is required");
            return;
        }
        $fullName = trim($contact->fullName ?? '');

        $sendResult = $emailManager->sendMessage($this->getMessages(),$email,$fullName,
        'Thank you for your endorsement',$messageText);
    }
}