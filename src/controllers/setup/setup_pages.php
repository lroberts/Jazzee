<?php
/**
 * Setup Program Pages
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage admin
 * @subpackage setup
 */
class SetupPagesController extends \Jazzee\PageBuilder {
  const MENU = 'Setup';
  const TITLE = 'Pages';
  const PATH = 'setup/pages';
  
  const ACTION_INDEX = 'Edit Program Pages';
  

  /**
   * Add the required JS
   */
  public function setUp(){
    parent::setUp();
    $this->addScript($this->path('resource/scripts/controllers/setup_pages.controller.js'));
  }
  
  /**
   * List the application Pages
   */
  public function actionListPages(){
    $pages = array();
    foreach($this->_application->getPages() AS $applicationPage){
      $pages[] = $this->pageArray($applicationPage);
    }
    $this->setVar('result', $pages);
    $this->loadView($this->controllerName . '/result');
  }
  
  /**
   * Save data from editing a page
   * @param integer $pageId
   */
  public function actionSavePage($pageId){
    $data = json_decode($this->post['data']);
    switch($data->status){
      case 'delete':
        if($applicationPage = $this->_em->getRepository('\Jazzee\Entity\ApplicationPage')->findOneBy(array('page' => $pageId, 'application'=>$this->_application->getId()))){
          $this->_em->remove($applicationPage);
          $this->_application->getPages()->remove($applicationPage->getId());
        } else {
          $this->setLayoutVar('status', 'error');
          $this->addMessage('error',"Unable to find a page with id {$pageId} in this application.");
        }
      break;
      case 'new-global':
        $applicationPage = new \Jazzee\Entity\ApplicationPage();
        $applicationPage->setPage($this->_em->getRepository('\Jazzee\Entity\Page')->findOneBy(array('id'=>$pageId, 'isGlobal'=>true)));
        $applicationPage->setApplication($this->_application);
        $applicationPage->setWeight($data->weight);
        $applicationPage->setTitle($data->title);
        $applicationPage->setMin($data->min);
        $applicationPage->setMax($data->max);
        if($data->isRequired) $applicationPage->required(); else $applicationPage->optional();
        $applicationPage->setInstructions($data->instructions);
        $applicationPage->setLeadingText($data->leadingText);
        $applicationPage->setTrailingText($data->trailingText);
        $this->_em->persist($applicationPage);
        break;
      case 'new':
        $page = new \Jazzee\Entity\Page();
        $page->notGlobal();
        $page->setType($this->_em->getRepository('\Jazzee\Entity\PageType')->find($data->classId));
        $this->_em->persist($page);
        $applicationPage = new \Jazzee\Entity\ApplicationPage();
        $applicationPage->setPage($page);
        $applicationPage->setWeight($data->weight);
        $applicationPage->setApplication($this->_application);
        $applicationPage->getJazzeePage()->setController($this);
        $applicationPage->getJazzeePage()->setupNewPage();
      default:
        if(!isset($applicationPage)) $applicationPage = $this->_em->getRepository('\Jazzee\Entity\ApplicationPage')->findOneBy(array('page' => $pageId, 'application'=>$this->_application->getId()));
        $this->savePage($applicationPage, $data);
    }
  }
  
  /**
   * List the global Pages
   */
  public function actionListGlobalPages(){
    $pages = array();
    foreach($this->_em->getRepository('\Jazzee\Entity\Page')->findByIsGlobal(true) AS $page){
      $pages[] = $this->pageArray($page);
    }
    $this->setVar('result', $pages);
    $this->loadView($this->controllerName . '/result');
  }
}