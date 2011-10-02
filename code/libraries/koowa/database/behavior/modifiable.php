<?php 

class KDatabaseBehaviorModifiable extends KDatabaseBehaviorAbstract
{
  protected function _initialize(KConfig $config)
  {
    $config->append(array(
      'priority'   => KCommand::PRIORITY_LOW,
    ));

    parent::_initialize($config);    
  }  
  
  public function getMixableMethods(KObject $mixer = null)
  {
    $methods = array();

    if(isset($mixer->modified_by) || isset($mixer->modified_on))
      $methods = parent::getMixableMethods($mixer);

    return $methods;  
  }    
  
  protected function _beforeTableUpdate(KCommandContext $context)
  {
    $modified = $this->getTable()->filter(array_flip($this->getModified()));
    
    if(!empty($modified))
    {
      if(isset($this->modified_by))
        $this->modified_by = (int) KFactory::get('lib.joomla.user')->get('id');
    
      if(isset($this->modified_on))
        $this->modified_on = gmdate('Y-m-d H:i:s');
    }
  }   
}