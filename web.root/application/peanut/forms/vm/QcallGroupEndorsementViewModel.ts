// required for all view models:
/// <reference path='../../../../nutshell/pnut/core/ViewModelBase.ts' />
/// <reference path='../../../../nutshell/typings/knockout/index.d.ts' />

namespace Peanut {

    export class QcallGroupEndorsementViewModel extends Peanut.ViewModelBase {
        // observables
        tab = ko.observable('form');

        form = {
            fullname : ko.observable(''),
            firstName : ko.observable(''),
            lastName : ko.observable(''),
            email : ko.observable(''),
            phone : ko.observable(''),
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



        init(successFunction?: () => void) {
            let me = this;
            Peanut.logger.write('Group endorsment Init');

            me.bindDefaultSection();
            successFunction();
        }

        submitEndorsement = () => {
            alert('submitting endorsement');
        }

        showPrivacyModal = () => {
            this.showModal('privacy-modal')
        }

    }
}