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

class QcallRegistrationsRepository extends \Tops\db\TEntityRepository
{
    protected function getTableName() {
        return 'qcall_registrations';
    }

    protected function getDatabaseId() {
        return null;
    }

    protected function getClassName() {
       // return 'Application\quakercall\db\entity\QcallRegistration';
        return null; // delete and uncomment above for deployment
    }

    protected function getFieldDefinitionList()
    {
        return array(
        'id'=>PDO::PARAM_STR,
        'participant'=>PDO::PARAM_STR,
        'contactId'=>PDO::PARAM_STR,
        'meetingId'=>PDO::PARAM_STR,
        'submissionDate'=>PDO::PARAM_STR,
        'location'=>PDO::PARAM_STR,
        'religion'=>PDO::PARAM_STR,
        'affiliation'=>PDO::PARAM_STR,
        'submissionId'=>PDO::PARAM_STR,
        'ipAddress'=>PDO::PARAM_STR,
        'createdby'=>PDO::PARAM_STR,
        'createdon'=>PDO::PARAM_STR,
        'changedby'=>PDO::PARAM_STR,
        'changedon'=>PDO::PARAM_STR,
        'active'=>PDO::PARAM_STR);
    }
}