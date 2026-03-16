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

/******
 *	Service Contract
 *	==================
 *	Request:
 *		interface ISystemMessageRequest {
 *        interface ISystemMessageRequest {
 *           content : string;
 *           email: string;
 *           subject: string;
 *           toName?: string;
 *           fromMailbox?: string;
 *         }
 *		}
 *	Response:
 *		no data
 ********/
class SendSystemMessageCommand extends TServiceCommand
{
    protected function run()
    {
        $request = $this->getRequest();
        $messageText = $request->content ?? null;
        if (!$messageText) {
            $this->addErrorMessage("Message text is required");
            return;
        }
        $email = trim($request->email ?? '');
        if ($email == '') {
            $this->addErrorMessage("Email is required");
            return;
        }
        $subject = $request->subject ?? null;
        if (!$subject) {
            $this->addErrorMessage("Subject is required");
        }
        $toName = $request->toName ?? null;
        if (!$toName) {
            $this->addErrorMessage("To name is required");
            return;
        }
        $fromMailbox = $request->fromMailbox ?? TPostOffice::AdminMailbox;

        $emailManager = new QCallEmailManager();
        $emailManager->sendMessage($this->getMessages(),
             $email, $toName,  $subject, $messageText,  $fromMailbox);
    }
}