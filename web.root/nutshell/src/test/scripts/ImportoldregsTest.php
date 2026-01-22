<?php

namespace PeanutTest\scripts;

use Application\quakercall\db\entity\QcallContact;
use Application\quakercall\db\entity\QcallEndorsement;
use Application\quakercall\db\entity\QcallGroupendorsement;
use Application\quakercall\db\entity\QcallMeeting;
use Application\quakercall\db\entity\QcallRegistration;
use Application\quakercall\db\repository\QcallEndorsementsRepository;
// use mysql_xdevapi\Exception;
use Application\quakercall\db\repository\QcallMeetingsRepository;
use Application\quakercall\db\repository\QcallContactsRepository;
use Application\quakercall\db\repository\QcallRegistrationsRepository;
use Application\quakercall\services\QcRegistration;
use PeanutTest\scripts\TestScript;
use Tops\sys\TCsvReader;

class ImportoldregsTest extends TestScript
{

    private function stateToAbbr(string $input): ?string
    {
        // Normalize input
        $input = trim($input);
        if ($input === '') {
            return null;
        }

        // Map of state names → abbreviations
        static $states = [
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
        ];

        // Build reverse lookup for abbreviations
        static $abbrs = null;
        if ($abbrs === null) {
            $abbrs = array_flip($states);
        }

        // If already a valid abbreviation, return it
        $upper = strtoupper($input);
        if (isset($abbrs[$upper])) {
            return $upper;
        }

        // Try matching full state name (case-insensitive)
        $normalized = ucwords(strtolower($input));
        return $states[$normalized] ?? null;
    }

    private function extractState($s)
    {
        if ($s === null) {
            return '';
        }
// Replace all punctuation with spaces
        $s = str_ireplace(['United States of America','United States','U.S.A.','U.S.A'],'USA',$s);

// Collapse multiple spaces and trim ends
        $s = preg_replace('/\p{P}+/u', ' ', $s);
        $s = preg_replace('/\s+/', ' ', trim($s));
        $words = explode(' ', $s);
        $last = strtoupper( array_pop($words));

        if ($last === 'USA' || $last === 'US') {
            $last = array_pop($words);

        }

        $last = trim($last);
        return $this->stateToAbbr($last ?? '');
    }
    private QcallRegistrationsRepository $repository;

    function stateForCity($location)
    {
        switch($location) {
            CASE 'alexandria' : return'VA';
            CASE 'ames' : return'IA';
            CASE 'amherst' : return'MA';
            CASE 'ann arbor' : return'MI';
            CASE 'annapolis' : return'MD';
            CASE 'appleton' : return'';
            CASE 'appleton' : return'';
            CASE 'arcata' : return'';
            CASE 'arlington' : return'VA';
            CASE 'asheville' : return'NC';
            CASE 'ashford' : return'';
            CASE 'auburn' : return'IA';
            CASE 'aurora' : return'CO';
            CASE 'austin' : return'TX';
            CASE 'baltimore' : return'MD';
            CASE 'baton rouge' : return'LA';
            CASE 'bellevue' : return'WA';
            CASE 'benton harbor' : return'';
            CASE 'berkeley' : return'CA';
            CASE 'bethlehem' : return'PA';
            CASE 'black mountain, nc, cherokee land' : return'NC';
            CASE 'bloomfield' : return'IN';
            CASE 'bloomington' : return'IL';
            CASE 'bloomsburg' : return'';
            CASE 'bordentown' : return'';
            CASE 'boston' : return'MA';
            CASE 'boulder' : return'CO';
            CASE 'brighton' : return'';
            CASE 'bronx' : return'NY';
            CASE 'brooklyn' : return'NY';
            CASE 'brooklyn, new york' : return'NY';
            CASE 'burliiowa' : return'IA';
            CASE 'burnsville' : return'';
            CASE 'cambridge' : return'MA';
            CASE 'carmel' : return'CA';
            CASE 'cassopolis' : return'';
            CASE 'chadds ford' : return'';
            CASE 'charlotte' : return'NC';
            CASE 'charlotte, mc' : return'NC';
            CASE 'cherry hill' : return'';
            CASE 'chester sprgs' : return'';
            CASE 'chicago' : return'IL';
            CASE 'chico' : return'CA';
            CASE 'cincinnati' : return'OH';
            CASE 'clive' : return'';
            CASE 'colby' : return'';
            CASE 'columbus' : return'OH';
            CASE 'cortland' : return'';
            CASE 'crownsville' : return'';
            CASE 'decatur' : return'IL';
            CASE 'deland' : return'';
            CASE 'denver' : return'CO';
            CASE 'dugger ind' : return'IN';
            CASE 'dunedin' : return'';
            CASE 'durango co 81301' : return'CO';
            CASE 'durham, nc 27705' : return'NC';
            CASE 'eastham' : return'';
            CASE 'easthampton' : return'';
            CASE 'easton' : return'';
            CASE 'eastsound' : return'';
            CASE 'elizabethtown' : return'';
            CASE 'essexville' : return'';
            CASE 'eugene' : return'OR';
            CASE 'fairton' : return'';
            CASE 'farmington' : return'';
            CASE 'fishers' : return'';
            CASE 'florence' : return'';
            CASE 'fort collins' : return'';
            CASE 'fort pierce' : return'';
            CASE 'fort wayne' : return'IN';
            CASE 'franklin' : return'';
            CASE 'frederick' : return'';
            CASE 'gainseville' : return'';
            CASE 'galveston' : return'';
            CASE 'glover' : return'';
            CASE 'grand junction' : return'CO';
            CASE 'greensboro' : return'NC';
            CASE 'greenville' : return'';
            CASE 'guilford' : return'NC';
            CASE 'gwynedd, zpa' : return'PA';
            CASE 'hardy' : return'';
            CASE 'harrisburg' : return'PA';
            CASE 'hemet' : return'';
            CASE 'hemlock' : return'';
            CASE 'hicksville' : return'';
            CASE 'hobart' : return'';
            CASE 'holland' : return'';
            CASE 'indianapolis' : return'IN';
            CASE 'irvine' : return'CA';
            CASE 'jamestown' : return'NC';
            CASE 'kalamazoo' : return'MI';
            CASE 'kaneohe, hawai’i' : return'HA';
            CASE 'kennett square' : return'PA';
            CASE 'klamath falls' : return'OR';
            CASE 'la jolla, ca 92037' : return'CA';
            CASE 'laporte' : return'';
            CASE 'lehighton' : return'';
            CASE 'lincoln' : return'NE';
            CASE 'linton' : return'';
            CASE 'lopez island' : return'';
            CASE 'los angeles, ca 90016' : return'';
            CASE 'louisville' : return'KY';
            CASE 'ma in the usa' : return'MA';
            CASE 'madison' : return'WO';
            CASE 'marquette' : return'IL';
            CASE 'medford' : return'OR';
            CASE 'media' : return'PA';
            CASE 'memphis' : return'TN';
            CASE 'milwaukee' : return'WI';
            CASE 'milwaukie,or 97222' : return'OR';
            CASE 'minneapolis' : return'MN';
            CASE 'minneapolis, mn (tcfm)' : return'MN';
            CASE 'modoc' : return'';
            CASE 'montague' : return'';
            CASE 'monticello' : return'VA';
            CASE 'mooresville' : return'';
            CASE 'morris plains' : return'NJ';
            CASE 'mount zion' : return'';
            CASE 'mountain grove' : return'';
            CASE 'muncie' : return'IN';
            CASE 'new hampshire' : return'NH';
            CASE 'new haven' : return'CN';
            CASE 'new jersey' : return'NJ';
            CASE 'new mexico' : return'NM';
            CASE 'new york' : return'NY';
            CASE 'new york, new york' : return'NY';
            CASE 'north carolina' : return'NC';
            CASE 'norwich' : return'CT';
            CASE 'ny 12804' : return'NY';
            CASE 'ocean view' : return'CA';
            CASE 'oklahoma city' : return'OK';
            CASE 'olympia' : return'WA';
            CASE 'orchard park' : return'';
            CASE 'owensboro' : return'';
            CASE 'palm harbor' : return'';
            CASE 'pasadena' : return'CA';
            CASE 'pelham' : return'';
            CASE 'pembroke' : return'';
            CASE 'pensacola' : return'';
            CASE 'pflugerville' : return'TX';
            CASE 'phila' : return'PA';
            CASE 'philadelphia' : return'PA';
            CASE 'philly' : return'PA';
            CASE 'pittsburgh' : return'PA';
            CASE 'plainfield' : return'NJ';
            CASE 'point reyes station' : return'';
            CASE 'port townsend' : return'WA';
            CASE 'prescott' : return'AZ';
            CASE 'princeton' : return'NJ';
            CASE 'redway' : return'';
            CASE 'reedley' : return'';
            CASE 'renton' : return'';
            CASE 'richmond' : return'VA';
            CASE 'rochester, ny  monroe.' : return'NY';
            CASE 'rockland' : return'MD';
            CASE 'rutledge' : return'';
            CASE 'saint augustine' : return'FL';
            CASE 'saint paul' : return'MN';
            CASE 'salem' : return'OR';
            CASE 'san diego' : return'CA';
            CASE 'san francisco' : return'CA';
            CASE 'san leandro' : return'CA';
            CASE 'santa cruz' : return'CA';
            CASE 'santa fe' : return'NM';
            CASE 'seattle' : return'WA';
            CASE 'sebastopol' : return'';
            CASE 'secane pa 29018' : return'PA';
            CASE 'setauket- east setauket' : return'';
            CASE 'silver spring' : return'MD';
            CASE 'silverton' : return'CO';
            CASE 'somerville' : return'MA';
            CASE 'sparks glencoe' : return'';
            CASE 'st augustine' : return'FL';
            CASE 'st petersburg' : return'FL';
            CASE 'st. augustine' : return'FL';
            CASE 'st. clair shores' : return'';
            CASE 'st. petersburg' : return'FL';
            CASE 'state college' : return'PA';
            CASE 'staunton' : return'VA';
            CASE 'tarrytown' : return'';
            CASE 'taylors falls' : return'';
            CASE 'toledo' : return'OH';
            CASE 'toms river' : return'';
            CASE 'ukiah' : return'CA';
            CASE 'va beach' : return'VA';
            CASE 'washington dc' : return'DC';
            CASE 'washington state' : return'WA';
            CASE 'washington, dc (capitol hill)' : return'DC';
            CASE 'wayne' : return'';
            CASE 'west norriton' : return'';
            CASE 'wilmington' : return'DE';
            CASE 'winston salem, north carolina' : return'NC';
            CASE 'worcester' : return'MA';
            CASE 'wynantskill' : return'';
            CASE 'yadkinville, north carolina' : return'NC';
            CASE 'zionsville' : return'';
            default: return'';
        }
    }

    public function execute()
    {
        $repository = new QcallRegistrationsRepository();
        $regs = $repository->getEntityCollection('meetingId = ?',[6]);
        foreach ($regs as $reg) {
            if (empty($reg->state)) {
                $location = trim($reg->location ?? '');
                $st = $this->stateForCity(strtolower($location));
                if (!empty($st)) {
                    print("$location in $st\n");
                    $reg->state = $st;
                    $repository->update($reg);
                    continue;
                }
/*
                $len = strlen($location);
                if ($len === 0) {
                    continue;
                }
                if ($len === 2) {
                    $state = strtoupper($location);
                }
                else {
                    $state = $this->extractState($location);
                }
                if (!empty($state)) {
                    $reg->state = $state;
                    $repository->update($reg);
                }*/
            }
        }
    }
}