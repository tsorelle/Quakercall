<?php 
/** 
 * Created by /tools/create-model.php 
 * Time:  2026-03-21 11:19:21
 */ 
namespace Application\quakercall\db\repository;

use \PDO;
use PDOStatement;
use Tops\db\TDatabase;
use \Tops\db\TEntityRepository;

class QcallSuppressionsRepository extends \Tops\db\TEntityRepository
{
    protected function getTableName() {
        return 'qcall_suppressions';
    }

    protected function getDatabaseId() {
        return null;
    }

    protected function getClassName() {
       return 'Application\quakercall\db\entity\QcallSuppression';
    }

    protected function getFieldDefinitionList()
    {
        return array(
        'id'=>PDO::PARAM_STR,
        'processedDate'=>PDO::PARAM_STR,
        'email'=>PDO::PARAM_STR,
        'reason'=>PDO::PARAM_STR,
        'disposition'=>PDO::PARAM_STR,
        'createdby'=>PDO::PARAM_STR,
        'createdon'=>PDO::PARAM_STR,
        'changedby'=>PDO::PARAM_STR,
        'changedon'=>PDO::PARAM_STR,
        'active'=>PDO::PARAM_STR);
    }
}