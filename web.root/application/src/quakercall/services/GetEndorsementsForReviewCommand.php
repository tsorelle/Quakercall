<?php

namespace Application\quakercall\services;

use Application\quakercall\db\repository\QcallEndorsementsRepository;
use Tops\services\TServiceCommand;

/** Service contract
 * Request none
 * Response:
 * export interface IEndorsementReviewItem {
 *      id : any;
 *      submissionId: string;
 *      submissionDate: string;
 *      contactId: string;
 *      name: string;
 *      comments: string;
 *      religion: string;
 *      howFound: string;
 *      ipAddress: string;
 *      email: string;
 *      phone: string;
 *      address1: string;
 *      address2: string;
 *      city: string;
 *      state: string;
 *      country: string;
 *      postalcode: string;
 * }
 *
 *
 */

class GetEndorsementsForReviewCommand extends TServiceCommand
{
    protected function run()
    {
        $repository =new QcallEndorsementsRepository();
        $response = $repository->getEndorsementsForApproval();
        $this->setReturnValue($response);
    }
}