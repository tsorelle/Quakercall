<?php 
/** 
 * Created by /tools/create-model.php 
 * Time:  2025-12-26 20:34:12
 */ 

// Deployment namespace:
namespace Application\quakercall\db\entity;


class QcallGdcustomer  extends \Tops\db\TimeStampedEntity 
{ 
    public $id;
    public $firstName;
    public $lastName;
    public $email;
    public $phone;
    public $organization;
    public $title;
    public $address1;
    public $address2;
    public $city;
    public $state;
    public $country;
    public $postalcode;
    public $fullname;
    public $sortcode;
    public $source;
    public $postedDate;
    public $importDate;
    public $subscribed;
    public $suppressed;
    public $suppressedReason;
    public $lastActivity;
    public $lastActivityDate;
    public $lastUpdate;
    public $active;

    public function getDtoDataTypes()
    {
        $types = parent::getDtoDataTypes();
        $types['postedDate'] = \Tops\sys\TDataTransfer::dataTypeDateTime;
        $types['importDate'] = \Tops\sys\TDataTransfer::dataTypeDateTime;
        $types['subscribed'] = \Tops\sys\TDataTransfer::dataTypeFlag;
        $types['suppressed'] = \Tops\sys\TDataTransfer::dataTypeFlag;
        return $types;
    }
}
