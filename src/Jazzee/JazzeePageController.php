<?php
namespace Jazzee;
/**
 * Dependancy free controller
 * 
 * Base page controller doesn't depend on anything so it is safe
 * for error pages and file pages to use it when they don't need acess
 * to configuration or session info setup by JazzeeController
 * @package jazzee
 */

class JazzeePageController extends \Foundation\VC\Controller
{ 
  /**
   *  @var \Jazzee\Configuration
   */
  protected $_config;
  
  /**
   *  @var \Foundation\Configuration
   */
  protected $_foundationConfig;
  
  /**
   * @var \Foundation\Cache
   */
  protected $_cache;
  
  /**
   * Absolute server path
   * @var string
   */
  protected $_serverPath;
  
  /**
   * Pear log instance for error logging
   * @var \Log
   */
  protected $_errorLog;
  
  /**
   * Pear log instance for authentication logging
   * @var \Log
   */
  protected $_authLog;
  
  /**
   * Pear log instance for message logging
   * @var \Log
   */
  protected $_messageLog;
  
  /**
   * Virtual File system root directory
   * @var \Foundation\Virtual\Directory
   */
  protected $_vfs;
  
  public function __construct(){
    $this->setupConfiguration();
    $this->setupVarPath();
    $this->setupLogging();
  }
  /**
   * Basic page disply setup
   * 
   * Create the default layout varialbes so the layout doesn't have to guess if they are available
   * @return null
   */
  protected function beforeAction(){
    $this->buildVirtualFilesystem();
    //required layout variables get default values
    $this->setLayoutVar('requiredCss', array());
    $this->setLayoutVar('requiredJs', array());
    $this->setLayoutVar('pageTitle', '');
    $this->setLayoutVar('layoutTitle', '');
    $this->setLayoutVar('layoutContentTop', '');
    $this->setLayoutVar('navigation', false);
    $this->setLayoutVar('status', 'success'); //used in some json ajax requests
    
    //yui css library
    $this->addCss($this->path('resource/foundation/styles/reset-fonts-grids.css'));
    $this->addCss($this->path('resource/foundation/styles/base.css'));
    
    //anytime css has to go before jquery ui theme
    $this->addCss($this->path('resource/foundation/styles/anytime.css'));
    //default jquery theme
    $this->addCss($this->path('resource/foundation/styles/jquerythemes/ui-lightness/style.css'));
    
    //our css
    $this->addCss($this->path('resource/styles/layout.css'));
    $this->addCss($this->path('resource/styles/style.css'));
  }
  
  /**
   * Create a good path even if modrewrite is not present
   * @param string $path
   * @return string
   */
  public function path($path){
    $prefix = $this->_serverPath . rtrim(dirname($_SERVER['SCRIPT_NAME']),'/\\.');
    return $prefix . '/' . $path;
  }

  /**
   * Call any after action properties, redirect, and exit
   * @param string $path
   */
  public function redirectPath($path){
    $this->redirect($this->path($path));
    $this->afterAction();
    exit(0);
  }

  /**
   * Call any after action properties, redirect, and exit
   * @param string $url
   */
  public function redirectUrl($url){
    $this->redirect($url);
    $this->afterAction();
    exit(0);
  }
  
  /**
   * No messages
   */
  public function getMessages(){
    return array();
  }
  
  /**
   * Build our virtual file system
   */
  protected function buildVirtualFileSystem(){
    $this->_vfs = new \Foundation\Virtual\VirtualDirectory();
    $this->_vfs->addDirectory('scripts', new \Foundation\Virtual\ProxyDirectory(__DIR__ . '/../scripts'));
    $this->_vfs->addDirectory('styles', new \Foundation\Virtual\ProxyDirectory(__DIR__ . '/../styles'));
    
    $virtualFoundation = new \Foundation\Virtual\VirtualDirectory();
    $foundationPath = \Foundation\Configuration::getSourcePath();
    $virtualFoundation->addDirectory('javascript', new \Foundation\Virtual\ProxyDirectory($foundationPath . '/src/javascript'));
    $media = new \Foundation\Virtual\VirtualDirectory();
    $media->addFile('blank.gif', new \Foundation\Virtual\RealFile('blank.gif,', $foundationPath . '/src/media/blank.gif'));
    $media->addFile('ajax-bar.gif', new \Foundation\Virtual\RealFile('ajax-bar.gif,', $foundationPath . '/src/media/ajax-bar.gif'));
    $media->addDirectory('icons', new \Foundation\Virtual\ProxyDirectory( $foundationPath . '/src/media/famfamfam_silk_icons_v013/icons'));
    
    $scripts = new \Foundation\Virtual\VirtualDirectory();
    $scripts->addFile('jquery.js', new \Foundation\Virtual\RealFile('jquery.js', $foundationPath . '/lib/jquery/jquery-1.7.1.min.js'));
    $scripts->addFile('jquery.json.js', new \Foundation\Virtual\RealFile('jquery.json.js', $foundationPath . '/lib/jquery/plugins/jquery.json-2.2.min.js'));
    $scripts->addFile('jquery.cookie.js', new \Foundation\Virtual\RealFile('jquery.cookie.js', $foundationPath . '/lib/jquery/plugins/jquery.cookie-1.min.js'));
    $scripts->addFile('jqueryui.js', new \Foundation\Virtual\RealFile('jqueryui.js', $foundationPath . '/lib/jquery/jquery-ui-1.8.16.min.js'));
    $scripts->addFile('jquery.qtip.js', new \Foundation\Virtual\RealFile('jquery.qtip.min.js', $foundationPath . '/lib/jquery/plugins/qtip/jquery.qtip.min.js'));
    $scripts->addFile('jquery.wysiwyg.js', new \Foundation\Virtual\RealFile('jquery.wysiwyg.js', $foundationPath . '/lib/jquery/plugins/jwysiwyg/jquery.wysiwyg.full.min.js'));
    $scripts->addFile('anytime.js', new \Foundation\Virtual\RealFile('anytime.js', $foundationPath . '/lib/anytime/anytimec.js'));
    $scripts->addFile('form.js', new \Foundation\Virtual\RealFile('form.js', $foundationPath . '/src/javascript/form.js'));
    
    $styles = new \Foundation\Virtual\VirtualDirectory();
    $styles->addDirectory('jquerythemes', new \Foundation\Virtual\ProxyDirectory($foundationPath . '/lib/jquery/themes'));
    
    $styles->addFile('base.css', new \Foundation\Virtual\RealFile('base.css', $foundationPath . '/lib/yui/base-min.css'));
    $styles->addFile('reset-fonts-grids.css', new \Foundation\Virtual\RealFile('reset-fonts-grids.css', $foundationPath . '/lib/yui/reset-fonts-grids-min.css'));
    $styles->addFile('jquery.qtip.css', new \Foundation\Virtual\RealFile('jquery.qtip.min.css', $foundationPath . '/lib/jquery/plugins/qtip/jquery.qtip.min.css'));
    $styles->addFile('anytime.css', new \Foundation\Virtual\RealFile('anytime.css', $foundationPath . '/lib/anytime/anytimec.css'));
    $styles->addFile('jquery.wysiwyg.css', new \Foundation\Virtual\RealFile('jquery.wysiwyg.css', $foundationPath . '/lib/jquery/plugins/jwysiwyg/jquery.wysiwyg.css'));
    $styles->addFile('jquery.wysiwyg.bg.png', new \Foundation\Virtual\RealFile('jquery.wysiwyg.bg.png', $foundationPath . '/lib/jquery/plugins/jwysiwyg/jquery.wysiwyg.bg.png'));
    $styles->addFile('jquery.wysiwyg.gif', new \Foundation\Virtual\RealFile('jquery.wysiwyg.gif',$foundationPath . '/lib/jquery/plugins/jwysiwyg/jquery.wysiwyg.gif'));

    $virtualFoundation->addDirectory('media',$media);
    $virtualFoundation->addDirectory('scripts',$scripts);
    $virtualFoundation->addDirectory('styles',$styles);
    
    $this->_vfs->addDirectory('foundation', $virtualFoundation);
    
    $jazzeePath = \Jazzee\Configuration::getSourcePath();
    $vOpenID = new \Foundation\Virtual\VirtualDirectory();
    $vOpenID->addDirectory('js', new \Foundation\Virtual\ProxyDirectory($jazzeePath . '/lib/openid-selector/js'));
    $vOpenID->addDirectory('css', new \Foundation\Virtual\ProxyDirectory($jazzeePath . '/lib/openid-selector/css'));
    $vOpenID->addDirectory('images', new \Foundation\Virtual\ProxyDirectory($jazzeePath . '/lib/openid-selector/images'));
    $this->_vfs->addDirectory('openid-selector', $vOpenID);
  }
  
  /**
   * No Navigation
   */
  public function getNavigation(){
    return false;
  }
  
  /**
   * Setup the var directories
   */
  protected function setupVarPath(){
    $var = $this->getVarPath();
    //check to see if all the directories exist and are writable
    $varDirectories = array('log','session','cache','tmp','uploads','cache/public');
    foreach($varDirectories as $dir){
      $path = $var . '/' . $dir;
      if(!is_dir($path)){
        if(!mkdir($path)){
          throw new Exception("Tried to create 'var/{$dir}' directory but {$path} is not writable by the webserver");
        }
      }
      if(!is_writable($path)){
        throw new Exception("Invalid path to 'var/{$dir}' {$path} is not writable by the webserver");
      }
    }
  }
  
  /**
   * Get the path to the var directory
   * @return string
   */
  protected function getVarPath(){
    $path = $this->_config->getVarPath();
    if(!$realPath = \realpath($path) or !\is_dir($realPath) or !\is_writable($realPath)){
      if($realPath) $path = $realPath; //nicer error message if the path exists
      throw new Exception("{$path} is not readable by the webserver so we cannot use it as the 'var' directory");
    }
    return $realPath;
  }
  
  /**
   * Setup configuration
   * 
   * Load config.ini.php
   * translate to foundation config
   * create absolute path
   * set defautl timezone
   */
  protected function setupConfiguration(){
    $this->_config = new \Jazzee\Configuration();
    
    $this->_foundationConfig = new \Foundation\Configuration();
    if($this->_config->getStatus() == 'DEVELOPMENT'){
      $this->_foundationConfig->setCacheType('array');
    } else {
      $this->_foundationConfig->setCacheType('apc');
    }
    $this->_foundationConfig->setMailSubjectPrefix($this->_config->getMailSubjectPrefix());
    $this->_foundationConfig->setMailDefaultFromAddress($this->_config->getMailDefaultFromAddress());
    $this->_foundationConfig->setMailDefaultFromName($this->_config->getMailDefaultFromName());
    $this->_foundationConfig->setMailOverrideToAddress($this->_config->getMailOverrideToAddress());
    $this->_foundationConfig->setMailServerType($this->_config->getMailServerType());
    $this->_foundationConfig->setMailServerHost($this->_config->getMailServeHost());
    $this->_foundationConfig->setMailServerPort($this->_config->getMailServerPort());
    $this->_foundationConfig->setMailServerUsername($this->_config->getMailServerUsername());
    $this->_foundationConfig->setMailServerPassword($this->_config->getMailServerPassword());
    
    
    $this->_cache = new \Foundation\Cache('Jazzee' . __DIR__,$this->_foundationConfig);
    
    \Foundation\VC\Config::setCache($this->_cache);
    
    if((empty($_SERVER['HTTPS']) OR $_SERVER['HTTPS'] == 'off')){
      $protocol = 'http';
    } else {
      $protocol = 'https';
    }
    
    $this->_serverPath = $protocol . '://' .  $_SERVER['SERVER_NAME'];
  }
  
  /**
   * Get the current configuration
   * @return \Jazzee\Configuration
   */
  public function getConfig(){
    return $this->_config;
  }
  
  /**
   * Setup logging
   */
  protected function setupLogging(){
    $path = $this->getVarPath() . '/log';
    //create an access log with browser information
    $accessLog = \Log::singleton('file', $path . '/access_log', '', array('lineFormat'=>'%{timestamp} %{message}'),PEAR_LOG_INFO);
    $accessMessage ="[{$_SERVER['REQUEST_METHOD']} {$_SERVER['REQUEST_URI']} {$_SERVER['SERVER_PROTOCOL']}] " .
      '[' . (!empty($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'-') . '] ' .
      '[' . (!empty($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'-') . ']';
    $accessLog->log($accessMessage, PEAR_LOG_INFO);
    
    //create an authenticationLog
    $this->_authLog = \Log::singleton('file', $path . '/authentication_log', '', array('lineFormat'=>'%{timestamp} %{message}'),PEAR_LOG_INFO);
    
    //create a messgage log
    $this->_messageLog = \Log::singleton('file', $path . '/messages_log', '', array('lineFormat'=>'%{timestamp} %{message}'),PEAR_LOG_INFO);
    
    $log = \Log::singleton('file', $path . '/error_log', '',array(), PEAR_LOG_ERR);
    $strict = \Log::singleton('file', $path . '/strict_log');
    $php = \Log::singleton('error_log', PEAR_LOG_TYPE_SYSTEM, 'Jazzee Error');
    $this->_errorLog = \Log::singleton('composite');
    $this->_errorLog->addChild($log);
    $this->_errorLog->addChild($strict);
    $this->_errorLog->addChild($php);
    
    //Handle PHP errors with out logs
    set_error_handler(array($this, 'handleError'));
    //catch any excpetions
    set_exception_handler(array($this, 'handleException'));
  }
  
  /**
   * Log something
   * @param type $string 
   */
  public function log($string){
    $this->_messageLog->log($string, PEAR_LOG_INFO);
  }
  
  /**
   * Handle PHP error
   * Takes input from PHPs built in error handler logs it  
   * throws a jazzee exception to handle if the error reporting level is high enough
   * @param $code
   * @param $message
   * @param $file
   * @param $line
   * @throws \Jazzee\Exception
   */
  public function handleError($code, $message, $file, $line){
    /* Map the PHP error to a Log priority. */
    switch ($code) {
      case E_WARNING:
      case E_USER_WARNING:
        $priority = PEAR_LOG_WARNING;
        break;
      case E_NOTICE:
      case E_USER_NOTICE:
        $priority = PEAR_LOG_NOTICE;
        break;
      case E_ERROR:
      case E_USER_ERROR:
        $priority = PEAR_LOG_ERR;
        break;
      default:
        $priority = PEAR_LOG_INFO;
    }
    if(error_reporting() === 0){// Error reporting is currently turned off or suppressed with @
      $this->_errorLog->log('Supressed error: ' . $message . ' in ' . $file . ' at line ' . $line, PEAR_LOG_INFO);
      return false;
    }
    $this->_errorLog->log($message . ' in ' . $file . ' at line ' . $line, $priority);
    throw new \Exception('Jazzee caught a PHP error');
  }
  

  
  /**
   * Handle PHP Exception
   * @param Exception $e
   */
  public function handleException(\Exception $e){
    $message = $e->getMessage();
    $userMessage = 'Unspecified Technical Difficulties';
    $error = 500;
    if($e instanceof \Lvc_Exception){
      $code = 404;
      $userMessage = 'Page not found.';
    }
    if($e instanceof \PDOException){
      $message = 'Problem with database connection. PDO says: ' . $message;
      $userMessage = 'We are experiencing a problem connecting to our database.  Please try your request again.';
    }
    if($e instanceof \Foundation\Exception){
      $userMessage = $e->getUserMessage();
    }
    if($e instanceof \Foundation\Virtual\Exception){
      $userMessage = $e->getUserMessage();
      $code = $e->getHttpErrorCode();
    }
    /* Map the PHP error to a Log priority. */
    switch ($e->getCode()) {
      case E_WARNING:
      case E_USER_WARNING:
        $priority = PEAR_LOG_WARNING;
        break;
      case E_NOTICE:
      case E_USER_NOTICE:
        $priority = PEAR_LOG_NOTICE;
        break;
      case E_ERROR:
      case E_USER_ERROR:
        $priority = PEAR_LOG_ERR;
        break;
      default:
        $priority = PEAR_LOG_INFO;
    }
    $this->_errorLog->log($message, $priority);
    
    // Get a request for the error page
    $request = new \Lvc_Request();
    $request->setControllerName('error');
    $request->setActionName('index');
    $request->setActionParams(array('error' => $error, 'message'=>$userMessage));
  
    // Get a new front controller without any routers, and have it process our handmade request.
    $fc = new \Lvc_FrontController();
    $fc->processRequest($request);
    exit(1);
  }
}