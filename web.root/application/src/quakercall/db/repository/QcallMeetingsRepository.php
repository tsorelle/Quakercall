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
        return $this->getSingleEntity('meetingCode=?',[$meetingCode]);
    }

    public function getMeetingsList()
    {
        $sql =
            'SELECT  id,meetingCode,meetingDate, meetingTime,theme,presenter,`meetingType` '.
            'FROM `qcall_meetings` WHERE meetingType = 1 '.
            'ORDER BY meetingDate DESC';
        $stmt = $this->executeStatement($sql);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * @return \stdClass {
     *     id, meetingCode
     *      ready
     *     meetingTime, theme, presenter, zoomMeetingId, zoomUrl, zoomPasscode,
     * }
     *
     * Ready value indicates that current time is -1 early, 0 on time, 1 concluded
     * basted on the startTime field.
     *
     * $interval establishes the timeframe for admission.  This must be expressed in MySql interval units:
     * 1 HOUR, 2 HOUR, 30 MINUTES  (not singular, something like 2 HOURS will cause an error)
     *
     * The startTime column in the qcall_meetings must be UTC, not local time.
     *
     */
    public function getCurrentMeeting($interval = '3 HOUR') {
        $sql =
            'SELECT id, meetingCode, '.
            "DATE_FORMAT( meetingDate, '%M %e, %Y') as dateOfMeeting, ".
            'meetingTime, theme, presenter, zoomMeetingId, zoomUrl, zoomPasscode, '.
            'CASE SIGN(TIMESTAMPDIFF(MINUTE, startTime,UTC_TIMESTAMP())) '.
            '	WHEN -1 THEN -1 '.
            '	WHEN 0 THEN 0 '.
            '	ELSE  '.
            '	  IF(SIGN(TIMESTAMPDIFF(HOUR,DATE_ADD(startTime, INTERVAL '.
                    $interval.'), UTC_TIMESTAMP())) > 0, 1, 0) '.
            'END AS ready '.
            'FROM qcall_meetings ORDER BY meetingDate DESC LIMIT 0,1 ';
        $stmt = $this->executeStatement($sql);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function getTestMeeting()
    {
        $sql =
            'SELECT id, meetingCode, '.
            "DATE_FORMAT( meetingDate, '%M %e, %Y') as dateOfMeeting, ".
            'meetingTime, theme, presenter, zoomMeetingId, zoomUrl, zoomPasscode, '.
            // 'SIGN(DATEDIFF(meetingDate, CURDATE())) AS ready '.
            '0 AS ready '.
            'FROM qcall_meetings ORDER BY meetingDate DESC LIMIT 0,1 ';
        $stmt = $this->executeStatement($sql);
        return $stmt->fetch(PDO::FETCH_OBJ);

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