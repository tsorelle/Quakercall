<?php 
/** 
 * Created by /tools/create-model.php 
 * Time:  2026-03-09 19:47:36
 */ 


namespace Application\quakercall\db\repository;

use Application\quakercall\db\entity\QcallDocument;
use \PDO;
use PDOStatement;
use Tops\db\TDatabase;
use \Tops\db\TEntityRepository;

class QcallDocumentsRepository extends \Tops\db\TEntityRepository
{
    public function getByName($filename, mixed $folder)
    {
        return $this->getSingleEntity('filename=? and folder=?',[$filename, $folder]);
    }

    public function findDuplicates(string $fileName, $folder, $excludeId) : array
    {
        $sql = 'filename = ? AND folder = ? AND id <> ?';
        $result = $this->getEntityCollection($sql, [$fileName,$folder,$excludeId]);
        return $result;
    }

    protected function getTableName() {
        return 'qcall_documents';
    }

    protected function getDatabaseId() {
        return null;
    }

    protected function getClassName() {
       // return 'Application\quakercall\db\entity\QcallDocument';
        return null; // delete and uncomment above for deployment
    }

    protected function getFieldDefinitionList()
    {
        return array(
        'id'=>PDO::PARAM_STR,
        'folder'=>PDO::PARAM_STR,
        'filename'=>PDO::PARAM_STR,
        'title'=>PDO::PARAM_STR,
        'description'=>PDO::PARAM_STR,
        'createdby'=>PDO::PARAM_STR,
        'createdon'=>PDO::PARAM_STR,
        'changedby'=>PDO::PARAM_STR,
        'changedon'=>PDO::PARAM_STR,
        'active'=>PDO::PARAM_STR);
    }
}