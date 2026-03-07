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

    private function doSortCodeTest($expected, $last,$first='',$middle = null) {
        $contact = new QcallContact();
        $contact->firstName = $first;
        $contact->lastName = $last;
        $contact->middleName = $middle;
        $contact->assignSortCode();
        $this->assertEquals($expected, $contact->sortcode);
    }
    public function testAssignSortCode()
    {
        $this->doSortCodeTest('','','');
        $this->doSortCodeTest('menchen,hl','Menchen','H.L.');
        $this->doSortCodeTest('menchen,hl,harvey','Menchen','H.L.','Harvey');
        $this->doSortCodeTest('sorelle,terry,layton','SoRelle','Terry', 'Layton');
        $this->doSortCodeTest('sorelle,terry','SoRelle','Terry');
        $this->doSortCodeTest('sorelle,terry','SoRelle','Terry','');
        $this->doSortCodeTest('sorelle,terry,layton','SoRelle','Terry',"Layton");
        $this->doSortCodeTest('sorelle','SoRelle');
        $this->doSortCodeTest('terry','','Terry');
        $this->doSortCodeTest('layton','','','Layton');
        $this->doSortCodeTest( 'sorelle,terry,l','SoRelle','Terry','L.');

    }


    private function AssignNamesTest(
        $expectedFirst, $expectedLast, $expectedMiddle, $expectedFull, $expectedSort,
        $lastOrFull = '', $firstName=null, $lastName = null, $middleName = null ) {
        $contact = new QcallContact();
        $contact->assignNames($lastOrFull,$firstName, $lastName, $middleName);
        $this->assertEquals($expectedFirst, $contact->firstName);
        $this->assertEquals($expectedLast, $contact->lastName);
        $this->assertEquals($expectedMiddle, $contact->middleName);
        $this->assertEquals($expectedFull, $contact->fullname);
    }
    public function testAssignName()
    {
        $this->AssignNamesTest(
            'Terry',
            'SoRelle',
            '',
            'Terry SoRelle',
            'sorelle,terry',

            'Terry SoRelle'
        );

        $this->AssignNamesTest(
            'Terry',
            'SoRelle',
            'Layton',
            'Terry Layton SoRelle',
            'sorelle,terry,layton',

            'Terry Layton SoRelle'
        );
        $this->AssignNamesTest(
            'Terry',
            'SoRelle',
            'Layton',
            'Mr. Terry Layton SoRelle Jr.',
            'sorelle,terry,layton',

            'Mr. Terry Layton SoRelle Jr.'
        );

        $this->AssignNamesTest(
            'Terry',
            'SoRelle',
            '',
            'Terry SoRelle',
            'sorelle,terry',

            'SoRelle',
            'Terry'
        );

        $this->AssignNamesTest(
            'Terry',
            'SoRelle',
            '',
            'Terry SoRelle',
            'sorelle,terry',

            'SoRelle',
            'Terry'
        );

    }
}
