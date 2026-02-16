<?php

namespace PeanutTest\scripts;

use Application\quakercall\db\entity\QcallContact;
use Application\quakercall\db\repository\QcallContactsRepository;
use PeanutTest\scripts\TestScript;
use Tops\mail\TEmailValidator;

class VerifycontactemailsTest extends TestScript
{

    public function execute()
    {
        $repo = new QcallContactsRepository();
        $contacts = $repo->getAll();
        $changedCount = 0;
        /**
         * @var $contact QcallContact
         */
        foreach ( $contacts  as  $contact) {
            if ($contact->email !== null && trim($contact->email) !== '') {
                $verify = TEmailValidator::CheckEmailAddress($contact->email);
                $changed = false;
                if ($verify->valid) {
                    if ($verify->changed) {
                        $changed = true;
                        $contact->email = $verify->email;
                    }
                    if ($changed && $contact->bounced == 1) {
                        $contact->bounced = 0;
                    }
                }
                else {
                    if ($contact->bounced == null || $contact->bounced == 0) {
                        $changed = true;
                        $contact->bounced = 1;
                    }
                }
                if ($changed) {
                    $changedCount++;
                    print "Updated contact $contact->fullname: $contact->email\n";
                    $repo->update($contact);
                }
            }
        }
        print "Changed contacts count: $changedCount\n";
        print "Done\n";
    }
}