<?php

namespace Application\quakercall\services;

use Application\quakercall\db\QcallDataManager;
use Tops\services\TServiceCommand;


/**
 * Service contract
 * export interface IMeetingListItem {
 *  id:             any;
 *  meetingCode: string;
 *  meetingDate: string;
 *  meetingTime: string;
 *  theme:       string;
 *  presenter:   string;
 * }
 *
 * export interface IRegistrationListItem {
 *  id:                any;
 *  participant:    string;
 *  contactId:      any;
 *  meetingId:      any;
 *  submissionDate: string;
 *  location:       string;
 *  religion:       string;
 *  affiliation:    string;
 *  submissionId:   any;
 *  confirmed:      string;
 * }
 *
 * export interface IGetRegistrationsResponse {
 *      meetings: IMeetingListItem[];
 *      registrations: IRegistrationListItem[];
 *      selectedMeeting: IMeetingListItem;
 */
class GetRegistrationListCommand extends TServiceCommand
{

    protected function run()
    {
        $manager = new QcallDataManager();
        $meetings = $manager->getMeetingsList();

        $result = new \stdClass();
        $result->meetings = $meetings;
        $result->registrations = $manager->getMeetingRegistrationList($meetings);
        $result->selectedMeeting = empty($meetings) ? null : $meetings[0];

        $this->setReturnValue($result);
    }
}