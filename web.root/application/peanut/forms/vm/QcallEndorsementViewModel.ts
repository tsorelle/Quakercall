// required for all view models:
/// <reference path='../../../../nutshell/pnut/core/ViewModelBase.ts' />
/// <reference path='../../../../nutshell/typings/knockout/index.d.ts' />

namespace Peanut {

    import IQcallFormResponse = QuakerCall.IQcallFormResponse;
    import IQcallFormRequest = QuakerCall.IQcallFormRequest;

    interface IQcallEndorsementRequest extends IQcallFormRequest{
        address1: string;
        address2: string;
        howFound: string;
        comments: string;
    }

    export class QcallEndorsementViewModel extends Peanut.ViewModelBase {
        // observables
        tab = ko.observable('form');
        formWarning : HTMLElement = null;
        form = {
            fullname : ko.observable(''),
            firstName : ko.observable(''),
            lastName : ko.observable(''),
            email : ko.observable(''),
            phone : ko.observable(''),
            address1 : ko.observable(''),
            address2 : ko.observable(''),
            city : ko.observable(''),
            state : ko.observable(''),
            postalCode : ko.observable(''),
            country : ko.observable(''),
            organization : ko.observable(''),
            subscribed : ko.observable(false),
            hearAbout: ko.observable(''),
            comments: ko.observable(''),

            emailError: ko.observable(false),
            nameError: ko.observable(false),
            addressError: ko.observable(false),
            religion : ko.observable('Quaker'),
            religionOther : ko.observable('')
        }

        result = {
            fullname 	   : ko.observable(''),
            phone        : ko.observable(''),
            location     : ko.observable(''),
            email        : ko.observable(''),
            organization : ko.observable(''),
            submissionId : ko.observable(''),
            startDate: ko.observable('')
        }

        protector: Peanut.formProtector;


        init(successFunction?: () => void) {
            let me = this;
            Peanut.logger.write('Endorsement Init');

            me.application.loadResources([
                '@pnut/ViewModelHelpers.js'
            ], () => {
                me.protector = new Peanut.formProtector();
                if (me.protector.isRapidReload()) {
                    // slow down, might be attack
                    alert('Click Ok to continue.')
                }
                me.protector.start();
                me.bindDefaultSection();
                me.formWarning = document.getElementById('form-warning');
                successFunction();
            });
        }

        showFormWarning() {
            this.formWarning.classList.remove('d-none');
        }
        hideFormWarning() {
            this.formWarning.classList.add('d-none');
        }

        submitEndorsement = () => {
            let me = this;
            me.hideFormWarning();
            this.form.nameError(false);
            this.form.emailError(false);
            this.form.addressError(false);
            let valid = true;
            let request : IQcallEndorsementRequest = {
                name: me.form.fullname().trim(),
                address1: me.form.address1().trim(),
                address2: me.form.address2().trim(),
                city: me.form.city().trim(),
                state: me.form.state().trim(),
                postalCode: me.form.postalCode().trim(),
                country: me.form.country().trim(),
                email: me.form.email().trim(),
                organization: me.form.organization().trim(),
                phone: me.form.phone().trim(),
                religion: me.form.religion().trim(),
                howFound: me.form.hearAbout().trim(),
                comments: me.form.comments().trim()
            }
            if (request.religion === 'Other') {
                let other = me.form.religionOther().trim();
                if (other) {
                    request.religion = other;
                }
            }
            if ( !Peanut.Helper.ValidateEmail(request.email) ) {
                this.form.emailError(true);
                valid = false;
            }

            if (!request.name) {
                this.form.nameError(true)
                valid = false;
            }
            if (!request.state) {
                this.form.addressError(true)
                valid = false;
            }

            if (!valid) {
                me.showFormWarning();
                return;
            }

            me.services.executeService('PostEndorsement',request,
                function(serviceResponse: Peanut.IServiceResponse) {
                    if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                        let response : IQcallFormResponse = serviceResponse.Value;
                        me.result.fullname 	  (response.fullname);
                        me.result.phone       (response.phone       );
                        me.result.location    (response.location    );
                        me.result.email       (response.email       );
                        me.result.organization(response.organization);
                        me.result.submissionId(response.submissionId);
                        me.tab('thanks');
                    }
                    else {
                        let debug = serviceResponse;
                    }
                }
            ).fail(function () {
                let trace = me.services.getErrorInformation();
            }).always(() => {
                me.hideWaiter();
            });

        }


        showPrivacyModal = () => {
            this.showModal('privacy-modal')
        }

    }
}