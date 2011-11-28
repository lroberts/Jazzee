<?php
namespace Jazzee\Entity\Element;
/**
 * PDF File Element
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 */
class PDFFileInput extends AbstractElement {
  public function addToField(\Foundation\Form\Field $field){
    if(!ini_get('file_uploads')){
      throw new \Jazzee\Exception('File uploads are not turned on for this system and a PDFFileInputElement is being created', E_ERROR);
    }
    $element = $field->newElement('FileInput', 'el' . $this->_element->getId());
    $element->setLabel($this->_element->getTitle());
    $element->setInstructions($this->_element->getInstructions());
    $element->setFormat($this->_element->getFormat());
    $element->setDefaultValue($this->_element->getDefaultValue());
    if($this->_element->isRequired()){
      $validator = new \Foundation\Form\Validator\NotEmpty($element);
      $element->addValidator($validator);
    }
    $element->addValidator(new \Foundation\Form\Validator\Virusscan($element));
    $element->addValidator(new \Foundation\Form\Validator\PDF($element));
    $element->addFilter(new \Foundation\Form\Filter\Blob($element));
    
    $config = new \Jazzee\Configuration();
    if($config->getMaximumApplicantFileUploadSize()) $max = $config->getMaximumApplicantFileUploadSize();
    else $max = \convertIniShorthandValue(\ini_get('upload_max_filesize'));
    if($this->_element->getMax() and \convertIniShorthandValue($this->_element->getMax()) < $max) $max = $this->_element->getMax();
    
    $element->addValidator(new \Foundation\Form\Validator\MaximumFileSize($element, $max));
    
    return $element;
  }
  
  public function getElementAnswers($input){
    $elementAnswers = array();
    if(!is_null($input)){
      $elementAnswer = new \Jazzee\Entity\ElementAnswer;
      $elementAnswer->setElement($this->_element);
      $elementAnswer->setPosition(0);
      $elementAnswer->setEBlob($input);
      $elementAnswers[] = $elementAnswer;
    }
    return $elementAnswers;
  }
  
  public function displayValue(\Jazzee\Entity\Answer $answer){
    $elementsAnswers = $answer->getElementAnswersForElement($this->_element);
    if(isset($elementsAnswers[0])){
      $blob = $elementsAnswers[0]->getEBlob();
      $name = $this->_element->getTitle() . '_' . $elementsAnswers[0]->getId();
      
      $config = new \Jazzee\Configuration();
      $path = $config->getVarPath()?$config->getVarPath():__DIR__ . '/../../../../var';
      if(!$varPath = \realpath($path) or !\is_dir($varPath) or !\is_writable($varPath)){
        if($varPath) $path = $varPath; //nicer error message if the path exists
        throw new \Jazzee\Exception("{$path} is not readable by the webserver so we cannot use it for caching PDF files");
      }
      $pdf = new \Foundation\Virtual\VirtualFile($name . '.pdf', $blob, $answer->getUpdatedAt()->format('c'));
      $cachePath = $varPath . '/cache/' . (sha1('applicant' . $answer->getApplicant()->getId() . 'answer' . $answer->getId() . 'element' . $this->_element->getId() . 'elementAnswer' . $elementsAnswers[0]->getId())) . '.pdfPreview.png';
      $png = new \Foundation\Virtual\VirtualFile($name . '.png', \thumbnailPDF($blob, 100, 0, $cachePath), $answer->getUpdatedAt()->format('c'));

      $session = new \Foundation\Session();
      $store = $session->getStore('files', 900);
      $pdfStoreName = md5($name . '.pdf');
      $pngStoreName = md5($name . '.png');
      $store->$pdfStoreName = $pdf; 
      $store->$pngStoreName = $png;
      
      return '<a href="index.php?url=file/' . \urlencode($name . '.pdf') . '"><img src="index.php?url=file/' . urlencode($name . '.png') . '" /></a>';
    }
    return null;
  }
  
  public function rawValue(\Jazzee\Entity\Answer $answer){
    $elementsAnswers = $answer->getElementAnswersForElement($this->_element);
    if(isset($elementsAnswers[0])){
      return base64_encode($elementsAnswers[0]->getEBlob());
    }
    return null;
  }
  
  public function pdfValue(\Jazzee\Entity\Answer $answer, \Jazzee\ApplicantPDF $pdf){
    $elementsAnswers = $answer->getElementAnswersForElement($this->_element);
    if(isset($elementsAnswers[0])){
      $pdf->addPdf($elementsAnswers[0]->getEBlob());
      return 'Attached';
    }
    return null;
  }
  
  public function formValue(\Jazzee\Entity\Answer $answer){
    return false;
  }
}
?>