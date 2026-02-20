<?php

namespace Application\quakercall\services;

use Application\quakercall\db\repository\QcallContactsRepository;
use Tops\services\TServiceCommand;

class SearchContactsCommand extends TServiceCommand
{

    protected function run()
    {
        $searchTerm = $this->getRequest();
        if (empty($searchTerm)) {
            $this->addErrorMessage('No search term specified.');
            return;
        }
        $repository = new QcallContactsRepository();
        if ($searchTerm == '#bounced') {
            $results = $repository->getBouncedEmails();

        }
        else {
            $results = $repository->searchByName($searchTerm);
        }
        if (empty($results)) {
            $this->addInfoMessage('No contacts found.');
        }
        $this->setReturnValue($results);
    }
}