<?php 
/** 
 * Created by /tools/create-model.php 
 * Time:  2025-12-25 13:59:49
 */ 
 // Deployment NS:
namespace Application\quakercall\db\repository;

use DateTime;
use \PDO;
use PDOStatement;
use Tops\db\TDatabase;
use \Tops\db\TEntityRepository;

class QcallMeetingsRepository extends \Tops\db\TEntityRepository
{
    public function getMeetingByCode(string $meetingCode)
    {
        return $this->getSingleEntity('meetingCode',[$meetingCode]);
    }

    public function meetingReady(string $meetingCode) {
        $meeting = $this->getMeetingByCode($meetingCode);
        if ($meeting) {
            $meetingDate = new DateTime($meeting->meetingDate);
            $today = new DateTime();
            if ($meetingDate < $today) {
                return -1;
            }
            if ($meetingDate > $today) {
                return +1;
            }
            return 0;

            // today fix for time
/*
            $meetingTime = $meeting->meetingTime;
            $timeParts = explode("-", $meetingTime);
            if (isset($timeParts[0]) && is_numeric($timeParts[0])) {
                $meetingTime .= sprintf('%02d:00 PM', $timeParts[0]);
                $startTime = new DateTime($meetingTime);
                $now = new DateTime('now');
                if ($now > $meetingTime) {

                }
            }
            $startTime = new dateTime($meetingTime);
*/


        }
        return false;

    }

    protected function getTableName() {
        return 'qcall_meetings';
    }

    protected function getDatabaseId() {
        return null;
    }

    protected function getClassName() {
       return 'Application\quakercall\db\entity\QcallMeeting';
    }

    protected function getFieldDefinitionList()
    {
        return array(
        'id'=>PDO::PARAM_STR,
        'meetingCode'=>PDO::PARAM_STR,
        'meetingDate'=>PDO::PARAM_STR,
        'meetingTime'=>PDO::PARAM_STR,
        'theme'=>PDO::PARAM_STR,
        'presenter'=>PDO::PARAM_STR,
        'zoomMeetingId'=>PDO::PARAM_STR,
        'zoomUrl'=>PDO::PARAM_STR,
        'zoomPasscode'=>PDO::PARAM_STR,
        'meetingType'=>PDO::PARAM_STR,
        'createdby'=>PDO::PARAM_STR,
        'createdon'=>PDO::PARAM_STR,
        'changedby'=>PDO::PARAM_STR,
        'changedon'=>PDO::PARAM_STR,
        'active'=>PDO::PARAM_STR);
    }

    public function findByDate($value)
    {
        return $this->getSingleEntity('meetingDate = ?',[$value]);
    }
    public function findByCode($value)
    {
        return $this->getSingleEntity('meetingCode = ?',[$value]);
    }
    public function findByTheme($value)
    {
        return $this->getSingleEntity('theme = ?',[$value]);
    }
}