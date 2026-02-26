<?php 
/** 
 * Created by /tools/create-model.php 
 * Time:  2025-12-23 22:05:13
 */ 

// Deployment namespace:
namespace Application\quakercall\db\entity;

use Tops\sys\TUsStates;

class QcallContact  extends \Tops\db\TimeStampedEntity
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
    public $bounced;
    public $emailRefused;
    public $active;

    public function getDtoDataTypes()
    {
        $types = parent::getDtoDataTypes();
        $types['postedDate'] = \Tops\sys\TDataTransfer::dataTypeDateTime;
        $types['subscribed'] = \Tops\sys\TDataTransfer::dataTypeFlag;
        $types['bounced'] = \Tops\sys\TDataTransfer::dataTypeFlag;
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

    public function assignSortCode()
    {
        $current = trim( $this->sortcode ?? '');
        if ($current) {
            return;
        }
        $result = strtolower( trim($this->lastName ?? ''));
        $first = strtolower(trim($this->firstName ?? ''));
        $middle = strtolower( trim($this->middleName ?? ''));
        if ($result !== '' && $first !== '') {
            $result .= ',';
        }
        if ($first !== '')
        {
            $result .= $first;
            if ($middle !== '') {
                $result .= ' '.$middle;
            }
        }
        $this->sortcode = $result;
    }

}
