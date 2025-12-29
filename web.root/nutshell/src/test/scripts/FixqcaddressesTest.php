<?php

namespace PeanutTest\scripts;

use Application\quakercall\db\entity\QcallContact;
use Application\quakercall\db\entity\QcallEndorsement;
use Application\quakercall\db\repository\QcallContactsRepository;
use Application\quakercall\db\repository\QcallEndorsementsRepository;
use Application\quakercall\db\repository\QcallMeetingsRepository;
use Tops\sys\TCsvReader;

class FixqcaddressesTest extends TestScript
{
    private QcallContactsRepository $contactsRepo;
    private QcallEndorsementsRepository $endorsementsRepo;
    private $contactsUpdated;
    private $endorsementsUpdated;

    private $notFound;
    private $skippedEndorsements;

    public function execute()
    {
        $csv = 'D:\dev\quakercall\data\google\Individual Endorsement - Form Responses.csv';
        $this->contactsRepo = new QcallContactsRepository();
        $this->endorsementsRepo = new QcallEndorsementsRepository();
        $this->contactsUpdated = 0;
        $this->endorsementsUpdated = 0;
        $this->notFound = [];
        $this->skippedEndorsements = [];
        $reader = new TCsvReader();
        $ok = $reader->openFile($csv);
        $this->assert("No file found: $$csv",$ok);
        if (!$ok) {
            return;
        }
        $processedCount = 0;
        while($values = $reader->next()) {
            $processedCount++;
            $this->fixAddress($values);
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
        if (!empty($this->notFound)) {
            print('=================================');
            print("No contacts found:\n");
            foreach ($this->notFound as $contact) {
                print($contact . ",\n");
            }
        }
        if (!empty($this->skippedEndorsements)) {
            print('=================================');
            print("Skipped endorsements:\n");
            foreach ($this->skippedEndorsements as $endorsement) {
                print($endorsement . ",\n");
            }
        }
    }

    private function  getFullName($firstName, $lastName)
    {
        $result = trim($firstName);
        $last = trim($lastName);
        if ($last) {
            if ($result) {
                $result .= " ";
            }
            $result .= $last;
        }
        return $result;
    }

    private function fixAddress(array $values)
    {
        // 0 - Submission Date,
        // 1 - First Name,
        // 2 - Last Name,
        // 3 - Your E-mail Address,
        // 4 - Street Address,
        // 5 - Street Address Line 2,
        // 6 - City,
        // 7 - State / Province,
        // 8 - Postal / Zip Code,
        // 9 - Country,
        // 10- Comments,
        // 11 - I am a...,
        // 12 - How did you find us?,
        // 13 - Submission ID

        $readonly = false;
        $email = $values[3];
        $fullName = strtolower( $this->getFullName($values[1], $values[2]));
        /** @var QcallContact[] $contacts */
        $endorsements = $this->endorsementsRepo->getAllByEmail($email);
        if (empty($endorsements)) {
            // create enorsements assign contact
            $endorsement = new QcallEndorsement();
            $endorsement->submissionDate = $values[0];
            $endorsement->comments = $values[10];
            $endorsement->endorserType = $values[11];
            $endorsement->howFound = $values[12];
            $endorsement->submissionId = $values[13];
            $endorsement->active = 1;
            $contact = $this->contactsRepo->findByEmail($email);
            if ($contact) {
                $endorsement->contactId = $contact->id;
            }
            else {
                $contact = new QcallContact();
                $contact->firstName = $values[1];
                $contact->lastName = $values[2];
                $contact->fullname = $values[1].' '.$values[2];
                $contact->email = $email;
                $contact->address1 = $values[4];;
                $contact->address2 = $values[5];
                $contact->city = $values[6];;
                $contact->state = $values[7];
                $contact->postalcode = $values[8];
                $contact->country = $values[9];
                $contact->sortcode = $values[2].', '.$values[1];
                $contact->source = 'endorsements';
                $contact->subscribed = 1;
                $contact->suppressed = 0;
                $contact->active = 1;

                if (!$readonly) {
                    $endorsement->contactId = $this->contactsRepo->insert($contact);
                    $this->endorsementsRepo->insert($endorsement);
                }
            }
        }

        // update contact address
        $contacts = $this->contactsRepo->getAllByEmail($email);
        if ($contacts) {
            foreach ($contacts as $contact) {

                $contact->address1 = $values[4];;
                $contact->address2 = $values[5];
                $contact->city = $values[6];;
                $contact->state = $values[7];
                $contact->postalcode = $values[8];
                $contact->country = $values[9];

                if (!$readonly) {
                    $this->contactsRepo->update($contact);
                }

                $this->contactsUpdated++;

                /** @var QcallEndorsement[] $endorsements */
                $endorsements = $this->endorsementsRepo->getAllByEmail($email);
                $skipped = true;
                if ($endorsements) {
                    foreach ($endorsements as $endorsement) {
                        if (strtolower($endorsement->name) == $fullName) {
                            $skipped = false;
                            $this->endorsementsUpdated++;
                            if (!$readonly) {
                                $endorsement->contactId = $contact->id;
                                $this->endorsementsRepo->update($endorsement);
                            }
                        }
                    }
                }
                if ($skipped) {
                    $txt = $fullName.' '.$email;

                    if (!in_array($txt, $this->skippedEndorsements)) {
                        $this->skippedEndorsements[] = $txt;
                    }
                }
            }
        }
        else {
            $contact = new QcallContact();
            $contact->firstName = $values[1];
            $contact->lastName = $values[2];
            $contact->fullname = $values[1].' '.$values[2];
            $contact->email = $email;
            $contact->address1 = $values[4];;
            $contact->address2 = $values[5];
            $contact->city = $values[6];;
            $contact->state = $values[7];
            $contact->postalcode = $values[8];
            $contact->country = $values[9];
            $contact->sortcode = $values[2].','.$values[1];
            $contact->source = 'endorsements';
            $contact->subscribed = 1;
            $contact->active = 1;
            $contact->suppressed = 0;
            if (!$readonly) {
                $this->contactsRepo->insert($contact);
            }
            if (!in_array($email, $this->notFound)) {
                $this->notFound[] = $email;
            }
        }
    }
}