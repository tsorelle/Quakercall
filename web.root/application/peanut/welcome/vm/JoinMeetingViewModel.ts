/**
 * Created by Terry on 5/7/2017.
 */

// required for all view models:
/// <reference path='../../../../nutshell/pnut/core/ViewModelBase.ts' />
/// <reference path='../../../../nutshell/typings/knockout/index.d.ts' />

// Module
namespace Peanut {
    interface ICurrentMeetingInfo {
        id : any;
        meetingCode : string;
        dateOfMeeting : string;
        meetingTime : string;
        theme : string;
        presenter : string;
        zoomMeetingId : string;
        zoomUrl : string;
        zoomPasscode : string;
        ready : any
    }

    interface IJoinMeetingRequest {
        meetingId: string;
        email : string;
        name? : string;
        subscribe: boolean;
        action: string;
    }

    interface IJoinMeetingResponse {
        meetingAvailable: boolean;
        registered : boolean;
        message : string;
        nameError: boolean;
        emailError: boolean;
        denied : boolean;
        zoomId : string;
        zoomHref :string;
        zoomPwd: string;
        subscribed: boolean;
    }

    interface IMeetingAlertMessage {
        fromAddress : string;
        fromName : string;
        messageSubject : string;
        messageBody : string;
    }

    // JoinMeeting view model
    export class JoinMeetingViewModel  extends Peanut.ViewModelBase {
        meetingTheme = ko.observable('');
        meetingDate = ko.observable('');
        meetingTime = ko.observable('');
        timeForMeeting = ko.observable(-1);
        ready = ko.observable(false);
        emailAddress = ko.observable('');
        emailError = ko.observable(false)
        participantName = ko.observable('');
        meetingId = ko.observable('2026-01');
        zoomUrl = ko.observable('#');
        zoomId = ko.observable('');
        zoomPasscode = ko.observable('');
        // needsEmail = ko.observable(true);
        needsName = ko.observable(false);
        nameError = ko.observable(false);
        registrationConfirmed = ko.observable(false);
        fatalError = ko.observable(false);
        fatalErrorTest = ko.observable(false);
        notSubscribed = ko.observable(false);
        joinEmail = ko.observable(true);

        action = 'check';

        fromAddress = ko.observable('');
        fromName = ko.observable('');
        messageSubject = ko.observable('');
        messageBody = ko.observable('');
        subjectError = ko.observable('');
        bodyError = ko.observable('');
        fromNameError = ko.observable('');
        fromAddressError = ko.observable('');

        messageText =
            ko.observable('Please enter your email address below to join the meeting.');

        // call this funtions at end of page
        init(successFunction?: () => void) {
            console.log('Init JoinMeeting');
            let me = this;
            me.application.loadResources([
                '@pnut/ViewModelHelpers.js'
            ], () => {
                let meetingId = me.getRequestVar('meeting');
                me.services.executeService('GetCurrentMeeting',meetingId,
                    function(serviceResponse: Peanut.IServiceResponse) {
                        if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                            let response : ICurrentMeetingInfo = serviceResponse.Value;
                            me.meetingDate(response.dateOfMeeting);
                            me.meetingTheme(response.theme);
                            me.meetingTime(response.meetingTime);
                            // me.needsName(!response.registered);
                            me.timeForMeeting(response.ready)
                            if (me.timeForMeeting() == 1) {
                                me.messageText('The meeting is not ready to start. Please check back later')
                            }
                            else {
                                if (me.timeForMeeting() == -1) {
                                    me.messageText('Sorry this meeting has concluded.')
                                }
                                else {
                                    me.zoomId(response.zoomMeetingId);
                                    me.zoomPasscode(response.zoomPasscode);
                                    me.zoomUrl(response.zoomUrl);
                                    // me.ready(true);
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
                    me.bindDefaultSection();
                    successFunction();
                });

            });
        }



        onContinue = () => {
            let me = this;
            let request : IJoinMeetingRequest = {
                meetingId : me.meetingId(),
                email : me.emailAddress(),
                name: me.participantName(),
                subscribe: me.joinEmail(),
                action: me.action,
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
                                me.notSubscribed(!response.subscribed);
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

        clearMessageForm = () => {
            this.fromAddress(this.emailAddress());
            this.fromName(this.participantName());
            this.messageSubject('');
            this.messageBody('');
            this.subjectError('');
            this.bodyError('');
            this.fromNameError('');
            this.fromAddressError('');
        }
        onShowMessageForm = () => {
            this.clearMessageForm();
            this.showModal('#message-modal')
        }
        onSendMessage = () => {
            let me = this;
            let request = <IMeetingAlertMessage>{
                fromAddress: this.fromAddress().trim(),
                messageBody: this.messageBody().trim(),
                fromName: this.fromName().trim(),
                messageSubject: this.messageSubject().trim(),
            }

            this.subjectError('');
            this.bodyError('');
            this.fromNameError('');
            this.fromAddressError('');

            let ok = true;
            if (!request.fromName) {
                this.fromNameError('Your name is required');
                ok = false;
            }
            if (!request.messageBody) {
                this.bodyError('Please enter a message')
                ok = false;
            }

            if (!Peanut.Helper.ValidateEmail(request.fromAddress)) {
                this.fromAddressError('A valid email address is required')
                ok = false;
            }

            if (!request.fromAddress) {
                this.fromAddressError('Valid email address is required')
                ok = false;
            }

            if (!request.messageSubject) {
                this.subjectError('Please enter a subject')
                ok = false;
            }
            if (!ok) {
                return;
            }


            //SendAdminAlertCommand
            me.clearMessageForm();
            me.application.hideServiceMessages();
            me.application.showWaiter('Please wait...');

            me.services.executeService('SendAdminAlert', request,
                function (serviceResponse: Peanut.IServiceResponse) {
                    if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                    } else {
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

            this.hideModal("#message-modal")
        }
    }
}
