<?php

abstract class KDispatcherAbstract extends KControllerAbstract
{
  protected $_controller;  
  
  public function __construct(KConfig $config)
	{
		parent::__construct($config);
		
		$this->_controller = $config->controller;
		
		if(KRequest::method() != 'GET') 
  		$this->registerCallback('after.dispatch' , array($this, 'forward'));

    $this->registerCallback('after.dispatch', array($this, 'render'));
	}       
	
	protected function _initialize(KConfig $config)
  {
    $config->append(array(
      'controller' => $this->_identifier->package,
      'request'	   => KRequest::get('get', 'string'),
    ))->append(array(
      'request' 	 => array('format' => KRequest::format() ? KRequest::format() : 'html')
    ));  

    parent::_initialize($config);
  } 
  
  public function getController()
	{
		if(!($this->_controller instanceof KControllerAbstract))
		{  
      if(!($this->_controller instanceof KIdentifier)) 
        $this->setController($this->_controller);

      $config = array(
        'request' 	   => $this->_request,
        'dispatched'   => true	      
      );

      $this->_controller = KFactory::tmp($this->_controller, $config);   
		}
	
		return $this->_controller;
	}  
	
	public function setController($controller)
	{
		if(!($controller instanceof KControllerAbstract))
		{
      if(is_string($controller) && strpos($controller, '.') === false ) 
      {
        if(KInflector::isPlural($controller)) 
          $controller = KInflector::singularize($controller);


        $identifier			= clone $this->_identifier;
        $identifier->path	= array('controller');
        $identifier->name	= $controller;   
      }
      else $identifier = KFactory::identify($controller);

      if($identifier->path[0] != 'controller')
        throw new KDispatcherException('Identifier: '.$identifier.' is not a controller identifier');

      $controller = $identifier;
		}
		
		$this->_controller = $controller;
	
		return $this;
	}
	
	protected function _actionDispatch(KCommandContext $context)
	{        	 
    $action = KRequest::get('post.action', 'cmd', strtolower(KRequest::method()));

    if(KRequest::method() != KHttpRequest::GET)
      $context->data = KRequest::get(strtolower(KRequest::method()), 'raw');;

    $result = $this->getController()->execute($action, $context);
   
    return $result;
	}     
	
	public function _actionForward(KCommandContext $context)
	{
		if (KRequest::type() == 'HTTP') {
      if($redirect = $this->getController()->getRedirect())
        KFactory::get('lib.joomla.application')->redirect($redirect['url'], $redirect['message'], $redirect['type']);  
		}

		if(KRequest::type() == 'AJAX') {
			$view = KRequest::get('get.view', 'cmd');
			$context->result = $this->getController()->execute('display', $context);
			return $context->result;
		}
	} 
	
	protected function _actionRender(KCommandContext $context)
	{
    if($context->headers) 
    {
      foreach($context->headers as $name => $value) {
        header($name.' : '.$value);
      }  
    }

    if($context->status)
      header(KHttpResponse::getHeader($context->status));

    if(is_string($context->result))
      return $context->result;
	}
}