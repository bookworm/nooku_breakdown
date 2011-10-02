<?php 

abstract class KControllerToolbarAbstract extends KObject implements KObjectIdentifiable
{
  protected $_title = '';
  protected $_icon = '';
  protected $_controller = null;
  protected $_commands = array(); 
  
  public function __construct(KConfig $config = null)
  {
    if(!isset($config)) $config = new KConfig();

    parent::__construct($config);

    $this->_controller = $config->controller;
    $this->setTitle($config->title);
    $this->setIcon($config->icon);    
  }  
  
  protected function _initialize(KConfig $config)
  {
    $config->append(array(
      'title'         => KInflector::humanize($this->getName()),
      'icon'          => $this->getName(),
      'controller'    => null,
    ));

    parent::_initialize($config); 
  }     
  
  public function getIdentifier()
  {
    return $this->_identifier;
  }
  
  public function getController()
  {
    return $this->_controller;
  }

  public function getName()
  {
    return $this->_identifier->name;
  }
  
  public function setTitle($title)
  {
    $this->_title = $title;
    return $this;      
  }
  
  public function getTitle()
  {
    return $this->_title;
  }

  public function setIcon($icon)
  {
    $this->_icon = $icon;
    return $this;
  }
  
  public function getIcon()
  {
    return $this->_icon;
  }
  
  public function addSeparator()
  {
    $this->_commands[] = new KControllerToolbarCommand('separator');
    return $this;    
  } 
  
  public function addCommand($name, $config = array())
  {
    $command = new KControllerToolbarCommand($name, $config);
    
    if(method_exists($this, '_command'.ucfirst($name))) {
      $function =  '_command'.ucfirst($name);
      $this->$function($command);     
    } 
    else 
    {
      if(!isset($command->attribs->href)) 
      {
        $command->append(array(
          'attribs'     => array(
          'data-action' => $command->getName()
          )     
        ));  
      }  
    }
    
    $this->_commands[$name] = $command;
    return $this;            
  }
  
  public function getCommands()
  {
    return $this->_commands;   
  }

  public function reset()
  {
    $this->_commands = array();
    return $this;
  }  
  
  public function __call($method, $args)
  {  
    $parts = KInflector::explode($method);

    if($parts[0] == 'add' && isset($parts[1])) {
      $config = isset($args[0]) ? $args[0] : array();	    
      $this->addCommand(strtolower($parts[1]), $config);
      return $this;        
    }

    return parent::__call($method, $args);    
  }
}