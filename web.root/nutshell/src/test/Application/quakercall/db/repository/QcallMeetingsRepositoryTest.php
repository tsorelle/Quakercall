<?php

namespace PeanutTest\unit;

use Application\quakercall\db\repository\QcallMeetingsRepository;
use PHPUnit\Framework\TestCase;

class QcallMeetingsRepositoryTest extends TestCase
{

    public function testGetUpcomingMeeting()
    {
        $repository = new QcallMeetingsRepository();
        $result = $repository->getUpcomingMeeting();
        $this->assertNotEmpty($result);

    }

    public function testGetMeetingsList()
    {
        $repository = new QcallMeetingsRepository();
        $result = $repository->getMeetingsList();
        $this->assertNotEmpty($result);

    }

    public function testGetCurrentMeeting()
    {
        $repository = new QcallMeetingsRepository();
        $result = $repository->getCurrentMeeting();
        $this->assertNotEmpty($result);
    }
}
