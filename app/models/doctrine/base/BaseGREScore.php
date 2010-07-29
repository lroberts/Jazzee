<?php

/**
 * BaseGREScore
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $registrationNumber
 * @property string $departmentCode
 * @property string $departmentName
 * @property string $firstName
 * @property string $middleInitial
 * @property string $lastName
 * @property date $birthDate
 * @property string $sex
 * @property date $testDate
 * @property string $testCode
 * @property string $testName
 * @property string $score1Type
 * @property int $score1Converted
 * @property decimal $score1Percentile
 * @property string $score2Type
 * @property int $score2Converted
 * @property decimal $score2Percentile
 * @property string $score3Type
 * @property int $score3Converted
 * @property decimal $score3Percentile
 * @property string $score4Type
 * @property int $score4Converted
 * @property decimal $score4Percentile
 * @property integer $sequenceNumber
 * @property integer $recordSerialNumber
 * @property integer $cycleNumber
 * @property date $processDate
 * 
 * @package    jazzee
 * @subpackage orm
 */
abstract class BaseGREScore extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('gre_score');
        $this->hasColumn('registrationNumber', 'integer', null, array(
             'type' => 'integer',
             'unique' => true,
             ));
        $this->hasColumn('departmentCode', 'string', 4, array(
             'type' => 'string',
             'length' => '4',
             ));
        $this->hasColumn('departmentName', 'string', 30, array(
             'type' => 'string',
             'length' => '30',
             ));
        $this->hasColumn('firstName', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('middleInitial', 'string', 1, array(
             'type' => 'string',
             'length' => '1',
             ));
        $this->hasColumn('lastName', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('birthDate', 'date', null, array(
             'type' => 'date',
             ));
        $this->hasColumn('sex', 'string', 1, array(
             'type' => 'string',
             'length' => '1',
             ));
        $this->hasColumn('testDate', 'date', null, array(
             'type' => 'date',
             ));
        $this->hasColumn('testCode', 'string', 2, array(
             'type' => 'string',
             'length' => '2',
             ));
        $this->hasColumn('testName', 'string', 32, array(
             'type' => 'string',
             'length' => '32',
             ));
        $this->hasColumn('score1Type', 'string', 1, array(
             'type' => 'string',
             'length' => '1',
             ));
        $this->hasColumn('score1Converted', 'int', 3, array(
             'type' => 'int',
             'length' => '3',
             ));
        $this->hasColumn('score1Percentile', 'decimal', null, array(
             'type' => 'decimal',
             ));
        $this->hasColumn('score2Type', 'string', 1, array(
             'type' => 'string',
             'length' => '1',
             ));
        $this->hasColumn('score2Converted', 'int', 3, array(
             'type' => 'int',
             'length' => '3',
             ));
        $this->hasColumn('score2Percentile', 'decimal', null, array(
             'type' => 'decimal',
             ));
        $this->hasColumn('score3Type', 'string', 1, array(
             'type' => 'string',
             'length' => '1',
             ));
        $this->hasColumn('score3Converted', 'int', 3, array(
             'type' => 'int',
             'length' => '3',
             ));
        $this->hasColumn('score3Percentile', 'decimal', null, array(
             'type' => 'decimal',
             ));
        $this->hasColumn('score4Type', 'string', 1, array(
             'type' => 'string',
             'length' => '1',
             ));
        $this->hasColumn('score4Converted', 'int', 4, array(
             'type' => 'int',
             'length' => '4',
             ));
        $this->hasColumn('score4Percentile', 'decimal', null, array(
             'type' => 'decimal',
             ));
        $this->hasColumn('sequenceNumber', 'integer', 4, array(
             'type' => 'integer',
             'length' => '4',
             ));
        $this->hasColumn('recordSerialNumber', 'integer', 2, array(
             'type' => 'integer',
             'length' => '2',
             ));
        $this->hasColumn('cycleNumber', 'integer', null, array(
             'type' => 'integer',
             ));
        $this->hasColumn('processDate', 'date', null, array(
             'type' => 'date',
             ));
    }

    public function setUp()
    {
        parent::setUp();
        
    }
}