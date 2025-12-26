<?php 
/** 
 * Created by /tools/create-model.php 
 * Time:  2025-12-23 16:22:51
 */ 

// Deployment namespace:
namespace Application\quakercall\db\entity;

class QcallEndorsement  extends \Tops\db\TimeStampedEntity 
{ 
    public $id;
    public $submissionDate;
    public $name;
    public $email;
    public $address;
    public $comments;
    public $endorserType;
    public $howFound;
    public $submissionId;
    public $ipAddress;
    public $active;

    public function getDtoDataTypes()
    {
        $types = parent::getDtoDataTypes();
        $types['submissionDate'] = \Tops\sys\TDataTransfer::dataTypeDate;
        return $types;
    }
}
