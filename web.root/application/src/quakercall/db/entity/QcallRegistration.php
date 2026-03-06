<?php 
/** 
 * Created by /tools/create-model.php 
 * Time:  2025-12-23 16:22:51
 */ 

// Deployment namespace:
namespace Application\quakercall\db\entity;

class QcallRegistration  extends \Tops\db\TimeStampedEntity 
{ 
    public $id;
    public $participant;
    public $contactId;
    public $meetingId;
    public $submissionDate;
    public $location;
    public $religion;
    public $affiliation;
    public $submissionId;
    public $ipAddress;
    public $confirmed;
    public $active;

    public function getDtoDataTypes()
    {
        $types = parent::getDtoDataTypes();
        $types['submissionDate'] = \Tops\sys\TDataTransfer::dataTypeDate;
        $types['confirmed'] = \Tops\sys\TDataTransfer::dataTypeFlag;
        return $types;
    }

    public function generateSubmissionId($extra='qc')
    {
        if (empty($this->submissionId)) {
            $requestTime = $_SERVER['REQUEST_TIME'] ?? '';
            $this->submissionId = $extra.$requestTime;
/*            $data = random_bytes(16);
            $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
            $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

            $this->submissionId = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));*/
        }
    }
}
