<?php

namespace PeanutTest\scripts;

use Application\quakercall\db\entity\QcallEndorsement;
use Application\quakercall\db\entity\QcallGroupendorsement;
use Application\quakercall\db\repository\QcallEndorsementsRepository;
// use mysql_xdevapi\Exception;
use PeanutTest\scripts\TestScript;

class ImportgroupsTest extends TestScript
{

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
    /**
     * @param array $values
     * @param mixed $record
     * @return array
     */
    public function assignValues(array $values, QcallGroupendorsement $record): array
    {
        $date = new \DateTime($values[0]);
        $record->submissionDate = $date->format('Y-m-d');
        $record->organizationType = $this->getOrgType($values[1]); //"Type of organization",
        $record->name = $values[2]; //"Organization name:",
        $record->address = $values[3]; //Address,
        $record->contactName = $values[4]; //"Authorized Contact",
        $record->phone = $values[5]; //"Phone Number",
        $record->email = $values[6]; // Email,
        if (!empty($values[7])) {
            $record->attachment = $values[6]; //"Attach Copy of Authorizing Minute, if required."
        }
        $record->active = 1;

        return $values;
    }

    public function execute()
    {
        $colcount = 6;
        $entityClass = 'Application\quakercall\db\entity\QcallGroupendorsement';
        $repoclass = 'Application\quakercall\db\repository\QcallGroupendorsementsRepository';
        $csv = 'D:\dev\quakercall\data\Meeting_Organization_Endorsemen2025-12-13_08_54_36.csv';
        $extraRows = 0;

        $ok = class_exists($entityClass);
        $repoexists = class_exists($repoclass);
        if (!$repoexists) {
            throw new \Exception("No repository found");
        }
        $repo = new $repoclass();
        $lines = file($csv, FILE_IGNORE_NEW_LINES);
        $lineCount = count($lines);
        for ($i = 1; $i < $lineCount; $i++) { // skips header row
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
            $values = $this->assignValues($values, $record);
            $record->active = 1;
            $repo->insert($record);
            $x=0;
        }
        $rows = $lineCount - $extraRows - 1;
        print "\nExtra rows: $extraRows\n";
        print("Processed $rows rows\n");
    }
}