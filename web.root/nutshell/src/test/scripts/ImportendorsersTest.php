<?php

namespace PeanutTest\scripts;

use Application\quakercall\db\entity\QcallContact;
use Application\quakercall\db\entity\QcallEndorsement;
use Application\quakercall\db\repository\QcallContactsRepository;
use Application\quakercall\db\repository\QcallEndorsementsRepository;
//use DateTime;
use PeanutTest\scripts\TestScript;
use Tops\sys\TCsvReader;

class ImportendorsersTest extends TestScript
{
    private QcallEndorsementsRepository $repo;
    private QcallContactsRepository $contactsRepo;
    private array $newContacts;

    /**
     * @param array $values
     * @param mixed $record
     * @return array
     */
    public function assignValues(array $values, mixed $record)
    {
        // 0 - "Submission Date",
        // 1 - "Your Name",
        // 2 - "Your E-mail Address",
        // 3 - Address,
        // 4 - Comments,
        // 5 - "I am a...",
        // 6 - "How did you find us?"


        $date = new \DateTime($values[0]);
        $record->submissionDate = $date->format('Y-m-d');
        $record->name = $values[1];
        $record->email = $values[2];
        $record->address = $values[3];
        $record->comments = $values[4];
        $record->endorserType = $values[5];
        $record->howFound = $values[6];
        $record->active = 1;
    }

    public function execute()
    {
        $entityClass = 'Application\quakercall\db\entity\QcallEndorsement';
        $repoclass = 'Application\quakercall\db\repository\QcallEndorsementsRepository';
        $csv = 'D:\dev\quakercall\data\Individual_Endorsement2025-12-13_08_52_43.csv';
        // $readOnly = true;
        $readOnly = false;

        $ok = class_exists($entityClass);
        if (!$ok) {
            throw new \Exception("No entity found: $entityClass");
        }
        $ok = class_exists($repoclass);
        if (!$ok) {
            throw new \Exception("No repository found: $repoclass");
        }
        $this->repo = new $repoclass();
        $this->contactsRepo = new QcallContactsRepository();
        $reader = new TCsvReader();
        $ok = $reader->openFile($csv);
        $this->assert("No file found: $$csv",$ok);
        if (!$ok) {
            return;
        }
        $this->newContacts = [];
        $processedCount = 0;
        while($values = $reader->next()) {
            $record = new $entityClass();
            $this->assignValues($values, $record);
            $processedCount++;
            if (!$readOnly) {
                $this->writeData($record);
               // $repo->insert($record);
            }
        }

        print("Processed $processedCount\n");
        if ($reader->brokenLines) {
            print "\nBroken lines found: ".count($reader->brokenLines)."\n";
        }
        if (!empty($reader->warnings)) {
            print("Warnings;\n");
            foreach ($reader->warnings as $warning) {
                print($warning . "\n");
            }
            print("\n");
        }
    }

    private function writeData(QcallEndorsement $record)
    {
        $this->addContact($record);
        $this->repo->insert($record);
    }

    private function addContact(QcallEndorsement $record)
    {
        $contact = $this->contactsRepo->findByEmail($record->email);
        if ($contact) {
            if (empty($contact->address1)) {
                if ($contact->firstName.' '.$contact->lastName == $record->name) {
                    $contact->address1 = $record->address;
                    $this->contactsRepo->update($contact);
                }
            }
        }
        else {
            $contact = new QcallContact();
            $fullName = $record->name;
            $nameparts = explode(' ', $fullName);
            $contact->active = 1;
            $contact->lastName = array_pop($nameparts);
            $contact->firstName = implode(' ', $nameparts);
            $contact->fullname = $fullName;
            $contact->email = $record->email;
            $contact->address1 = $record->address;
            // $contact->phone = $record->;
            $contact->sortcode = $contact->lastName . ','. $contact->firstName;
            $contact->subscribed=0;
            $contact->suppressed=0;
            $this->contactsRepo->insert($contact);
            $this->newContacts[] = $contact->email;
        }
    }
}