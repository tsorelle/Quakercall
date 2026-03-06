<?php

namespace Application\quakercall\services;

use Application\quakercall\db\QcallDataManager;
use Tops\mail\TEmailValidator;
use Tops\mail\TPostOffice;
use Tops\services\TServiceCommand;

class PostEndorsementCommand extends TServiceCommand
{

    protected function run()
    {
        $request = $this->getRequest();
        if (empty($request)) {
            $this->addErrorMessage('No request received');
            return;
        }

       if (empty($request->name)) {
            $this->addErrorMessage('No name received');
            return;
        }

        $validation = TEmailValidator::CheckEmailAddress($request->email);
        if ($validation->valid === false) {
            $this->addErrorMessage('Invalid email address');
            return;
        }
        if ($validation->changed == true && !empty($validation->email)) {
            $request->email = $validation->email;
        }

        $manager = new QcallDataManager();
        $result = $manager->postEndorsement($request);
        if (!empty($result->error)) {
            $this->addErrorMessage($result->error);
            return;
        }
        $date = date('Y-m-d');
        TPostOffice::SendMessageToUs('friends@quakercall.org',
            "New endorsement received on $date",
            "A new endorsement from $request->name has been submitted.  Please review for approval."
        );
        $this->setReturnValue($result);
    }
}