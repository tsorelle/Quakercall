/// <reference path='../../../../nutshell/pnut/core/ViewModelBase.ts' />
/// <reference path='../../../../nutshell/typings/knockout/index.d.ts' />
/// <reference path='../../../../nutshell/pnut/js/htmlEditContainer.ts' />

namespace Peanut {

    import ISystemMessageRequest = QuakerCall.ISystemMessageRequest;

    export interface IEndorsementReviewItem {
        id : any;
        submissionId: string;
        submissionDate: string;
        comments: string;
        ipAddress: string;
        email: string;
        phone: string;
        address1: string;
        address2: string;
        city: string;
        state: string;
        country: string;
        postalcode: string;
    }

    export interface IGroupEndorsementReviewItem  extends IEndorsementReviewItem {
        organizationName: string;
        contactName: string;
        documentUrl: string;
    }
    export interface IIndividualEndorsementReviewItem extends IEndorsementReviewItem {
        name: string;
        contactId: string;
        religion: string;
        howFound: string;
    }

    interface IGetEndorsementsResponse {
        endorsements: IIndividualEndorsementReviewItem[];
        groupEndorsements: IGroupEndorsementReviewItem[];
    }

    interface IEndorsementUpdateResponse {
        endorsements: any[];
        messageText: string;
    }

    interface  IConfirmationMessageRequest {
        contactId: any;
        messageText: string;
    }

    export class ApprovalsViewModel extends Peanut.ViewModelBase {
        // observables
        endorsements = ko.observableArray<IIndividualEndorsementReviewItem>([]);
        groupEndorsements = ko.observableArray<IGroupEndorsementReviewItem>([]);
        newEndorsements = ko.observable(false);
        newGroupEndorsements = ko.observable(false);
        confirmCancelMessage = ko.observable('Want to cancel this endorsement?');
        form = {
            isPerson: ko.observable(true),

            // common to both endorsements
            id: ko.observable(),
            submissionId: ko.observable(''),
            submissionDate: ko.observable(''),
            comments: ko.observable(''),
            ipAddress: ko.observable(''),
            email: ko.observable(''),
            phone: ko.observable(''),
            address1: ko.observable(''),
            address2: ko.observable(''),
            city: ko.observable(''),
            state: ko.observable(''),
            country: ko.observable(''),
            postalcode: ko.observable(''),
            mailTo: ko.observable(''),

            // individual endorsements
            contactId: ko.observable(''),
            religion: ko.observable(''),
            howFound: ko.observable(''),
            name: ko.observable(''),

            // group endorsements
            organizationName: ko.observable(''),
            contactName: ko.observable(''),
            documentUrl: ko.observable('')

        }


        private htmlEditor: Peanut.htmlEditContainer;
        private editorInitialized = false;
        public onEditorInit = () => {
            this.editorInitialized = true;
        }

        init(successFunction?: () => void) {
            let me = this;
            Peanut.logger.write('Approvals Init');

            me.application.loadResources([
                '@pnut/htmlEditContainer'
            ], () => {
                me.application.registerComponents(['@pnut/modal-confirm'], () => {
                    me.htmlEditor = new Peanut.htmlEditContainer(me);
                    me.services.executeService('GetEndorsementsForReview', null,
                        function (serviceResponse: Peanut.IServiceResponse) {
                            if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                                me.showEndorsements(serviceResponse.Value)
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

        showEndorsements = (response: IGetEndorsementsResponse) => {
            let me = this;
            me.assignGroupEndorsements(response.groupEndorsements);
            me.assignIndividualEndorsements(response.endorsements);
        }

        assignGroupEndorsements = (endorsementsList: IGroupEndorsementReviewItem[]) => {
            let me = this;
            me.groupEndorsements(endorsementsList);
            me.newGroupEndorsements(endorsementsList.length > 0);
        }
        assignIndividualEndorsements = (endorsmentList: IIndividualEndorsementReviewItem[]) => {
            let me = this;
            me.endorsements(endorsmentList);
            me.newEndorsements(endorsmentList.length > 0);
        }


        editAcknowlegement = (messageText) => {
            let me = this;
            if (this.editorInitialized) {
                this.showContent(messageText);
            } else {
                // this.htmlEditor.height = 300;
                me.htmlEditor.addOptions({height: '20em'})

                this.htmlEditor.initialize('confirmation-editor', () => {
                    this.editorInitialized = true;
                    me.showContent(messageText)
                });
            }
        }

        private content = '';
        private showContent = (content: string) => {
            this.htmlEditor.setContent(content);
            this.showModal("confirmation-message-modal")
        }

        sendConfirmationMessage = () => {
            let me = this;
            let senderName = me.form.isPerson() ? me.form.name().trim() : me.form.contactName().trim();
            if (!senderName) {
                senderName = me.form.organizationName().trim();
            }

            let request: ISystemMessageRequest = {
                content: this.htmlEditor.getContent(),
                email: me.form.email(),
                subject: 'Thanks you for your endorsement',
                toName: senderName
            }

            me.services.executeService('SendSystemMessage', request,
                function (serviceResponse: Peanut.IServiceResponse) {
                    if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                    } else {
                        let debug = serviceResponse;
                    }
                }).fail(() => {
                let trace = me.services.getErrorInformation();
            }).always(() => {
                // me.hideWaiter();
                this.hideModal('approval-form')
            });

            this.hideModal("confirmation-message-modal")
        }

        view = (item: IIndividualEndorsementReviewItem) => {
            let me = this;
            me.form.isPerson(true);
            me.form.contactId(item.contactId);
            me.form.name(item.name);
            me.form.religion(item.religion);
            me.form.howFound(item.howFound);
            me.showForm(item)
        }

        viewOrganization = (item: IGroupEndorsementReviewItem) => {
            let me = this;
            me.form.isPerson(false);
            me.form.organizationName(item.organizationName);
            me.form.contactName(item.contactName);
            me.form.documentUrl(item.documentUrl);
            me.showForm(item);
        }

        showForm = (item: IEndorsementReviewItem) => {
            let me = this;
            me.form.id(item.id);
            me.form.submissionId(item.submissionId);
            me.form.submissionDate(item.submissionDate);
            me.form.comments(item.comments);
            me.form.ipAddress(item.ipAddress);
            me.form.email(item.email);
            me.form.phone(item.phone);
            me.form.address1(item.address1);
            me.form.address2(item.address2);
            me.form.city(item.city);
            me.form.state(item.state);
            me.form.country(item.country);
            me.form.postalcode(item.postalcode);
            me.form.mailTo('mailto:' +  item.email);

            me.showModal('approval-form')

        }


        approveEndorsement = () => {
            let me = this;
            let id = this.form.id();
            let serviceName = me.form.isPerson() ?
                'ApproveIndividualEndorsement' : 'ApproveGroupEndorsement';
            me.services.executeService(serviceName, id,
                function (serviceResponse: Peanut.IServiceResponse) {
                    if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                        let response = <IEndorsementUpdateResponse>serviceResponse.Value;
                        if (me.form.isPerson()) {
                            let list = <IIndividualEndorsementReviewItem[]>response.endorsements;
                            me.assignIndividualEndorsements(list);
                        } else {
                            let list = <IGroupEndorsementReviewItem[]>response.endorsements;
                            me.assignGroupEndorsements(list);
                        }
                        if (response.messageText) {
                            me.editAcknowlegement(response.messageText);
                        }
                    } else {
                        let debug = serviceResponse;
                    }
                }).fail(() => {
                let trace = me.services.getErrorInformation();
            }).always(() => {
                // me.hideWaiter();
                this.hideModal('approval-form')
            });
        }

        cancelEndorsement = () => {
            let me = this;
            let msg = 'Are you sure you want to cancel the endorsement from ' +
              (me.form.isPerson() ? me.form.name() : me.form.organizationName()) + '?'
            me.confirmCancelMessage(msg);
            me.hideModal('approval-form')
            me.showModal('confirm-cancel-modal');
        }

        onConfirmCancel = () => {
            let me = this;
            me.hideModal('confirm-cancel-modal');
            let id = this.form.id();
            let serviceName = me.form.isPerson() ?
                'CancelEndorsement' : 'CancelGroupEndorsement';
            me.services.executeService(serviceName, id,
                function (serviceResponse: Peanut.IServiceResponse) {
                    if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                        if (me.form.isPerson()) {
                            let list = <IIndividualEndorsementReviewItem[]>serviceResponse.Value;
                            me.assignIndividualEndorsements(list);
                        } else {
                            let list = <IGroupEndorsementReviewItem[]>serviceResponse.Value;
                            me.assignGroupEndorsements(list);
                        }
                    } else {
                        let debug = serviceResponse;
                    }
                }).fail(() => {
                let trace = me.services.getErrorInformation();
            }).always(() => {
                // me.hideWaiter();
                this.hideModal('approval-form')
            });
        }
    }
}