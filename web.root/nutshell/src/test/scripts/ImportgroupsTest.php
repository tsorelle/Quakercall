<?php

namespace PeanutTest\scripts;

use Application\quakercall\db\entity\QcallContact;
use Application\quakercall\db\entity\QcallContactUpdate;
use Application\quakercall\db\entity\QcallEndorsement;
use Application\quakercall\db\entity\QcallGroupendorsement;
use Application\quakercall\db\repository\QcallContactsRepository;
use Application\quakercall\db\repository\QcallContactUpdatesRepository;
use Application\quakercall\db\repository\QcallEndorsementsRepository;
// use mysql_xdevapi\Exception;
use Application\quakercall\db\repository\QcallGroupendorsementsRepository;
use Peanut\contacts\db\model\repository\ContactsRepository;
use PeanutTest\scripts\TestScript;
use Tops\sys\TCsvReader;

class ImportgroupsTest extends TestScript
{
    private $updated = [];
    private $failed = [];

    private QcallGroupendorsementsRepository $endorsementsRepository;
    private QcallContactUpdatesRepository $updatesRepository;
    private QcallContactsRepository $contactsRepository;
    private function getOrgType($description)
    {
        switch ($description) {
            case 'Quaker Monthly, Quarterly or Yearly Meeting' :
                return 'meeting';
            case 'Quaker Organization'                         :
                return 'org';
            default                                       :
                return 'other';
        }
    }

    private function concatName($request)
    {
        $result = '';
        if (!empty($request->firstName)) {
            $result .= $request->firstName;
        }
        if (!empty($request->lastName)) {
            if (!empty($result)) {
                $result .= ' ';
            }
            $result .= $request->lastName;
        }
        return $result;
    }

    private function getSortCode($request)
    {
        $result = '';
        if (!empty($request->organization)) {
            $result .= $request->organization;
        }

        if (!empty($request->lastName)) {
            if (!empty($result)) {
                $result .= ',';
            }
            $result .= $request->lastName;
        }
        if (!empty($request->firstName)) {
            if (!empty($result)) {
                $result .= ',';
            }
            $result .= $request->firstName;
        }

        return strtolower($result);
    }


    private function getEndorsement($submissionDate,$name) {
        // $sql =    'SELECT id FROM qcall_groupendorsements WHERE submissionDate = ? AND `name` =  ?';
        $sql =    'submissionDate = ? AND `name` =  ?';
        $result = $this->endorsementsRepository->getSingleEntity($sql,[$submissionDate,$name]);
        return $result;
    }
    public function assignContact(array $values): void
    {
        $readOnly = false;
        try {

            $date = new \DateTime($values[0]);
        }
        catch (\Exception $e) {
            return;
        }
        $submissionDate = $date->format('Y-m-d');
        $orgName = $values[2];
        $endorsement = $this->getEndorsement($submissionDate,$orgName);
        if ($endorsement) {

            $contact = new QcallContactUpdate();
            // $record->organizationType = $this->getOrgType($values[1]); //"Type of organization",
            $name = $values[2]; //"Organization name:",

            $contact->endorsementId = $endorsement->id;
            $contact->organization = $orgName;
            $contact->address1 =         $values[3];
            $contact->address2 =         $values[4];
            $contact->city =             $values[5];
            $contact->state =            $values[6];
            $contact->postalcode =       $values[7];

            $contact->firstName =        $values[8];
            $contact->lastName =         $values[9];
            $contact->phone =            $values[10];
            $contact->email =            $values[11];

            $contact->fullname =         $this->concatName($contact);
            $contact->sortcode =         $this->getSortCode($contact);
            $contact->source =           'org-endorsement';
            // $contact->active = 1;
            $contact->subscribed = 0;
            $contact->bounced = 0;
            $contact->endorsementId = $endorsement->id;

            $this->updated[] = $orgName;
            if (!$readOnly) {
                $contactId = $this->updatesRepository->insert($contact);
                // $endorsement->contactId = $contactId;
                // $this->endorsementsRepository->update($endorsement);
            }
        }
        else {
            $this->failed[] = $orgName;
        }



/*        $record->contactName = $values[4]; //"Authorized Contact",
        $record->phone = $values[10]; //"Phone Number",
        $record->email = $values[6]; // Email,
        if (!empty($values[7])) {
            $record->attachment = $values[6]; //"Attach Copy of Authorizing Minute, if required."
        }
        $record->active = 1;*/

    }


    /**
     * @param array $values
     * @param mixed $record
     * @return array
     */
    public function assignValues(array $values, QcallGroupendorsement $record): array
    {
        $contact = new \stdClass();
        $date = new \DateTime($values[0]);
        $record->submissionDate = $date->format('Y-m-d');
        $record->organizationType = $this->getOrgType($values[1]); //"Type of organization",
        $record->name = $values[2]; //"Organization name:",


        $contact->address1 =         $values[3];
        $contact->address2 =         $values[4];
        $contact->city =             $values[5];
        $contact->state =            $values[6];
        $contact->postalcode =       $values[7];

        $contact->firstName =        $values[8];
        $contact->lastName =         $values[9];
        $contact->phone =            $values[10];
        $contact->email =            $values[11];

        $contact->fullname =         $this->concatName($contact);
        $contact->sortcode =         $this->getSortCode($contact);
        $contact->source =           'org-endorsement';


        $record->contactName = $values[4]; //"Authorized Contact",
        $record->phone = $values[10]; //"Phone Number",
        $record->email = $values[6]; // Email,
        if (!empty($values[7])) {
            $record->attachment = $values[6]; //"Attach Copy of Authorizing Minute, if required."
        }
        $record->active = 1;

        return $values;
    }

    public function execute()
    {
        $this->endorsementsRepository = new QcallGroupendorsementsRepository();
        $this->updatesRepository = new QcallContactUpdatesRepository();
        // $colcount = ;
/*        $entityClass = 'Application\quakercall\db\entity\QcallGroupendorsement';
        $repoclass = 'Application\quakercall\db\repository\QcallGroupendorsementsRepository';*/

        // $csv = 'D:\dev\quakercall\data\Meeting_Organization_Endorsemen2026-01-05_06_59_59.csv';
        $csv = 'D:\dev\quakercall\data\Meeting_Organization_Endorsemeny_cleaned.csv';

        $reader = new TCsvReader();
        $ok = $reader->openFile($csv);
        $this->assert("No file found: $$csv",$ok);
        if (!$ok) {
            return;
        }
$processedCount = 0;
        while($values = $reader->next()) {
            $this->assignContact($values);
            $processedCount++;
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

        $failedCount = count($this->failed);
        $updatedCount = count($this->updated);
        $processedCount = $failedCount + $updatedCount;
        print "Processed: $processedCount\n";
        print "Updated: $updatedCount\n";
        print "Failed: $failedCount\n";
        foreach ($this->failed as $orgName) {
            print "$orgName\n";
        }

    }
}