<?php 
/** 
 * Created by /tools/create-model.php 
 * Time:  2025-12-23 16:22:51
 */ 

// Deployment namespace:
namespace Application\quakercall\db\entity;

class QcallRegistration  extends \Tops\db\TimeStampedEntity 
{ 
    public $id;
    public $participant;
    public $contactId;
    public $meetingId;
    public $submissionDate;
    public $location;
    public $religion;
    public $affiliation;
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
