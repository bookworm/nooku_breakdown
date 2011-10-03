<?php

class KFilterSlug extends KFilterAbstract
{	
	protected $_separator;
	protected $_length;
	
	public function __construct(KConfig $config) 
	{
    parent::__construct($config);

    $this->_length    = $config->length;
    $this->_separator = $config->separator; 
	}
	
	protected function _initialize(KConfig $config)
  {
    $config->append(array(
      'separator' => '-',
      'length' 	  => 100 
    ));

    parent::_initialize($config);     
  }

	protected function _validate($value)
	{
  	return KFactory::tmp('lib.koowa.filter.cmd')->validate($value);
	}
	
	protected function _sanitize($value)
	{
		$value = str_replace($this->_separator, ' ', $value);
		$value = KFactory::tmp('lib.koowa.filter.ascii')->sanitize($value);
		$value = trim(strtolower($value));
		$value = preg_replace(array('/\s+/','/[^A-Za-z0-9\-]/'), array($this->_separator,''), $value);
		
		if(strlen($value) > $this->_length)
  		$value = substr($value, 0, $this->_length);
		
		return $value;
	}
}