<?php

class KDatabaseBehaviorLockable extends KDatabaseBehaviorAbstract
{
  protected $_lifetime;
    
  protected function _initialize(KConfig $config)
  {
    $config->append(array(
      'priority'   => KCommand::PRIORITY_HIGH,
      'lifetime'   => '900'
    ));

    $this->_lifetime = $config->lifetime;

    parent::_initialize($config);  
  }             
  
  public function getMixableMethods(KObject $mixer = null)
  {
    $methods = array();

    if(isset($mixer->locked_by) && isset($mixer->locked_on))
      $methods = parent::getMixableMethods($mixer);

    return $methods;
  }    
  
  public function lock()
  {
    if(!$this->isNew() && !$this->locked()) {
      $this->locked_by = (int) KFactory::get('lib.joomla.user')->get('id');
      $this->locked_on = gmdate('Y-m-d H:i:s');
      $this->save();      
    }

    return true;
  }  
  
  public function unlock()
  {
    $userid = KFactory::get('lib.joomla.user')->get('id');

    if(!$this->isNew() && $this->locked_by != 0 && $this->locked_by == $userid)
    {
      $this->locked_by = 0;
      $this->locked_on = 0;
      $this->save();   
    }

    return true;
  }  
  
  public function locked()
  {
    $result = false;     
    
    if(!$this->isNew())
    {
      if(isset($this->locked_on) && isset($this->locked_by)) 
      {    
        $locked  = strtotime($this->locked_on);
        $current = strtotime(gmdate('Y-m-d H:i:s'));
              
        if($current - $locked < $this->_lifetime) {
          $userid = KFactory::get('lib.joomla.user')->get('id');
          if($this->locked_by != 0 && $this->locked_by != $userid)
            $result= true;         
        }
      }
    }

    return $result;
  }   
  
  public function lockMessage()
  {
    $message = '';

    if($this->locked())
    {
      $user = KFactory::tmp('lib.joomla.user', array($this->locked_by));      
      $date = KTemplateHelper::factory('date')->humanize(array('date' => $this->locked_on));

      $message = JText::sprintf('Locked by %s %s', $user->get('name'), $date);
    }

    return $message;       
  } 
  
  protected function _beforeTableUpdate(KCommandContext $context)
  {
    return (bool) !$this->locked();
  }          
  
  protected function _beforeTableDelete(KCommandContext $context)
  {
    return (bool) !$this->locked();
  }
}