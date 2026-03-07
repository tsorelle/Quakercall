<?php 
/** 
 * Created by /tools/create-model.php 
 * Time:  2025-12-23 22:05:13
 */ 

// Deployment namespace:
namespace Application\quakercall\db\entity;

use Tops\sys\TStrings;
use Tops\sys\TUsStates;

class QcallContact  extends \Tops\db\TimeStampedEntity
{ 
    public $id;
    public $firstName;
    public $lastName;
    public $middleName;
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
        $result = TStrings::StripPunctuation($this->lastName);
        $first = TStrings::StripPunctuation($this->firstName);
        $middle = TStrings::StripPunctuation($this->middleName);

        if ($result !== '' && $first !== '') {
            $result .= ',';
        }
        if ($first !== '')
        {
            $result .= $first;
            if ($middle !== '') {
                $result .= ',';
            }
        }
        $this->sortcode = $result.$middle;;
    }

    public function getLocation() {
        $result = $this->city;
        $state = trim($this->state ?? '');
        $country = trim($this->country ?? '');

        if ($result !== '' && $state !== '') {
            $result .= ', ';
        }
        $result .= $state;
        if ($result !== '' && $country !== '') {
            $result .= ' '.$country;
        }

        return $result;
    }

    /**
     * Remove common title prefixes/suffixes from a name string,
     * but preserve middle initials and other legitimate periods.
     */




    /**
     * In this contact system use first and last names only for sort and search purposes,
     * and for backward compatibility and compatibility with external systems (e.g. Godaddy Marketing)
     * that require it.
     *
     * The fullName property should be preferred for display purposes. The sortCode property may be used for sorting
     * in unusual cases but is mostly there for backward compatibility.
     *
     * This routine can generate values for name fields depending on the names provided in the parameters:
     * Examples:
     *  assignNames('Terry SoRelle');  Assigns the fullName and parses out firstName and lastName
     *  assignNames('SoRelle','Terry'); Assigns firstName and lastName, then compiles the fullname.
     *  assignNames('SoRelle','Terry','Mr. Terry SoRelle and family');  Assigns the three fields specfically no generated names.
     *  In all cases sortCode is derived from firstName and lastName.
     *
     * @param $lastOrFull
     * @param $first
     * @param $last
     * @return bool  false if no input, otherwise true
     */
    public function assignNames($lastOrFull,$first = null,$last = null, $middle = null) : bool {
        if ($lastOrFull == null) {
            // nothing is assigned
            return false;
        }

        $lastOrFull = TStrings::TrimStart($lastOrFull);
        if (TStrings::TrimEnd($lastOrFull) == '') {
            return false;
        }

        $this->middleName = trim($middle ?? '');

        if ($last !== null) {
            $this->lastName = $last;
            $this->firstName = $first;
            $this->fullname = $lastOrFull;
        }
        else if ($first !== null) {
                $this->firstName = $first;
                $this->lastName = $lastOrFull;
                $this->fullname = TStrings::ConcatName($first,$lastOrFull,$this->middleName);
        }
        else {
            // $simpleName = TStrings::stripTitles($lastOrFull);
            $parsed = TStrings::SplitName($lastOrFull);
            $this->lastName = $parsed->lastName;
            $this->firstName = $parsed->firstName;
            $this->middleName = $parsed->middleName;
            $this->fullname = $lastOrFull;
        }
        $this->assignSortCode();
        return true;
    }
}
