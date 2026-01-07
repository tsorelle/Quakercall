<?php 
/** 
 * Created by /tools/create-model.php 
 * Time:  2025-12-23 16:22:51
 */ 

// Deployment namespace:
namespace Application\quakercall\db\entity;

class QcallGroupendorsement  extends \Tops\db\TimeStampedEntity 
{ 
    public $id;
    public $contactId;
    public $submissionDate;
    public $typeId;
    public $organizationType;
    public $organizationName;
    public $address;
    public $contactName;
    public $phone;
    public $email;
    public $attachments;
    public $submissionId;
    public $ipAddress;
    public $approved;
    public $active;

    public function getDtoDataTypes()
    {
        $types = parent::getDtoDataTypes();
        $types['submissionDate'] = \Tops\sys\TDataTransfer::dataTypeDate;
        $types['approved'] = \Tops\sys\TDataTransfer::dataTypeFlag;
        return $types;
    }
}
