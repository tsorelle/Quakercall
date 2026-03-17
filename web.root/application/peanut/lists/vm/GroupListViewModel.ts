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
        endorsements : IGroupListItem[]  = [];
        // observables
        endorsementList = ko.observableArray<IGroupListItem>([]);
        endorsementCount = ko.observable('')
        asOf = ko.observable('')
        currentPage = ko.observable(1);
        maxPages = ko.observable(15);
        itemsPerPage = 10;

        init(successFunction?: () => void) {
            let me = this;
            me.application.registerComponents(
            '@pnut/pager', () => {
                me.application.loadResources([
                        '@pnut/ViewModelHelpers'
                    ], () => {
                    me.services.executeService('GetGroupList', null,
                        function (serviceResponse: Peanut.IServiceResponse) {
                            if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                                let response: IGetGroupListResponse = serviceResponse.Value;
                                me.endorsements = response.list;
                                let count = me.endorsements.length;
                                let max = Math.ceil(count / me.itemsPerPage);
                                me.maxPages(max);
                                me.currentPage(1);
                                me.changePage();
                                // me.endorsementList(response.list)
                                me.endorsementCount(response.count)
                                me.asOf(response.lastDate)

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
                });
            });
        }

        changePage = (move: number = 0) => {
            let current = this.currentPage() + move;
            let start = this.itemsPerPage * (current - 1)
            let end = start + this.itemsPerPage;
            let pageSet = this.endorsements.slice(start,end);
            if (pageSet.length > 0) {
                this.endorsementList(pageSet);
                this.currentPage(current);
                // this.selectContact(pageSet[0]);
            }
            // this.pageview('view');
            Peanut.Helper.ScrollToTop();
        }

    }
}
