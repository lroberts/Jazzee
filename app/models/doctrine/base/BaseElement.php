<?php

/**
 * BaseElement
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $pageID
 * @property integer $fixedID
 * @property integer $elementType
 * @property string $title
 * @property string $format
 * @property string $instructions
 * @property string $defaultValue
 * @property boolean $required
 * @property double $min
 * @property double $max
 * @property Page $Page
 * @property ElementType $ElementType
 * @property Doctrine_Collection $ListItems
 * @property Doctrine_Collection $ElementAnswer
 * 
 * @package    jazzee
 * @subpackage orm
 */
abstract class BaseElement extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('element');
        $this->hasColumn('pageID', 'integer', null, array(
             'type' => 'integer',
             ));
        $this->hasColumn('fixedID', 'integer', null, array(
             'type' => 'integer',
             ));
        $this->hasColumn('elementType', 'integer', null, array(
             'type' => 'integer',
             ));
        $this->hasColumn('title', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('format', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('instructions', 'string', 3000, array(
             'type' => 'string',
             'length' => '3000',
             ));
        $this->hasColumn('defaultValue', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('required', 'boolean', null, array(
             'type' => 'boolean',
             ));
        $this->hasColumn('min', 'double', null, array(
             'type' => 'double',
             ));
        $this->hasColumn('max', 'double', null, array(
             'type' => 'double',
             ));


        $this->index('UniqueFixedID', array(
             'fields' => 
             array(
              0 => 'fixedID',
              1 => 'pageID',
             ),
             'type' => 'unique',
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Page', array(
             'local' => 'pageID',
             'foreign' => 'id',
             'onDelete' => 'CASCADE',
             'onUpdate' => 'CASCADE'));

        $this->hasOne('ElementType', array(
             'local' => 'elementType',
             'foreign' => 'id'));

        $this->hasMany('ElementListItem as ListItems', array(
             'local' => 'id',
             'foreign' => 'elementID'));

        $this->hasMany('ElementAnswer', array(
             'local' => 'id',
             'foreign' => 'elementID'));
    }
}