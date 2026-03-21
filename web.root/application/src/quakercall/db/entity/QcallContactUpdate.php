<?php 
/** 
 * Created by /tools/create-model.php 
 * Time:  2026-01-05 20:09:14
 */ 

// Deployment namespace:
namespace Application\quakercall\db\entity;

class QcallContactUpdate extends \Tops\db\TimeStampedEntity
{
    public $endorsementId;
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
    public $bounced;
    public $createon;
    public $active;

    public function getDtoDataTypes()
    {
        $types = parent::getDtoDataTypes();
        $types['postedDate'] = \Tops\sys\TDataTransfer::dataTypeDateTime;
        $types['importDate'] = \Tops\sys\TDataTransfer::dataTypeDateTime;
        $types['subscribed'] = \Tops\sys\TDataTransfer::dataTypeFlag;
        $types['bounced'] = \Tops\sys\TDataTransfer::dataTypeFlag;
        $types['createon'] = \Tops\sys\TDataTransfer::dataTypeDateTime;
        return $types;
    }
}
