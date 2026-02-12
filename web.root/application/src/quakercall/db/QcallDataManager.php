<?php

namespace Application\quakercall\db;

use Application\quakercall\db\entity\QcallContact;
use Application\quakercall\db\entity\QcallRegistration;
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

    public function getMeetingByCode($meetingCode)
    {
        return $this->getMeetingsRepo()->getMeetingByCode($meetingCode);
    }

    public function registerParticipant(string $participantName, string $email, $meetingId, $subscribe=false, $confirmed=true)
    {
        $regRepo = $this->getRegistrationsRepository();

        $contact = $this->findContact($email,$participantName);
        if ($contact) {
            $contactId = $contact->id;
            if ($subscribe) {
                $this->subscribe($contact);
            }
        }
        else {
            $contactId = $this->createBasicContact($participantName,$email,$subscribe);
        }

        $registration = $regRepo->getByParticipant($contactId,$meetingId);
        if ($registration === false) {
            $registration = new QcallRegistration();
            $registration->contactId = $contactId;
            $registration->participant = $participantName;
            $registration->submissionDate = date("Y-m-d");
            $registration->active = 1;
            $registration->confirmed = 1;
            $registration->meetingId = $meetingId;
            $registration->location = '';
            $registration->religion = '';
            $registration->affiliation = '';

            $regRepo->insert($registration);
        }
        else {
            $registration->active = 1;
            $registration->confirmed = 1;
            $regRepo->update($registration);
        }
        return true;
    }

    public function isRegistered($meetingId, string $email): bool
    {
        return $this->getRegistrationsRepository()->isRegistered($meetingId, $email);
    }

    public function confirmRegistration($meetingId, string $email)
    {
        return $this->getRegistrationsRepository()->confirm($meetingId, $email);
    }

    /**
     * @param QcallContact $contact
     * @return void
     */
    public function makeSortCode($firstName,$lastName): string
    {
        $hasFirst = !empty($firstName);
        if (!empty($lastName)) {
            $sortcode = $lastName;
            if ($hasFirst) {
                $sortcode .= ',';
            }
        }
        if ($hasFirst) {
            $sortcode .= $firstName;
        }
        return strtolower($sortcode);
    }

    public function isSubscribed(string $email)
    {
        return $this->getContactsRepo()->isSubscribed($email);
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

    public function getCurrentMeeting() {
        return $this->getMeetingsRepo()->getCurrentMeeting();
    }

    private function findContact(string $email, string $participantName)
    {
        $contacts = $this->getContactsRepo()->getAllByEmail($email);
        $fullname = strtolower($participantName);
        if ($contacts) {
            foreach ($contacts as $contact) {
                if (strtolower($contact->fullname) == $fullname) {
                    return $contact;
                }
            }
        }
        return false;
    }

    private function splitName(string $participantName)
    {
        $result = new \stdClass();
        $parts = explode(' ', $participantName);
        $result->last = array_pop($parts);
        $suffix = strtolower( preg_replace('/[[:punct:]]/', '', $result->last) );

        if ($suffix=='jr' || $suffix=='sr' || $suffix=='md' || $suffix=='ii' || $suffix=='iii') {
            $result->last = array_pop($parts);
        }
        $result->first = implode(' ', $parts);
        return $result;
    }

    private function createBasicContact(string $participantName, string $email,$subscribed=false)
    {
        $contactRepo = $this->getContactsRepo();
        $alreadySubscribed = $contactRepo->isSubscribed($email);
        if ($alreadySubscribed) {
            $subscribed = false;
        }

        $contact = new QcallContact();
        $contact->fullname = $participantName;
        $contact->email = $email;
        $name = $this->splitName($participantName);
        $contact->lastName = $name->last;
        $contact->firstName = $name->first;
        $contact->sortcode = $this->makeSortCode($contact->firstName,$contact->lastName);
        $contact->phone = '';
        $contact->organization = '';
        $contact->title = '';
        $contact->address1 = '';
        $contact->address2 = '';
        $contact->city = '';
        $contact->state = '';
        $contact->postalcode = '';
        $contact->country = '';
        $contact->subscribed = $subscribed;
        $contact->bounced = 0;
        $contact->active = 1;
        $contact->source = 'registrations';
        return $contactRepo->insert($contact);
    }

    private function subscribe($contact)
    {
        $this->getContactsRepo()->subscribe($contact);
    }

    public function getEndorsementsForReview() {
        return $this->getEndorsementsRepo()->getEndorsementsForApproval();
    }

    public function updateEndorsementStatus($endorsementId, $status)
    {
        $repo = $this->getEndorsementsRepo();
        $endorsement = $repo->getEndorsement($endorsementId);
        if (!$endorsement) {
            return false;
        }
        if ($status = 1) {
            $endorsement->approved = 1;
            $repo->update($endorsement);
            return true;
        }
        $endorsement->active = 0;
        $repo->update($endorsement);
        return true;
    }

    public function getGroupendorsementsForReview() {}


}