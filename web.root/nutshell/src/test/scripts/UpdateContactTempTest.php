<?php

namespace PeanutTest\scripts;

use Application\quakercall\db\entity\QcallContact;
use Application\quakercall\db\repository\QcallContactsRepository;
use Application\quakercall\db\repository\QcallGroupendorsementsRepository;
use Tops\db\TQuery;

class UpdateContactTempTest extends TestScript

{
    public function getAllUpdates()
    {
        $query = new TQuery();
        $sql = 'select * from qcall_contact_updates';
        $stmt = $query->executeStatement($sql);
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
    public function execute()
    {
        ini_set('display_errors', 1);
        error_reporting(E_ALL);
        print("Processing updates\n");

        $updates = $this->getAllUpdates();
        print('debug'."\n");

        foreach ($updates as $update) {
            print($update->fullname . "\n");
            $contact = new QcallContact();
            $contact->assignFromObject($update);
            $contact->active = 1;
            $contactsRepo = new QcallContactsRepository();
            $endorsementsRepo =new QcallGroupendorsementsRepository();
            $id = $contactsRepo->insert($contact);
            $endorsement =$endorsementsRepo->get($update->endorsementId);
            if ( $endorsement ) {
                $endorsement->contactId = $id;
                $endorsementsRepo->update($endorsement);
            }
            else {
                continue;
            }
        }
    }
}