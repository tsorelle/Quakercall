var Peanut;
(function (Peanut) {
    class JoinMeetingViewModel extends Peanut.ViewModelBase {
        constructor() {
            super(...arguments);
            this.meetingTheme = ko.observable('');
            this.meetingDate = ko.observable('');
            this.meetingTime = ko.observable('');
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
            this.notSubscribed = ko.observable(false);
            this.joinEmail = ko.observable(true);
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
            this.waiting = true;
            this.onContinue = () => {
                let me = this;
                if (me.waiting) {
                    return null;
                }
                if (!me.protector.likelyHuman()) {
                    return null;
                }
                let request = {
                    meetingId: me.meetingId(),
                    email: me.emailAddress(),
                    name: me.participantName(),
                    subscribe: me.joinEmail(),
                    action: me.action,
                };
                me.application.hideServiceMessages();
                me.application.showWaiter('Please wait...');
                me.waiting = true;
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
                                me.notSubscribed(!response.subscribed);
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
                    me.waiting = false;
                    me.protector.start();
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
                if (!this.protector.likelyHuman()) {
                    return null;
                }
                this.clearMessageForm();
                this.showModal('#message-modal');
            };
            this.onSendMessage = () => {
                let me = this;
                if (me.waiting) {
                    return null;
                }
                if (!me.protector.likelyHuman()) {
                    return null;
                }
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
                me.waiting = true;
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
                    me.waiting = false;
                    me.protector.start();
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
                me.protector = new Peanut.formProtector();
                let meetingId = me.getRequestVar('meeting');
                if (me.protector.isRapidReload()) {
                    alert('Click Ok to continue.');
                }
                me.services.executeService('GetCurrentMeeting', meetingId, function (serviceResponse) {
                    if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                        let response = serviceResponse.Value;
                        me.meetingDate(response.dateOfMeeting);
                        me.meetingTheme(response.theme);
                        me.meetingTime(response.meetingTime);
                        me.timeForMeeting(response.ready);
                        if (me.timeForMeeting() == -1) {
                            me.messageText('The meeting is not ready to start. Please check back later');
                        }
                        else {
                            if (me.timeForMeeting() == 1) {
                                me.messageText('Sorry this meeting has concluded.');
                            }
                            else {
                                me.zoomId(response.zoomMeetingId);
                                me.zoomPasscode(response.zoomPasscode);
                                me.zoomUrl(response.zoomUrl);
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
                    me.protector.start();
                    me.bindDefaultSection();
                    me.waiting = false;
                    successFunction();
                });
            });
        }
    }
    Peanut.JoinMeetingViewModel = JoinMeetingViewModel;
})(Peanut || (Peanut = {}));
//# sourceMappingURL=JoinMeetingViewModel.js.map