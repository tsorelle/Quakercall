<?php 
/** 
 * Created by /tools/create-model.php 
 * Time:  2025-12-23 16:22:51
 */ 

// Deployment namespace:
namespace Application\quakercall\db\entity;

use Tops\sys\TUsStates;

class QcallGroupendorsement  extends \Tops\db\TimeStampedEntity
{ 
    public $id;
    public $organizationName;
    public $typeId;
    public $submissionId;
    public $submissionDate;
    public $contactName;
    public $email;
    public $phone;
    public $address1;
    public $address2;
    public $city;
    public $state;
    public $country;
    public $postalcode;
    public $document;
    public $documentationType;
    public $comments;
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

    public function normalizeStateAndCountry() {
        $state = trim($this->state ?? '');
        $country = trim($this->country ?? '');
        $city = trim($this->city ?? '');
        if (!(empty($state) && empty($country) && empty($city))) {
            $this->state = TUsStates::convertToAbbrevation($state);
            $this->country = TUsStates::getCountryNameForState($this->state,$country);
        }
        else {
            $this->state = $state;
            $this->country = $country;
            $this->city = $city;
        }
    }
}
