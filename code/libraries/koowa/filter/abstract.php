<?php

abstract class KFilterAbstract implements KFilterInterface
{
  protected $_chain = null;
  protected $_walk = true;  
  
  public function __construct(KConfig $config) 
  {
    $this->_chain = new KFilterChain();
    $this->addFilter($this);

    $this->_initialize($config);           
  }        
  
  protected function _initialize(KConfig $config) { }
  
  final public function execute($name, KCommandContext $context) 
  { 
    $function = '_'.$name;
    return $this->$function($context->data);
  }    
  
  final public function validate($data)
  {
    if($this->_walk && (is_array($data) || is_object($data))) 
    {
      $arr = (array)$data;
      
      foreach($arr as $value) {
        if($this->validate($value) ===  false) 
          return false;
      }
    } 
    else 
    { 
      $context = $this->_chain->getContext();
      $context->data = $data;
      
      $result = $this->_chain->run('validate', $context);
      
      if($result ===  false) return false;
    }
      
    return true;
  }
  
  public final function sanitize($data)
	{
		if($this->_walk && (is_array($data) || is_object($data))) 
		{
			$arr = (array)$data;
				
			foreach($arr as $key => $value) 
			{
				if(is_array($data)) 
					$data[$key] = $this->sanitize($value);
				
				if(is_object($data)) 
					$data->$key = $this->sanitize($value);	
			}
		}
		else
		{
			$context = $this->_chain->getContext();
			$context->data = $data;
			
			$data = $this->_chain->run('sanitize', $context);
		}
		
		return $data;
	}     
	
	public function addFilter(KFilterInterface $filter, $priority = null)
	{	
		$this->_chain->enqueue($filter, $priority);
		return $this;
	}
	
	public function getHandle()
	{
		return spl_object_hash( $this );
	}

	public function getPriority()
	{
		return KCommand::PRIORITY_NORMAL;
	}   
	
	abstract protected function _validate($value);
	abstract protected function _sanitize($value);
}