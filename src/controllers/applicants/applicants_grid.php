<?php

set_time_limit(1200);

/**
 * Applicant Grid
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class ApplicantsGridController extends \Jazzee\AdminController
{

  const MENU = 'Applicants';
  const TITLE = 'Grid View';
  const PATH = 'applicants/grid';
  const ACTION_INDEX = 'All Applicants';

  /**
   * Add the required JS
   */
  protected function setUp()
  {
    parent::setUp();
    $this->layout = 'json';
    $this->addCss('https://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables.css');
    $this->addCss('https://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables_themeroller.css');
    $this->addScript('https://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js');
    
    $this->addScript($this->path('resource/scripts/classes/Display.class.js'));
    $this->addScript($this->path('resource/scripts/classes/Application.class.js'));
    $this->addScript($this->path('resource/scripts/classes/ApplicantData.class.js'));
    $this->addScript($this->path('resource/scripts/classes/DisplayChooser.class.js'));
    $this->addScript($this->path('resource/scripts/classes/DisplayManager.class.js'));
    $this->addScript($this->path('resource/scripts/classes/Grid.class.js'));
    $this->addScript($this->path('resource/scripts/controllers/applicants_grid.controller.js'));

    $this->addCss($this->path('resource/styles/grid.css'));
    $this->addCss($this->path('resource/styles/displaymanager.css'));

    $this->addScript($this->path('resource/foundation/scripts/form.js'));
    $this->addScript($this->path('resource/foundation/scripts/jquery.wysiwyg.js'));
    $this->addScript($this->path('resource/foundation/scripts/jquery.filter_input.js'));
    $this->addCss($this->path('resource/foundation/styles/jquery.wysiwyg.css'));

    $scripts = array();
    
    //add all of the JazzeePage scripts for display
    $types = $this->_em->getRepository('\Jazzee\Entity\PageType')->findAll();
    $scripts = array();
    $scripts[] = $this->path('resource/scripts/page_types/JazzeePage.js');
    foreach ($types as $type) {
      $class = $type->getClass();
      $scripts[] = $this->path($class::pageBuilderScriptPath());
    }
    $scripts = array_unique($scripts);
    foreach ($scripts as $path) {
      $this->addScript($path);
    }
    
    //add all of the Jazzee element scripts for data rendering
    $this->addScript($this->path('resource/scripts/element_types/JazzeeElement.js'));

    $types = $this->_em->getRepository('\Jazzee\Entity\ElementType')->findAll();
    $scripts[] = $this->path(\Jazzee\Interfaces\Element::PAGEBUILDER_SCRIPT);
    $scripts[] = $this->path('resource/scripts/element_types/List.js');
    $scripts[] = $this->path('resource/scripts/element_types/FileInput.js');
    foreach ($types as $type) {
      $class = $type->getClass();
      $scripts[] = $this->path($class::PAGEBUILDER_SCRIPT);
    }
    $scripts = array_unique($scripts);
    foreach ($scripts as $path) {
      $this->addScript($path);
    }
  }

  public function actionIndex()
  {
    $this->layout = 'wide';
  }
  
  /**
   * List all applicants
   */

  public function actionDownloadXls()  
  {
    //use a full applicant display where display is needed
    $display = new \Jazzee\Display\FullApplication($this->_application);
    $applicationPages = array();
    $pageAnswerCount = $this->_em->getRepository('Jazzee\Entity\Application')->getPageAnswerCounts($this->_application);
    $rows = array();
    $header = array(
      'ID',
      'First Name',
      'Middle Name',
      'Last Name',
      'Suffix',
      'Email',
      'Locked',
      'Last Login',
      'Last Update',
      'Account Created',
      'Status',
      'Progress',
      'Tags'
    );
    $applicationPages = array();
    foreach ($this->_em->getRepository('\Jazzee\Entity\ApplicationPage')->findBy(array('application' => $this->_application->getId(), 'kind' => \Jazzee\Entity\ApplicationPage::APPLICATION), array('weight' => 'asc')) as $applicationPage) {
      if($applicationPage->getJazzeePage() instanceof \Jazzee\Interfaces\CsvPage){
        $applicationPage->getJazzeePage()->setController($this);
        $applicationPages[] = $applicationPage;
        for ($i = 1; $i <= $pageAnswerCount[$applicationPage->getPage()->getId()]; $i++) {
          foreach ($applicationPage->getJazzeePage()->getCsvHeaders() as $title) {
            $header[] = $applicationPage->getTitle() . ' ' . $i . ' ' . $title;
          }
        }
      }
    }
    $fileName = tempnam($this->_config->getVarPath() . '/tmp/', 'applicants_download');
    $handle = fopen($fileName, 'w+');
    $this->writeXlsFile($header, $handle);
    $applicants = explode(',',$this->post['applicantIds']);
    foreach ($applicants as $id) {
      $applicant = $this->_application->formatApplicantArray($this->_em->getRepository('Jazzee\Entity\Applicant')->findArray($id, $display));
      $arr = array(
        $applicant['id'],
        $applicant['firstName'],
        $applicant['middleName'],
        $applicant['lastName'],
        $applicant['suffix'],
        $applicant['email'],
        $applicant['isLocked']? 'yes':'no',
        $applicant['lastLogin']->format('c'),
        $applicant['updatedAt']->format('c'),
        $applicant['createdAt']->format('c'),
        $applicant['decision'] ? $applicant['decision']['status'] : 'none',
        $applicant['percentComplete'] * 100 . '%'
      );
      $tags = array();
      foreach ($applicant['tags'] as $tag) {
        $tags[] = $tag['title'];
      }
      $arr[] = implode(' ', $tags);
      $pages = array();
      foreach($applicationPages as $applicationPage){
        $pages[$applicationPage->getPage()->getId()] = array();
      }
      foreach($applicant['pages'] as $page){
        $pages[$page['id']] = $page;
      }
      foreach ($applicationPages as $applicationPage) {
        for ($i = 0; $i < $pageAnswerCount[$applicationPage->getPage()->getId()]; $i++) {
          $arr = array_merge($arr, $applicationPage->getJazzeePage()->getCsvAnswer($pages[$applicationPage->getPage()->getId()], $i));
        }
      }
      $this->writeXlsFile($arr, $handle);
    }
    rewind($handle);
    fclose($handle);
    $this->setVar('outputType', 'file');
    $this->setVar('type', 'text/xls');
    $this->setVar('filename', $this->_application->getProgram()->getShortName() . '-' . $this->_application->getCycle()->getName() . date('-mdy') . '.xls');
    $this->setVar('filePath', $fileName);
    $this->loadView('applicants_grid/download');
  }
  
  /**
   * Write array data to csv file
   * @param array $data
   * @param resource $handle
   */
  protected function writeXlsFile(array $data, $handle){
    $string = '';
    foreach ($data as $value) {
      $string .= '"' . $value . '"' . "\t";
    }
    $string .= "\n";
    fwrite($handle, $string);
  }
  
  /**
   * List all applicants
   */

  public function actionDownloadJson()  
  {
    //use a full applicant display where display is needed
    $display = new \Jazzee\Display\FullApplication($this->_application);
    $applicants = explode(',',$this->post['applicantIds']);
    $applicants = array_slice($applicants, 0, 50);
    $arr = array();
    $count = 0;
    foreach ($applicants as $id) {
      $applicant = $this->_em->getRepository('Jazzee\Entity\Applicant')->findArray($id, $display);
      $arr[] = $this->_application->formatApplicantArray($applicant);
      $count++;
    }
    $this->setVar('outputType', 'json');
    $this->setVar('filename', $this->_application->getProgram()->getShortName() . '-' . $this->_application->getCycle()->getName() . date('-mdy') . '.json');
    $this->setVar('output', array('applicants' => $arr));
    $this->loadView('applicants_grid/download');
  }
  
  /**
   * List all applicants
   */

  public function actionDownloadXml()  
  {
    $applicants = explode(',',$this->post['applicantIds']);
    $xml = new DOMDocument();
    $xml->formatOutput = true;
    $applicantsXml = $xml->createElement("applicants");
    $count = 0;
    foreach ($applicants as $id) {
      $applicant = $this->_em->getRepository('Jazzee\Entity\Applicant')->find($id, true);
      $appXml = $applicant->toXml($this);
      $node = $xml->importNode($appXml->documentElement, true);
      $applicantsXml->appendChild($node);
      if ($count > 50) {
        $count = 0;
        $this->_em->clear();
        gc_collect_cycles();
      }
      $count++;
    }
    $xml->appendChild($applicantsXml);
    $this->setVar('outputType', 'xml');
    $this->setLayout('xml');
    $this->setLayoutVar('filename', $this->_application->getProgram()->getShortName() . '-' . $this->_application->getCycle()->getName() . date('-mdy'));
    $this->setVar('xml', $xml);
    $this->loadView('applicants_grid/download');
  }
  
  /**
   * List all applicants
   */
  public function actionDownloadPdfArchive()  
  {
    //use a full applicant display where display is needed
    $display = new \Jazzee\Display\FullApplication($this->_application);
    $applicants = explode(',',$this->post['applicantIds']);

    // ensure garbage collection is on, we need it
    gc_enable();

    $directoryName = $this->_application->getProgram()->getShortName() . '-' . $this->_application->getCycle()->getName() . date('-mdy');
    $zipFile = $this->_config->getVarPath() . '/tmp/' . uniqid() . '.zip';
    $zip = new ZipArchive;
    $zip->open($zipFile, ZipArchive::CREATE);
    $zip->addEmptyDir($directoryName);

    $tempFileArray = array();
    $tmppath = $this->_config->getVarPath() . '/tmp';
    foreach(array_chunk($applicants, 20) as $limitedIds){
      $applicantsDisplayArray = $this->_em->getRepository('Jazzee\Entity\Applicant')->findDisplayArrayByApplication($this->_application, $display, $limitedIds);
      foreach($applicantsDisplayArray as $applicantArray){
        $pdf = new \Jazzee\ApplicantPDF($this->_config->getPdflibLicenseKey(), \Jazzee\ApplicantPDF::USLETTER_LANDSCAPE, $this);
        $temp_file_name = tempnam($tmppath, 'JazzeeTempDL');
        $tempFileArray[] = $temp_file_name;
        file_put_contents($temp_file_name, $pdf->pdfFromApplicantArray($this->_application, $applicantArray));
        $externalIDSuffix = ($applicantArray['externalId'] != null) ? ' - '.$applicantArray['externalId'] : "";
        $zip->addFile($temp_file_name, $directoryName . '/' . $applicantArray['fullName'] . $externalIDSuffix . '.pdf');
        unset($pdf);
      }
    }
    $zip->close();
    $this->setVar('outputType', 'file');
    $this->setVar('type', 'application/zip');
    $this->setVar('filename', $directoryName . '.zip');
    $this->setVar('filePath', $zipFile);
    $this->loadView('applicants_grid/download');
  }


  public function actionDownloadPdfTemplateArchive()  
  {
    //use a full applicant display where display is needed
    $display = new \Jazzee\Display\FullApplication($this->_application);
    $applicants = explode(',',$this->post['applicantIds']);
    error_log("pdf template? ".$this->post['pdftemplate']);
$templateId = (isset($this->post['pdftemplate'])) ? $this->post['pdftemplate']
: null;
    // ensure garbage collection is on, we need it
    gc_enable();

    $directoryName = $this->_application->getProgram()->getShortName() . '-' . $this->_application->getCycle()->getName() . date('-mdy');
    $zipFile = $this->_config->getVarPath() . '/tmp/' . uniqid() . '.zip';
    $zip = new ZipArchive;
    $zip->open($zipFile, ZipArchive::CREATE);
    $zip->addEmptyDir($directoryName);

    $tempFileArray = array();
    $tmppath = $this->_config->getVarPath() . '/tmp';
//    foreach(array_chunk($applicants, 20) as $limitedIds){
//      $applicantsDisplayArray = $this->_em->getRepository('Jazzee\Entity\Applicant')->findDisplayArrayByApplication($this->_application, $display, $limitedIds);
//      foreach($applicantsDisplayArray as $applicantArray){
    foreach($applicants as $applicantId){
        $applicant = $this->getApplicantById($applicantId);

	if(($templateId != null) && ($template = $this->_application->getTemplateById($templateId))){
           $pdf = new \Jazzee\TemplatePDF($this->_config->getPdflibLicenseKey(), $template, $this);
           $temp_file_name = tempnam($tmppath, 'JazzeeTempDL');
           $tempFileArray[] = $temp_file_name;
           file_put_contents($temp_file_name, $pdf->pdf($applicant));
           $externalIDSuffix = ($applicant->getExternalId() != null) ? ' - '.$applicant->getExternalId() : "";
           $zip->addFile($temp_file_name, $directoryName . '/' . $applicant->getFullName() . $externalIDSuffix . '.pdf');
           unset($pdf);
        }else{
	   throw new Error("Template ID [".$templateId."] not found.");
        }
      }
    //}
    $zip->close();
    $this->setVar('outputType', 'file');
    $this->setVar('type', 'application/zip');
    $this->setVar('filename', $directoryName . '.zip');
    $this->setVar('filePath', $zipFile);
    $this->loadView('applicants_grid/download');
  }
  
  /**
   * Get applicant JSON
   */
  public function actionListApplicants(){
    $applicants = $this->_em->getRepository('Jazzee\Entity\Applicant')->findIdsByApplication($this->_application, true);
    $this->setVar('result', $applicants);
    $this->loadView('applicants_single/result');
  }
  
  /**
   * Get applicant JSON
   */
  public function actionGetApplicants(){

    $results = array();
    $display = $this->getDisplay($this->post['display']);
    $applicants = $this->_em->getRepository('Jazzee\Entity\Applicant')->findDisplayArrayByApplication($this->_application, $display, $this->post['applicantIds']);

    $pages = array();
    $this->setVar('result', array('applicants' => $applicants,
				  'pages' => $pages));
    $this->loadView('applicants_single/result');
  }

  /**
   * Controll actions with the index action
   * @param string $controller
   * @param string $action
   * @param \Jazzee\Entity\User $user
   * @param \Jazzee\Entity\Program $program
   * @return bool
   */
  public static function isAllowed($controller, $action, \Jazzee\Entity\User $user = null, \Jazzee\Entity\Program $program = null, \Jazzee\Entity\Application $application = null)
  {
    if (in_array($action, array('getApplicants', 'listApplicants', 'describeDisplay','downloadXls','downloadJson','downloadXml', 'downloadPdfArchive', 'downloadPdfTemplateArchive'))) {
      $action = 'index';
    }

    return parent::isAllowed($controller, $action, $user, $program, $application);
  }

}