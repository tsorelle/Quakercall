<?php

namespace PeanutTest\unit;

use \Tops\sys\TUsStates;
use PHPUnit\Framework\TestCase;

class TUsStatesTest extends TestCase
{

    public function testConvertToAbbrevation()
    {
        $tests = [
            'Federated States of Micronesia' => 'FM',
            'Marshall Islands' => 'MH',
            'Texas'=>'TX',
            ' texas'=>'TX',
            'TX '=>'TX',
            '' => '',
            NULL => '',
            'South hampton' => 'South hampton',
            'Northern Mariana Islands' => 'MP',
            "Arkansas" => "AR",
            'Nebraska' => 'NE',
        ];
        foreach ($tests as $name => $expected) {
            $actual = TUsStates::convertToAbbrevation($name);
            $this->assertEquals($expected, $actual);
        }
    }

    public function testGetCountryAbbreviation()
    {
        $tests = ['us',' usa','U.S.A','United States','Estados Unidos','usa','',NULL];
        foreach ($tests as $name) {
            $actual = TUsStates::getCountryAbbreviation($name);
            $this->assertEquals('USA', $actual);
        }

        foreach ($tests as $name) {
            $actual = TUsStates::getCountryAbbreviation($name,'');
            $this->assertEquals('', $actual);
        }
        $tests = ['uk','Armenia','Republic of Congo'];
        foreach ($tests as $name) {
            $actual = TUsStates::getCountryAbbreviation($name);
            $this->assertEquals($name, $actual);
        }

    }

    public function testGetStateLookup()
    {
        $actual = TUsStates::getStateLookup();
        $this->assertNotEmpty($actual);
        $last = array_pop($actual);
        $this->assertEquals('Palau', $last->Name);
        $this->assertEquals('PW', $last->Value);

        $actual = TUsStates::getStateLookup(true);
        $this->assertNotEmpty($actual);
        $last = array_pop($actual);
        $this->assertEquals('District of Columbia', $last->Name);
        $this->assertEquals('DC', $last->Value);
    }

    public function testGetFullCountryName()
    {
        $tests = [
            'us' => true,
            'usa' => true,
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
