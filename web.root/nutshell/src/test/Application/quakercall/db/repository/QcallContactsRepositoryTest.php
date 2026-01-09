<?php

namespace PeanutTest\unit;

use Application\quakercall\db\repository\QcallContactsRepository;
use PHPUnit\Framework\TestCase;

class QcallContactsRepositoryTest extends TestCase
{

    public function testGetEmail() {
        $repository = new QcallContactsRepository();
        $testAddress = 'terry.sorelle@outlook.com';
        $actual = $repository->getAllByEmail($testAddress);
        $this->assertIsArray($actual);
        $actual = $repository->getAllByEmail($testAddress,false);
        $this->assertIsArray($actual);
    }

    public function testFindOrganizationEndorser()
    {
        $repository = new QcallContactsRepository();
        $test= 'Newtown Friends Meeting';
        $actual = $repository->findOrganizationEndorser($test);
        $this->assertNotEmpty($actual);
        $actual = $repository->findOrganizationEndorser('No such group');
        $this->assertEmpty($actual);

    }
}
