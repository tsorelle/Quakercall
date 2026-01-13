<?php

namespace PeanutTest\scripts;

use Application\quakercall\db\entity\QcallContact;
use Application\quakercall\db\entity\QcallEndorsement;
use Application\quakercall\db\entity\QcallPersonalendorsement;
use Application\quakercall\db\repository\QcallContactsRepository;
use Application\quakercall\db\repository\QcallEndorsementsRepository;
//use DateTime;
use Application\quakercall\db\repository\QcallPersonalendorsementsRepository;
use PeanutTest\scripts\TestScript;
use Tops\db\TQuery;
use Tops\sys\TCsvReader;

class ImportendorsersTest extends TestScript
{
    private QcallEndorsementsRepository $endorsementsRepo;
    private QcallPersonalendorsementsRepository $inputsRepo;
    private QcallContactsRepository $contactsRepo;

    private TQuery $query;
    private array $newContacts;



    private function convertReligion($religion)
    {
        switch ($religion) {
            case 'Friend (Quaker)': return 'Quaker';
            case 'Non-Friend' : return 'Other';
        }
        return $religion;
    }

    private function logMessage($importId, $message) {
        $sql = 'INSERT INTO `qcall_endorsement_importlog` (`dateStamp`,  `importId`,  `message`) VALUES ( ?,?,?)';
        $date = date('Y-m-d H:i:s');
        $this->query->executeStatement($sql, [$date, $importId, $message]);
    }
    public function execute()
    {
        $readOnly = false;
        $processedCount = 0;
        $this->query = new TQuery();
        $this->inputsRepo = new QcallPersonalendorsementsRepository();
        $this->endorsementsRepo = new QcallEndorsementsRepository();
        $this->contactsRepo = new QcallContactsRepository();
        $inputRecords = $this->inputsRepo->getAll(true);
        $notfound = [];
        $resolved = [];
        $endorsementMatchProblems = [];
        /** @var QcallPersonalendorsement $record */
        foreach ($inputRecords as $record) {
            $fullname = $record->firstName . ' ' . $record->lastName;
            /** @var QcallEndorsement $endorsement */
            $endorsement = $this->endorsementsRepo->getSingleEntity('email=? and name=?', [$record->email, $fullname]);
/*            if (!$endorsement) {
                $endorsements = $this->endorsementsRepo->getEntityCollection('email=?', [$record->email]);
                if (!empty($endorsements)) {
                    $count = count($endorsements);
                    if ($count== 1) {
                        $endorsement = $endorsements[0];
                    }
                    else if (count($endorsements) > 1) {
                        $endorsementMatchProblems[] = $record->id;
                        continue;
                    }
                }
            }*/
            $contact = null;
            if ($endorsement) {
                $contact = $this->contactsRepo->get($endorsement->contactId);
            } else {
                $endorsement = new QcallEndorsement();
            }
            if (empty($contact)) {
                $contact = $this->contactsRepo->getSingleEntity('firstName=? and lastName=? and email=?',
                    [$record->firstName, $record->lastName, $record->email]);
                if (empty($contact)) {
                    $contact = new QcallContact();
                    $contact->firstName = $record->firstName;
                    $contact->lastName = $record->lastName;
                    $contact->email = $record->email;
                }
            }
            $contact->address1 = $record->address1;
            $contact->address2 = $record->address2;
            $contact->city = $record->city;
            $contact->state = $record->state;
            $contact->postalcode = $record->postalcode;
            $contact->country = $record->country;

            $endorsement->submissionId = $record->submissionId;
            $endorsement->religion = $this->convertReligion($record->religion);
            $endorsement->howFound = $record->howFound;
            $endorsement->comments = $record->comments;
            $endorsement->email = $record->email;
            $endorsement->name = $fullname;
            if (empty($contact->id)) {
                $others = $this->contactsRepo->getSingleEntity('email=? and subscribed=1', [$record->email]);
                $subscribe = empty($others) ? 1 : 0;
                $contact->firstName = $record->firstName;
                $contact->lastName = $record->lastName;
                $contact->fullname = $record->firstName.' '.$record->lastName;
                $contact->sortcode = strtolower($record->lastName.','.$record->firstName);
                $contact->active = 1;
                $contact->bounced = 0;
                $contact->subscribed = $subscribe;
                $contact->source = 'endorsements';
                $contact->importDate = date('Y-m-d');
                $endorsement->contactId = $this->contactsRepo->insert($contact);
            } else {
                $endorsement->contactId = $contact->id;
            }
            if (empty($endorsement->id)) {
                $endorsement->active = 1;
                $this->endorsementsRepo->insert($endorsement);
            } else {
                $this->endorsementsRepo->update($endorsement);
            }
        }

        print("Processed $processedCount\n");
    }
}