<?php 
/**
 * manage_roles index view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage manage
 */
if($roles): ?>
  <h5>Roles:</h5>
  <ul>
  <?php foreach($roles as $role): ?>
  <li><?php print $role->getName() ?>
    <?php if($this->controller->checkIsAllowed('manage_roles', 'edit')): ?>
      (<a href='<?php print $this->path('admin/manage/roles/edit/') . $role->getId()?>'>Edit</a>)
    <?php endif;?>
    
  </li>
  <?php endforeach;?>
  </ul>
<?php endif; ?>
<?php if($this->controller->checkIsAllowed('manage_roles', 'new')): ?>
  <p><a href='<?php print $this->path('admin/manage/roles/new')?>'>Add a new role</a></p>
<?php endif;?>
