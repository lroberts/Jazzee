<?php
namespace Jazzee\Entity;

/** 
 * User
 * Admin users details
 * @Entity(repositoryClass="\Jazzee\Entity\UserRepository")
 * @Table(name="users") 
 * @package    jazzee
 * @subpackage orm
 **/
class User{
  /**
    * @Id 
    * @Column(type="bigint")
    * @GeneratedValue(strategy="AUTO")
  */
  private $id;
  
  /**
   * Unique eduPersonPrincipalName for use with SAML authentication
   * @Column(type="string", unique=true) 
   * */
  private $eduPersonPrincipalName;
  
  /** @Column(type="string", nullable="true") */
  private $email;
  
  /** @Column(type="string", nullable="true") */
  private $firstName;
  
  /** @Column(type="string", nullable="true") */
  private $lastName;
  
  /** 
   * @ManyToOne(targetEntity="Program",cascade={"all"})
   * @JoinColumn(onDelete="SET NULL", onUpdate="CASCADE") 
   */
  private $defaultProgram;
  
  /** 
   * @ManyToOne(targetEntity="Cycle",cascade={"all"})
   * @JoinColumn(onDelete="SET NULL", onUpdate="CASCADE") 
   */
  private $defaultCycle;
  
  /**
   * @ManyToMany(targetEntity="Role", inversedBy="users")
   * @JoinTable(name="user_roles")
  **/
  private $roles;
  

  public function __construct(){
    $this->roles = new \Doctrine\Common\Collections\ArrayCollection();
  }
  
  /**
   * Get id
   *
   * @return bigint $id
   */
  public function getId(){
    return $this->id;
  }

  /**
   * Set eduPersonPrincipalName
   *
   * @param string $eduPersonPrincipalName
   */
  public function setEduPersonPrincipalName($eduPersonPrincipalName){
    $this->eduPersonPrincipalName = $eduPersonPrincipalName;
  }

  /**
   * Get eduPersonPrincipalName
   *
   * @return string $eduPersonPrincipalName
   */
  public function getEduPersonPrincipalName(){
    return $this->eduPersonPrincipalName;
  }

  /**
   * Set email
   *
   * @param string $email
   */
  public function setEmail($email){
    $this->email = $email;
  }

  /**
   * Get email
   *
   * @return string $email
   */
  public function getEmail(){
    return $this->email;
  }

  /**
   * Set firstName
   *
   * @param string $firstName
   */
  public function setFirstName($firstName){
    $this->firstName = $firstName;
  }

  /**
   * Get firstName
   *
   * @return string $firstName
   */
  public function getFirstName(){
    return $this->firstName;
  }

  /**
   * Set lastName
   *
   * @param string $lastName
   */
  public function setLastName($lastName){
    $this->lastName = $lastName;
  }

  /**
   * Get lastName
   *
   * @return string $lastName
   */
  public function getLastName(){
    return $this->lastName;
  }

  /**
   * Set defaultProgram
   *
   * @param Entity\Program $defaultProgram
   */
  public function setDefaultProgram(Program $defaultProgram){
    $this->defaultProgram = $defaultProgram;
  }

  /**
   * Get defaultProgram
   *
   * @return Entity\Program $defaultProgram
   */
  public function getDefaultProgram(){
    return $this->defaultProgram;
  }

  /**
   * Set defaultCycle
   *
   * @param Entity\Cycle $defaultCycle
   */
  public function setDefaultCycle(Cycle $defaultCycle){
    $this->defaultCycle = $defaultCycle;
  }

  /**
   * Get defaultCycle
   *
   * @return Entity\Cycle $defaultCycle
   */
  public function getDefaultCycle(){
    return $this->defaultCycle;
  }

  /**
   * Add role
   *
   * @param Entity\Role $role
   */
  public function addRole(Role $role){
    $this->roles[] = $role;
  }

  /**
   * Get roles
   *
   * @return Doctrine\Common\Collections\Collection $roles
   */
  public function getRoles(){
    return $this->roles;
  }
  
  /**
   * Check if a user is allowed to access a resource
   * 
   * @param string $controller
   * @param string $action
   * @param \Jazzee\Entity\Program $program
   */
  public function isAllowed($controller, $action, \Jazzee\Entity\Program $program = null){
    foreach($this->roles as $role) {
      if(($role->isGlobal() or $role->getProgram() == $program) and $role->isAllowed($controller, $action)) {
        return true;
      }
    }
    return false;
  }
}

/**
 * UserRepository
 * Special Repository methods for User to make searchign for special conditions easier
 */
class UserRepository extends \Doctrine\ORM\EntityRepository{
  
  /**
   * find all by name
   * 
   * @param string $firstName
   * @param string $lastName
   * 
   * @return Doctrine\Common\Collections\Collection \Jazzee\Entity\User
   */
  public function findByName($firstName, $lastName){
    $query = $this->_em->createQuery('SELECT u FROM Jazzee\Entity\User u WHERE (u.firstName IS NULL OR u.firstName LIKE :firstName) AND (u.lastName IS NULL OR u.lastName LIKE :lastName)');
    $query->setParameter('firstName', $firstName);
    $query->setParameter('lastName', $lastName);
    return $query->getResult();
  }
  
/**
   * find all users in a program
   * 
   * @param \Jazzee\Entity\Program $program
   * 
   * @return Doctrine\Common\Collections\Collection \Jazzee\Entity\User
   */
  public function findByProgram($program){
    $query = $this->_em->createQuery('SELECT u FROM Jazzee\Entity\User u JOIN u.roles r WHERE r.program = :programId');
    $query->setParameter('programId', $program->getId());
    return $query->getResult();
  }
}