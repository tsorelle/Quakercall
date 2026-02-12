<?php
namespace PeanutTest\scripts;

use PeanutTest\scripts\TestScript;
use Tops\sys\TWebSite;

class WebsiteTest extends TestScript
{

    public function execute()
    {
        print (
            sprintf("Domain: %s\nBase: %s\nSite: %s\nExpand 1: %s\nExpand 2: %s\n",
                TWebSite::GetDomain(),
                TWebSite::GetBaseUrl(),
                TWebSite::GetSiteUrl(),
                TWebSite::ExpandUrl('admin/registrations'),
                TWebSite::ExpandUrl('who-we-are')

            )
        );

    }
}