<?php

namespace Application\quakercall\services;

use Application\quakercall\db\repository\QcallEndorsementsRepository;
use Application\quakercall\db\repository\QcallGroupendorsementsRepository;
use Tops\services\TServiceCommand;
use Tops\sys\TConfiguration;
use Tops\sys\TWebSite;

/**
 * Service Contract
 *	Request (none)
 *   Response
 *   ==========
 *   interface IEndorsementReviewItem {
 *      id : any;
 *      submissionId: string;
 *      submissionDate: string;
 *      comments: string;
 *      ipAddress: string;
 *      email: string;
 *      phone: string;
 *      address1: string;
 *      address2: string;
 *      city: string;
 *      state: string;
 *      country: string;
 *      postalcode: string;
 *  }
 *
 *   interface IGroupEndorsementReviewItem  extends IEndorsementReviewItem {
 *      organizationName: string;
 *      contactName: string;
 *      documentUrl: string;
 *  }
 *  interface IIndividualEndorsementReviewItem extends IEndorsementReviewItem {
 *      name: string;
 *      contactId: string;
 *      religion: string;
 *      howFound: string;
 *  }
 *
 *  interface IGetEndorsementsResponse {
 *      endorsements: IIndividualEndorsementReviewItem[];
 *      groupEndorsements: IGroupEndorsementReviewItem[];
 *      filesUrl: string;
 *      messageText: string;
 *  }
 *
 ************** */


class GetEndorsementsForReviewCommand extends TServiceCommand
{
    protected function run()
    {
        $response = new \stdClass();
        $repository =new QcallEndorsementsRepository();
        $response->endorsements = $repository->getEndorsementsForApproval();
        $repository = new QcallGroupendorsementsRepository();
        $response->groupEndorsements = $repository->getGroupEndorsementsForApproval();
        $this->setReturnValue($response);
    }
}