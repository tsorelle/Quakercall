<?php

namespace PeanutTest\scripts;

use Application\quakercall\db\entity\QcallContact;
use Application\quakercall\db\entity\QcallEndorsement;
use Application\quakercall\db\entity\QcallGroupendorsement;
use Application\quakercall\db\entity\QcallMeeting;
use Application\quakercall\db\entity\QcallRegistration;
use Application\quakercall\db\repository\QcallEndorsementsRepository;
// use mysql_xdevapi\Exception;
use Application\quakercall\db\repository\QcallMeetingsRepository;
use Application\quakercall\db\repository\QcallContactsRepository;
use Application\quakercall\services\QcRegistration;
use PeanutTest\scripts\TestScript;
use Tops\sys\TCsvReader;

class ImportoldregsTest extends TestScript
{
    private QcallMeetingsRepository $meetingsRepo;
    private QcallContactsRepository $contactsRepo;
    private int $meetingId=0;
    private $meetingTheme;
    private array $meetingCodes;

    /**
     * @param array $values
     * @param mixed $record
     * @return array
     */
    public function assignValues(array $values, QcallRegistration $record): array
    {
        // 0 "Submission Date",
        // 1 "Register NOW!",
        // 2 Name,
        // 3 Email,
        // 4 "Phone Number",
        // 5 "State in which you reside",
        // 6  Affiliation

        if ($values[1] === $this->meetingTheme) {
            $record->meetingId = $this->meetingId;
        }
        else {

        }

        $date = new \DateTime($values[0]);
        $meetingDate = $date->format('Y-m-d');
        $submissionDate = $date->format('Y-m-d H:i:s' );
        if ($values[1] === $this->meetingTheme) {
            $meetingId = $this->meetingId;
        }
        else {
            $meeting = $this->meetingsRepo->findByTheme($values[1]);
            if (!$meeting) {
                $meeting = new QcallMeeting();
                $meeting->active = 1;
                $meeting->meetingDate = $meetingDate;
                $meeting->meetingTime = '7-8:30PM';
                $meeting->theme = $values[1];
                $year = $date->format('Y' );
                if (isset($this->meetingCodes[$year])) {
                    $this->meetingCodes[$year]++ ;
                }
                else {
                    $this->meetingCodes[$year] = 1;
                }

                $meeting->meetingCode = sprintf('%s-%02d', $year, $this->meetingCodes[$year]);
                $meetingId = $this->meetingsRepo->insert($meeting);
            }
            $this->meetingId = $meetingId;
            $this->meetingTheme = $meeting->theme;
        }
        $fullName = $values[2];
        $email = $values[3];
        $contact = $this->contactsRepo->findByEmail($email);
        if ($contact) {
            $contactId = $contact->id;
        }
        else {
            $contact = new QcallContact();
            $nameparts = explode(' ', $fullName);
            $contact->active = 1;
            $contact->lastName = array_pop($nameparts);
            $contact->firstName = implode(' ', $nameparts);
            $contact->fullname = $fullName;
            $contact->email = $email;
            $contact->phone = $values[4];
            $contact->sortcode = $contact->lastName . ','. $contact->firstName;
            $contact->subscribed=0;
            $contact->suppressed=0;
            $contactId = $this->contactsRepo->insert($contact);
        }
        $record->active = 1;
        $record->submissionDate = $submissionDate;
        $record->participant = $fullName;
        $record->contactId = $contactId;
        $record->meetingId = $this->meetingId;
        $record->location = $values[5];
        $record->religion = $values[6];
        // $record->organization

        // $record->submissionDate = $date->format('Y-m-d');
        // $record->
        return $values;
    }

    public function execute()
    {
        $this->meetingTheme = null;
        $this->meetingCodes = [];
        $readOnly = false;
        $entityClass = 'Application\quakercall\db\entity\QcallRegistration';
        $repoclass = 'Application\quakercall\db\repository\QcallRegistrationsRepository';
        $csv = 'D:\dev\quakercall\data\Register_for_the_National_Zoom_2025-12-13_08_57_50.csv';

        $this->meetingsRepo = new QcallMeetingsRepository();
        $this->contactsRepo = new QcallContactsRepository();
        if (!class_exists($entityClass)) {
            throw new \Exception("No entity class found: $entityClass ");
        }

        if (!class_exists($repoclass)) {
            throw new \Exception("No repository found: $repoclass");
        }
        $repo = new $repoclass();

        $lines = file($csv, FILE_IGNORE_NEW_LINES);
        $file = [];
        $n = -1;

        $cnt = count($lines);
        for($i=0;$i<$cnt;$i++) {
            $line = $lines[$i];
            if ($line == 'Other"') {
                $prev = $file[$n] ;
                if ($prev) {
                    $fix = $prev . '&' . 'Other';
                    $file[$n] = $fix;
                }
            } else {
                $n++;
                $file[] = $line;
            }
        }

        $reader = new TCsvReader();
        $ok = $reader->openFile($file);
        $this->assert("No file found: $$csv",$ok);
        if (!$ok) {
            return;
        }
        $test=[];
        $processedCount = 0;

        $this->meetingTheme = null;
        while($values = $reader->next()) {
            $record = new $entityClass();
            $this->assignValues($values, $record);
            $processedCount++;
            if (!$readOnly) {
                $repo->insert($record);
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
}