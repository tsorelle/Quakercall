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
        'organizationType'=>PDO::PARAM_STR,
        'NAME'=>PDO::PARAM_STR,
        'address'=>PDO::PARAM_STR,
        'contactName'=>PDO::PARAM_STR,
        'phone'=>PDO::PARAM_STR,
        'email'=>PDO::PARAM_STR,
        'attachment'=>PDO::PARAM_STR,
        'submissionId'=>PDO::PARAM_STR,
        'ipAddress'=>PDO::PARAM_STR,
        'createdby'=>PDO::PARAM_STR,
        'createdon'=>PDO::PARAM_STR,
        'changedby'=>PDO::PARAM_STR,
        'changedon'=>PDO::PARAM_STR,
        'active'=>PDO::PARAM_STR);
    }
}