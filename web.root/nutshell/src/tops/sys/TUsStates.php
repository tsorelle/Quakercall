<?php

namespace Tops\sys;

use Tops\sys\TLookupItem;
use Tops\sys\TNameValuePair;

class TUsStates
{
    private static $stateList = [
        // Full name → abbreviation

        // States
        'Alabama' => 'AL', 'Alaska' => 'AK', 'Arizona' => 'AZ', 'Arkansas' => 'AR',
        'California' => 'CA', 'Colorado' => 'CO', 'Connecticut' => 'CT',
        'Delaware' => 'DE', 'Florida' => 'FL', 'Georgia' => 'GA',
        'Hawaii' => 'HI', 'Idaho' => 'ID', 'Illinois' => 'IL', 'Indiana' => 'IN',
        'Iowa' => 'IA', 'Kansas' => 'KS', 'Kentucky' => 'KY', 'Louisiana' => 'LA',
        'Maine' => 'ME', 'Maryland' => 'MD', 'Massachusetts' => 'MA',
        'Michigan' => 'MI', 'Minnesota' => 'MN', 'Mississippi' => 'MS',
        'Missouri' => 'MO', 'Montana' => 'MT', 'Nebraska' => 'NE', 'Nevada' => 'NV',
        'New Hampshire' => 'NH', 'New Jersey' => 'NJ', 'New Mexico' => 'NM',
        'New York' => 'NY', 'North Carolina' => 'NC', 'North Dakota' => 'ND',
        'Ohio' => 'OH', 'Oklahoma' => 'OK', 'Oregon' => 'OR', 'Pennsylvania' => 'PA',
        'Rhode Island' => 'RI', 'South Carolina' => 'SC', 'South Dakota' => 'SD',
        'Tennessee' => 'TN', 'Texas' => 'TX', 'Utah' => 'UT', 'Vermont' => 'VT',
        'Virginia' => 'VA', 'Washington' => 'WA', 'West Virginia' => 'WV',
        'Wisconsin' => 'WI', 'Wyoming' => 'WY',

        // District
        'District of Columbia' => 'DC',

    ];

    private static array $all;

    private static $territoriesEtc = [
        // Territories & Possessions
        'Puerto Rico' => 'PR',
        'Guam' => 'GU',
        'U.S. Virgin Islands' => 'VI',
        'American Samoa' => 'AS',
        'Northern Mariana Islands' => 'MP',

        // Freely Associated States (USPS-recognized)
        'Federated States of Micronesia' => 'FM',
        'Marshall Islands' => 'MH',
        'Palau' => 'PW',
    ];

    public static function getStateList($statesOnly = false)
    {
        if ($statesOnly) {
            return self::$stateList;
        }
        if (!isset(self::$all)) {
            self::$all = array_merge(self::$stateList, self::$territoriesEtc);
        }
        return self::$all;
    }

    public static function getStateLookup($statesOnly = false)
    {
        $list = self::getStateList($statesOnly);
        return TNameValuePair::CreateArray($list);
    }

    public static function convertToAbbrevation($text): string
    {
        if ($text === null) {
            return '';
        }
        $trimmed = trim($text);
        // Normalize input for lookup
        $normalized = ucwords(strtolower($trimmed));
        $normalized = str_replace([' Of ', ' And '], [' of ', ' and '], $normalized);

        $list = self::getStateList();
        return $list[$normalized] ?? $trimmed;
    }

    public static function getFullCountryName($name, $default='United States of America'): string {
        if ($name === null) {
            return $default;
        }
        $name = trim($name);
        $cmp = strtolower(str_replace('.','',$name));
        if (empty($name) || $cmp == 'us' || $cmp == 'usa' || $cmp == 'united states') {
            return $default;
        }
        return $name;
    }

    public static function getCountryAbbreviation($name, $default='USA'): string {
        if ($name === null) {
            return $default;
        }
        $name = trim($name);
        $cmp = strtolower( str_replace('.','',$name));
        if (empty($name) || $cmp == 'us' || $cmp == 'usa'
            || $cmp == 'united states' || $cmp == 'united states of america'
            || $cmp == 'estados unidos') {
            return $default;
        }
        return $name;
    }
}