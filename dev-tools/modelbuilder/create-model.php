<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 8/31/2017
 * Time: 8:02 AM
 */
$projectFileRoot =   str_replace('\\','/', realpath(__DIR__.'/../../web.root')).'/';
$topsSysRoot = $projectFileRoot."nutshell/src/tops/sys";
include_once $topsSysRoot.'/TPath.php';
\Tops\sys\TPath::Initialize($projectFileRoot,'application/config');
include_once $projectFileRoot.'application/config/peanut-bootstrap.php';
\Peanut\Bootstrap::initialize($projectFileRoot);
$inifile = __DIR__."/modelbuilder-cms.ini";
$config = parse_ini_file(__DIR__."/modelbuilder-cms.ini",true);
\Tops\db\TModelBuilder::Build($config,__DIR__);
