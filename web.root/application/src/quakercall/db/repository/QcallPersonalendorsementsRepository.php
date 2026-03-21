<?php 
/** 
 * Created by /tools/create-model.php 
 * Time:  2026-01-12 02:47:18
 */ 
 // Deployment NS:
namespace Application\quakercall\db\repository;



use \PDO;
use PDOStatement;
use Tops\db\TDatabase;
use \Tops\db\TEntityRepository;

class QcallPersonalendorsementsRepository extends \Tops\db\TEntityRepository
{
    protected function getTableName() {
        return 'qcall_personalendorsements';
    }

    protected function getDatabaseId() {
        return null;
    }

    protected function getClassName() {
        return 'Application\quakercall\db\entity\QcallPersonalendorsement';
    }

    protected function getFieldDefinitionList()
    {
        return array(
        'id'=>PDO::PARAM_STR,
        'submissionDate'=>PDO::PARAM_STR,
        'contactId'=>PDO::PARAM_INT,
        'firstName'=>PDO::PARAM_STR,
        'lastName'=>PDO::PARAM_STR,
        'email'=>PDO::PARAM_STR,
        'address1'=>PDO::PARAM_STR,
        'address2'=>PDO::PARAM_STR,
        'city'=>PDO::PARAM_STR,
        'state'=>PDO::PARAM_STR,
        'country'=>PDO::PARAM_STR,
        'comments'=>PDO::PARAM_STR,
        'postalcode'=>PDO::PARAM_STR,
        'religion'=>PDO::PARAM_STR,
        'howFound'=>PDO::PARAM_STR,
        'submissionId'=>PDO::PARAM_STR,
        'ipAddress'=>PDO::PARAM_STR,
        'createdby'=>PDO::PARAM_STR,
        'createdon'=>PDO::PARAM_STR,
        'changedby'=>PDO::PARAM_STR,
        'changedon'=>PDO::PARAM_STR,
        'active'=>PDO::PARAM_STR,
        'resolved'=>PDO::PARAM_STR);
    }
}