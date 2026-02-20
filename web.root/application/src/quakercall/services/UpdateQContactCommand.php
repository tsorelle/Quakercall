<?php

namespace Application\quakercall\services;

use Application\quakercall\db\entity\QcallContact;
use Application\quakercall\db\repository\QcallContactsRepository;
use Tops\mail\TEmailValidator;
use Tops\services\TServiceCommand;

class UpdateQContactCommand extends TServiceCommand
{

    private function  makeSortCode($firstName, $lastName)
    {
        $result = trim($firstName);
        $lastName = trim($lastName);
        if (!empty($result)) {
            if (empty($lastName)) {
                return $result;
            }
            $result .= ' ';
        }
        return $result.$lastName;
    }

    protected function run()
    {
        $request = $this->getRequest();
        if (!$request) {
            $this->addErrorMessage('No request received');
            return;
        }
        $email = $request->contact->email ?? '';
        $validation = TEmailValidator::CheckEmailAddress($email);
        if ($validation->valid === false) {
            $this->addErrorMessage('Invalid email address');
            return;
        }
        if ($validation->changed == true) {
            $request->contact->email = $validation->email ?? '';
        }
        $fullName = $request->contact->fullname ?? '';
        if (empty($fullName)) {
            $this->addErrorMessage('Full name cannot be empty');
            return;
        }
        $isNew = empty($request->contact->id);
        $repository = new QcallContactsRepository();
        if ($isNew) {
            $contact = new QcallContact();
            $contact->assignFromObject($request->contact);
            $contact->sortcode = $this->makeSortCode($request->contact->firstName, $request->contact->lastName);
            $id = $repository->insert($contact);
            if ($id) {
                $this->addInfoMessage("New contact for ".$request->contact->fullname." was created");
            }
        }
        else {
            $contact = $repository->get($request->contact->id);
            if (empty($contact)) {
                $this->addErrorMessage('Contact not found');
                return;
            }
            $contact->assignFromObject($request->contact);
            $ok = $repository->update($contact);
            if ($ok) {
                $this->addInfoMessage("Contact for ".$request->contact->fullname." was updated.");
            }
        }
        if ($request->searchTerm == '#bounced') {
            $results = $repository->getBouncedEmails();
        }
        else {
            $results = $repository->searchByName($request->searchTerm);
        }
        if (empty($results)) {
            $this->addInfoMessage('No contacts found.');
        }
        $this->setReturnValue($results);


    }
}