var Peanut;
(function (Peanut) {
    class JoinMeetingViewModel extends Peanut.ViewModelBase {
        constructor() {
            super(...arguments);
            this.ready = ko.observable(false);
            this.emailAddress = ko.observable('');
            this.emailError = ko.observable(false);
            this.participantName = ko.observable('');
            this.meetingId = ko.observable('');
            this.zoomUrl = ko.observable('#');
            this.zoomId = ko.observable('');
            this.zoomPasscode = ko.observable('');
            this.needsName = ko.observable(false);
            this.nameError = ko.observable(false);
            this.registrationConfirmed = ko.observable(false);
            this.fatalError = ko.observable(false);
            this.fatalErrorTest = ko.observable(false);
            this.action = 'check';
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
        }
        init(successFunction) {
            console.log('Init JoinMeeting');
            let me = this;
            me.bindDefaultSection();
            successFunction();
        }
    }
    Peanut.JoinMeetingViewModel = JoinMeetingViewModel;
})(Peanut || (Peanut = {}));
//# sourceMappingURL=JoinMeetingViewModel.js.map