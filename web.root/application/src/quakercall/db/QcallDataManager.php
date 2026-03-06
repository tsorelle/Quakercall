<?php

namespace Application\quakercall\db;

use Application\quakercall\db\entity\QcallContact;
use Application\quakercall\db\entity\QcallEndorsement;
use Application\quakercall\db\entity\QcallRegistration;
use Application\quakercall\db\repository\QcallContactsRepository;
use Application\quakercall\db\repository\QcallEndorsementsRepository;
use Application\quakercall\db\repository\QcallGroupendorsementsRepository;
use Application\quakercall\db\repository\QcallMeetingsRepository;
use Application\quakercall\db\repository\QcallRegistrationsRepository;
use Tops\db\TQuery;
use Tops\services\IMessageContainer;
use Tops\sys\TWebSite;

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

    /**
     * Use if first and last name is deprecated so only use this for backward compatibility
     *
     * @param $firstName
     * @param $lastName
     * @param $email
     * @param $subscribed
     * @return QcallContact
     */
    public function newContactFromFirstAndLastName($firstName,$lastName,$email,$subscribed=1)
    {
        return $this->makeNewContact("$firstName $lastName",$email,$subscribed);
    }

    /**
     * @param string $fullName
     * @param string $email     NOTE: we assume email already cleaned and validated!  Use TEmailValidator before call
     * @param $subscribed
     * @return QcallContact
     */
    public function makeNewContact(string $fullName, string $email,$subscribed=1)
    {
        $contactRepo = $this->getContactsRepo();

        $contact = new QcallContact();
        $contact->assignNames($fullName);
        $contact->email = $email;
        $contact->phone = '';
        $contact->organization = '';
        $contact->title = '';
        $contact->address1 = '';
        $contact->address2 = '';
        $contact->city = '';
        $contact->state = '';
        $contact->postalcode = '';
        $contact->country = '';
        $contact->subscribed = $contactRepo->isSubscribed($email) ? 0 : $subscribed;
        $contact->bounced = 0;
        $contact->active = 1;
        return $contact;
    }


    /**
     * Use only for backward compatibility
     *
     * @param $fullName
     * @param string $email
     * @param $subscribed
     * @return false|string
     */
    public function createBasicContact($fullName, string $email,$subscribed=false)
    {
        $contact = $this->makeNewContact($fullName,$email,$subscribed);
        $contact->source = 'registrations';
        return ($this->getContactsRepo())->insert($contact);
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

    public function findEndorsement($email, $fullname)
    {
        $query = new TQuery();

        $sql =
            'SELECT r.id '.
            'FROM `qcall_endorsements` r '.
            'JOIN `qcall_contacts` c ON c.id = r.`contactId` '.
            'WHERE c.`email` = ? AND r.`name` = ?';

        $id = $query->getValue($sql,[$email,$fullname]);
        if (!$id) {
            return false;
        }
        return ($this->getEndorsementsRepo())->get($id);
    }


    public function findRegistration($meetingId, $email, $fullname)
    {
        $query = new TQuery();

        $sql =
            'SELECT r.id '.
            'FROM `qcall_registrations` r '.
            'JOIN `qcall_contacts` c ON c.id = r.`contactId` '.
            'WHERE c.`email` = ? AND r.`meetingId` = ? AND r.`participant` = ?';

        $registrationId = $query->getValue($sql,[$email,$meetingId,$fullname]);
        if (!$registrationId) {
            return false;
        }
        return ($this->getRegistrationsRepository())->get($registrationId);
    }

    public function generateSubmissionId($extra='qc')
    {
        $requestTime = $_SERVER['REQUEST_TIME'] ?? '';
        return $extra.$requestTime;
    }

    public function postEndorsement($endorsementRequest)
    {
        $response = new \stdClass();
        $endorsementRepo = $this->getEndorsementsRepo();
        $endorsement = $this->findEndorsement(
            $endorsementRequest->email,
            $endorsementRequest->name);
        if ($endorsement) {
            $response->error = 'You have already endorsed the call.';
            return $response;
        }
        /**
         * @var $endorsement QcallEndorsement
         */
        $endorsement = new QcallEndorsement();
        $contactRepo = $this->getContactsRepo();
        /**
         * @var QcallContact $contact
         */
        $contact = $contactRepo->findByEmailAndName($endorsementRequest->email, $endorsementRequest->name);
        $poCode = $endorsementRequest->postalCode ?? '';
        $state = $endorsementRequest->state ?? '';
        $phone = $endorsementRequest->phone ?? '';

        if ($contact) {
            $endorsement->contactId = $contact->id;
            $changed = false;
            if ($phone !== '' && $contact->phone !== $phone) {
                $contact->phone = $phone;
                $changed = true;
            }
            if ($state !== $contact->state) {
                $contact->state = $endorsementRequest->state;
                $changed = true;
            }

            if ($poCode !== $contact->postalcode) {
                $contact->address1 = $endorsementRequest->address1 ?? '';
                $contact->address2 = $endorsementRequest->address2 ?? '';
                $contact->city = $endorsementRequest->city ?? '';
                $contact->country = $endorsementRequest->country ?? '';
                $contact->postalcode = $poCode;
                $changed = true;
            }
            if ($changed) {
                $contactRepo->update($contact);
            }
        }
        else {
            $contact = $this->makeNewContact($endorsementRequest->name,$endorsementRequest->email);
            $contact->source = 'endorsements';
            $contact->phone = $registrationRequest->phone ?? '';
            $contact->address1 = $registrationRequest->address1 ?? '';
            $contact->address2 = $registrationRequest->address2 ?? '';
            $contact->city = $registrationRequest->city ?? '';
            $contact->state = $registrationRequest->state ?? '';
            $contact->country = $registrationRequest->country ?? '';
            $contact->postalcode = $registrationRequest->postalCode ?? '';
            $contact->normalizeStateAndCountry();
            $endorsement->contactId = $contactRepo->insert($contact);
            if (empty($endorsement->contactId)) {
                $response->error = 'Unable to create contact.';
                return $response;
            }
        }
        $endorsement->name =  $endorsementRequest->name;
        $endorsement->submissionDate = (new \DateTime())->format('Y-m-d');
        $endorsement->email = $endorsementRequest->email;
        $endorsement->address =  $contact->getLocation();
        $endorsement->comments = $endorsementRequest->comments;
        $endorsement->submissionId = $this->generateSubmissionId($contact->id);
        $endorsement->religion = $endorsementRequest->religion;
        $endorsement->affiliation = $endorsementRequest->organization;
        $endorsement->howFound = $endorsementRequest->howFound;
        $endorsement->ipAddress = TWebSite::GetClientIp();
        $endorsement->approved = 0;
        $endorsement->active = 1;
        $endorsementId = $endorsementRepo->insert($endorsement);
        if (!$endorsementId)
        {
            $response->error = 'Cannot post the endorsement.';
            return $response;
        }
        $response->fullname = $endorsement->name;
        $response->phone = $contact->phone;
        $response->location = $endorsement->address;
        $response->email = $contact->email;
        $response->organization = $endorsement->affiliation;
        $response->submissionId = $endorsement->submissionId;
        $response->endorsemenId = $endorsementId;
        $response->contactId = $endorsement->contactId;
        $response->religion = $endorsement->religion;

        return $response;
    }

    public function PostMeetingRegistration($registrationRequest)
    {
        $response = new \stdClass();
        $regRepo = $this->getRegistrationsRepository();
        $registration = $this->findRegistration(
            $registrationRequest->meetingId,
            $registrationRequest->email,
            $registrationRequest->name);
        if ($registration) {
            $response->error = 'You are already registered for this meeting.';
            return $response;
        }
        /**
         * @var $registration QcallRegistration
         */
        $registration = new QcallRegistration();
        $contactRepo = $this->getContactsRepo();
        /**
         * @var QcallContact $contact
         */
        $contact = $contactRepo->findByEmailAndName($registrationRequest->email, $registrationRequest->name);
        $poCode = $registrationRequest->postalCode ?? '';
        $phone = $registrationRequest->phone ?? '';

        if ($contact) {
            $registration->contactId = $contact->id;
            $changed = false;
            if ($phone !== '' && $contact->phone !== $phone) {
                $contact->phone = $phone;
                $changed = true;
            }
            if ($poCode !== '' && $contact->postalcode !== $poCode) {
                $contact->postalcode = $poCode;
                $changed = true;
            }
            if ($changed) {
                $contactRepo->update($contact);
            }
        }
        else {
            $contact = $this->makeNewContact($registrationRequest->name,$registrationRequest->email);
            $contact->source = 'registrations';
            $contact->phone = $registrationRequest->phone ?? '';
            $contact->city = $registrationRequest->city ?? '';
            $contact->state = $registrationRequest->state ?? '';
            $contact->country = $registrationRequest->country ?? '';
            $contact->postalcode = $registrationRequest->postalCode ?? '';
            $contact->normalizeStateAndCountry();
            $registration->contactId = $contactRepo->insert($contact);
            if (empty($registration->contactId)) {
                $response->error = 'Unable to create contact.';
                return $response;
            }
        }
        $registration->meetingId = $registrationRequest->meetingId;
        $registration->participant =  $registrationRequest->name;
        $registration->submissionDate = (new \DateTime())->format('Y-m-d');
        $registration->location = $contact->getLocation();
        $registration->generateSubmissionId($contact->id);
        $registration->active = 1;
        $registration->confirmed = 0;
        $registration->religion = $registrationRequest->religion;
        $registration->affiliation = $registrationRequest->organization;
        $registration->ipAddress = TWebSite::GetClientIp();
        $registrationId = $regRepo->insert($registration);
        if (!$registrationId)
        {
            $response->error = 'Cannot post the registration.';
            return $response;
        }
        $response->fullname = $registration->participant;
        $response->phone = $contact->phone;
        $response->location = $contact->getLocation();
        $response->email = $contact->email;
        $response->organization = $registration->affiliation;
        $response->submissionId = $registration->submissionId;
        $response->registrationId = $registrationId;
        $response->contactId = $registration->contactId;
        $response->religion = $registration->religion;
        return $response;
    }
}