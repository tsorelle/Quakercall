<?php 
/** 
 * Created by /tools/create-model.php 
 * Time:  2025-12-23 16:22:51
 */ 
 // Deployment NS:
namespace Application\quakercall\db\repository;

use Application\quakercall\db\entity\QcallEndorsement;
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
        $sqlHeader = // 'SELECT c.`firstName`,c.`lastName`,c.`city`,c.`state` '.
            // "SELECT  CONCAT(c.firstName,' ',c.lastName) AS `Name`, ".
            "SELECT  e.name AS `Name`, ".
		    "CONCAT(c.`city`, IF(c.`state`='','',' '),c.state, ".
		    "IF(c.`country`='United States' OR c.`country`='US' OR c.`country` = 'USA','',concat(' ',c.`country`))) ".
		    'AS Location '.
            'FROM `qcall_endorsements` e '.
            'JOIN qcall_contacts c ON e.`contactId` = c.id '.
            'WHERE approved = 1 AND e.active=1 ';

        $sql = $sqlHeader." AND c.`state` <> '' ";
        $sql .= 'ORDER BY c.state,c.city,c.`lastName`,c.`firstName`';

        $stmt = $this->executeStatement($sql);
        $withAddress = $stmt->fetchAll(PDO::FETCH_OBJ);

        // list blank addresses last
        $sql = $sqlHeader." AND c.`state` = '' ";
        $sql .= 'ORDER BY c.state,c.city,c.`lastName`,c.`firstName`';
        $stmt = $this->executeStatement($sql);
        $blankAddress = $stmt->fetchAll(PDO::FETCH_OBJ);
        return [...$withAddress, ...$blankAddress];
    }

    public function getEndorsementCount()
    {
    }

    public function getLastEndorsementDate()
    {
        $sql = 'SELECT MAX(submissionDate) from qcall_endorsements where active=1 AND approved=1';
        $stmt = $this->executeStatement($sql);
        $result = $stmt->fetch(PDO::FETCH_COLUMN);
        return $result;
    }

    public function approve($id)
    {
/*        $sql = 'UPDATE qcall_endorsements SET approved=1 WHERE id=?';
        $stmt = $this->executeStatement($sql, [$id]);*/
        /**
         * @var $endorsement QcallEndorsement
         */
        $endorsement = $this->get($id);
        if (!$endorsement) {
            return false;
        }
        $endorsement->approved = 1;
        $endorsement->approvalDate = date('Y-m-d');
        $this->update($endorsement);
        return $endorsement;
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
        'approvalDate'=>PDO::PARAM_STR,
        'approved'=>PDO::PARAM_STR);
    }

    public function getAllByEmail($email)
    {
        return $this->getEntityCollection('email = ?', [$email]);
    }

    public function getEndorsementsForApproval() {
        $sql =
            'SELECT e.id,e.submissionDate,e.submissionId,e.contactId,e.name, '.
            'e.comments,e.religion,e.howFound,e.ipAddress,c.email,c.phone, '.
            'c.address1,c.address2,c.city,c.state,c.country,c.postalcode '.
            'FROM qcall_endorsements e '.
            'JOIN qcall_contacts c ON e.`contactId` = c.id '.
            'WHERE (e.`approved` = 0 OR e.`approved` IS NULL) AND e.active = 1  '.
            'ORDER BY e.submissionDate';
        $stmt = $this->executeStatement($sql);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

}