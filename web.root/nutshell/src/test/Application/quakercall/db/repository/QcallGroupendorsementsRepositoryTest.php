<?php

namespace PeanutTest\Application\quakercall\db\repository;

use Application\quakercall\db\repository\QcallGroupendorsementsRepository;
use PHPUnit\Framework\TestCase;

class QcallGroupendorsementsRepositoryTest extends TestCase
{

    public function testGetEndorsement()
    {

    }

    public function testGetGroupendorsementList()
    {
        $repo = new QcallGroupendorsementsRepository();
        $list = $repo->getGroupendorsementList();
        $this->assertNotEmpty($list);

    }

    public function testGetEndorsementCount()
    {

    }

    public function testGetGroupEndorsementsForApproval()
    {
        $repo = new QcallGroupendorsementsRepository();
        $list = $repo->getGroupEndorsementsForApproval();
        $this->assertNotEmpty($list);
    }

    public function testGetLastEndorsementDate()
    {

    }
}
