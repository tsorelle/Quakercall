// required for all view models:
/// <reference path='../../../../nutshell/pnut/core/ViewModelBase.ts' />
/// <reference path='../../../../nutshell/typings/knockout/index.d.ts' />

namespace Peanut {
    import IMeetingInfo = QuakerCall.IMeetingInfo;

    interface IRegistrationRequest {
        meetingId : any;
        name : string;
        email : string;
        city : string;
        state: string;
        country: string;
        phone: string;
        organization: string;
        religion: string;
    }
    interface IRegistrationResponse {
        fullname : string;
        phone : string;
        location : string;
        email : string;
        organization : string;
        submissionId : string;
    }

    interface IMeetingInfoResponse extends IMeetingInfo {
        nyStartTime: string;
        startDate: string;
        image: string;
    }
    export class QcallRegistrationViewModel extends Peanut.ViewModelBase {
        // observables
        meetingTheme = ko.observable('');
        meetingSubtitle =  ko.observable('')
        meetingDate = ko.observable('');
        meetingTime = ko.observable('');
        timeLink = ko.observable('')
        showDetails = ko.observable(false);
        tab = ko.observable('');
        imagePath = ko.observable('');
        presenterCaption = ko.observable('');
        nyStartTime = ko.observable('');
        meetingId = 0;


        form = {
            fullname : ko.observable(''),
            firstName : ko.observable(''),
            lastName : ko.observable(''),
            email : ko.observable(''),
            phone : ko.observable(''),
            city : ko.observable(''),
            state : ko.observable(''),
            country : ko.observable(''),
            organization : ko.observable(''),
            subscribed : ko.observable(false),

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

        messageText= ko.observable('');

        protector: Peanut.formProtector;

        init(successFunction?: () => void) {
            let me = this;
            Peanut.logger.write('Registration Init');
            me.application.loadResources([
                '@pnut/ViewModelHelpers.js'
            ], () => {
                me.protector = new Peanut.formProtector();
                let meetingId = me.getRequestVar('meeting');
                if (me.protector.isRapidReload()) {
                    // slow down, might be attack
                    alert('Click Ok to continue.')
                }
                me.showDetails(false);
                me.services.executeService('GetCurrentMeeting',true,
                    function(serviceResponse: Peanut.IServiceResponse) {
                        if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                            let response : IMeetingInfoResponse = serviceResponse.Value;
                            me.meetingId = response.id;
                            me.meetingDate(response.dateOfMeeting);
                            me.meetingTheme(response.theme);
                            me.meetingSubtitle(response.subtitle);
                            me.meetingTime(response.meetingTime);
                            let timelink = `https://dateful.com/time-zone-converter?t=7:00pm&d=${response.startDate}&tz2=EST-EDT-Eastern-Time`
                            me.timeLink(timelink);
                            me.nyStartTime(response.nyStartTime);
                            let caption = (response.presenter) ? 'With '+response.presenter : '';
                            me.presenterCaption(caption)
                            if (response.image) {
                                me.imagePath (response.image);
                            }
                            me.showDetails(true);
                            if (response.ready == -1) {
                                me.tab('form');
                            }
                            else {
                                if (response.ready == 1) {
                                    me.messageText('This meeting has concluded.')
                                    me.tab('done');
                                }
                                else {
                                    me.messageText('This meeting is in progress.')
                                    me.tab('inprogress')
                                }
                            }

                        }
                        else {
                            let debug = serviceResponse;
                        }
                    }
                ).fail(function () {
                    let trace = me.services.getErrorInformation();
                }).always(() => {
                    me.hideWaiter();
                    me.protector.start();
                    me.bindDefaultSection();
                    successFunction();
                });
            });

        }

        clearForm = () => {
            this.form.fullname('');
            // this.form.firstName('');
            // this.form.lastName('');
            this.form.email('');
            this.form.phone('');
            this.form.city('');
            this.form.state('');
            this.form.country('');
            this.form.subscribed(false);
        }

        submitRegistration = () => {
            let me = this;
            this.form.nameError(false);
            this.form.emailError(false);
            let valid = true;
            let request : IRegistrationRequest = {
                meetingId: me.meetingId,
                city: me.form.city().trim(),
                name: me.form.fullname().trim(),
                country: me.form.country().trim(),
                email: me.form.email().trim(),
                organization: me.form.organization().trim(),
                phone: me.form.phone().trim(),
                state: me.form.state().trim(),
                religion: me.form.religion().trim()
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

            if (!valid) {
                return;
            }

            me.services.executeService('PostMeetingRegistration',request,
                function(serviceResponse: Peanut.IServiceResponse) {
                    if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                        let response : IRegistrationResponse = serviceResponse.Value;
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


    }
}
