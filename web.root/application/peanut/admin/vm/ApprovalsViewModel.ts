/// <reference path='../../../../nutshell/pnut/core/ViewModelBase.ts' />
/// <reference path='../../../../nutshell/typings/knockout/index.d.ts' />
/// <reference path='../../../../nutshell/pnut/js/htmlEditContainer.ts' />

namespace Peanut {

    export interface IEndorsementReviewItem {
        id : any;
        submissionId: string;
        submissionDate: string;
        contactId: string;
        name: string;
        comments: string;
        religion: string;
        howFound: string;
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

    interface IEndorsementUpdateResponse {
        endorsements: IEndorsementReviewItem[];
        messageText: string;
    }

    interface  IConfirmationMessageRequest {
        contactId: any;
        messageText: string;
    }

    export class ApprovalsViewModel extends Peanut.ViewModelBase {
        // observables
        endorsements = ko.observableArray<IEndorsementReviewItem>([]);
        newEndorsements = ko.observable(false);
        form = {
            id : ko.observable(),
            submissionId: ko.observable(''),
            submissionDate: ko.observable(''),
            contactId: ko.observable(''),
            name: ko.observable(''),
            comments: ko.observable(''),
            religion: ko.observable(''),
            howFound: ko.observable(''),
            ipAddress: ko.observable(''),
            email: ko.observable(''),
            phone: ko.observable(''),
            address1: ko.observable(''),
            address2: ko.observable(''),
            city: ko.observable(''),
            state: ko.observable(''),
            country: ko.observable(''),
            postalcode: ko.observable('')
        }

        private htmlEditor : Peanut.htmlEditContainer;
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
                me.htmlEditor =  new Peanut.htmlEditContainer(me);
                me.services.executeService('GetEndorsementsForReview',null,
                    function(serviceResponse: Peanut.IServiceResponse) {
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
        }

        showEndorsements = (endorsements: IEndorsementReviewItem[]) => {
            let me = this;
            me.endorsements(endorsements);
            me.newEndorsements(endorsements.length > 0);

        }

        editAcknowlegement = (messageText) => {
            let me = this;
            if (this.editorInitialized) {
                this.showContent(messageText);
            }
            else {
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
            let request : IConfirmationMessageRequest = {
                contactId: this.form.contactId(),
                messageText: this.htmlEditor.getContent()
            }

            me.services.executeService('SendEndorserAcknowledgement',request,
                function(serviceResponse: Peanut.IServiceResponse) {
                    if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                        let response = <IEndorsementUpdateResponse>serviceResponse.Value;
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



        view = (item: IEndorsementReviewItem) => {
            // alert("View "+ item.name)
            this.form.id             (item.id            );
            this.form.submissionId   (item.submissionId  );
            this.form.submissionDate (item.submissionDate);
            this.form.contactId      (item.contactId     );
            this.form.name           (item.name          );
            this.form.comments       (item.comments      );
            this.form.religion       (item.religion      );
            this.form.howFound       (item.howFound      );
            this.form.ipAddress      (item.ipAddress     );
            this.form.email          (item.email         );
            this.form.phone          (item.phone         );
            this.form.address1       (item.address1      );
            this.form.address2       (item.address2      );
            this.form.city           (item.city          );
            this.form.state          (item.state         );
            this.form.country        (item.country       );
            this.form.postalcode     (item.postalcode    );

            this.showModal('approval-form')
        }

        approve = () => {
            let me = this;
            let id = this.form.id();
            me.services.executeService('ApproveEndorsement',id,
                function(serviceResponse: Peanut.IServiceResponse) {
                    if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                        let response = <IEndorsementUpdateResponse>serviceResponse.Value;
                        me.showEndorsements(response.endorsements);
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
    }
}