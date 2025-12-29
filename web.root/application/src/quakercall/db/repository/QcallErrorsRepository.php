<?php 
/** 
 * Created by /tools/create-model.php 
 * Time:  2025-12-29 15:19:13
 */ 
 // Deployment NS:
namespace Application\quakercall\db\repository;


use \PDO;
use PDOStatement;
use Tops\db\TDatabase;
use \Tops\db\TEntityRepository;

class QcallErrorsRepository extends \Tops\db\TEntityRepository
{
    protected function getTableName() {
        return 'qcall_errors';
    }

    protected function getDatabaseId() {
        return null;
    }

    protected function getClassName() {
       return 'Application\quakercall\db\entity\QcallError';
    }

    protected function getFieldDefinitionList()
    {
        return array(
        'id'=>PDO::PARAM_STR,
        'occurred'=>PDO::PARAM_STR,
        'message'=>PDO::PARAM_STR,
        'postdata'=>PDO::PARAM_STR,
        'exception'=>PDO::PARAM_STR,
        'meetingId'=>PDO::PARAM_STR);
    }
}