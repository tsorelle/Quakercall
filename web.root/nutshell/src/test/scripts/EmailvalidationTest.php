<?php

namespace PeanutTest\scripts;


use Tops\db\TQuery;

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
        return $this->domain_has_mx($email);
    }

    private function domain_has_mx(string $email): bool
    {
        $domain = strtolower( substr(strrchr($email, '@'), 1));
        if( in_array($domain, $this->domains) ){
            return true;
        }
        if (checkdnsrr($domain, 'MX')) {
            $this->domains[] = $domain;
            return true;
        }
        return false;
    }

    private function smtp_mailbox_check(string $email): int
    {
        list($user, $domain) = explode('@', $email);

        // Get MX records
        if (!getmxrr($domain, $mxhosts)) {
            return 0;
        }

        $mx = $mxhosts[0];

        // Connect to mail server
        $connection = @fsockopen($mx, 25, $errno, $errstr, 5);
        if (!$connection) {
            return 0;
        }

        stream_set_timeout($connection, 5);

        $read = function() use ($connection) {
            return fgets($connection, 1024);
        };

        $write = function($cmd) use ($connection) {
            fputs($connection, $cmd . "\r\n");
        };

        $read(); // banner
        $write("HELO example.com");
        $read();
        $write("MAIL FROM:<check@example.com>");
        $read();
        $write("RCPT TO:<$email>");
        $response = $read();
        $write("QUIT");

        fclose($connection);
        if (strpos($response, '250') === 0) {
            return 1;
        }
        else if (stripos($response, 'spam') !== 0) {
            return -1;
        }
/*        if ($ok !== 1) {
           // print "$email failed: $response\n";
        }*/
        return 0;
    }

    public function execute()
    {
        $query = new TQuery();
        $list = $query->getAll('SELECT email FROM qcall_gdcustomers_leftout');
        $blocked = [];
        $failed = [];
        $okCount = 0;

        foreach ($list as $item) {
            $ok = $this->validate($item->email);
            if (!$ok) {
                $failed[] = $item->email;
            }
            else {
                $okCount++;
            }
            // $this->assert($ok,"$item->email failed\n");
        }

        $blockedCount = count($blocked);
        $failedCount = count($failed);
        print "\nOk: $okCount\n";
        print "\nFailed: ($failedCount\n";
        foreach ($failed as $email) {
            print "$email\n";
        }

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