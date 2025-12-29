<?php 
/** 
 * Created by /tools/create-model.php 
 * Time:  2025-12-29 15:19:13
 */ 

namespace Application\quakercall\db\entity;


class QcallError  extends \Tops\db\TAbstractEntity 
{ 
    public $id;
    public $occurred;
    public $message;
    public $postdata;
    public $exception;
    public $meetingId;

    public function getDtoDataTypes()
    {
        $types = parent::getDtoDataTypes();
        $types['occurred'] = \Tops\sys\TDataTransfer::dataTypeDateTime;
        return $types;
    }
}
