<?php

namespace PeanutTest\unit;

use Application\quakercall\db\QcallDataManager;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertNotEmpty;

class QcallDataManagerTest extends TestCase
{

    public function testGetCurrentMeeting()
    {
        $manager = new QcallDataManager();
        $current = $manager->getCurrentMeeting();
        $this->assertNotEmpty($current);

        $meeting = $manager->getMeetingByCode('2026-01');
        assertNotEmpty($meeting);
    }
/*
    public function testGetMeetingByCode()
    {

    }

    public function testIsRegistered()
    {

    }

    public function testRegisterParticipant()
    {

    }

    public function testIsSubscribed()
    {

    }

    public function testConfirmRegistration()
    {

    }
*/
}
