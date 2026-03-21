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

    public function findByEmailAndName($email, $fullname)
    {
        $result = $this->getSingleEntity('email = ? and fullName = ?', [$email, $fullname]);
        return $result;
    }

    public function setBounced(mixed $email)
    {
        $sql = 'UPDATE qcall_contacts SET bounced = 1 WHERE email = ? ';
        $stmt = $this->executeStatement($sql, [$email]);
    }

    public function unsubscribe(string $email)
    {
        $sql = 'UPDATE qcall_contacts SET subscribed = 0 WHERE email = ? ';
        $stmt = $this->executeStatement($sql, [$email]);
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
        'middleName'=>PDO::PARAM_STR,
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
        'emailRefused'=>PDO::PARAM_STR,
        'createdby'=>PDO::PARAM_STR,
        'createdon'=>PDO::PARAM_STR,
        'changedby'=>PDO::PARAM_STR,
        'changedon'=>PDO::PARAM_STR,
        'active'=>PDO::PARAM_STR);
    }

    public function searchByEmail($email)
    {
        return $this->getEntityCollection('email=?', [$email]);
    }

    public function findByEmail($email)
    {
        return $this->getSingleEntity('email=?', [$email]);
    }

    public function findByOrganization($name)
    {
        return $this->getSingleEntity('organization=?', [$name]);
    }

    public function findOrganizationEndorser($name)
    {
        return $this->getSingleEntity('organization=? AND source="org-endorsement"', [$name]);
    }

    public function getAllByEmail($email, $ignoreOrgEndorsement=true) {
        $where =  ($ignoreOrgEndorsement) ?
            "email=? AND (IFNULL(`source`,'') <> 'org-endorsement')" :
            "email=?";

        return $this->getEntityCollection($where, [$email]);
    }
    public function findByFullname($fullname) {
        return $this->getSingleEntity("CONCAT(firstName,' ',lastname) ==?", [$fullname]);
    }

    public function searchByName($name) {
        $result = $this->getEntityCollection("fullname LIKE '%$name%'", []);
        return $result;
    }

    public function getBouncedEmails() {
        $result = $this->getEntityCollection('bounced=?',[1]);
        return $result;
    }

    public function getEmailRecipients() {
        $sql =
            'SELECT '.
            '`firstName` AS `first_name`, '.
            '`lastName` AS `last_name`, `email`, '.
            "IFNULL (`phone`,'') AS `phone` ".
            'FROM `qcall_contacts` c WHERE `subscribed` =1 AND `active` = 1 '.
            'AND (bounced = 0 OR bounced IS NULL) '.
            "AND NOT (firstname = '' AND lastname = '' AND fullname = '') ".
            'ORDER BY `sortCode`';

        $stmt = $this->executeStatement($sql);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getUnPostedEmailRecipients() {
        $sql = 'SELECT MAX(`postedDate`) FROM qcall_contacts WHERE active=1 AND postedDate IS NOT NULL';
        $lastPostedDate = $this->getValue($sql);
        $sql = 'SELECT '.
            '  `firstName` AS `first_name`, '.
            '  `lastName`  AS `last_name`, '.
            '  `email`     AS `email`, '.
            "  IFNULL(`phone`,'') AS `phone` ".
            'FROM `qcall_contacts` '.
            'WHERE (subscribed = 1 AND ACTIVE = 1 AND (bounced=0 OR bounced IS NULL)  '.
            "AND (`firstName` <> ''  OR `lastName` <> '' OR `fullname` <> '')) ";

        $params = [];
        if (empty($lastPostedDate)) {
            $sql .= ' AND postedDate IS NULL';
        }
        else {
            $sql .= ' AND (postedDate IS NULL OR  postedDate <  ?)';
            $params = [$lastPostedDate];
        }
        $stmt = $this->executeStatement($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function setPostedDate() {

        $postedTime = date('Y-m-d H:i:s');

        $sql =
            'UPDATE `qcall_contacts` SET posteddate = ? '.
            'WHERE subscribed = 1 AND ACTIVE = 1 AND (bounced=0 OR bounced IS NULL) '.
            "AND (`firstName` <> ''  OR `lastName` <> '' OR `fullname` <> '')";
        $this->executeStatement($sql, [$postedTime]);
        return $postedTime;
    }

}