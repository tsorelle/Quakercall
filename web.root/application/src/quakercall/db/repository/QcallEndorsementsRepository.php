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

class QcallEndorsementsRepository extends \Tops\db\TEntityRepository
{
    public function getEndorsement(int $contactId)
    {
        return $this->getSingleEntity('contactID = ?',[$contactId] );
    }

    public function getEndorsementList()
    {
        $sql = 'SELECT e.`organizationName`,c.`city`,c.`state` '.
            'FROM `qcall_endorsements` e '.
            'JOIN qcall_contacts c ON e.`contactId` = c.id '.
            'WHERE approved = 1 '.
            'ORDER BY e.`organizationName` ';
        $stmt = $this->executeStatement($sql);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getEndorsementCount()
    {
    }

    public function getLastEndorsementDate()
    {
    }

    protected function getTableName() {
        return 'qcall_endorsements';
    }

    protected function getDatabaseId() {
        return null;
    }

    protected function getClassName() {
       return 'Application\quakercall\db\entity\QcallEndorsement';
    }

    protected function getFieldDefinitionList()
    {
        return array(
        'id'=>PDO::PARAM_INT,
        'submissionDate'=>PDO::PARAM_STR,
        'submissionId'=>PDO::PARAM_INT,
        'contactId'=>PDO::PARAM_INT,
        'name'=>PDO::PARAM_STR,
        'email'=>PDO::PARAM_STR,
        'address'=>PDO::PARAM_STR,
        'comments'=>PDO::PARAM_STR,
        'howFound'=>PDO::PARAM_STR,
        'religion'=>PDO::PARAM_STR,
        'ipAddress'=>PDO::PARAM_STR,
        'createdby'=>PDO::PARAM_STR,
        'createdon'=>PDO::PARAM_STR,
        'changedby'=>PDO::PARAM_STR,
        'changedon'=>PDO::PARAM_STR,
        'active'=>PDO::PARAM_STR,
        'approved'=>PDO::PARAM_STR);
    }

    public function getAllByEmail($email)
    {
        return $this->getEntityCollection('email = ?', [$email]);
    }

}