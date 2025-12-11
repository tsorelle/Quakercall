<?php

namespace Application\quakercall\services;

use Tops\services\TServiceCommand;

class SendAdminAlertCommand extends TServiceCommand
{

    protected function run()
    {
        // TODO: Implement run() method.
        $this->addInfoMessage('Your message has been sent.');
    }
}