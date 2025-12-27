<?php

namespace PeanutTest\scripts;

use Application\quakercall\db\entity\QcallContact;
use Application\quakercall\db\entity\QcallEndorsement;
use Application\quakercall\db\entity\QcallGdcustomer;
use Application\quakercall\db\repository\QcallEndorsementsRepository;
//use DateTime;
//use mysql_xdevapi\Exception;
use PeanutTest\scripts\TestScript;
use Tops\sys\TStrings;

class ImportcontactsTest extends TestScript
{
    private $importDate;

    private function formatSort(
        // QcallContact
        QcallGdcustomer
                                $record)
    {
        $result = '';
        if (!empty($record->firstName)) {
            $result = $record->firstName;
        }
        if (!empty($record->lastName)) {
            if (!empty($result)) {
                $result = ','.$result;
            }
            $result =   $record->lastName.$result;
        }

        if (!empty($record->organization)) {
            if (!empty($result)) {
                $result = ','.$result;
            }
            $result =   $result.$record->organization;
        }
        return $result;
    }

    private function formatName($first,$last)
    {
        $result = '';
        if (!empty($first)) {
            $result = $first;
        }
        if (!empty($last)) {
            if (!empty($result)) {
                $result .= ' ';
            }
            $result .= $last;
        }
        return $result;
    }

    private function setFullNameAndSort(// QcallContact
                                        QcallGdcustomer
                                        $record)
    {
        $personName = $this->formatName($record->firstName, $record->lastName);
        if (empty($organizationName)) {
            $record->fullname = $personName;
        }
        else {
            if (empty($personName)) {
                $record->fullname = $organizationName;
            }
        }
        $record->sortcode = $this->formatSort($record);
    }

    /**
     * @param array $values
     * @param mixed $record
     * @return array
     */
    public function assignValues(array $values,
                                 QcallGdcustomer
                                 // QcallContact
                                 $record): void
    {
        //  0 Email,
        //  1 First name,
        //  2 Last name,
        //  3 Nickname,
        //  4 Job title,
        //  5 Organization,
        //  6 Phone,
        //  7 Address1,
        //  8 Address2,
        //  9 City,
        //  10 State,
        //  11 Country,
        //  12 Postal code,
        //  13 Created at,
        //  14 Last updated at,
        //  15 Last activity type,
        //  16 Last activity at,
        //  17 Customer,
        //  18 Member,
        //  19 Private page access,
        //  20 Subscriber,
        //  21 Blog subscriber,
        //  22 Suppressed,
        //  23 Suppression reason,
        //  24 Suppressed at,
        //  25 Tracking disabled

        $record->email = trim($values[0]);
        $record->firstName = trim($values[1]);
        $record->lastName = trim($values[2]);
        $record->organization = trim($values[5]);
        $this->setFullNameAndSort($record);
        $record->title = $values[4];
        $record->phone = $values[6];
        $record->address1 = $values[7];
        $record->address2 = $values[8];
        $record->city = $values[9];
        $record->state = $values[10];
        $record->country = $values[11];
        $record->postalcode = $values[12];
        if (!empty($values[13])) {
            $date = new \DateTime($values[13]);
            $record->postedDate = $date->format('Y-m-d H:i:s');
        }
        $record->subscribed = $values[20] == 'true' ? true : false;
        $record->suppressed = $values[22] == 'true' ? true : false;
        $record->importDate =  $this->importDate;
        $record->active = true;

        // customer list only
        $record->lastUpdate = $values[14];
        $record->lastActivity = $values[15];
        $record->lastActivityDate = $values[16];
    }

    public function execute()
    {

        $this->importDate = date('Y-m-d H:i:s');

        $colcount = 26;
        $entityClass = 'Application\quakercall\db\entity\QcallGdcustomer';
        $repoclass = 'Application\quakercall\db\repository\QcallGdcustomersRepository';
//        $entityClass = 'Application\quakercall\db\entity\QcallContact';
//        $repoclass = 'Application\quakercall\db\repository\QcallContactsRepository';
        $csv = 'D:\dev\quakercall\data\customers-2025-12-23.csv';
        $extraRows = 0;

        $ok = class_exists($entityClass);
        $repoexists = class_exists($repoclass);
        if (!$repoexists) {
            throw new \Exception("No repository found");
        }
        $repo = new $repoclass();
        // $record = $repo->findByEmail('fma@austinquakers.org');
        $lines = file($csv, FILE_IGNORE_NEW_LINES);
        $lineCount = count($lines);
        for ($i = 1; $i < $lineCount; $i++) {
            $line = $lines[$i];
            $values = str_getcsv($line);
            if (count($values) < $colcount) {
                $extraRows++;
            }
            while (count($values) < $colcount) {
                $i++;
                $line .= "\n". $lines[$i];
                $values = str_getcsv($line);
            }
            // $record = new QcallEndorsement();
            $record = new $entityClass();
            $this->assignValues($values, $record);
            // $record->active = 1;
            $repo->insert($record);
            $x=0;
        }
        $rows = $lineCount - $extraRows - 1;
        print "\nExtra rows: $extraRows\n";
        print("Processed $rows rows\n");
    }



}