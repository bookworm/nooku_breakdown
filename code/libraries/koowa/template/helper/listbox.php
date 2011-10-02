<?php

class KTemplateHelperListbox extends KTemplateHelperSelect
{
  protected function _listbox($config = array())
 	{
		$config = new KConfig($config);
    $config->append(array(
    'name'		=> '',
    'state' 	=> null,
    'attribs'	=> array(),
    'model'	  => null,
    'prompt'  => '- Select -', 
    ))->append(array(
      'value'	   => $config->name,
      'selected' => $config->{$config->name}    
    ))->append(array(
      'text'		 => $config->value,
      'column'   => $config->value,
      'deselect' => true   
    ));
		
		$app        = $this->getIdentifier()->application;
    $package    = $this->getIdentifier()->package;
		$identifier = $app.'::com.'.$package.'.model.'.($config->model ? $config->model : KInflector::pluralize($package));
		
 		$list = KFactory::tmp($identifier)->getColumn($config->column);
		
    $options   = array();
    if($config->deselect) 
    	$options[] = $this->option(array('text' => JText::_($config->prompt)));
		
 		foreach($list as $item) {
			$options[] =  $this->option(array('text' => $item->{$config->text}, 'value' => $item->{$config->value}));
		}
		
		$config->options = $options;

		return $this->optionlist($config);
 	}
 	
 	public function __call($method, array $arguments)
  {   
    if(!in_array($method, $this->getMethods())) 
    {
      $config = $arguments[0];
      $config['name']  = KInflector::singularize(strtolower($method));

      return $this->_listbox($config); 
    }

    return parent::__call($method, $arguments);   
  }
}