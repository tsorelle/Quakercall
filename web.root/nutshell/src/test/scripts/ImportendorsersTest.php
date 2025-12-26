<?php

namespace PeanutTest\scripts;

use Application\quakercall\db\entity\QcallEndorsement;
use Application\quakercall\db\repository\QcallEndorsementsRepository;
//use DateTime;
//use mysql_xdevapi\Exception;
use PeanutTest\scripts\TestScript;
use Tops\sys\TCsvReader;

class ImportendorsersTest extends TestScript
{

    /**
     * @param array $values
     * @param mixed $record
     * @return array
     */
    public function assignValues(array $values, mixed $record)
    {
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
        $readOnly = true;
        // $readOnly = false;

        $ok = class_exists($entityClass);
        if (!$ok) {
            throw new \Exception("No entity found: $entityClass");
        }
        $ok = class_exists($repoclass);
        if (!$ok) {
            throw new \Exception("No repository found: $repoclass");
        }
        $repo = new $repoclass();
        $reader = new TCsvReader();
        $ok = $reader->openFile($csv);
        $this->assert("No file found: $$csv",$ok);
        if (!$ok) {
            return;
        }
        $processedCount = 0;
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