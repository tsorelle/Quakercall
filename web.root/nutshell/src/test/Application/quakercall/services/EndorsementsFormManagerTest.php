<?php

namespace PeanutTest\unit;

use Application\quakercall\services\EndorsementsFormManager;
use PHPUnit\Framework\TestCase;

class EndorsementsFormManagerTest extends TestCase
{

    public function testFindContact()
    {
        $manager = new EndorsementsFormManager();
        $actual = $manager->findContact( 'e@mail.com','Not','Here');
        $this->assertEmpty($actual);

        $actual = $manager->findContact( 'terry.sorelle@outlook.com','Liz','Yeats');
        $this->assertEmpty($actual);

        $actual = $manager->findContact('terry.sorelle@outlook.com','Terry','SoRelle');
        $this->assertnotEmpty($actual);
        $this->assertEquals('Terry',$actual->firstName);
        $this->assertEquals('SoRelle',$actual->lastName);
        $this->assertEquals('terry.sorelle@outlook.com',$actual->email);

        $actual = $manager->findContact('terry.sorelle@outlook.com','Terry SoRelle');
        $this->assertnotEmpty($actual);
        $this->assertEquals('Terry',$actual->firstName);
        $this->assertEquals('SoRelle',$actual->lastName);
        $this->assertEquals('terry.sorelle@outlook.com',$actual->email);

    }

/*    public function testGetMeetingId()
    {

    }

    public function testPostOrgEndorsement()
    {

    }

    public function testProcessForm()
    {

    }

    public function testProcessOrgEndorsement()
    {

    }

    public function testProcessEndorsement()
    {

    }*/
}
