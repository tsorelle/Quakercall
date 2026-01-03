<?php 
/** 
 * Created by /tools/create-model.php 
 * Time:  2025-12-23 22:05:13
 */ 
 // Deployment NS:
namespace Application\quakercall\db\repository;

use \PDO;
use PDOStatement;
use Tops\db\TDatabase;
use \Tops\db\TEntityRepository;

class QcallContactsRepository extends \Tops\db\TEntityRepository
{
    public function isSubscribed($email)
    {
        $count = $this->getRecordCount('email = ? and subscribed=1', [$email]);
        return $count > 0;
    }

    public function subscribe($contact)
    {
        if ($contact && !empty($contact->email)) {
            if (!$this->isSubscribed($contact->email)) {
                $contact->subscribed = 1;
                $this->update($contact);
            }
        }
    }

    protected function getTableName() {
        return 'qcall_contacts';
    }

    protected function getDatabaseId() {
        return null;
    }

    protected function getClassName() {
        return 'Application\quakercall\db\entity\QcallContact';
    }

    protected function getFieldDefinitionList()
    {
        return array(
        'id'=>PDO::PARAM_INT,
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
        'createdon'=>PDO::PARAM_STR,
        'changedby'=>PDO::PARAM_STR,
        'changedon'=>PDO::PARAM_STR,
        'active'=>PDO::PARAM_STR);
    }

    public function findByEmail($email)
    {
        return $this->getSingleEntity('email=?', [$email]);
    }

    public function getAllByEmail($email) {
        return $this->getEntityCollection('email=?', [$email]);
    }
    public function findByFullname($fullname) {
        return $this->getSingleEntity("CONCAT(firstName,' ',lastname) ==?", [$fullname]);
    }


}