/// <reference path='../../../../nutshell/pnut/core/ViewModelBase.ts' />
/// <reference path='../../../../nutshell/typings/knockout/index.d.ts' />

namespace Peanut {

    export interface IMeetingListItem {
        id:			 any;
        meetingCode: string;
        meetingDate: string;
        meetingTime: string;
        theme:       string;
        presenter:   string;
    }

    export interface IRegistrationListItem {
        id:				any;
        participant:    string;
        contactId:      any;
        meetingId:      any;
        submissionDate: string;
        location:       string;
        religion:       string;
        affiliation:    string;
        submissionId:   any;
        confirmed:      string;
    }

    export interface IGetRegistrationsResponse {
        meetings: IMeetingListItem[];
        registrations: IRegistrationListItem[];
        selectedMeeting: IMeetingListItem;
    }


    export class RegistrationsViewModel extends Peanut.ViewModelBase {
        // observables
        meetingList = ko.observableArray<IMeetingListItem>([]);
        registrationList = ko.observableArray<IRegistrationListItem>([]);
        meetingDescription = ko.observable<string>();
        selectedMeeting = ko.observable<IMeetingListItem>();
        meetingDate = ko.observable('');
        registrationCount = ko.observable(0);
        prevEntries = ko.observable(false);
        moreEntries = ko.observable(false);
        itemsPerPage = 10;
        totalItems = 0;
        currentPage = ko.observable(1);
        maxPages = ko.observable();

        private  allRegistrations : IRegistrationListItem[] = [];
        init(successFunction?: () => void) {
            let me = this;
            Peanut.logger.write('VmName Init');
            me.services.executeService('GetRegistrationList',null,
                function(serviceResponse: Peanut.IServiceResponse) {
                    if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                        let response: IGetRegistrationsResponse = serviceResponse.Value;
                        me.allRegistrations = response.registrations;
                        me.meetingList(response.meetings);
                        me.selectedMeeting(response.selectedMeeting);
                        me.meetingDate(response.selectedMeeting.meetingDate);
                        me.registrationCount(response.registrations.length);
                        let pageCount = Math.ceil( me.allRegistrations.length / me.itemsPerPage );
                        me.maxPages(pageCount);
                        me.getPage(1);
                    } else {
                        let debug = serviceResponse;
                    }
                }).fail(() => {
                        let trace = me.services.getErrorInformation();
                }).always(() => {
                      // me.hideWaiter();
                    me.bindDefaultSection();
                    successFunction();
                });
        }

        getPage = (pageNumber: number) => {
            let me = this;
            let startIndex = (pageNumber - 1) * me.itemsPerPage;
            let page = me.allRegistrations.slice(startIndex, startIndex + me.itemsPerPage)
            me.registrationList(page);
            this.prevEntries(pageNumber > 1);
            this.moreEntries(pageNumber < this.maxPages());
            me.currentPage(pageNumber)
        }

        getPrevious = () => {
            let prev = this.currentPage() -1;
            this.getPage(prev);
        }

        getNext = () => {
            let next = this.currentPage() + 1;
            this.moreEntries(next < this.maxPages());
            this.getPage(next);
        }

    }
}