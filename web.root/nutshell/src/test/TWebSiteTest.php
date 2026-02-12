<?php

namespace PeanutTest\unit;

use Tops\sys\TWebSite;
use PHPUnit\Framework\TestCase;

class TWebSiteTest extends TestCase
{
    public function testExpandUrl()
    {
        $actual = TWebSite::ExpandUrl('admin/registrations');
        $this->assertNotEmpty($actual);
    }

    public function testGetBaseUrl()
    {
        $actual = TWebSite::getBaseUrl();
        $this->assertNotEmpty($actual);
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
