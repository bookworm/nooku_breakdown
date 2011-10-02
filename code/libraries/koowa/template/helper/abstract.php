<?php

abstract class KTemplateHelperAbstract extends KObject implements KTemplateHelperInterface
{ 
  protected $_template;  
  
  public function __construct(KConfig $config)
	{
    parent::__construct($config);

    $this->_template = $config->template;        
	}       
	
	public function getIdentifier()
	{
		return $this->_identifier;
	}  
	
	public function getTemplate()
  {
    return $this->_template;
  }
}