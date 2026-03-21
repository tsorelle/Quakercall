<?php 
/** 
 * Created by /tools/create-model.php 
 * Time:  2026-01-12 02:47:18
 */ 

namespace Application\quakercall\db\entity;



class QcallPersonalendorsement  extends \Tops\db\TimeStampedEntity 
{ 
    public $id;
    public $submissionDate;
    public $contactId;
    public $firstName;
    public $lastName;
    public $email;
    public $address1;
    public $address2;
    public $city;
    public $state;
    public $country;
    public $comments;
    public $postalcode;
    public $religion;
    public $howFound;
    public $submissionId;
    public $ipAddress;
    public $active;
    public $resolved;

    public function getDtoDataTypes()
    {
        $types = parent::getDtoDataTypes();
        $types['submissionDate'] = \Tops\sys\TDataTransfer::dataTypeDate;
        $types['resolved'] = \Tops\sys\TDataTransfer::dataTypeFlag;
        return $types;
    }
}
