<?php
namespace Jazzee\Entity;

/**
 * Custom hydrator for applicant records the loads all of the answer data from each
 * page and element class
 * 
 */
class ApplicantArrayHydrator extends \Doctrine\ORM\Internal\Hydration\ArrayHydrator
{
  /**
   * Hydrate applicant records
   * 
   * @param type $stmt
   * @param type $resultSetMapping
   * @param array $hints
   * @return array
   */
  public function hydrateAll($stmt, $resultSetMapping, array $hints = array()) 
  {
    $result = parent::hydrateAll($stmt, $resultSetMapping, $hints);
    foreach($result as $key => $applicant){
      $answers = $applicant['answers'];
      $applicant['answers'] = array();
      foreach($answers as $answer){
        $pageId = $answer['page_id'];
        if(!isset($applicant['answers'][$pageId])){
          $applicant['answers'][$pageId] = array();
        }
        $applicant['answers'][$pageId][] = $this->hydrateAnswer($answer);
      }
      $result[$key] = $applicant;
    }
    
    return $result;
  }
  
  /**
   * Hydrate answer array seperatly so children can be done recursivly
   * 
   * @param array $answer
   * 
   * @return array
   */
  protected function hydrateAnswer(array $answer)
  {
    $elements = $answer['elements'];
    $answer['elements'] = array();
    foreach($elements as $element){
      $elementId = $element['element_id'];
      if(!isset($answer['elements'][$elementId])){
        $answer['elements'][$elementId] = array();
      }
      $answer['elements'][$elementId][] = $element;
    }
    if(array_key_exists('children', $answer)){
      $children = $answer['children'];
      $answer['children'] = array();
      foreach($children as $child){
        $answer['children'][] = $this->hydrateAnswer($child);
      }
    } else {
      $answer['children'] = array();
    }
    
    return $answer;
  }
}