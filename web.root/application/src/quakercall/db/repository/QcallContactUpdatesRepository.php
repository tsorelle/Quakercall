<?php 
/** 
 * Created by /tools/create-model.php 
 * Time:  2026-01-05 20:09:14
 */ 
 // Deployment NS:
namespace Application\quakercall\db\repository;



use \PDO;
use PDOStatement;
use Tops\db\TDatabase;
use \Tops\db\TEntityRepository;

class QcallContactUpdatesRepository extends \Tops\db\TEntityRepository
{

    public function getAllUpdates()
    {
        $sql = 'select * from qcall_contact_updates';
        $stmt = $this->executeStatement($sql);
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
    protected function getTableName() {
        return 'qcall_contact_updates';
    }

    protected function getDatabaseId() {
        return null;
    }

    protected function getClassName() {
        return 'Application\quakercall\db\entity\QcallContactUpdate';
    }

    protected function getFieldDefinitionList()
    {
        return array(
        'id'=>PDO::PARAM_STR,
        'endorsementId'=>PDO::PARAM_STR,
        'firstName'=>PDO::PARAM_STR,
        'lastName'=>PDO::PARAM_STR,
        'email'=>PDO::PARAM_STR,
        'phone'=>PDO::PARAM_STR,
        'organization'=>PDO::PARAM_STR,
        'title'=>PDO::PARAM_STR,
        'address1'=>PDO::PARAM_STR,
        'address2'=>PDO::PARAM_STR,
        'city'=>PDO::PARAM_STR,
        'state'=>PDO::PARAM_STR,
        'country'=>PDO::PARAM_STR,
        'postalcode'=>PDO::PARAM_STR,
        'fullname'=>PDO::PARAM_STR,
        'sortcode'=>PDO::PARAM_STR,
        'source'=>PDO::PARAM_STR,
        'postedDate'=>PDO::PARAM_STR,
        'importDate'=>PDO::PARAM_STR,
        'subscribed'=>PDO::PARAM_STR,
        'bounced'=>PDO::PARAM_STR,
        'createdby'=>PDO::PARAM_STR,
        'createon'=>PDO::PARAM_STR,
        'changedby'=>PDO::PARAM_STR,
        'changedon'=>PDO::PARAM_STR,
        'active'=>PDO::PARAM_STR);
    }
}