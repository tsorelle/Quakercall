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

class QcallGroupendorsementsRepository extends \Tops\db\TEntityRepository
{
    protected function getTableName() {
        return 'qcall_groupendorsements';
    }

    protected function getDatabaseId() {
        return null;
    }

    protected function getClassName() {
        return 'Application\quakercall\db\entity\QcallGroupendorsement';
    }

    protected function getFieldDefinitionList()
    {
        return array(
        'id'=>PDO::PARAM_STR,
        'contactId'=>PDO::PARAM_STR,
        'submissionDate'=>PDO::PARAM_STR,
        'typeId'=>PDO::PARAM_INT,
        'organizationType'=>PDO::PARAM_STR,
        'organizationName'=>PDO::PARAM_STR,
        'address'=>PDO::PARAM_STR,
        'contactName'=>PDO::PARAM_STR,
        'phone'=>PDO::PARAM_STR,
        'email'=>PDO::PARAM_STR,
        'attachments'=>PDO::PARAM_STR,
        'submissionId'=>PDO::PARAM_STR,
        'approved'=>PDO::PARAM_STR,
        'ipAddress'=>PDO::PARAM_STR,
        'createdby'=>PDO::PARAM_STR,
        'createdon'=>PDO::PARAM_STR,
        'changedby'=>PDO::PARAM_STR,
        'changedon'=>PDO::PARAM_STR,
        'active'=>PDO::PARAM_STR);
    }

    public function getEndorsement(int $contactId)
    {
        return $this->getSingleEntity('contactID = ?',[$contactId] );
    }

    public function getGroupendorsementList() {
        $sql = 'SELECT e.`organizationName`,c.`city`,c.`state` '.
            'FROM `qcall_groupendorsements` e '.
            'JOIN qcall_contacts c ON e.`contactId` = c.id '.
            'WHERE approved = 1 '.
            'ORDER BY e.`organizationName` ';
        $stmt = $this->executeStatement($sql);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getEndorsementCount() {
        return $this->getCount(false,'approved=1');
    }

    public function getLastEndorsementDate() {
        $sql = 'SELECT MAX(e.`submissionDate`) FROM qcall_groupendorsements e WHERE approved=1';
        return $this->getValue($sql);
    }


}