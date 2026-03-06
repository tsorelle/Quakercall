declare namespace QuakerCall {


    interface IQcallFormResponse {
        fullname : string;
        phone : string;
        location : string;
        email : string;
        organization : string;
        submissionId : string;
    }

    interface IQcallFormRequest {
        name : string;
        email : string;
        city : string;
        state: string;
        country: string;
        phone: string;
        postalCode: string;
        organization: string;
        religion: string;
    }

    export interface IMeetingInfo {
        id: any;
        meetingCode: string;
        dateOfMeeting: string;
        meetingTime: string;
        theme: string;
        subtitle: string;
        presenter: string;
        ready : any;
    }


    export interface IContactItem {
        id : any;
        fullname : string;
        firstName : string;
        lastName : string;
        email : string;
        phone : string;
        city : string;
        state : string;
        country : string;
        subscribed : any;
        bounced : any;
        active : any;
    }
    export interface IContact extends IContactItem{
        organization : string;
        title : string;
        address1 : string;
        address2 : string;
        sortcode : string;
        source : string;
        postedDate : string;
        importDate : string;
        emailRefused : any;
    }
}