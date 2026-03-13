// required for all view models:
/// <reference path='../../../../nutshell/pnut/core/ViewModelBase.ts' />
/// <reference path='../../../../nutshell/typings/knockout/index.d.ts' />

namespace Peanut {

    interface IGroupEndorsementUpdateRequest {
        organizationName : string;
        typeId : any;
        contactName : string;
        email : string;
        phone : string;
        address1 : string;
        address2 : string;
        city : string;
        state : string;
        country : string;
        postalcode : string;
        documentationType : string;
        documentUrl? : string;
    }
    interface IGroupEndorsement extends IGroupEndorsementUpdateRequest {
        id : any;
        ipAddress : string;
        document : string;
        submissionId : string;
        submissionDate : string;
        approved : any;
        active : any;
    }

    export class QcallGroupEndorsementViewModel extends Peanut.ViewModelBase {
        // observables
        tab = ko.observable('form');

        formWarning : HTMLElement = null;
        form = {
            organizationName: ko.observable(''),
            typeId : ko.observable('meeting'),
            contactName : ko.observable(''),
            email : ko.observable(''),
            phone : ko.observable(''),
            address1 : ko.observable(''),
            address2 : ko.observable(''),
            city : ko.observable(''),
            state : ko.observable(''),
            postalCode : ko.observable(''),
            country : ko.observable(''),
            documentationType : ko.observable('upload'),
            documentUrl : ko.observable(''),


            emailError: ko.observable(false),
            nameError: ko.observable(false),
            addressError: ko.observable(false),
            contactNameError: ko.observable(false),
            documentError: ko.observable(false),
            documentationUrlError : ko.observable(false),
            phoneError: ko.observable(false),
        }

        result = {
            organizationName: ko.observable(''),
            contactName 	   : ko.observable(''),
            location     : ko.observable(''),
            phone        : ko.observable(''),
            email        : ko.observable(''),
            submissionId : ko.observable(''),
        }



        init(successFunction?: () => void) {
            let me = this;
            Peanut.logger.write('Group endorsment Init');
            me.application.loadResources([
                '@pnut/ViewModelHelpers.js'
            ], () => {
                me.bindDefaultSection();
                me.formWarning = document.getElementById('form-warning');
                successFunction();
            });
        }

        showPrivacyModal = () => {
            this.showModal('privacy-modal')
        }

        getFilesForUpload() {
            let files = null;
            files = Peanut.Helper.getSelectedFiles('#documentFile');
            if (!files) {
                return false;
            }
            return files;
        }

        clearErrorMessages() {
            this.form.emailError(false);
            this.form.nameError(false);
            this.form.addressError(false);
            this.form.contactNameError(false);
            this.form.documentError(false);
            this.form.documentationUrlError(false);
            this.form.phoneError(false);
        }

        validateForm() {
            let me = this;
            me.clearErrorMessages();
            let valid = true;
            let request : IGroupEndorsementUpdateRequest = {
                organizationName: me.form.organizationName().trim(),
                contactName: me.form.contactName().trim(),
                address1: me.form.address1().trim(),
                address2: me.form.address2().trim(),
                city: me.form.city().trim(),
                state: me.form.state().trim(),
                country: me.form.country().trim(),
                email: me.form.email().trim(),
                phone: me.form.phone().trim(),
                postalcode: me.form.postalCode().trim(),
                typeId: me.form.typeId(),
                documentationType: this.form.documentationType(),
            }

            if (request.documentationType === 'url') {
                let url = this.form.documentUrl().trim();
                if (url == '') {
                    this.form.documentError(true);
                    valid = false;
                }
                else {
                    if (Peanut.Helper.isValidUrl(url)) {
                        request.documentUrl = url;
                    } else {
                        this.form.documentError(true);
                        this.form.documentationUrlError(true);
                        valid = false;
                    }
                }
            }

            if (request.organizationName === '') {
                this.form.nameError(true);
                valid = false;
            }
            if (request.contactName === '') {
                this.form.contactNameError(true);
                valid = false;
            }
            if (request.address1 === '' || request.city === '' || request.state === '' || request.country === '' || request.postalcode === '') {
               this.form.addressError(true);
               valid = false;
            }

            if (!Peanut.Helper.ValidateEmail(request.email)) {
                this.form.emailError(true);
                valid = false;
            }


            if (!valid) {
                me.showFormWarning();
                return false;
            }

            return request;
        }

        submitEndorsement = () => {
            let me = this;
            let request = me.validateForm();
            let files = null;
            if (this.form.documentationType() === 'upload') {
                files = this.getFilesForUpload();
                if (!files.length) {
                    this.form.documentError(true);
                    me.showFormWarning();
                    return false;
                }
            }
            if (request === false) {
                return;
            }

            me.services.postForm( 'PostGroupEndorsement', request, files, null,
                function (serviceResponse: Peanut.IServiceResponse) {
                    if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                        let response = serviceResponse.Value;
                        me.result.organizationName(response.organizationName);
                        me.result.contactName(response.contactName);
                        me.result.location(response.location);
                        me.result.phone(response.phone);
                        me.result.email(response.email);
                        me.result.submissionId(response.submissionId);
                        me.tab('thanks');
                    }
                    else {
                   }
                }).fail(() => {
                let trace = me.services.getErrorInformation();
            }).always(() => {
                me.application.hideWaiter();
            });



        }

        showFormWarning() {
            this.formWarning.classList.remove('d-none');
        }
        hideFormWarning() {
            this.formWarning.classList.add('d-none');
        }

    }
}