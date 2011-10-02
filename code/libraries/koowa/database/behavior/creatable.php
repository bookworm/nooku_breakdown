<?php

class KDatabaseBehaviorCreatable extends KDatabaseBehaviorAbstract
{
  public function getMixableMethods(KObject $mixer = null)
  {
    $methods = array();

    if(isset($mixer->created_by) || isset($mixer->created_on))
      $methods = parent::getMixableMethods($mixer);

    return $methods;  
  }  
  
  protected function _beforeTableInsert(KCommandContext $context)
  {
    if(isset($this->created_by) && empty($this->created_by))
      $this->created_by  = (int) KFactory::get('lib.joomla.user')->get('id');

    if(isset($this->created_on) && (empty($this->created_on) || $this->created_on == $context->caller->getDefault('created_on')))
      $this->created_on  = gmdate('Y-m-d H:i:s');
  }
}