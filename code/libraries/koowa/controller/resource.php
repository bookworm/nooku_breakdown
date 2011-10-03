<?php

abstract class KControllerResource extends KControllerAbstract
{
  protected $_redirect = null;
  protected $_redirect_message = null;
  protected $_redirect_type = 'message';
  protected $_view;
  protected $_model;
  
  public function __construct(KConfig $config)
  {
    parent::__construct($config);

    $this->_model = $config->model;
    $this->_view = $config->view;
    $this->registerActionAlias('display', 'get');

    if($config->readonly) 
      $this->getBehavior('executable')->setReadOnly(true);     
  }    
  
  protected function _initialize(KConfig $config)
  {
    $config->append(array(
      'model'      => $this->_identifier->name,
      'view'       => $this->_identifier->name,
      'behaviors'  => array('executable', 'commandable'),
      'readonly'   => true, 
      'request'    => array('format' => 'html')     
    ));         
      
    parent::_initialize($config);

    if(isset($config->request->view)) 
      $config->view = $config->request->view;      
  }   

  public function getView()
  {
    if(!$this->_view instanceof KViewAbstract)
    {    
      if(!($this->_view instanceof KIdentifier)) $this->setView($this->_view);

      $config = array(
        'model' => $this->getModel(),
      );
  
      $this->_view = KFactory::tmp($this->_view, $config);

      if(isset($this->_request->layout)) $this->_view->setLayout($this->_request->layout);

      if(!file_exists(dirname($this->_view->getIdentifier()->filepath)))
        throw new KControllerException('View :'.$this->_view->getName().' not found', KHttpResponse::NOT_FOUND); 
    }

    return $this->_view;  
  }
  
  public function setView($view)
  {
    if(!($view instanceof KViewAbstract))
    {
      if(is_string($view) && strpos($view, '.') === false) 
      {
        $identifier     = clone $this->_identifier;
        $identifier->path = array('view', $view);
        $identifier->name = $this->getRequest()->format;
      } 
      else $identifier = KFactory::identify($view);
        
      if($identifier->path[0] != 'view')
        throw new KControllerException('Identifier: '.$identifier.' is not a view identifier');

      $view = $identifier;
    }
    
    $this->_view = $view;
    
    return $this->_view;
  }  
  
  public function getModel()
  {     
    if(!$this->_model instanceof KModelAbstract) 
    {
      if(!($this->_model instanceof KIdentifier))
        $this->setModel($this->_model);

      $options = array(
        'state' => $this->getRequest()
      );

      $this->_model = KFactory::tmp($this->_model);
      $this->_model->set($this->getRequest());      
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

        $identifier       = clone $this->_identifier;
        $identifier->path = array('model');
        $identifier->name = $model;    
      }
      else $identifier = KFactory::identify($model);

      if($identifier->path[0] != 'model') 
        throw new KControllerException('Identifier: '.$identifier.' is not a model identifier');

      $model = $identifier;    
    }

    $this->_model = $model;

    return $this->_model;     
  }   

  public function getRedirect()
  {
    $result = array();

    if(!empty($this->_redirect))
    {
      $result = array(
        'url'     => JRoute::_($this->_redirect, false),
        'message' => $this->_redirect_message,
        'type'    => $this->_redirect_type,          
      );    
    }

    return $result;  
  }  
  
  public function setRedirect( $url, $msg = null, $type = 'message')
  {
    $this->_redirect         = $url;
    $this->_redirect_message = $msg;
    $this->_redirect_type    = $type;

    return $this;
  } 

  protected function _actionGet(KCommandContext $context)
  { 
    $result = $this->getView()->display();   
    return $result; 
  }       
  
  public function __set($property, $value)
  {
    parent::__set($property, $value);

    if($this->_model instanceof KModelAbstract)
      $this->getModel()->set($property, $value);
  }    
  
  public function __call($method, $args)
  {
    if(!isset($this->_mixed_methods[$method])) 
    {
      $state = $this->getModel()->getState();

      if(isset($state->$method) || in_array($method, array('layout', 'view', 'format'))) {
        $this->$method = $args[0];
        return $this;     
      }
    }

    return parent::__call($method, $args); 
  }
}