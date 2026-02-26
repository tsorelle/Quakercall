<?php

namespace PeanutTest\scripts;

use Application\quakercall\db\entity\QcallContact;
use Application\quakercall\db\repository\QcallContactsRepository;
use PeanutTest\scripts\TestScript;
use Tops\mail\TEmailValidator;

class NormalizeContactsTest extends TestScript
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
            if (empty($contact->state)) {
                continue;
            }
            $contact->normalizeStateAndCountry();
            $repo->update($contact);
            $changedCount++;
        }
        print "Changed contacts count: $changedCount\n";
        print "Done\n";
    }
}