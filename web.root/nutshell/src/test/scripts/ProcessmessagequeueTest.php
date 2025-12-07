<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 9/12/2017
 * Time: 10:01 AM
 */

namespace PeanutTest\scripts;


use Peanut\PeanutTasks\TaskManager;
use Peanut\QnutCalendar\services\SendCommitteeNotificationsCommand;
use Peanut\QnutCalendar\services\SendEventNotificationsCommand;
// use Peanut\QnutDirectory\services\messaging\ProcessMessageQueueCommand;
use Peanut\PeanutMailings\services\ProcessMessageQueueCommand;
use Tops\services\MessageType;
use Tops\services\TServiceCommand;
use Tops\services\TServiceResponse;


class ProcessmessagequeueTest extends TestScript
{

    public function execute()
    {
        $runDate = $_GET['date'] ?? (new \DateTime())->format('Y-m-d');
        $manager = new TaskManager();
        /**
         * @var $response TServiceResponse
         */
        $response = $manager->doTestRun('processMessageQueue',$runDate);
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