<?php 
/** 
 * Created by /tools/create-model.php 
 * Time:  2026-03-09 19:47:36
 */ 

namespace Application\quakercall\db\entity;

class QcallDocument  extends \Tops\db\TimeStampedEntity 
{ 
    public $id;
    public $folder;
    public $filename;
    public $title;
    public $description;
    public $active;

}
