<?php

class KControllerToolbarCommand extends KConfig
{
  public function __construct( $name, $config = array() )
  { 
    parent::__construct($config);

    $this->append(array(
      'icon'     => 'icon-32-'.$name,
      'id'       => $name,
      'label'    => ucfirst($name),
      'disabled' => false,
      'title'		 => '', 
      'attribs'  => array(
      'class'    => array(),
      )   
    ));

    $this->_name = $name;  
  } 
  
  public function getName()
  {
    return $this->_name;
  }
}