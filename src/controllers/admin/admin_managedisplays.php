<?php

/**
 * Allows a user to manage their displays
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class AdminManagedisplaysController extends \Jazzee\AdminController
{
  const PATH = 'managedisplays';
  /**
   * Add the required JS
   */
  public function setUp()
  {
    parent::setUp();
    $this->setLayoutVar('status', 'success');
    $this->layout = 'json';
    $this->setVar('result', 'nothing');
  }

  /**
   * Create a new display
   */
  public function actionNew()
  {
    $display = new \Jazzee\Entity\Display('user');
    $display->setName('New');
    $display->setUser($this->_user);
    $display->setApplication($this->_application);
    $this->_em->persist($display);
    $this->_em->flush();
    $this->addMessage('success', 'Created new display');
    $this->setVar('result', $display->getId());
    $this->loadView('applicants_single/result');
  }

  /**
   * Create a new display
   */
  public function actionDeleteDisplay()
  {
    $obj = json_decode($this->post['display']);
    if($display = $this->_em->getRepository('Jazzee\Entity\Display')->findOneBy(array('id'=>$obj->id, 'user'=>$this->_user))){
      $this->addMessage('success', $display->getName() . ' deleted');
      $this->getEntityManager()->remove($display);
    }
    $this->loadView('applicants_single/result');
  }

  /**
   * Create a new display
   */
  public function actionSaveDisplay()
  {
    $obj = json_decode($this->post['display']);
    if($display = $this->_em->getRepository('Jazzee\Entity\Display')->findOneBy(array('id'=>$obj->id, 'user'=>$this->_user))){
      $display->setName($obj->name);
      foreach ($display->getElements() as $displayElement) {
        $display->getElements()->removeElement($displayElement);
        $this->getEntityManager()->remove($displayElement);
      }
      foreach($obj->elements as $eObj){
        switch($eObj->type){
          case 'applicant':
            $displayElement = new \Jazzee\Entity\DisplayElement('applicant');
            $displayElement->setName($eObj->name);
            break;
          case 'element':
            $displayElement = new \Jazzee\Entity\DisplayElement('element');
            if(!$element = $this->_application->getElementById($eObj->name)){
              throw new \Jazzee\Exception("{$eObj->name} is not a valid Jazzee Element ID, so it cannot be used in a 'element' display element.  Element: " . var_export($eObj, true));
            }
            $displayElement->setElement($element);
            break;
          case 'page':
            $displayElement = new \Jazzee\Entity\DisplayElement('page');
            if(!$applicationPage = $this->_application->getApplicationPageByPageId($eObj->pageId)){
              throw new \Jazzee\Exception("{$eObj->pageId} is not a valid Page ID, so it cannot be used in a 'page' display element.  Element: " . var_export($eObj, true));
            }
            $displayElement->setName($eObj->name);
            $displayElement->setPage($applicationPage->getPage());
            break;
          default:
            throw new \Jazzee\Exception("{$eObj->type} is not a valid DisplayElement type");
        }
        $displayElement->setTitle($eObj->title);
        $displayElement->setWeight($eObj->weight);
        $display->addElement($displayElement);
        $this->getEntityManager()->persist($displayElement);
      }
      $this->_em->persist($display);
      $this->addMessage('success', $display->getName() . ' saved');
    }
    $this->loadView('applicants_single/result');
  }

  /**
   * Any user can access
   * @param string $controller
   * @param string $action
   * @param \Jazzee\Entity\User $user
   * @param \Jazzee\Entity\Program $program
   * @return bool
   */
  public static function isAllowed($controller, $action, \Jazzee\Entity\User $user = null, \Jazzee\Entity\Program $program = null, \Jazzee\Entity\Application $application = null)
  {
    if (in_array($action, array('saveDisplay', 'new', 'deleteDisplay')) AND $user) {
      return true;
    }

    return parent::isAllowed($controller, $action, $user, $program, $application);
  }

}