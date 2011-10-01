<?php

class KFactoryAdapterJoomla extends KFactoryAdapterAbstract
{
  protected $_alias_map = array(
    'Database'      => 'DBO',
    'Authorization' => 'ACL',
    'Xml'    	    	=> 'XMLParser'   
	);  
	
	public function instantiate($identifier, KConfig $config)
	{
		$instance = false;

    if($identifier->type == 'lib' && $identifier->package == 'joomla')
    {
      $name = ucfirst($identifier->name);

      if(array_key_exists($name, $this->_alias_map)) 
      	$name = $this->_alias_map[$name];

      $instance = call_user_func_array(array('JFactory', 'get'.$name), $config->toArray());
    }

    return $instance; 
	}
}