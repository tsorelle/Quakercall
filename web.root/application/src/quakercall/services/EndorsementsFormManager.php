<?php

namespace Application\quakercall\services;

// get new endorsements from jotforms where submissiondate > 2025-12-06


use Application\quakercall\db\entity\QcallContact;
use Application\quakercall\db\entity\QcallEndorsement;
use Application\quakercall\db\entity\QcallError;
use Application\quakercall\db\entity\QcallGroupendorsement;
use Application\quakercall\db\entity\QcallRegistration;
use Application\quakercall\db\repository\QcallContactsRepository;
use Application\quakercall\db\repository\QcallEndorsementsRepository;
use Application\quakercall\db\repository\QcallErrorsRepository;
use Application\quakercall\db\repository\QcallGroupendorsementsRepository;
use Application\quakercall\db\repository\QcallMeetingsRepository;
use Application\quakercall\db\repository\QcallRegistrationsRepository;
use stdClass;
use Tops\sys\TStrings;

class EndorsementsFormManager
{
    private static QcallErrorsRepository $errorsRepo;


    private static function postError(QcallError $error, $meetingId = null, $formId = null)
    {
        if (!isset(self::$errorsRepo)) {
            self::$errorsRepo = new QcallErrorsRepository();
        }
        $error->occurred = date('Y-m-d H:i:s');
        $error->postdata = print_r($_POST, true);
        $error->meetingId = $_POST['meetingid'] ?? '';
        self::$errorsRepo->insert($error);
    }

    private static function logErrorMessage($message)
    {
        $error = new QcallError();
        $error->message = $message;
        self::postError($error);
    }

    private static function logException(\Exception $exception)
    {
        // $formId = $_POST['formID'] ?? '';
        $error = new QcallError();
        $error->message = $exception->getMessage();
        $error->exception = sprintf(
            "\nLine %s\nFile: %s\nStack Trace:\n%s\n",
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );

        self::postError($error);
    }

    private $contactRepository;

    private function getContactRepository()
    {
        if (!$this->contactRepository) {
            $this->contactRepository = new QcallContactsRepository();
        }
        return $this->contactRepository;
    }

    private $registrationRepository;

    private function getRegistrationRepository()
    {
        if (!$this->registrationRepository) {
            $this->registrationRepository = new QcallRegistrationsRepository();
        }
        return $this->registrationRepository;
    }

    private $meetingsRepository;

    private function getMeetingRepository()
    {
        if (!$this->meetingsRepository) {
            $this->meetingsRepository = new QcallMeetingsRepository();
        }
        return $this->meetingsRepository;
    }

    public static function processForm()
    {
        error_reporting(E_WARNING);
        ini_set('display_errors', 1);
        // print "<pre>Process Form\n</pre>";

        global $_POST;

        try {
            $instance = new EndorsementsFormManager();
            // print "<pre>Make inst\n</pre>";

            $result = new stdClass();
            $meetingCode = $_POST['meetingid'] ?? '';
            if (empty($meetingCode)) {
                // probably accidental repost or someone hacking
                exit('Registration already processed. Please return to <a href="https://quakercall.net">Home page</a>');
            }
            // throw new \Exception("Something went wrong while processing the request.");
            $result->formId = $_POST['formID'] ?? '';
            $result->meetingId = $instance->getMeetingId($meetingCode);
            $result->submissionDate = DATE('Y-m-d');
            $result->submissionId = $_POST['submission_id'] ?? '';
            $result->formKey = $_POST['formkey'] ?? '';
            $result->ipAddress = $_POST['ip'] ?? '';
            if (isset($_POST['firstname'])) {
                // received from test form
                $result->firstName = $_POST['firstname'] ?? '';
                $result->lastName = $_POST['lastname'] ?? '';
            } else {
                $result->firstName = $_POST['name']['first'] ?? '';
                $result->lastName = $_POST['name']['last'] ?? '';

            }
            $result->email = $_POST['email'] ?? '';
            $result->location = $_POST['location'] ?? '';
            $result->phone = $_POST['phonenumber'] ?? '';
            if (isset($_POST['religiousaffiliation'])) {
                if (is_array($_POST['religiousaffiliation'])) {
                    $result->affiliation = $_POST['religiousaffiliation']['other'] ?? '';
                } else {
                    $result->affiliation = $_POST['religiousaffiliation'] ?? '';
                }
            } else {
                $result->affiliation = '';
            }
            $result->meeting = $_POST['friendsmeeting'] ?? '';
            $result->testmode = $_POST['testmode'] ?? '';

            if ($result->testmode !== 'yes') {
                // print "Posting Test Mode\n";
                $instance->postRegistration($result);
            }
            return $result;
        } catch (\Exception $e) {
            self::logException($e);
            exit ('An unexpected error has occurred and was reported to the administrator.  Please try again later.');
        }
        return [];
    }

    public static function processEndorsement()
    {
        error_reporting(E_WARNING);
        ini_set('display_errors', 1);
        // print "<pre>Process Form\n</pre>";

        global $_POST;

        try {
            $instance = new EndorsementsFormManager();
            // print "<pre>Make inst\n</pre>";

            $result = new stdClass();

            $result->formId = $_POST['formID'] ?? '';
            $result->submissionDate = DATE('Y-m-d');
            $result->submissionId = $_POST['submission_id'] ?? '';
            $result->formKey = $_POST['formkey'] ?? '';
            $result->ipAddress = $_POST['ip'] ?? '';

            if (isset($_POST['firstname'])) {
                // received from test form
                $result->firstName = $_POST['firstname'] ?? '';
                $result->lastName = $_POST['lastname'] ?? '';
            } else {
                $result->firstName = $_POST['endorser-name']['first'] ?? '';
                $result->lastName = $_POST['endorser-name']['last'] ?? '';

            }
            $result->comments = $_POST['comments'] ?? '';
            if (isset($_POST['endorser-address'])) {
                $result->address1 = $_POST['endorser-address']['addr_line1'] ?? '';
                $result->address2 = $_POST['endorser-address']['addr_line2'] ?? '';
                $result->city = $_POST['endorser-address']['city'] ?? '';
                $result->state = $_POST['endorser-address']['state'] ?? '';
                $result->postalcode = $_POST['endorser-address']['postal'] ?? '';
                $result->country = $_POST['endorser-address']['country'] ?? '';
            } else {
                // probably testing
                $result->address1 = $_POST['addr_line1'] ?? '';
                $result->address2 = $_POST['addr_line2'] ?? '';
                $result->city = $_POST['city'] ?? '';
                $result->state = $_POST['state'] ?? '';
                $result->postalcode = $_POST['postal'] ?? '';
                $result->country = $_POST['country'] ?? '';
            }

            $result->email = $_POST['endorser-email'] ?? '';
            $result->howFound = $_POST['how-found'] ?? '';
            if (isset($_POST['religion'])) {
                if (is_array($_POST['religion'])) {
                    $result->religion = $_POST['religion']['other'] ?? '';
                } else {
                    $result->religion = $_POST['religion'] ?? '';
                }
            } else {
                $result->religion = '';
            }
            $result->meeting = $_POST['friendsmeeting'] ?? '';
            $result->found = $_POST['how-found'] ?? '';


            $result->testmode = $_POST['testmode'] ?? '';

            if ($result->testmode !== 'yes') {
                // print "Posting Test Mode\n";
                $instance->postEndorsement($result);
            }
        } catch (\Exception $e) {
            self::logException($e);
            exit ('An unexpected error has occurred and was reported to the administrator.  Please try again later.');
        }
        return $result;
    }

    public function getMeetingId($meetingCode)
    {
        $meetingRepo = $this->getMeetingRepository();
        $id = $meetingRepo->getIdForFieldValue('meetingCode', $meetingCode);
        if (!$id) {
            throw new \Exception("Meeting not found for code '$meetingCode'");
        }
        return $id;
    }


    private static function concatName($request)
    {
        $result = '';
        if (!empty($request->firstName)) {
            $result .= $request->firstName;
        }
        if (!empty($request->lastName)) {
            if (!empty($result)) {
                $result .= ' ';
            }
            $result .= $request->lastName;
        }
        return $result;
    }

    private function findContact($email, $fullname)
    {
        $contacts = $this->getContactRepository()->getAllByEmail($email);
        $fullname = strtolower($fullname);
        if ($contacts) {
            foreach ($contacts as $contact) {
                if (strtolower($contact->fullname) == $fullname) {
                    return $contact;
                }
            }
        }
        return false;
    }

    private function createRelatedContact($request,$source='registrations')
    {
        // todo: replace createBasicContact after Jan 25
        $contactRepo = $this->getContactRepository();
        $alreadySubscribed = $contactRepo->isSubscribed($request->email);
        $contact = new QcallContact();
        $contact->assignFromObject($request);
        if (empty($contact->fullname)) {
            $contact->fullname = trim( $contact->firstName . ' ' . $contact->lastName);
        }
        $sortcode = '';
        $hasFirst = !empty($request->firstName);
        if (!empty($contact->lastName)) {
            $sortcode = $request->lastName;
            if ($hasFirst) {
                $sortcode .= ',';
            }
        }
        if ($hasFirst) {
            $sortcode .= $request->firstName;
        }
        $contact->sortcode = trim(strtolower($sortcode));
        $contact->subscribed = !$alreadySubscribed;
        $contact->bounced = 0;
        $contact->active = 1;
        $contact->source = $source;

        return $contactRepo->insert($contact);
    }

    private function validRequest($request)
    {
        $message = '';
        /*        $meetingId = $request->meetingId ?? 'Not found.';
                $formId = $request->formId ?? 'Not found.';*/

        if (!(isset($request->firstName) || isset($request->lastName))) {
            $message .= 'Name';
        }
        if (!isset($request->email)) {
            $message .= 'email ';
        };
        if (!isset($request->meetingId)) {
            $message .= 'meetingId ';
        }
        if (!isset($request->formId)) {
            $message .= 'formId ';
        }
        if (empty($message)) {
            return true;
        }

        self::logErrorMessage("Missing data: $message");
        return false;
    }

    private function validEndorsementRequest($request)
    {
        $message = '';


/*        if (empty($request->firstName) && empty($request->lastName)) {
            $message .= 'Name ';
        }
        if (empty($request->email)) {
            $message .= 'email ';
        };*/
        if (empty($request->submissionId)) {
            $message .= 'submissionId ';
        }
        if (empty($message)) {
            return true;
        }

        self::logErrorMessage("Missing data: $message");
        return false;

    }

    private function postEndorsement(stdClass $request)
    {
        if (!$this->validEndorsementRequest($request)) {
            exit('Sorry, Invalid Request');
        }

        $endorsementRepo = new QcallEndorsementsRepository();
        $contactsRepo = new QcallContactsRepository();

        $email = $request->email;
        $endorserName = self::concatName($request);
        $request->fullname = $endorserName;

        $contact = $this->findContact($email,$endorserName);
        if ($contact) {
            $contactId = $contact->id;
            $contact->assignFromObject($request);
            $contactsRepo->update($contact);
            $endorsement = $endorsementRepo->getEndorsement($contactId);
        }
        else {
            $contactId = $this->createRelatedContact($request,'endorsements');
            $endorsement = false;
        }


        if ($endorsement === false) {
            $endorsement = new QcallEndorsement();
            $endorsement->assignFromObject($request);
            $endorsement->contactId = $contactId;
            $endorsement->name = $endorserName;
            $endorsement->active = 1;
            $result = $endorsementRepo->insert($endorsement);
        }
        else {
            $endorsement->assignFromObject($request);
            $result = $endorsementRepo->update($endorsement);
        }

        return $result;


    }

    public function postOrgEndorsement($request) {
        if (!$this->validEndorsementRequest($request)) {
            exit('Sorry, Invalid Request');
        }
        $endorsementRepo = new QcallGroupendorsementsRepository();
        $contactsRepo = new QcallContactsRepository();
        $contact = $contactsRepo->findOrganizationEndorser($request->organizationName);
        if ($contact) {
            $contactId = $contact->id;
            $contact->assignFromObject($request);
            $contactsRepo->update($contact);
            $endorsement = $endorsementRepo->getEndorsement($contactId);
        }
        else {
            $contactId = $this->createRelatedContact($request,'org-endorsement');
            $endorsement = false;
        }

        if ($endorsement === false) {
            $endorsement = new QcallGroupendorsement();
            $endorsement->assignFromObject($request);
            $endorsement->contactId = $contactId;
            $endorsement->active = 1;
            $endorsement->approved = 0;
            $result = $endorsementRepo->insert($endorsement);
        }
        else {
            $endorsement->assignFromObject($request);
            $endorsement->approved = 0;
            $result = $endorsementRepo->update($endorsement);
        }

        return $result;


    }

    public static function processOrgEndorsement()
    {
        // error_reporting(E_WARNING,E_ALL);
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        // print "<pre>Process Form\n</pre>";

        global $_POST;

        try {
            $instance = new EndorsementsFormManager();
            // print "<pre>Make inst\n</pre>";

            $result = new stdClass();

            $result->formId = $_POST['formID'] ?? '';
            $result->submissionDate = DATE('Y-m-d');
            $result->submissionId = $_POST['submission_id'] ?? '';
            $result->formKey = $_POST['formkey'] ?? '';
            $result->ipAddress = $_POST['ip'] ?? '';
            $result->testmode = $_POST['testmode'] ?? '';


            // for endorsement
            $result->organizationName = $_POST['organizationname'] ?? '';
            $result->organization = $_POST['organizationname'] ?? '';

            if (empty($_POST['attachcopy'])) {
                $result->attachments = '';
            }
            else {
                $attachments = $_POST['attachcopy'] ?? '';
                if (is_array($attachments)) {
                    $result->attachments = implode(',', $attachments);
                } else {
                    $result->attachments = $attachments;
                }
            }

            if ((!empty($_POST['contactname'])) && is_array($_POST['contactname'])) {
                $result->firstName = $_POST['contactname']['first'] ?? '';
                $result->lastName = $_POST['contactname']['last'] ?? '';
            } else {
                // local testing
                $result->firstName = $_POST['first'] ?? '';
                $result->lastName = $_POST['last'] ?? '';
            }
            
            $result->fullname = self::concatName($result);

            if ((!empty($_POST['address'])) && is_array($_POST['address'])) {
                $result->address1 = $_POST['address']['addr_line1'] ?? '';
                $result->address2 = $_POST['address']['addr_line2'] ?? '';
                $result->city = $_POST['address']['city'] ?? '';
                $result->state = $_POST['address']['state'] ?? '';
                $result->postalcode = $_POST['address']['postal'] ?? '';
            } else {
                // local testing
                $result->address1 = $_POST['addr_line1'] ?? '';
                $result->address2 = $_POST['addr_line2'] ?? '';
                $result->city = $_POST['city'] ?? '';
                $result->state = $_POST['state'] ?? '';
                $result->postalcode = $_POST['postal'] ?? '';
            }
            $result->phone = $_POST['phonenumber'] ?? '';
            $result->country = $_POST['country'] ?? '';

            $orgType = $_POST['organizationtype'] ?? '';
            $result->organizationType = $orgType;
            if (strstr($orgType, 'Meeting')) {
                $result->typeId = 1;
            }
            else if (strstr($orgType, 'Organization')) {
                $result->typeId = 2;
            }
            else {
                $result->typeId = 0;
            }


            // no longer supporting submissions from non-quaker organizations
/*
            $orgType = $_POST['organizationtype'] ?? null;
            if (empty($orgType)) {
                $result->organizationType = 'Other';
                $result->typeId = 9;
            }
            else {
                if (is_array($_POST['organizationtype'])) {
                    $result->organizationType = $_POST['organizationtype']['other'] ?? '';
                } else {
                    $result->organizationType = $orgType;
                    if (strstr($orgType, 'Meeting')) {
                        $result->typeId = 1;
                    }
                    else if (strstr($orgType, 'Organization')) {
                        $result->typeId = 2;
                    }
                    else {
                        $result->typeId = 9;
                    }
                }
            }
*/

            $result->email = $_POST['email'] ?? '';

            if ($result->testmode !== 'yes') {
                // print "Posting Test Mode\n";
                $instance->postOrgEndorsement($result);
            }
            
        } catch (\Exception $e) {
            self::logException($e);
            exit ('An unexpected error has occurred and was reported to the administrator.  Please try again later.');
        }
        return $result;
    } // end processOrEndorsement function

}// end class