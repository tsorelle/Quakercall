<?php

namespace PeanutTest\unit;

use \Tops\sys\TUsStates;
use PHPUnit\Framework\TestCase;

class TUsStatesTest extends TestCase
{

    public function testGetCountryForState()
    {
        $tests = [
            'FM' => 'Federated States of Micronesia',
            'Federated States of Micronesia' => 'Federated States of Micronesia',
            'texas' => 'United States',
            'DC' => 'United States',
            'TX' => 'United States',
            'San Salvador' => '',
            'XX' => '',
            'MH' => 'Marshall Islands',
            'PW' => 'Palau',
            'Marshall Islands' => 'Marshall Islands',
            'Palau' => 'Palau'
        ];

        foreach ($tests as $state => $country) {
            $actual = TUsStates::getCountryNameForState($state);
            $this->assertEquals($country, $actual);
        }

        $expected = 'El Salvador';
        $actual = TUsStates::getCountryNameForState('San Salvador',$expected);
        $this->assertEquals($expected, $actual);
    }

    public function testConvertToAbbrevation()
    {
        $tests = [
            'South hampton' => 'South Hampton',
            'Palau' => 'PW',
            'Federated States of Micronesia' => 'FM',
            'Marshall Islands' => 'MH',
            'Texas'=>'TX',
            ' texas'=>'TX',
            'TX '=>'TX',
            '' => '',
            NULL => '',
            'Northern Mariana Islands' => 'MP',
            "Arkansas" => "AR",
            'Nebraska' => 'NE',
        ];
        foreach ($tests as $name => $expected) {
            $actual = TUsStates::convertToAbbrevation($name);
            $this->assertEquals($expected, $actual);
        }
    }

    public function testGetStateLookup()
    {
        $actual = TUsStates::getStateLookup();
        $this->assertNotEmpty($actual);
        $last = array_pop($actual);
        $this->assertEquals('District of Columbia', $last->Name);
        $this->assertEquals('DC', $last->Value);

        $actual = TUsStates::getStateLookup(TUsStates::TERRITORIES);
        $this->assertNotEmpty($actual);
        $last = array_pop($actual);
        $this->assertEquals('Northern Mariana Islands', $last->Name);
        $this->assertEquals('MP', $last->Value);

        $actual = TUsStates::getStateLookup(TUsStates::ASSOCIATED);
        $this->assertNotEmpty($actual);
        $last = array_pop($actual);
        $this->assertEquals('Palau', $last->Name);
        $this->assertEquals('PW', $last->Value);
    }

    // passed: 2/24/2026
    public function testnormalizeCountryName()
    {
        $tests = ['us',' usa','U.S.A','United States','Estados Unidos','USA','',NULL];
        foreach ($tests as $name) {
            $actual = TUsStates::normalizeCountryName($name);
            $this->assertEquals('United States', $actual);
        }

        $tests = [
        'Federated States of Micronesia' => 'Federated States of Micronesia',
        'Marshall Islands' => 'Marshall Islands',
        'Palau' => 'Palau',
        ];

        foreach ($tests as $name => $abbreviation) {
            $actual = TUsStates::normalizeCountryName($name,'');
            $this->assertEquals($actual, $name);
        }

        $tests = ['uk','Armenia','Republic of Congo'];
        foreach ($tests as $name) {
            $actual = TUsStates::normalizeCountryName($name);
            $this->assertEquals($name, $actual);
        }

        $tests = ['uk','Armenia','Republic of Congo'];
        foreach ($tests as $name) {
            $actual = TUsStates::normalizeCountryName($name);
            $this->assertEquals($name, $actual);
        }

    }


    // passed: 2/24/2026
    public function testGetFullCountryName()
    {
        $tests = [
            'us' => true,
            'United States' => true,
            'USA' => true,
            'U.S.A' => true,
            ' ' => true,
            null => true,
            'United Kingdom' => false,
            'Republic of Congo' => false,
            'EU' => false,
            'Papua' => false
        ];
        foreach ($tests as $name => $returnUSA) {
            $actual = TUsStates::getFullCountryName($name);
            if ($returnUSA) {
                $this->assertEquals('United States of America', $actual);
            }
            else {
                $this->assertEquals(trim($name), $actual);
            }
        }
    }
}
