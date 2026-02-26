/// <reference path='../../../../nutshell/pnut/core/ViewModelBase.ts' />
/// <reference path='../../../../nutshell/typings/knockout/index.d.ts' />
/// <reference path='../../../../nutshell/pnut/js/ViewModelHelpers.ts' />
/// <reference path='../../types/qcall.d.ts' />
namespace Peanut {

    import IContactItem = QuakerCall.IContactItem;

    interface IContactUpdateRequest {
        contact: IContactItem;
        searchTerm: string;
    }
    export class QcontactsViewModel extends Peanut.ViewModelBase {
        // observables
        testmessage = ko.observable('Contacts')
        contacts = ko.observableArray<QuakerCall.IContactItem>();
        showNoContacts = ko.observable(false)
        allContacts: QuakerCall.IContactItem[];
        visibleContacts: QuakerCall.IContactItem[];
        showTable = ko.observable(false);
        searchName = ko.observable('');
        subscribedOnly = ko.observable(false);
        filterable = ko.observable(true)
        prevEntries = ko.observable(false);
        moreEntries = ko.observable(false);
        itemsPerPage = 10;
        totalItems = 0;
        totalCount = ko.observable(0);
        subscriberCount = ko.observable(0);
        currentPage = ko.observable(1);
        maxPages = ko.observable();
        searchTerm = '';

        form = {
            id : ko.observable(0),
            fullname : ko.observable(''),
            firstName : ko.observable(''),
            lastName : ko.observable(''),
            email : ko.observable(''),
            phone : ko.observable(''),
            city : ko.observable(''),
            state : ko.observable(''),
            country : ko.observable(''),
            subscribed : ko.observable(false),
            bounced : ko.observable(false),
            active : ko.observable(true),

            emailError: ko.observable(false),
            nameError: ko.observable(false)
        }

        init(successFunction?: () => void) {
            let me = this;
            Peanut.logger.write('Qcontacts Init');

            me.application.loadResources([
                '@pnut/ViewModelHelpers.js'
            ], () => {
                me.bindDefaultSection();
                successFunction();
            });
        }

        clearForm = () => {
            this.form.id(0);
            this.form.fullname('');
            this.form.firstName('');
            this.form.lastName('');
            this.form.email('');
            this.form.phone('');
            this.form.city('');
            this.form.state('');
            this.form.country('');
            this.form.subscribed(false);
            this.form.bounced(false);
            this.form.active(true);
        }

        clearErrors = () => {

        }

        assignForm = (contact : IContactItem) => {
            this.form.id(contact.id || 0);
            this.form.fullname(contact.fullname);
            this.form.firstName(contact.firstName);
            this.form.lastName(contact.lastName);
            this.form.email(contact.email);
            this.form.phone(contact.phone || '');
            this.form.city(contact.city);
            this.form.state(contact.state);
            this.form.country(contact.country);
            this.form.subscribed(contact.subscribed == 1);
            this.form.bounced(contact.bounced == 1);
            this.form.active(contact.active == 1);

            this.form.emailError(false);
            this.form.nameError(false)
        }

        showContacts = (contacts: QuakerCall.IContactItem[])=> {
            this.allContacts = [];
            this.showTable(false);
            let count = contacts.length
            if (count) {
                this.allContacts = contacts;
                this.initialFilter();
                this.showTable(true);
            }
            this.showNoContacts(count < 1)
            this.getPage(1);

        }

        newContact = () => {
            this.clearForm();
            this.showModal('contact-update-modal');
        }


        updateContact = ()=> {
            let me = this;
            let name = this.form.fullname().trim();
            if (name == '') {
                this.refreshFullName();
                name = this.form.fullname().trim();
            }
            if (name == '') {
                this.form.nameError(true);
                return;
            }

            let request: IContactUpdateRequest = {
                contact : {
                    id: this.form.id(),
                    subscribed: this.form.subscribed() ? 1 : 0,
                    active: this.form.active() ? 1 : 0,
                    bounced: this.form.bounced() ? 1 : 0,
                    fullname: this.form.fullname().trim(),
                    firstName: this.form.firstName().trim(),
                    lastName: this.form.lastName().trim(),
                    email: this.form.email().trim(),
                    phone: this.form.phone().trim(),
                    city: this.form.city().trim(),
                    state: this.form.state().trim(),
                    country: this.form.country().trim(),
                },
                searchTerm: this.searchTerm
            }

            if ( !Peanut.Helper.ValidateEmail(this.form.email()) ) {
                this.form.emailError(true);
                return;
            }
            if (request.contact.firstName == '' && request.contact.lastName == '') {
                request.contact.lastName = request.contact.fullname;
            }

            me.showWaiter(`Updating contact ${request.contact.fullname}`);
            me.services.executeService('UpdateQContact',request,
                function(serviceResponse: Peanut.IServiceResponse) {
                    if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                        me.hideModal('contact-update-modal')
                        me.showContacts(serviceResponse.Value)
                    } else {
                        let debug = serviceResponse;
                    }
                }).fail(() => {
                let trace = me.services.getErrorInformation();
            }).always(() => {
                me.hideWaiter();
            });


        }

        initialFilter = () => {
            let count = this.allContacts.length;
            this.totalCount(count);
            let pageCount = 1;
            let filterable = false;
            let subscribedOnly = false;
            if (count > 0) {
                this.visibleContacts = this.allContacts.filter(c => c.subscribed == 1);
                let visibleCount = this.visibleContacts.length;
                if (visibleCount) {
                    filterable = (count > visibleCount);
                    subscribedOnly = true;
                } else {
                    this.visibleContacts = this.allContacts;
                }
                pageCount = Math.ceil(this.visibleContacts.length / this.itemsPerPage);
                this.filterable(visibleCount !== count);
                if (visibleCount > 0) {
                    subscribedOnly =true;
                } else {
                    this.visibleContacts = this.allContacts;
                }
            }
            this.showNoContacts(count === 0);
            let telst = this.showNoContacts();
            this.filterable(filterable);
            this.subscribedOnly(subscribedOnly)
            this.maxPages(pageCount);
            this.getPage(1);
        }


        filterContacts = (subscribedOnly: boolean) => {
            if (!subscribedOnly) {
                this.visibleContacts = this.allContacts;
            }
            else {
                this.visibleContacts = this.allContacts.filter(c => c.subscribed == 1);
            }
            let total = this.visibleContacts.length;
            let pageCount = Math.ceil( this.visibleContacts.length / this.itemsPerPage );
            this.maxPages(pageCount);
            this.subscribedOnly(subscribedOnly);
            this.getPage(1);
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


        getPage = (pageNumber: number) => {
            let me = this;
            if (this.maxPages() > 1) {
                let startIndex = (pageNumber - 1) * me.itemsPerPage;
                let page = this.visibleContacts.slice(startIndex, startIndex + me.itemsPerPage)
                me.contacts(page);
                this.prevEntries(pageNumber > 1);
                this.moreEntries(pageNumber < this.maxPages());
                me.currentPage(pageNumber)
            }
            else {
                me.contacts(this.visibleContacts)
                this.prevEntries(false);
                this.moreEntries(false);
                me.currentPage(1)
            }

        }


        getBounces = () => {
            this.searchName('');
            this.searchContacts( '#bounced');
        }

        findContacts = () => {
            let searchTerm = this.searchName().trim();
            if (searchTerm) {
                this.searchContacts(searchTerm);
            }
        }

        onSearchEnter = (data, event) => {
            if (event.key === "Enter") {
                this.findContacts();
            }
            return true;
        }

        refreshFullName = () => {
            let name = this.form.firstName().trim();
            let last = this.form.lastName().trim();
            if (name) {
                if (last) {
                    name += ' ';
                }
            }
            name += last;
            this.form.fullname(name)

        }

        editContact = (contact: IContactItem) => {
            this.assignForm(contact);
            this.showModal('contact-update-modal');
        }
        searchContacts = (searchTerm) => {
            let me = this;
            me.searchTerm = '';
            me.showWaiter('Searching for contacts');
            me.services.executeService('SearchContacts',searchTerm,
                function(serviceResponse: Peanut.IServiceResponse) {
                    if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                        me.showContacts(serviceResponse.Value)
                        me.searchTerm = searchTerm;
                    } else {
                        let debug = serviceResponse;
                    }
                }).fail(() => {
                let trace = me.services.getErrorInformation();
            }).always(() => {
                me.hideWaiter();
            });

        }

        toggleSubscribedFilter = () => {
            let state = this.subscribedOnly();
            this.filterContacts(!state);
        }
    }
}