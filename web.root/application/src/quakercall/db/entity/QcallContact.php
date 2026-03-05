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
    function stripTitles(string $name): string
    {
        // Titles to remove (prefixes and suffixes)
        $titles = [
            'mr','mrs','ms','miss','mx',
            'dr','prof','rev','fr',
            'sr','sra','jr',
            'ii','iii','iv'
        ];

        // Build regex that matches titles with optional trailing period
        // Example: \b(?:mr\.?|mrs\.?|dr\.?)\b
        $parts = array_map(fn($t) => $t . '\.?', $titles);
        $pattern = '/\b(?:' . implode('|', $parts) . ')\b/i';

        // Remove titles but leave other periods intact
        $clean = preg_replace($pattern, '', $name);

        // Collapse extra whitespace
        $clean = preg_replace('/\s+/u', ' ', $clean);
        // Remove leading punctuation/whitespace
        $clean = TStrings::TrimStart($clean);
        // Remove trailing punctuation/whitespace
        return TStrings::TrimEnd($clean);
    }


    public function splitName(string $fullName)
    {
        $simpleName = $this->stripTitles($fullName);
        $parts = explode(' ', $simpleName);
        $this->lastName = array_pop($parts);
        $this->firstName = count($parts) > 0 ? $parts[0] : '';
    }


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
    public function assignNames($lastOrFull,$first = null,$last = null) : bool {
        if ($lastOrFull == null) {
            // nothing is assigned
            return false;
        }

        $lastOrFull = TStrings::TrimStart($lastOrFull);
        if (TStrings::TrimEnd($lastOrFull) == '') {
            return false;
        }

        if ($last !== null) {
            $this->lastName = $last;
            $this->firstName = $first;
            $this->fullname = $lastOrFull;
        }
        else if ($first !== null) {
                $this->firstName = $first;
                $this->lastName = $lastOrFull;
                $this->fullname = "$first $lastOrFull";
        }
        else {
            $simpleName = $this->stripTitles($lastOrFull);
            $parts = explode(' ', $simpleName);
            $this->lastName = array_pop($parts);
            $this->firstName = count($parts) > 0 ? $parts[0] : '';
            $this->fullname = $lastOrFull;
        }
        if (empty($this->firstName)) {
            $this->sortcode = $this->lastName;
        }
        else {
            $this->sortcode = "$this->lastName,$this->firstName";
        }
        return true;
    }
}
