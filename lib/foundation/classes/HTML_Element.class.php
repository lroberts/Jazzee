<?php
/**
 * Dynamic HTML elements
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 */
require_once('Foundation.class.php');

class HTML_Element extends Foundation{
  /**
   * An array of all the possible attributes for this element and their member names
   * @var array
   */
  protected $_attributes = array();
  
  /**
   * HTML element attributes
   * @var string
   */
  public $id;
  public $dir;
  public $lang;
  public $style;
  public $title;
  public $xml_lang;
  
  /**
   * The classes for an element
   * @var array
   */
  protected $classes = array();
  
  /**
   * Constructor
   */
  public function __construct(){
    $this->_attributes['class'] = 'class';
    $this->_attributes['dir'] = 'dir';
    $this->_attributes['id'] = 'id';
    $this->_attributes['lang'] = 'lang';
    $this->_attributes['style'] = 'style';
    $this->_attributes['title'] = 'title';
    $this->_attributes['xml_lang'] = 'xml:lang';
  }
  
  /**
   * Get all of the availalbe attributes for this element
   * @return array 
   */
  public function getAttributes(){
    return $this->_attributes;
  }
  
  /**
   * Override set for special properties
   * @param string $name
   * @param mixed $value
   */
  public function __set($name, $value){
    $method = 'set' . ucfirst($name);
    if(method_exists($this, $method)){
      $this->$method($value);
    }
  }
  
  /**
   * Override get for special properties
   * @param string $name
   */
  public function __get($name){
    $method = 'get' . ucfirst($name);
    if(method_exists($this, $method)){
      return $this->$method();
    }
    return null;
  }
  
  /**
   * Override isset for special properties
   * @param string $name
   */
  public function __isset($name){
    $method = 'isset' . ucfirst($name);
    if(method_exists($this, $method)){
      return $this->$method();
    }
    return false;
  }
  
  /**
   * Set the classes
   * @param string $name
   */
  public function setClass($name){
    $this->classes[] = $name;
  }
  
  /**
   * Get the classes as a list
   */
  public function getClass(){
    return implode(' ', $this->classes);
  }
}
?>