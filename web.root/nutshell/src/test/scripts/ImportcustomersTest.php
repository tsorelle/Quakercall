<?php

namespace PeanutTest\scripts;

use Application\quakercall\db\entity\QcallContact;
use Application\quakercall\db\entity\QcallEndorsement;
use Application\quakercall\db\entity\QcallGdcustomer;
use Application\quakercall\db\repository\QcallEndorsementsRepository;
//use DateTime;
//use mysql_xdevapi\Exception;
use Application\quakercall\db\repository\QcallGdcustomersRepository;
use PeanutTest\scripts\TestScript;
use Tops\db\TQuery;
use Tops\sys\TStrings;

class ImportcustomersTest extends TestScript
{
    private $importDate;

    private QcallGdcustomersRepository $customersRepository;
    private function formatSort( QcallGdcustomer  $record)
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
        $record->suppressedReason = $values[23] ?? '';
        $record->importDate =  $this->importDate;
        $record->active = true;

        // customer list only
        $record->lastUpdate = $values[14];
        $record->lastActivity = $values[15];
        $record->lastActivityDate = $values[16];
    }

    /**
     * @param string $csv
     * @return void
     */
    public function import(string $csv): void
    {
        $this->customersRepository->truncate();;
        $colcount = 26;
        $lines = file($csv, FILE_IGNORE_NEW_LINES);
        $lineCount = count($lines);
        $rows = 0;
        for ($i = 1; $i < $lineCount; $i++) {
            $line = $lines[$i];
            $values = str_getcsv($line);
            while (count($values) < $colcount) {
                $i++;
                $line .= "\n" . $lines[$i];
                $values = str_getcsv($line);
            }
            $rows++;
            $record = new QcallGdcustomer();
            $this->assignValues($values, $record);
            $this->customersRepository->insert($record);
        }
        print("Processed $rows rows\n");
    }

    public function execute()
    {
        $this->customersRepository = new QcallGdcustomersRepository();
        $this->importDate = date('Y-m-d H:i:s');
        $defaultDate = date('Y-m-d');
        $csv =  sprintf('D:\dev\quakercall\data\customers-%s.csv', $defaultDate);
        $this->import($csv);

        $csv =  sprintf('D:\dev\quakercall\data\suppressed-%s.csv', $defaultDate);
        $this->customersRepository->setTableName('qcall_gdcustomers_suppressed');
        $this->import($csv);
    }



}