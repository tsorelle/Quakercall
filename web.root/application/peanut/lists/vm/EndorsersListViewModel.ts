// required for all view models:
/// <reference path='../../../../nutshell/pnut/core/ViewModelBase.ts' />
/// <reference path='../../../../nutshell/typings/knockout/index.d.ts' />

namespace Peanut {

    interface IEndorsersListItem {
        Name : string;
        Location: string;
    }
    interface IGetEndorsersListResponse {
        list: IEndorsersListItem[];
        count: string;
        lastDate: string;
    }
    export class EndorsersListViewModel extends Peanut.ViewModelBase {
        // observables
        endorsements : IEndorsersListItem[]  = [];
        endorsementList = ko.observableArray<IEndorsersListItem>([]);
        endorsementCount = ko.observable('')
        asOf = ko.observable('')

        currentPage = ko.observable(1);
        maxPages = ko.observable(10);
        itemsPerPage = 15;


        init(successFunction?: () => void) {
            let me = this;

            // const box = document.getElementById('page-content');
            // box.style.margin = '0';
            // box.style.maxWidth = '100%';

            Peanut.logger.write('EndorsersList Init');
            me.application.registerComponents(
                '@pnut/pager', () => {
                    me.application.loadResources([
                        '@pnut/ViewModelHelpers'
                    ], () => {
                        // let fd = this.getPageVarialble('formdata');
                        me.services.executeService('GetIndividualEndorsersList',null,
                            function(serviceResponse: Peanut.IServiceResponse) {
                                if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                                    let response: IGetEndorsersListResponse = serviceResponse.Value;
                                    me.endorsements = response.list;
                                    let count = me.endorsements.length;
                                    let max =  Math.ceil(count / me.itemsPerPage);
                                    me.maxPages(max);
                                    me.currentPage(1);
                                    me.changePage();
                                    // me.endorsementList(response.list)
                                    me.endorsementCount(response.count)
                                    me.asOf(response.lastDate)

                                } else {
                                    // noinspection JSUnusedGlobalSymbols
                                    let debug = serviceResponse;
                                }
                            }).fail(() => {
                            // noinspection JSUnusedGlobalSymbols
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
