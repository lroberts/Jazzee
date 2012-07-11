<?php
namespace Jazzee\Entity;

/**
 * ApplicationRepository
 * Special Repository methods for Application to make searchign for special conditions easier
 * 
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class ApplicationRepository extends \Doctrine\ORM\EntityRepository
{

  /**
   * findOneByProgramAndCycle
   * Search for an Application using its Program and Cycle
   * @param Program $program
   * @param Cycle $cycle
   * @return Application
   */
  public function findOneByProgramAndCycle(Program $program, Cycle $cycle)
  {
    $query = $this->_em->createQuery('SELECT a FROM Jazzee\Entity\Application a WHERE a.program = :programId AND  a.cycle = :cycleId');
    $query->setParameter('programId', $program->getId());
    $query->setParameter('cycleId', $cycle->getId());
    $result = $query->getResult();
    if (count($result)) {
      return $result[0];
    }

    return false;
  }

  /**
   * findByProgram
   * Search for all the Applications belonging to a program
   * @param Program $program
   * @return Doctrine\Common\Collections\Collection $applications
   */
  public function findByProgram(Program $program)
  {
    $query = $this->_em->createQuery('SELECT a FROM Jazzee\Entity\Application a JOIN a.cycle c WHERE a.program = :programId ORDER BY c.start DESC');
    $query->setParameter('programId', $program->getId());

    return $query->getResult();
  }

  /**
   * Find an application be the program short name and cycle name
   *
   * @param string $programShortName
   * @param string $cycleNamme
   * @return Application
   */
  public function findEasy($programShortName, $cycleName)
  {
    $query = $this->_em->createQuery('SELECT a FROM Jazzee\Entity\Application a WHERE a.program = (SELECT p FROM Jazzee\Entity\Program p WHERE p.shortName = :programShortName) AND  a.cycle = (SELECT c FROM \Jazzee\Entity\Cycle c WHERE c.name= :cycleName)');
    $query->setParameter('programShortName', $programShortName);
    $query->setParameter('cycleName', $cycleName);
    $result = $query->getResult();
    if (count($result)) {
      return $result[0];
    }

    return false;
  }

}