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
        sortCode:       string;
        sortName:       string;
        dateSort:       string;
    }

    interface ISummaryListItem {
        count: number;
    }
    interface IAffiliationListItem extends ISummaryListItem {
        affiliation: string;
    }
    interface IReligionListItem extends ISummaryListItem {
        religion: string;
    }
    interface ILocationListItem extends ISummaryListItem {
        state: string;
        country: string;
    }

    export interface IGetRegistrationsResponse {
        meetings: IMeetingListItem[];
        registrations: IRegistrationListItem[];
        selectedMeeting: IMeetingListItem;
        affiliations: IAffiliationListItem[];
         religions: IReligionListItem[];
         locations: ILocationListItem[];
    }


    export class RegistrationsViewModel extends Peanut.ViewModelBase {
        // observables
        tab = ko.observable('registrations');

        meetingList = ko.observableArray<IMeetingListItem>([]);
        registrationList = ko.observableArray<IRegistrationListItem>([]);
        affiliationsList = ko.observableArray<IAffiliationListItem>([]);
        religionsList = ko.observableArray<IReligionListItem>([]);
        locationsList = ko.observableArray<ILocationListItem>([]);

        meetingDescription = ko.observable<string>();
        selectedMeeting = ko.observable<IMeetingListItem>();
        meetingDate = ko.observable('');
        registrationCount = ko.observable(0);
        confirmedCount = ko.observable(0);
        prevEntries = ko.observable(false);
        moreEntries = ko.observable(false);
        itemsPerPage = 10;
        totalItems = 0;
        currentPage = ko.observable(1);
        maxPages = ko.observable();
        // filterConfirmed = ko.observable(false);
        currentFilter = ko.observable('all');
        sort = ko.observable('Most recent');
        sortOptions = ko.observableArray<string>(
            ['Received','Most recent','Participant']
            // ,'Location','Religion','Affiliation']
        );

        private registrations: IRegistrationListItem[] = [];
        private  allRegistrations : IRegistrationListItem[] = [];
        private  confirmedRegistrations : IRegistrationListItem[] = null;
        init(successFunction?: () => void) {
            let me = this;
            Peanut.logger.write('VmName Init');
            me.services.executeService('GetRegistrationList',null,
                function(serviceResponse: Peanut.IServiceResponse) {
                    if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                        let response: IGetRegistrationsResponse = serviceResponse.Value;
                        me.registrations = response.registrations;
                        me.affiliationsList(response.affiliations);
                        me.religionsList(response.religions);
                        me.locationsList(response.locations);
                        me.allRegistrations = [...response.registrations];
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
            let filter = me.currentFilter();
            let list = me.currentFilter() == 'confirmed' ? me.confirmedRegistrations : me.allRegistrations;
            let startIndex = (pageNumber - 1) * me.itemsPerPage;
            let page = list.slice(startIndex, startIndex + me.itemsPerPage)
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

        toggleFilter = ()=> {
            if (this.currentFilter() == 'all') {
                this.filterConfirmed();
            }
            else {
                this.showAll();
            }
        }
        filterConfirmed = () => {
            let me = this;
            this.currentFilter('confirmed');
            if (me.confirmedRegistrations == null) {
                me.confirmedRegistrations = me.allRegistrations.filter(item => item.confirmed != 'No');
                me.confirmedCount(me.confirmedRegistrations.length);
            }
            this.pageOne(me.confirmedRegistrations);
        }

        showAll = () => {
            let me = this;
            this.currentFilter('all')
            this.pageOne(me.allRegistrations);
        }

        pageOne = (list: IRegistrationListItem[]) => {
            this.registrationList([]);
            this.registrationList(list);
            let pageCount = Math.ceil( list.length / this.itemsPerPage );
            this.maxPages(pageCount);
            this.getPage(1);
        }

        applySort = (sortColumn: string) => {
            let me = this;
            this.sort(sortColumn);

            switch (sortColumn) {
                case 'Received':
                    me.allRegistrations.sort((a,
                       b) => (a.dateSort || '').localeCompare(b.dateSort || ''));

                    break;
                case 'Participant':
                    me.allRegistrations.sort((a,
                       b) => (a.sortCode || '').localeCompare(b.sortCode || ''));
                    break
                case 'Most recent':
                    me.allRegistrations = [...me.registrations];
                    break;
            }
            me.pageOne(me.allRegistrations);
        }

        showRegistrationsList = () => {
            this.tab('registrations');
        }
        showAffiliationsList = () => {
            this.tab('affiliations');
        }
        showReligionsList = () => {
            this.tab('religions');
        }
        showLocationsList = () => {
            this.tab('locations');
        }


    }
}