/**
 * Created by Terry on 5/7/2017.
 */

// required for all view models:
/// <reference path='../../../../nutshell/pnut/core/ViewModelBase.ts' />
/// <reference path='../../../../nutshell/typings/knockout/index.d.ts' />

// Module
namespace Peanut {
    interface IJoinMeetingRequest {
        meetingId: string;
        email : string;
        name? : string;
        action: string;
    }

    interface IJoinMeetingResponse {
        registered : boolean;
        message : string;
        nameError: boolean;
        emailError: boolean;
        denied : boolean;
        zoomId : string;
        zoomHref :string;
        zoomPwd: string;
    }

    // JoinMeeting view model
    export class JoinMeetingViewModel  extends Peanut.ViewModelBase {
        ready = ko.observable(false);
        emailAddress = ko.observable('');
        emailError = ko.observable(false)
        participantName = ko.observable('');
        meetingId = ko.observable('');
        zoomUrl = ko.observable('#');
        zoomId = ko.observable('');
        zoomPasscode = ko.observable('');
        // needsEmail = ko.observable(true);
        needsName = ko.observable(false);
        nameError = ko.observable(false);
        registrationConfirmed = ko.observable(false);
        fatalError = ko.observable(false);
        fatalErrorTest = ko.observable(false);

        action = 'check';

        messageText =
            ko.observable('Please enter your email address below to join the meeting.');

        // call this funtions at end of page
        init(successFunction?: () => void) {
            console.log('Init JoinMeeting');
            let me = this;
            me.bindDefaultSection();
            successFunction();
        }



        onContinue = () => {
            let me = this;
            let request : IJoinMeetingRequest = {
                meetingId : me.meetingId(),
                email : me.emailAddress(),
                name: me.participantName(),
                action: me.action
            }

            me.application.hideServiceMessages();
            me.application.showWaiter('Please wait...');

            me.services.executeService('CheckMeetingRegistration',request,
                function(serviceResponse: Peanut.IServiceResponse) {
                    if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                        let response : IJoinMeetingResponse = serviceResponse.Value;
                        // me.needsName(!response.registered);
                        me.ready(response.registered);
                        if (response.denied) {
                            me.fatalError(true);
                            return;
                        }
                        if (response.registered) {
                            me.messageText('Click the link below, or use the meeting id and passcode to join the meeting');
                            me.zoomId(response.zoomId);
                            me.zoomPasscode(response.zoomPwd);
                            me.zoomUrl(response.zoomHref);
                            me.action='done';
                        }
                        else {
                            // me.needsName(request.action == 'check' || (!response.emailError));
                            me.nameError(response.nameError);
                            me.emailError(response.emailError);
                            me.messageText(response.message);
                            if (!(response.emailError || response.nameError)) {
                                me.messageText('To register for the meeting, enter your name and click "Continue"')
                                me.action = 'register'
                                me.needsName(true);
                            }
                        }
                    }
                    else {
                        let debug = serviceResponse;
                        me.fatalError(true);
                    }
                }
            ).fail(function () {
                let trace = me.services.getErrorInformation();
                me.fatalError(true);
            }).always(() => {
                me.hideWaiter();
            });
        };
        onShowError = () => {
            this.application.showError('This is an error.');
        };
    }
}
