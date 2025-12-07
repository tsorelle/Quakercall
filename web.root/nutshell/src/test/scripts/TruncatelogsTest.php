<?php

namespace PeanutTest\scripts;

use Peanut\PeanutTasks\TaskManager;
use PeanutTest\scripts\TestScript;
use Tops\services\MessageType;
use Tops\services\TServiceResponse;

class TruncatelogsTest extends TestScript
{

    public function execute()
    {
        $runDate = $_GET['date'] ?? (new \DateTime())->format('Y-m-d');
        $manager = new TaskManager();
        /**
         * @var $response TServiceResponse
         */
        $response = $manager->doTestRun('truncateLogs',$runDate);
        if ($response instanceof TServiceResponse) {
            foreach ($response->Messages as $message) {
                $this->assert($message->MessageType !== MessageType::Error, $message->Text);
                if ($message->MessageType !== MessageType::Error) {
                    print
                        ($message->MessageType == MessageType::Warning ? 'WARNING: ' : 'INFO: ').
                        "$message->Text\n";
                }
            }
        }
        else {
            $this->assert(false,$response);
        }

    }
}