<?php

namespace PeanutTest\scripts;


use Tops\db\TQuery;
use Tops\mail\TEmailValidator;
class EmailvalidationTest extends TestScript
{
    /**
     * @param array $values
     * @param mixed $record
     * @return array
     */

    private $domains =[];

    private function validate($email) : bool
    {
        $validation = TEmailValidator::Validate($email);
        if ($validation->isValid === false) {
            print $validation->error."\n";
            return false;
        }
        if ($this->domain_has_mx($email)) {
            return true;
        }
        print("Invalid domain: ".$email."\n");
        return false;
    }



    public function execute()
    {
//        $query = new TQuery();
//        $list = $query->getAll('SELECT distinct email FROM qcall_contacts where active=1');
//        $blocked = [];
        $failedCount = 0;
        $okCount = 0;

        $list = [
            'terry.sorelle@gmail.com',
            'Liz Yeats  <liz.yeats@outlook.com>',
            'invalidmail',
            'tsorelle@mail.cmxm',
            ];


        /*foreach ($list as $item) {
            $email = $item->email;
        */
        foreach ($list as $email) {
            $response = TEmailValidator::CheckEmailAddress($email);
            $ok = $response->valid ?? false;
            if (!$ok) {
                print "Failed: $response->email\n";
                $failedCount++;
            }
            else {
                $okCount++;
            }
            // $this->assert($ok,"$item->email failed\n");
        }

        // $blockedCount = count($blocked);
        print "\nOk: $okCount\n";
        print "\nFailed:$failedCount\n";
        print "Done\n";

/*
        $colcount = 4;
        $csv = 'D:\dev\quakercall\data\leftout-2026-01-27.csv';



        $lines = file($csv, FILE_IGNORE_NEW_LINES);
        $lineCount = count($lines);
        $rows =0;


        for ($i = 1; $i < $lineCount; $i++) {
            $line = $lines[$i];
            $values = str_getcsv($line);
            $email = $values[2] ?? null;
            if (!$email) {
                continue;
            }
            // print "$email\n";
            // Call the validation endpoint
            $response = $validator->validate($email);

// Convert to array
            $data = $response->getData();

            if ($data['is_valid']) {
                echo "Valid email\n";
            } else {
                echo "Invalid email: " . $data['reason'] . "\n";
            }


            // $record = new QcallEndorsement();
            $rows++;
        }
        print("Processed $rows rows\n");*/
    }



}