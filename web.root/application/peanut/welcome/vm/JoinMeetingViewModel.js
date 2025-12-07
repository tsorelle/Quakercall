var Peanut;
(function (Peanut) {
    class JoinMeetingViewModel extends Peanut.ViewModelBase {
        constructor() {
            super(...arguments);
            this.ready = ko.observable(false);
            this.emailAddress = ko.observable('');
            this.participantName = ko.observable('');
            this.zoomUrl = ko.observable('#');
            this.meetingId = ko.observable('');
            this.passcode = ko.observable('');
            this.needsEmail = ko.observable(true);
            this.needsName = ko.observable(false);
            this.messageText = ko.observable('Please enter your email address below to join the meeting.');
            this.onContinue = () => {
                if (this.needsName()) {
                    this.ready(true);
                }
                else {
                    this.messageText('No registration was found for your email address. Correct the address or enter your name to register');
                    this.needsName(true);
                }
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