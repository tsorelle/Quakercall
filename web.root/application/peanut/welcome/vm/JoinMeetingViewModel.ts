/**
 * Created by Terry on 5/7/2017.
 */

// required for all view models:
/// <reference path='../../../../nutshell/pnut/core/ViewModelBase.ts' />
/// <reference path='../../../../nutshell/typings/knockout/index.d.ts' />

// Module
namespace Peanut {
    interface IJoinMeetingRequest {
        email : string;
        name? : string;
        action: string;
    }

    interface IJoinMeetingResponse {
        registered : boolean;
        error : string;
    }

    // JoinMeeting view model
    export class JoinMeetingViewModel  extends Peanut.ViewModelBase {
        ready = ko.observable(false);
        emailAddress = ko.observable('');
        participantName = ko.observable('');
        zoomUrl = ko.observable('#');
        meetingId = ko.observable('');
        passcode = ko.observable('');
        needsEmail = ko.observable(true);
        needsName = ko.observable(false);
        registrationConfirmed = ko.observable(false);

        action = 'checkregistration';

        messageText =
            ko.observable('Please enter your email address below to join the meeting.');

        // call this funtions at end of page
        init(successFunction?: () => void) {
            console.log('Init JoinMeeting');
            let me = this;
/*
            me.hideElement('top-navbar');
            me.hideElement('breadcrumb-menu');
            me.hideElement('site-footer');
*/
            me.bindDefaultSection();
            successFunction();
        }



        onContinue = () => {
            if (this.needsName()) {
                this.ready(true)
            }
            else {
                this.messageText('No registration was found for your email address. Correct the address or enter your name to register')
                this.needsName(true);
            }
        };
        onShowError = () => {
            this.application.showError('This is an error.');
        };
    }
}
