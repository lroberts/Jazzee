<?php
namespace Jazzee\Page;
/**
 * AbstractPage
 * 
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage pages
 */
abstract class AbstractPage implements \Jazzee\Interfaces\Page {
  const ERROR_MESSAGE = 'There was a problem saving your data on this page.  Please correct the errors below and retry your request.';
 /**
  * The ApplicationPage Entity
  * @var \Jazzee\Entity\ApplicationPage
  */
  protected $_applicationPage;
    
  /**
   * Our controller
   * @var \Jazzee\Controller
   */
  protected $_controller;
  
  /**
   * The Applicant
   * @var \Jazzee\Entity\Applicant
   */
  protected $_applicant;
  
  /**
   * Our form
   * @var \Foundation\Form
   */
  protected $_form;
  
 /**
  * Contructor
  * 
  * @param \Jazzee\Entity\ApplicationPage $applicationPage
  */
  public function __construct(\Jazzee\Entity\ApplicationPage $applicationPage){
    $this->_applicationPage = $applicationPage;
  }
  
  /**
   * 
   * @see Jazzee.Page::setController()
   */
  public function setController(\Jazzee\Controller $controller){
    $this->_controller = $controller;
  }
  
  /**
   * 
   * @see Jazzee.Page::setApplicant()
   */
  public function setApplicant(\Jazzee\Entity\Applicant $applicant){
    $this->_applicant = $applicant;
  }
  
  /**
   * @see Jazzee.Page::getForm()
   */
  public function getForm(){
    if(is_null($this->_form)) $this->_form = $this->makeForm();
    //reset the CSRF token on every request so when submission fails token validation doesn't even if the session has timed out
    $this->_form->setCSRFToken($this->_controller->getCSRFToken());
    return $this->_form;
  }
  
  /**
   * Make the form for the page
   * @return \Foundation\Form or false if no form
   */
  abstract protected function makeForm();
  
  /**
   * 
   * @see Jazzee.Page::validateInput()
   */
  public function validateInput($arr){
    if($input = $this->getForm()->processInput($arr)){
      return $input;
    }
    $this->_controller->addMessage('error', self::ERROR_MESSAGE);
    return false;
  }
  
  /**
   * Most pages don't require any setup
   * @see Jazzee.Page::setupNewPage()
   */
  public function setupNewPage(){
    return;
  }
  
/**
   * (non-PHPdoc)
   * @see Jazzee.Page::showReviewPage()
   */
  public function showReviewPage(){
    return true;
  }
  
  /**
   * Convert an answer to an xml element
   * @param \DomDocument $dom
   * @param \Jazzee\Entity\Answer $answer
   * @return \DomElement
   */
  protected function xmlAnswer(\DomDocument $dom, \Jazzee\Entity\Answer $answer){
    $answerXml = $dom->createElement('answer');
    $answerXml->setAttribute('answerId', $answer->getId());
    $answerXml->setAttribute('uniqueId', $answer->getUniqueId());
    $answerXml->setAttribute('updatedAt', $answer->getUpdatedAt()->format('c'));
    $answerXml->setAttribute('pageStatus', $answer->getPageStatus());
    $answerXml->setAttribute('publicStatus', ($answer->getPublicStatus()?$answer->getPublicStatus()->getName():''));
    $answerXml->setAttribute('privateStatus', ($answer->getPrivateStatus()?$answer->getPrivateStatus()->getName():''));
    foreach($answer->getPage()->getElements() as $element){
      $element->getJazzeeElement()->setController($this->_controller);
      $eXml = $dom->createElement('element');
      $eXml->setAttribute('elementId', $element->getId());
      $eXml->setAttribute('title', htmlentities($element->getTitle(),ENT_COMPAT,'utf-8'));
      $eXml->setAttribute('type', htmlentities($element->getType()->getClass(),ENT_COMPAT,'utf-8'));
      $eXml->setAttribute('weight', $element->getWeight());
      if($value = $element->getJazzeeElement()->rawValue($answer)) $eXml->appendChild($dom->createCDATASection(preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $value)));
      $answerXml->appendChild($eXml);
    }
    $attachment = $dom->createElement('attachment');
    if($answer->getAttachment()) $attachment->appendChild($dom->createCDATASection(base64_encode($answer->getAttachment()->getAttachment())));
    $answerXml->appendChild($attachment);
    
    $children = $dom->createElement('children');
    foreach($answer->getChildren() as $child){
      $children->appendChild($this->xmlAnswer($dom, $child));
    }
    $answerXml->appendChild($children);
    return $answerXml;
  }
  
  /**
   * Default CSV headers are just the elements for a page
   * @return array 
   */
  public function getCsvHeaders(){
    $headers = array();
    foreach($this->_applicationPage->getPage()->getElements() as $element){
      $headers[] = $element->getTitle();
    }
    return $headers;
  }
  
  /**
   * Defaults to just usign the element display values
   * @param int $position
   * @return array
   */
  function getCsvAnswer($position){
    $arr = array();
    $answers = $this->_applicant->findAnswersByPage($this->_applicationPage->getPage());
    foreach($this->_applicationPage->getPage()->getElements() as $element){
      $element->getJazzeeElement()->setController($this->_controller);
      if(isset($answers[$position])){
        $arr[] = $element->getJazzeeElement()->displayValue($answers[$position]);
      } else {
        $arr[] = '';
      }
    }
    return $arr;
  }
  
  /**
   * Create a table from answers
   * and append any attached PDFs
   * @param \Jazzee\ApplicantPDF $pdf 
   */
  public function renderPdfSection(\Jazzee\ApplicantPDF $pdf){
    if($this->getAnswers()){
      $pdf->addText($this->_applicationPage->getTitle(), 'h3');
      $pdf->write();
      $pdf->startTable();
      $pdf->startTableRow();
      foreach($this->_applicationPage->getPage()->getElements() as $element)$pdf->addTableCell($element->getTitle());
      foreach($this->getAnswers() as $answer){
        $pdf->startTableRow();
        foreach($this->_applicationPage->getPage()->getElements() as $element){
          $element->getJazzeeElement()->setController($this->_controller);
          $pdf->addTableCell($element->getJazzeeElement()->pdfValue($answer, $pdf));
        }
        if($attachment = $answer->getAttachment()) $pdf->addPdf($attachment->getAttachment());
      }
      $pdf->writeTable();
    }
  }
  
  /**
   * By default just set the varialbe dont check it
   * @param string $name
   * @param string $value 
   */
  public function setVar($name, $value){
    $var = $this->_applicationPage->getPage()->setVar($name, $value);
    $this->_controller->getEntityManager()->persist($var);
  }
  
  /**
   * Check if the current controller is an admin controller
   * @throws \Jazzee\Exception if it isn't
   */
  protected function checkIsAdmin(){
    if($this->_controller instanceof \Jazzee\AdminController) return true;
    throw new \Jazzee\Exception('Admin only action was called from a non admin controller');
  }
  
  /**
   * Abstract page kills all queries
   * @param \stdClass $obj 
   */
  public function testQuery(\stdClass $obj){
    return false;
  }
}

?>