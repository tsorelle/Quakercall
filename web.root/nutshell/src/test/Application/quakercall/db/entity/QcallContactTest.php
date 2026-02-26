<?php

namespace PeanutTest\unit;

use Application\quakercall\db\entity\QcallContact;
use PHPUnit\Framework\TestCase;

class QcallContactTest extends TestCase
{

    private function doStateCountryTest($expectedState, $expectedCountry, $state = '', $country = '')
    {
        $contact = new QcallContact();
        $contact->state = $state;
        $contact->country = $country;
        $contact->normalizeStateAndCountry();
        $actualState = $contact->state;
        $actualCountry = $contact->country;
        $this->assertEquals($expectedCountry, $actualCountry);
        $this->assertEquals($expectedState, $actualState);
    }
    public function testNormalizeStateAndCountry()
    {
        $this->doStateCountryTest('NC', 'United States', 'N.C.','United States');
        $this->doStateCountryTest('NC', 'United States', 'North Carolina','');
        $this->doStateCountryTest('NC', 'United States', 'North Carolina',' U. S.');
        $this->doStateCountryTest('', 'Palau', '','Palau');
        $this->doStateCountryTest('PW', 'Palau', 'PW','');
        $this->doStateCountryTest('TX', 'United States', 'texas','U.S.');
        $this->doStateCountryTest('TX', 'United States', 'tx');
        $this->doStateCountryTest('East Anglia', 'United Kingdom', 'East Anglia','United Kingdom');


    }

    private function doSortCodeTest($expected, $last,$first='') {
        $contact = new QcallContact();
        $contact->firstName = $first;
        $contact->lastName = $last;

        // $contact->middleName = $middle;

        $contact->assignSortCode();
        $this->assertEquals($expected, $contact->sortcode);

    }
    public function testAssignSortCode()
    {
        $this->doSortCodeTest('sorelle,terry','SoRelle','Terry');
        // middle name not used in QCall
        // $this->doSortCodeTest('sorelle,terry layton','SoRelle','Terry',"Layton");
        $this->doSortCodeTest('sorelle','SoRelle');
        $this->doSortCodeTest('terry','','Terry');
    }
}
