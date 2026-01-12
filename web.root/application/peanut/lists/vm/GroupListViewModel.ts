// required for all view models:
/// <reference path='../../../../nutshell/pnut/core/ViewModelBase.ts' />
/// <reference path='../../../../nutshell/typings/knockout/index.d.ts' />

namespace Peanut {

    interface IGroupListItem {
        organizationName : string;
        city: string;
        state: string;
    }
    interface IGetGroupListResponse {
        list: IGroupListItem[];
        count: string;
        lastDate: string;
    }
    export class GroupListViewModel extends Peanut.ViewModelBase {
        // observables
        endorsementList = ko.observableArray<IGroupListItem>([]);
        endorsementCount = ko.observable('')
        asOf = ko.observable('')

        init(successFunction?: () => void) {
            let me = this;

            const box = document.getElementById('page-content');
            box.style.margin = '0';
            box.style.maxWidth = '100%';

            Peanut.logger.write('GroupList Init');
            // let fd = this.getPageVarialble('formdata');
            me.services.executeService('GetGroupList',null,
                function(serviceResponse: Peanut.IServiceResponse) {
                    if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                        let response: IGetGroupListResponse = serviceResponse.Value;
                        me.endorsementList(response.list)
                        me.endorsementCount(response.count)
                        me.asOf(response.lastDate)
/*
                        me.allRegistrations = response.registrations;
                        me.meetingList(response.meetings);
                        me.selectedMeeting(response.selectedMeeting);
                        me.meetingDate(response.selectedMeeting.meetingDate);
                        me.registrationCount(response.registrations.length);
                        let pageCount = Math.ceil( me.allRegistrations.length / me.itemsPerPage );
                        me.maxPages(pageCount);
                        me.getPage(1);
*/
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
    }
}
