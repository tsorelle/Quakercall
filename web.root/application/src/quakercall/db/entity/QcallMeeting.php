<?php 
/** 
 * Created by /tools/create-model.php 
 * Time:  2025-12-25 13:59:49
 */ 

// Deployment namespace: namespace
namespace Application\quakercall\db\entity;

class QcallMeeting  extends \Tops\db\TimeStampedEntity 
{ 
    public $id;
    public $meetingCode;
    public $meetingDate;
    public $meetingTime;
    public $theme;
    public $presenter;
    public $zoomMeetingId;
    public $zoomUrl;
    public $zoomPasscode;
    public $meetingType;
    public $active;

    public function getDtoDataTypes()
    {
        $types = parent::getDtoDataTypes();
        $types['meetingDate'] = \Tops\sys\TDataTransfer::dataTypeDate;
        return $types;
    }
}
