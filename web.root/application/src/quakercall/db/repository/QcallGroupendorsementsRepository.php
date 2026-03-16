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
use Tops\sys\TConfiguration;

class QcallGroupendorsementsRepository extends \Tops\db\TEntityRepository
{
    public function getGroupEndorsementsForApproval()
    {
        $documentPath = TConfiguration::getValue('documents', 'location', 'application/documents');
        if (!str_starts_with($documentPath, '/')) {
            $documentPath = '/'.$documentPath;
        }
        if (!str_ends_with($documentPath, '/')) {
            $documentPath .= '/';
        }

        $sql =
            'SELECT id,submissionId,submissionDate,comments,ipAddress,email, '.
            'phone,address1,address2,city,state,country,postalcode, '.
            'organizationName,contactName, '.
            'CASE documentationType '.
            "    WHEN 'upload' THEN CONCAT('%sendorsements/',document) ".
            "    WHEN 'url' THEN document ".
            "    ELSE '' ".
            'END AS documentUrl '.
            'FROM qcall_groupendorsements '.
            'WHERE (`approved` = 0 OR `approved` IS NULL) AND active = 1 '.
            'ORDER BY submissionDate' ;
        $sql = sprintf($sql, $documentPath);
        $stmt = $this->executeStatement($sql);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function approve($id)
    {
        $endorsement = $this->get($id);
        if (!$endorsement) {
            return false;
        }
        $endorsement->approved = 1;
        $this->update($endorsement);
        return $endorsement;
    }

    public function cancelEndorsement($id)
    {
        $endorsement = $this->get($id);
        if (!$endorsement) {
            return false;
        }
        $endorsement->active = 0;
        $endorsement->approved = 0;
        $this->update($endorsement);
        return $endorsement;
    }

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
        'organizationName'=>PDO::PARAM_STR,
        'typeId'=>PDO::PARAM_INT,
        'submissionId'=>PDO::PARAM_STR,
        'submissionDate'=>PDO::PARAM_STR,
        'contactName'=>PDO::PARAM_STR,
        'email'=>PDO::PARAM_STR,
        'phone'=>PDO::PARAM_STR,
        'address1'=>PDO::PARAM_STR,
        'address2'=>PDO::PARAM_STR,
        'city'=>PDO::PARAM_STR,
        'state'=>PDO::PARAM_STR,
        'country'=>PDO::PARAM_STR,
        'postalcode'=>PDO::PARAM_STR,
        'documentationType'=>PDO::PARAM_STR,
        'document'=>PDO::PARAM_STR,
        'comments'=>PDO::PARAM_STR,
        'ipAddress'=>PDO::PARAM_STR,
        'approved'=>PDO::PARAM_STR,
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
        $sql = 'SELECT e.`organizationName`,e.`city`,e.`state` '.
            'FROM `qcall_groupendorsements` e '.
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