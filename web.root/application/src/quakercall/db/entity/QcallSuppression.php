<?php 
/** 
 * Created by /tools/create-model.php 
 * Time:  2026-03-21 11:19:21
 */

namespace Application\quakercall\db\entity;

class QcallSuppression  extends \Tops\db\TimeStampedEntity 
{ 
    public $id;
    public $processedDate;
    public $email;
    public $reason;
    public $disposition;
    public $active;

    public function getDtoDataTypes()
    {
        $types = parent::getDtoDataTypes();
        $types['processedDate'] = \Tops\sys\TDataTransfer::dataTypeDateTime;
        return $types;
    }
}
