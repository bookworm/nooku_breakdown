<?php

class KControllerBehaviorCommandable extends KControllerBehaviorAbstract
{
  protected $_toolbar;     
  
  public function __construct(KConfig $config)
	{
    parent::__construct($config);
    $this->_toolbar = $config->toolbar;   
	}
	
	protected function _initialize(KConfig $config)
  {
    $config->append(array(
      'toolbar'	=> null,
    ));

    parent::_initialize($config);   
  }      
  
  public function getToolbar()
  { 
    if(!$this->_toolbar instanceof KControllerToolbarAbstract)
    {	   
      if(!($this->_toolbar instanceof KIdentifier)) 
        $this->setToolbar($this->_toolbar);

      $config = array(
        'controller' => $this->getMixer()
      );

      $this->_toolbar = KFactory::tmp($this->_toolbar, $config);    
    }    
 
    return $this->_toolbar;
  }  
  
  public function setToolbar($toolbar)
  {
    if(!($toolbar instanceof KControllerToolbarAbstract))
    {
      if(is_string($toolbar) && strpos($toolbar, '.') === false ) 
      {
        $identifier         = clone $this->_identifier;
        $identifier->path   = array('controller', 'toolbar');
        $identifier->name   = $toolbar;        
      }
      else $identifier = KFactory::identify($toolbar);

      if($identifier->path[1] != 'toolbar')
        throw new KControllerBehaviorException('Identifier: '.$identifier.' is not a toolbar identifier');

      $toolbar = $identifier;     
    }

    $this->_toolbar = $toolbar;
    return $this;
  }  
  
  protected function _beforeGet(KCommandContext $context)
  {
    if(!$this->_toolbar)
      $this->setToolbar($this->getView()->getName());   
  }  
  
  protected function _afterRead(KCommandContext $context)
  { 
    if($this->_toolbar)
    {
      $name = ucfirst($context->caller->getIdentifier()->name);

      if($this->getModel()->getState()->isUnique()) {        
        $saveable = $this->canEdit();
        $title    = 'Edit '.$name;
      } 
      else {
        $saveable = $this->canAdd();
        $title    = 'New '.$name;  
      }

      if($saveable)
      {
        $this->getToolbar()
          ->setTitle($title)
          ->addCommand('save')
          ->addCommand('apply');    
      }
   
      $this->getToolbar()->addCommand('cancel',  array('attribs' => array('data-novalidate' => 'novalidate')));        
    }  
  }   
  
  protected function _afterBrowse(KCommandContext $context)
  {    
    if($this->_toolbar)
    {
      if($this->canAdd()) 
      {
        $identifier = $context->caller->getIdentifier();
        $config = array('attribs' => array(
          'href' => JRoute::_( 'index.php?option=com_'.$identifier->package.'&view='.$identifier->name)
        ));

        $this->getToolbar()->addCommand('new', $config);  
      }

      if($this->canDelete())
        $this->getToolbar()->addCommand('delete');
    } 
  }          
}