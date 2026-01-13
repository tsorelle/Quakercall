<?php

namespace unit;

use Application\quakercall\db\repository\QcallEndorsementsRepository;
use PHPUnit\Framework\TestCase;

class QcallEndorsementsRepositoryTest extends TestCase
{

    public function testGetLastEndorsementDate()
    {
        $repo = new QcallEndorsementsRepository();
        $actual = $repo->getLastEndorsementDate();
        $this->assertTrue($actual >= '2026-01-12');
    }
    /*
        public function testGetAllByEmail()
        {

        }
    */
    public function testGetEndorsementList()
    {
        $repo = new QcallEndorsementsRepository();
        $actual = $repo->getEndorsementList();
        $count = count($actual);
        $this->assertIsArray($actual);
        $this->assertNotEmpty($actual);
    }

/*    public function testGetEndorsement()
    {

    }

    public function testGetEndorsementCount()
    {

    }*/
}
