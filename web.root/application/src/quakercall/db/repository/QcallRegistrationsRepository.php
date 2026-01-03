<?php 
/** 
 * Created by /tools/create-model.php 
 * Time:  2025-12-23 16:22:51
 */ 
 // Deployment NS:
namespace Application\quakercall\db\repository;

use \PDO;
use PDOStatement;
use Tops\db\TDatabase;
use \Tops\db\TEntityRepository;

class QcallRegistrationsRepository extends \Tops\db\TEntityRepository
{
    public function getRegistrationList(mixed $meetingId) : array
    {
        $sql =
            'SELECT id,participant,contactId,meetingId, submissionDate, '.
            'location,religion,affiliation, submissionId, '.
            "IF(confirmed=1,'Yes','No') AS confirmed ".
            'FROM `qcall_registrations` WHERE meetingId = ? '.
            'ORDER BY `submissionDate` DESC';
        $stmt = $this->executeStatement($sql, [$meetingId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function isRegistered(mixed $meetingId, string $email) : bool
    {
        $sql =
            'SELECT COUNT(*) '.
            'FROM qcall_registrations r '.
            'JOIN qcall_contacts c ON r.contactId = c.id '.
            'WHERE r.meetingId = ? AND c.email = ?';

        $stmt = $this->executeStatement($sql,[$meetingId, $email]);
        $result = $stmt->fetch();
        $count = (empty($result) ?  0 : $result[0]);
        return $count > 0;
    }
    public function confirm($meetingId, string $email, $participantName=null)
    {
        $params = [$meetingId, $email];
        $sql =
            'UPDATE qcall_registrations r '.
            'JOIN qcall_contacts c ON r.contactId = c.id '.
            "SET confirmed = 1, r.changedOn = NOW(), r.changedBy = 'system' ".
            'WHERE r.meetingId=? AND c.email=? ';
        if ($participantName != null) {
            $sql .= ' AND r.participantName=?';
            $params = [$meetingId, $email,$participantName];
        }
        $stmt = $this->executeStatement($sql, $params);
        return $stmt->rowCount() > 0;
    }

    protected function getTableName() {
        return 'qcall_registrations';
    }

    protected function getDatabaseId() {
        return null;
    }

    protected function getClassName() {
       return 'Application\quakercall\db\entity\QcallRegistration';
    }

    protected function getFieldDefinitionList()
    {
        return array(
        'id'=>PDO::PARAM_INT,
        'participant'=>PDO::PARAM_STR,
        'contactId'=>PDO::PARAM_INT,
        'meetingId'=>PDO::PARAM_INT,
        'submissionDate'=>PDO::PARAM_STR,
        'location'=>PDO::PARAM_STR,
        'religion'=>PDO::PARAM_STR,
        'affiliation'=>PDO::PARAM_STR,
        'submissionId'=>PDO::PARAM_STR,
        'ipAddress'=>PDO::PARAM_STR,
        'createdby'=>PDO::PARAM_STR,
        'createdon'=>PDO::PARAM_STR,
        'changedby'=>PDO::PARAM_STR,
        'changedon'=>PDO::PARAM_STR,
        'confirmed'=>PDO::PARAM_STR,
        'active'=>PDO::PARAM_STR);
    }

    public function getByParticipant($contactId,$meetingId) {
        return $this->getSingleEntity('contactId = ? and meetingId = ?',[$contactId,$meetingId]);
    }
}