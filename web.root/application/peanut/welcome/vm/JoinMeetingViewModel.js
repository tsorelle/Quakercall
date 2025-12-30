var Peanut;
(function (Peanut) {
    class JoinMeetingViewModel extends Peanut.ViewModelBase {
        constructor() {
            super(...arguments);
            this.timeForMeeting = ko.observable(-1);
            this.ready = ko.observable(false);
            this.emailAddress = ko.observable('');
            this.emailError = ko.observable(false);
            this.participantName = ko.observable('');
            this.meetingId = ko.observable('2026-01');
            this.zoomUrl = ko.observable('#');
            this.zoomId = ko.observable('');
            this.zoomPasscode = ko.observable('');
            this.needsName = ko.observable(false);
            this.nameError = ko.observable(false);
            this.registrationConfirmed = ko.observable(false);
            this.fatalError = ko.observable(false);
            this.fatalErrorTest = ko.observable(false);
            this.action = 'check';
            this.fromAddress = ko.observable('');
            this.fromName = ko.observable('');
            this.messageSubject = ko.observable('');
            this.messageBody = ko.observable('');
            this.subjectError = ko.observable('');
            this.bodyError = ko.observable('');
            this.fromNameError = ko.observable('');
            this.fromAddressError = ko.observable('');
            this.messageText = ko.observable('Please enter your email address below to join the meeting.');
            this.onContinue = () => {
                let me = this;
                let request = {
                    meetingId: me.meetingId(),
                    email: me.emailAddress(),
                    name: me.participantName(),
                    action: me.action
                };
                me.application.hideServiceMessages();
                me.application.showWaiter('Please wait...');
                me.services.executeService('CheckMeetingRegistration', request, function (serviceResponse) {
                    if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                        let response = serviceResponse.Value;
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
                            me.action = 'done';
                        }
                        else {
                            me.nameError(response.nameError);
                            me.emailError(response.emailError);
                            me.messageText(response.message);
                            if (!(response.emailError || response.nameError)) {
                                me.messageText('To register for the meeting, enter your name and click "Continue"');
                                me.action = 'register';
                                me.needsName(true);
                            }
                        }
                    }
                    else {
                        let debug = serviceResponse;
                        me.fatalError(true);
                    }
                }).fail(function () {
                    let trace = me.services.getErrorInformation();
                    me.fatalError(true);
                }).always(() => {
                    me.hideWaiter();
                });
            };
            this.onShowError = () => {
                this.application.showError('This is an error.');
            };
            this.clearMessageForm = () => {
                this.fromAddress(this.emailAddress());
                this.fromName(this.participantName());
                this.messageSubject('');
                this.messageBody('');
                this.subjectError('');
                this.bodyError('');
                this.fromNameError('');
                this.fromAddressError('');
            };
            this.onShowMessageForm = () => {
                this.clearMessageForm();
                this.showModal('#message-modal');
            };
            this.onSendMessage = () => {
                let me = this;
                let request = {
                    fromAddress: this.fromAddress().trim(),
                    messageBody: this.messageBody().trim(),
                    fromName: this.fromName().trim(),
                    messageSubject: this.messageSubject().trim(),
                };
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
                    this.bodyError('Please enter a message');
                    ok = false;
                }
                if (!Peanut.Helper.ValidateEmail(request.fromAddress)) {
                    this.fromAddressError('A valid email address is required');
                    ok = false;
                }
                if (!request.fromAddress) {
                    this.fromAddressError('Valid email address is required');
                    ok = false;
                }
                if (!request.messageSubject) {
                    this.subjectError('Please enter a subject');
                    ok = false;
                }
                if (!ok) {
                    return;
                }
                me.clearMessageForm();
                me.application.hideServiceMessages();
                me.application.showWaiter('Please wait...');
                me.services.executeService('SendAdminAlert', request, function (serviceResponse) {
                    if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                    }
                    else {
                        let debug = serviceResponse;
                        me.fatalError(true);
                    }
                }).fail(function () {
                    let trace = me.services.getErrorInformation();
                    me.fatalError(true);
                }).always(() => {
                    me.hideWaiter();
                });
                this.hideModal("#message-modal");
            };
        }
        init(successFunction) {
            console.log('Init JoinMeeting');
            let me = this;
            me.application.loadResources([
                '@pnut/ViewModelHelpers.js'
            ], () => {
                if (me.timeForMeeting() == -1) {
                    me.messageText('The meeting is not ready to start. Please check back later');
                }
                else {
                    if (me.timeForMeeting() == 1) {
                        me.messageText('Sorry this meeting has concluded.');
                    }
                }
                me.bindDefaultSection();
                successFunction();
            });
        }
    }
    Peanut.JoinMeetingViewModel = JoinMeetingViewModel;
})(Peanut || (Peanut = {}));
//# sourceMappingURL=JoinMeetingViewModel.js.map