<?php

namespace PeanutTest\unit;

use Tops\sys\TWebSite;
use PHPUnit\Framework\TestCase;


/**
 *  These tests are only fully valid in web context
 *  Run browser test: /peanut/test/website to confirm
 */

class TWebSiteTest extends TestCase
{
    public function testExpandUrl()
    {
        $actual = TWebSite::ExpandUrl('admin/registrations');
        // only works in web context unit test just ensures no exception and some value returned
        $this->assertNotEmpty($actual);
    }

    public function testGetBaseUrl()
    {
        $actual = TWebSite::getBaseUrl();
        // only works in web context unit test just ensures no exception
        $this->assertTrue(true);
    }


    /*    public function testAppendRequestParams()
        {

        }

        public function testSetBaseUrl()
        {

        }

        public function testGetEnvironmentName()
        {

        }


        public function testGetSiteUrl()
        {

        }

        public function testGetDomain()
        {

        }*/

}
