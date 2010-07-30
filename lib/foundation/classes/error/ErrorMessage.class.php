<?php
/**
 * An error message
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage error
 * 
*/

class ErrorMessage{
  /**
   * PHP Error Constant for severity
   * @var integer
   */
  protected $level;
  
  /**
   * The textual representation of the level
   * @var string
   */
  protected $levelString;
  
  /**
   * Text of the error
   * @var string
   */
  protected $message;
  
  /**
   * The filename that raised the error
   * @var string
   */
  protected $fileName;
  
  /**
   * The line number that raised the error
   * @var integer
   */
  protected $lineNumber;
  
  /**
   * Points to the active symbol table at the point the error occurred. 
   * In other words, errcontext will contain an array of every variable that existed in the scope the error was triggered in.
   * Must not modify error context.
   * @var array
   */
  protected $context;
  
  /**
   * Constructor
   * @param $errno integer PHP Error Constant for severity
   * @param $errstr string the actuall text of the error
   * @param $errfile string optional the filename that raised the error
   * @param $errline integer optional the line number that raised the error
   */
  public function __construct($level, $message, $fileName = null, $lineNumber = null, $context = null){
    $this->level = $level;
    $this->levelString = $this->errorToText($level);
    $this->message = $message;
    $this->fileName = $fileName;
    $this->lineNumber = $lineNumber;
    $this->context = $context;
  }
  
  /**
   * Override get for protected properties
   * @param string $name
   */
  public function __get($name){
    if(isset($this->$name)){
      return $this->$name;
    }
    return null;
  }
  
  protected function errorToText($errNum){
    switch ($errNum) {
      /* Fatal run-time errors.
       * These indicate errors that can not be recovered from, such as a memory allocation problem.
       * Execution of the script is halted.
       */
      case E_ERROR:
        $string = 'E_ERROR';
        break;
      /* Run-time warnings (non-fatal errors).
       * Execution of the script is not halted.
       */
      case E_WARNING:
        $string = 'E_WARNING';
        break;
      /* Compile-time parse errors.
       * Parse errors should only be generated by the parser.
       */
      case E_PARSE:
        $string = 'E_PARSE';
        break;
      /* Run-time notices.
       * Indicate that the script encountered something that could indicate an error, but could also happen in the normal course of running a script.
       */
      case E_NOTICE:
        $string = 'E_NOTICE';
        break;
      /* Fatal errors that occur during PHP's initial startup.
       * This is like an E_ERROR, except it is generated by the core of PHP.
       */
      case E_CORE_ERROR:
        $string = 'E_CORE_ERROR';
        break;
      /* Warnings (non-fatal errors) that occur during PHP's initial startup.
       * This is like an E_WARNING, except it is generated by the core of PHP.
       */
      case E_CORE_WARNING:
        $string = 'E_CORE_WARNING';
        break;
      /* Fatal compile-time errors.
       * This is like an E_ERROR, except it is generated by the Zend Scripting Engine.
       */
      case E_COMPILE_ERROR:
        $string = 'E_COMPILE_ERROR';
        break;
      /* Compile-time warnings (non-fatal errors).
       * This is like an E_WARNING, except it is generated by the Zend Scripting Engine.
       */
      case E_COMPILE_WARNING:
        $string = 'E_COMPILE_WARNING';
        break;
      /* User-generated error message.
       * This is like an E_ERROR, except it is generated in PHP code by
       * using the PHP function trigger_error().
       */
      case E_USER_ERROR:
        $string = 'E_USER_ERROR';
        break;
      /* User-generated warning message.
       * This is like an E_WARNING, except it is generated in PHP code by
       * using the PHP function trigger_error().
       */
      case E_USER_WARNING:
        $string = 'E_USER_WARNING';
        break;
      /* User-generated notice message.
       * This is like an E_NOTICE, except it is generated in PHP code by
       * using the PHP function trigger_error().
       */
      case E_USER_NOTICE:
        $string = 'E_USER_NOTICE';
        break;
      /* Enable to have PHP suggest changes to your code which will ensure the
       * best interoperability and forward compatibility of your code.
       */
      case E_STRICT:
        $string = 'E_STRICT';
        break;
      /* Catchable fatal error. It indicates that a probably dangerous
       * error occured, but did not leave the Engine in an unstable state.
       * If the error is not caught by a user defined handle (see also
       * set_error_handler()), the application aborts as it was an E_ERROR.
       */    
      case E_RECOVERABLE_ERROR:
        $string = 'E_RECOVERABLE_ERROR';
        break;
      /* Run-time notices. Enable this to receive warnings about code that
       * will not work in future versions.
       */
      case E_DEPRECATED:
        $string = 'E_DEPRECATED';
        break;
      /* User-generated warning message. This is like an E_DEPRECATED, except it
       * is generated in PHP code by using the PHP function trigger_error().
       */
      case E_USER_DEPRECATED:
        $string = 'E_USER_DEPRECATED';
        break;
      /* Access level log entry, not an error
       */
      case E_ACCESS:
        $string = 'E_ACCESS';
        break;
      default:
        $string = 'E_UNKNOWN';
    }
    return $string;
  }
}
?>