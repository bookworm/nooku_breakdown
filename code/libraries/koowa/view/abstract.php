<?php 

abstract class KViewAbstract extends KObject implements KObjectIdentifiable
{
  protected $_model;
	public $output = '';
	public $mimetype = '';
	protected $_layout;
  
  public function __construct(KConfig $config = null)
	{
    if(!isset($config)) $config = new KConfig();

    parent::__construct($config);

    $this->output = $config->output;
    $this->mimetype = $config->mimetype;
    $this->setModel($config->model);
    $this->setLayout($config->layout);     
	}       
	
	protected function _initialize(KConfig $config)
  {
    $config->append(array(
      'model'    => $this->getName(),
      'output'	 => '',
      'mimetype' => '',
      'layout'   => 'default',          
    ));

    parent::_initialize($config);    
  }   
  
  public function getIdentifier()
	{
		return $this->_identifier;
	}

	public function getName()
	{
		$total = count($this->_identifier->path);
		return $this->_identifier->path[$total - 1];
	}

	public function getFormat()
	{
		return $this->_identifier->name;
	}

	public function display()
	{
		return $this->output;
	} 
	
	public function getModel()
	{
    if(!$this->_model instanceof KModelAbstract) 
    {
      if(!($this->_model instanceof KIdentifier))
        $this->setModel($this->_model);

      $this->_model = KFactory::tmp($this->_model);      
    }

    return $this->_model;   
	}    
	
  public function setModel($model)
	{
    if(!($model instanceof KModelAbstract))
    {
      if(is_string($model) && strpos($model, '.') === false ) 
      {
        if(KInflector::isSingular($model)) 
          $model = KInflector::pluralize($model);

        $identifier			= clone $this->_identifier;
        $identifier->path	= array('model');
        $identifier->name	= $model;
      }
      else $identifier = KFactory::identify($model);

      if($identifier->path[0] != 'model')
        throw new KControllerException('Identifier: '.$identifier.' is not a model identifier');
    
      $model = $identifier;   
    }

    $this->_model = $model;

    return $this;    
	}  
	
  public function getLayout()
  {
    return $this->_layout;
  }

  public function setLayout($layout)
  {
    $this->_layout = $layout;
    return $this;
  }     
  
  public function createRoute( $route = '')
	{
    $route = trim($route);

    if($route == 'index.php' || $route == 'index.php?') {
    	$result = $route;
    } 
    else if (substr($route, 0, 1) == '&') 
    {
    	$url   = clone KRequest::url();
    	$vars  = array();
    	parse_str($route, $vars);
	
    	$url->setQuery(array_merge($url->getQuery(true), $vars));
	
    	$result = 'index.php?'.$url->getQuery();
    }
    else 
    {
    	if(substr($route, 0, 10) == 'index.php?') 
    		$route = substr($route, 10);

    	$parts = array();
    	parse_str($route, $parts);
    	$result = array();

    	if(!isset($parts['option'])) 
    		$result[] = 'option=com_'.$this->_identifier->package;

    	if(!isset($parts['view']))
    	{
    		$result[] = 'view='.$this->getName();
    		if(!isset($parts['layout']) && $this->_layout != $this->_layout_default)
    			$result[] = 'layout='.$this->getLayout();
    	}
	
    	if(!isset($parts['format']) && $this->_identifier->name != 'html')
    		$result[] = 'format='.$this->_identifier->name;

    	if(!empty($route)) $result[] = $route;

    	$result = 'index.php?'.implode('&', $result);
    }

    return JRoute::_($result); 
	}

	public function __toString()
	{
    return $this->display();
	}
}