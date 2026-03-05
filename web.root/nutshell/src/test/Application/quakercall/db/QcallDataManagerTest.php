<?php

namespace PeanutTest\unit;

use Application\quakercall\db\QcallDataManager;
use Application\quakercall\db\repository\QcallContactsRepository;
use Application\quakercall\db\repository\QcallMeetingsRepository;
use Application\quakercall\db\repository\QcallRegistrationsRepository;
use PHPUnit\Framework\TestCase;
use Tops\db\TQuery;
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


    private function testContact(QcallDataManager $manager,$testName,$testEmail,
                                                  $expectedFirst,$expectedLast,$expectedSubscribed, $subscribedInput=null)
    {
        if ($subscribedInput === null) {
            $actual = $manager->makeNewContact($testName, $testEmail);
        } else {
            $actual = $manager->makeNewContact($testName, $testEmail, $subscribedInput);
        }
        $this->assertNotEmpty($actual);
        $this->assertEquals($expectedFirst, $actual->firstName, 'Test name: ' . $testName);
        $this->assertEquals($expectedLast, $actual->lastName, 'Test name: ' . $testName);
        $this->assertEquals($testEmail, $actual->email, 'Test name: ' . $testName);
        $this->assertEquals($expectedSubscribed, $actual->subscribed, 'Test name: ' . $testName);
        $this->assertEquals(0, $actual->bounced, 'Test name: ' . $testName);
        return $actual;
    }

    public function doMakeContactTest(QcallDataManager $manager,$testName,
                                      $expectedFirst,$expectedLast)
    {
        $this->testContact($manager,$testName,'terry.sorelle@outlook.com',$expectedFirst,$expectedLast,0);
        $this->testContact($manager,$testName,'nobody@outlook.com',$expectedFirst,$expectedLast,1);
    }
    public function testMakeNewContact() {
        $manager = new QcallDataManager();

        $testName = 'Terry Layton SoRelle';
        $this->doMakeContactTest($manager, $testName,'Terry','SoRelle');
        $testName = 'Terry H.L. SoRelle';
        $this->doMakeContactTest($manager, $testName,'Terry','SoRelle');
        $testName = 'Terry SoRelle';
        $this->doMakeContactTest($manager, $testName,'Terry','SoRelle');
        $testName = 'Madonna';
        $this->doMakeContactTest($manager, $testName,'',$testName);
        $testName = 'Mr. Terry Layton SoRelle Jr.';
        $this->doMakeContactTest($manager, $testName,'Terry','SoRelle');
    }

    public function

    testPostRegistration()
    {
        $meetingRepo = new QcallMeetingsRepository();
        $meeting = $meetingRepo->getMeetingByCode('2026-01');
        $manager = new QcallDataManager();
        $request = new \stdClass();
        $request->meetingId = $meeting->id;
        $request->name = 'Test Name';
        $request->email = 'mail@test.com';
        $request->city = 'Austin';
        $request->state = 'Texas';
        $request->country = '';
        $request->phone = '123-456-7890';
        $request->organization = 'Friends Meeting of Austin';
        $request->religion = 'Quaker';

        $response = $manager->PostMeetingRegistration($request);
        $contactId = $response->contactId ?? null;
        $registrationId = $response->registrationId ?? null;
        $contactsRepo = new QcallContactsRepository();
        $registrationsRepo = new QcallRegistrationsRepository();
        if ($contactId !== null) {
            $contact = $contactsRepo->get($response->contactId);
        }
        if ($registrationId !== null) {
            $registration = $registrationsRepo->get($registrationId);
        }

        try {
            $this->assertNotEmpty($contactId,'Contact creation failed');
            $this->assertNotEmpty($registrationId,'Registration creation failed. ');
            $this->assertNotEmpty($contact,'No contact found');
            $this->assertNotEmpty($registration,'No registration found');
            $this->assertNotEmpty($response->fullname,'Fullname not returned');
            $this->assertNotEmpty($response->phone,'Phone not returned');
            $this->assertNotEmpty($response->location,'Location not returned');
            $this->assertNotEmpty($response->email,'Email not returned');
            $this->assertNotEmpty($response->organization,'Organization not returned');
            $this->assertNotEmpty($response->submissionId,'Submission id not returned');
            $this->assertNotEmpty($response->religion,'Religion not returned');
        } finally {
            $query = new TQuery();
            if ($contactId !== null) {
                $query->execute("DELETE FROM qcall_registrations WHERE contactId = $contactId");
                $query->execute("DELETE FROM qcall_contacts WHERE id = $contactId");
            }
            else if ($registrationId !== null) {
                $query->execute("DELETE FROM qcall_registrations WHERE registrationId = $registrationId");
            }
        }
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
