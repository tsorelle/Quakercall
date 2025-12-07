var Mailboxes;
(function (Mailboxes) {
    class ContactFormViewModel extends Peanut.ViewModelBase {
        constructor() {
            super(...arguments);
            this.headerMessage = ko.observable('Send a Message');
            this.fromAddress = ko.observable('');
            this.fromName = ko.observable('');
            this.messageSubject = ko.observable('');
            this.messageBody = ko.observable('');
            this.formVisible = ko.observable(false);
            this.mailboxList = ko.observableArray([]);
            this.mailboxSelectSubscription = null;
            this.selectedMailbox = ko.observable(null);
            this.subjectError = ko.observable('');
            this.bodyError = ko.observable('');
            this.fromNameError = ko.observable('');
            this.fromAddressError = ko.observable('');
            this.mailboxSelectError = ko.observable('');
            this.selectRecipientCaption = ko.observable('');
            this.userIsAnonymous = ko.observable(false);
            this.getMailbox = (doneFunction) => {
                let me = this;
                me.application.hideServiceMessages();
                let request = {
                    mailbox: me.mailboxCode,
                    context: me.getVmContext()
                };
                me.services.executeService('peanut.Mailboxes::GetContactForm', request, function (serviceResponse) {
                    if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                        if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                            let response = serviceResponse.Value;
                            me.addTranslations(response.translations);
                            me.selectRecipientCaption(response.translations['mail-select-recipient']);
                            me.fromAddress(response.fromAddress);
                            me.fromName(response.fromName);
                            me.userIsAnonymous(response.fromAddress.trim() == '');
                            if (me.mailboxSelectSubscription !== null) {
                                me.mailboxSelectSubscription.dispose();
                                me.mailboxSelectSubscription = null;
                            }
                            if (response.mailboxList.length > 1) {
                                me.mailboxCode = 'all';
                                me.selectedMailbox(null);
                                me.mailboxSelectSubscription = me.selectedMailbox.subscribe(me.onMailBoxSelected);
                                me.headerMessage(response.translations['mail-header-select']);
                            }
                            else {
                                let mailbox = response.mailboxList.pop();
                                me.mailboxCode = mailbox.mailboxcode;
                                me.selectedMailbox(mailbox);
                                let test = me.selectedMailbox();
                                me.headerMessage(response.translations['mail-header-send'] + ': ' + mailbox.displaytext);
                            }
                            me.mailboxList(response.mailboxList);
                            me.formVisible(true);
                        }
                        else {
                            me.formVisible(false);
                        }
                    }
                }).fail(() => {
                    let trace = me.services.getErrorInformation();
                }).always(() => {
                    if (doneFunction) {
                        doneFunction();
                    }
                });
            };
            this.createMessage = () => {
                let me = this;
                me.mailboxSelectError('');
                me.subjectError('');
                me.bodyError('');
                me.fromAddressError('');
                me.fromNameError('');
                if (me.mailboxCode === 'all') {
                    let box = this.selectedMailbox();
                    if (!box) {
                        me.mailboxSelectError(': ' + me.translate('mail-error-recipient'));
                        return false;
                    }
                    me.mailboxCode = box.mailboxcode;
                }
                let message = {
                    toName: '',
                    mailboxCode: me.mailboxCode,
                    fromName: me.fromName(),
                    fromAddress: me.fromAddress(),
                    subject: me.messageSubject(),
                    body: me.messageBody()
                };
                let valid = true;
                if (message.fromAddress.trim() == '') {
                    me.fromAddressError(': ' + me.translate('form-error-your-email-blank'));
                    valid = false;
                }
                else {
                    let fromAddressOk = Peanut.Helper.ValidateEmail(message.fromAddress);
                    if (!fromAddressOk) {
                        me.fromAddressError(': ' + me.translate('form-error-email-invalid'));
                        valid = false;
                    }
                }
                if (message.fromName.trim() == '') {
                    me.fromNameError(': ' + me.translate('form-error-your-name-blank'));
                    valid = false;
                }
                if (message.subject.trim() == '') {
                    me.subjectError(': ' + me.translate('form-error-email-subject-blank'));
                    valid = false;
                }
                if (message.body.trim() == '') {
                    me.bodyError(': ' + me.translate('form-error-email-message-blank'));
                    valid = false;
                }
                if (valid) {
                    return message;
                }
                return null;
            };
            this.sendMessage = () => {
                let me = this;
                let message = me.createMessage();
                if (message) {
                    me.application.hideServiceMessages();
                    me.application.showWaiter(me.translate('wait-sending-message'));
                    me.services.executeService('peanut.Mailboxes::SendContactMessage', message, function (serviceResponse) {
                        if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                            me.formVisible(false);
                            me.headerMessage(me.translate('mail-thanks-message'));
                        }
                    }).fail(function () {
                        let trace = me.services.getErrorInformation();
                    }).always(function () {
                        me.application.hideWaiter();
                    });
                }
            };
            this.onMailBoxSelected = (selected) => {
                let me = this;
                let title = 'Send a Message';
                if (selected) {
                    me.headerMessage(me.translate('mail-header-send') + ':  ' + selected.displaytext);
                }
                else {
                    me.headerMessage(me.translate('mail-header-select'));
                }
            };
        }
        init(successFunction) {
            let me = this;
            Peanut.logger.write('ContactForm Init');
            me.mailboxCode = me.getRequestVar('box', 'all');
            me.showLoadWaiter();
            me.application.loadResources([
                '@pnut/ViewModelHelpers.js'
            ], () => {
                me.getMailbox(() => {
                    me.application.registerComponents(['@pkg/peanut-riddler/riddler-captcha'], () => {
                        me.application.hideWaiter();
                        me.bindDefaultSection();
                        successFunction();
                    });
                });
            });
        }
    }
    Mailboxes.ContactFormViewModel = ContactFormViewModel;
})(Mailboxes || (Mailboxes = {}));
//# sourceMappingURL=ContactFormViewModel.js.map