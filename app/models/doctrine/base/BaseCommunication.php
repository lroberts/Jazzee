<?php

/**
 * BaseCommunication
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $parentID
 * @property integer $senderID
 * @property integer $recipientID
 * @property enum $senderType
 * @property enum $recipientType
 * @property string $text
 * @property Communication $Parent
 * @property Doctrine_Collection $Reply
 * 
 * @package    jazzee
 * @subpackage orm
 */
abstract class BaseCommunication extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('communication');
        $this->hasColumn('parentID', 'integer', null, array(
             'type' => 'integer',
             ));
        $this->hasColumn('senderID', 'integer', null, array(
             'type' => 'integer',
             ));
        $this->hasColumn('recipientID', 'integer', null, array(
             'type' => 'integer',
             ));
        $this->hasColumn('senderType', 'enum', null, array(
             'type' => 'enum',
             'values' => 
             array(
              0 => 'applicant',
              1 => 'user',
             ),
             ));
        $this->hasColumn('recipientType', 'enum', null, array(
             'type' => 'enum',
             'values' => 
             array(
              0 => 'applicant',
              1 => 'user',
             ),
             ));
        $this->hasColumn('text', 'string', 4000, array(
             'type' => 'string',
             'length' => '4000',
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Communication as Parent', array(
             'local' => 'parentID',
             'foreign' => 'id'));

        $this->hasMany('Communication as Reply', array(
             'local' => 'id',
             'foreign' => 'parentID'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $this->actAs($timestampable0);
    }
}