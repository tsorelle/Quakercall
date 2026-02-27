<?php

namespace Application\quakercall\services;

use Application\quakercall\db\entity\QcallContact;
use Application\quakercall\db\entity\QcallEndorsement;
use Peanut\PeanutMailings\sys\MailTemplateManager;
use Tops\mail\TEmailAddress;
use Tops\mail\TEmailValidator;
use Tops\mail\TPostOffice;
use Tops\services\IMessageContainer;
use Tops\sys\TTemplateManager;

class QCallEmailManager
{

    public function getAcknowlegementText(QcallContact $contact) : string
    {
        $contact->normalizeStateAndCountry();
        $tokens = [];
        $tokens['email'] = $contact->email;
        $tokens['name']   = $contact->fullname;
        $tokens['phone']  = empty($contact->phone) ? '(not provided)' : $contact->phone;
        $tokens['city']   = $contact->city ?? '(not provided)';
        $tokens['state']  = $contact->state ?? '(not provided)';
        $tokens['country']= $contact->country ?? '';

        $templateManager = new MailTemplateManager();
        $template = $templateManager->getTemplateContent('endorsement-acknowledge.html');
        return TTemplateManager::ReplaceContentTokens($template, $tokens);
    }

    public function sendMessage(IMessageContainer $messageContainer, string $email, string $fullName,
                                string $subject, string $messageText) : bool
    {
        $validation = TEmailValidator::CheckEmailAddress($email);
        $ok = $validation->valid ?? false;
        if (!$ok) {
            $messageContainer->addErrorMessage("Email address '$email' is not valid");
            return false;
        }
        $recipient = new TEmailAddress($email, $fullName);
        $sendOk  = TPostOffice::SendMessageFromUs($recipient,'Thank you for your endorsement',$messageText);
        if (!$sendOk) {
            $messageContainer->addInfoMessage('Message failed to send.');
            return false;
        }

        $messageContainer->addInfoMessage('Message sent.');
        return true;
    }
}