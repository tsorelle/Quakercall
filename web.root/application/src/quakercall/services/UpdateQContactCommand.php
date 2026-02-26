<?php

namespace Application\quakercall\services;

use Application\quakercall\db\entity\QcallContact;
use Application\quakercall\db\repository\QcallContactsRepository;
use Tops\mail\TEmailValidator;
use Tops\services\TServiceCommand;
use Tops\sys\TUsStates;

class UpdateQContactCommand extends TServiceCommand
{
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
            $contact->normalizeStateAndCountry();
            $contact->assignSortCode();
            $id = $repository->insert($contact);
            if ($id) {
                $this->addInfoMessage("New contact for ".$request->contact->fullname." was created");
            }
        }
        else {
            /**
             * @var QcallContact $contact
             */
            $contact = $repository->get($request->contact->id);
            if (empty($contact)) {
                $this->addErrorMessage('Contact not found');
                return;
            }
            $city = $request->contact->city ?? '';
            $state = $request->contact->state ?? '';
            $country = $request->contact->country ?? '';
            $changedLocation = ($city != $contact->city || $state != $contact->state || $country != $contact->country);

            $contact->assignFromObject($request->contact);
            $contact->normalizeStateAndCountry();
            if ($changedLocation) {
                // we dont need physical address
                $contact->address1 = '';
                $contact->address2 = '';
                $contact->postalcode = '';
            }

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