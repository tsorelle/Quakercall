<?php

namespace Application\quakercall\db;

use Application\quakercall\db\repository\QcallContactsRepository;
use Application\quakercall\db\repository\QcallEndorsementsRepository;
use Application\quakercall\db\repository\QcallGroupendorsementsRepository;
use Application\quakercall\db\repository\QcallMeetingsRepository;
use Application\quakercall\db\repository\QcallRegistrationsRepository;

class QcallDataManager
{
    private QcallContactsRepository $contactRepo;
    private QcallEndorsementsRepository $endorsementsRepo;
    private QcallGroupendorsementsRepository $groupendorsementsRepo;
    private QcallMeetingsRepository $meetingsRepo;

    private QcallRegistrationsRepository $registrationsRepo;

    protected function getRegistrationsRepository() {
        if (!isset($this->registrationsRepo)) {
            $this->registrationsRepo = new QcallRegistrationsRepository();
        }
        return $this->registrationsRepo;
    }


    protected function getContactsRepo() {
        if (!isset($this->contactRepo)) {
            $this->contactRepo = new QcallContactsRepository();
        }
        return $this->contactRepo;
    }

    protected function getEndorsementsRepo() {
        if (!isset($this->endorsementsRepo)) {
            $this->endorsementsRepo = new QcallEndorsementsRepository();
        }
        return $this->endorsementsRepo;
    }

    protected function getGroupendorsementsRepo() {
        if (!isset($this->groupendorsementsRepo)) {
            $this->groupendorsementsRepo = new QcallGroupendorsementsRepository();
        }
        return $this->groupendorsementsRepo;
    }
    protected function getMeetingsRepo() {
        if (!isset($this->meetingsRepo)) {
            $this->meetingsRepo = new QcallMeetingsRepository();
        }
        return $this->meetingsRepo;
    }

    public function getContacts($email, $fullname=null) {
        $response = [];
        $repo = $this->getContactsRepo();
        if ($fullname) {
            $result = $repo->findByFullname($fullname);
            if ($result) {
                $response[] = $result;
            }
        }
        if (empty($response)) {
            $response = $repo->getAllByEmail($email);
        }
        return $response;
    }

    public function getMeetingRegistrationList(array $meetingList, $meetingId=null) {
        if (empty($meetingList)) {
            throw new \Exception('Meeting list is empty');
        }
        if (empty($meetingId)) {
            $meetingId = $meetingList[0]->id;
        }
        return $this->getRegistrationsRepository()->getRegistrationList($meetingId);
    }

    public function getMeetingsList()
    {
        return $this->getMeetingsRepo()->getMeetingsList();
    }

}