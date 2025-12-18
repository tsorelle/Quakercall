<?php
$root = $_SERVER['DOCUMENT_ROOT'];
// include  $root.'/application/config/peanut-bootstrap.php';
print "<h1>Registration</h1><h2>";
$result = \Application\quakercall\services\JotFormManager::processForm();
print "Result = $result</h2>";
// print class_exists('Application\quakercall\services\JotFormManager') ? 'Success</h2>' : 'Failed</h2>';