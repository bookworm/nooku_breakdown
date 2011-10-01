<?php

abstract class KControllerService extends KControllerResource
{
  protected function _initialize(KConfig $config)
  {
    $config->append(array(
      'persistable' => false,
      'behaviors'  => array('discoverable', 'editable'),
      'readonly'   => false, 
    ));

    parent::_initialize($config); 
  }     
  
  public function setView($view)
  {
    if(is_string($view) && strpos($view, '.') === false ) 
    { 
      if(!isset($this->_request->view)) 
      { 
        if($this->getModel()->getState()->isUnique())
          $view = KInflector::singularize($view);
        else 
          $view = KInflector::pluralize($view);      
      }
    }

    return parent::setView($view);       
  }     
  
  protected function _actionBrowse(KCommandContext $context)
  {
    $data = $this->getModel()->getList();
    return $data;
  }      
  
  protected function _actionRead(KCommandContext $context)
  {
    $data = $this->getModel()->getItem();
    $name = ucfirst($this->getView()->getName());

    if($this->getModel()->getState()->isUnique() && $data->isNew())
      $context->setError(new KControllerException($name.' Not Found', KHttpResponse::NOT_FOUND));

    return $data;     
  }     
  
  protected function _actionEdit(KCommandContext $context)
  { 
    $data = $this->getModel()->getData();
  
    if(count($data)) 
    {
      $data->setData(KConfig::toData($context->data));

      if($data->save() === true)  
        $context->status = KHttpResponse::RESET_CONTENT;
      else
        $context->status = KHttpResponse::NO_CONTENT; 
    } 
    else 
      $context->setError(new KControllerException('Resource Not Found', KHttpResponse::NOT_FOUND));

    return $data;     
  } 
  
  protected function _actionAdd(KCommandContext $context)
  {
    $data = $this->getModel()->getItem();

    if($data->isNew())  
    { 
      $data->setData(KConfig::toData($context->data));

      if($data->save() === false) 
      {    
        $error = $data->getStatusMessage();
        $context->setError(new KControllerException(
          $error ? $error : 'Add Action Failed', KHttpResponse::INTERNAL_SERVER_ERROR
        ));    
      } 
      else $context->status = KHttpResponse::CREATED;       
    } 
    else $context->setError(new KControllerException('Resource Already Exists', KHttpResponse::BAD_REQUEST));

    return $data;
  } 
  
  protected function _actionDelete(KCommandContext $context)
  {
    $data = $this->getModel()->getData();

    if(count($data)) 
    {
      $data->setData(KConfig::toData($context->data));

      if($data->delete() === false) 
      {
        $error = $data->getStatusMessage();
        $context->setError(new KControllerException(
          $error ? $error : 'Delete Action Failed', KHttpResponse::INTERNAL_SERVER_ERROR
        ));     
      }
      else $context->status = KHttpResponse::NO_CONTENT;    
    } 
    else  $context->setError(new KControllerException('Resource Not Found', KHttpResponse::NOT_FOUND));

    return $data;    
  }  
  
  protected function _actionGet(KCommandContext $context)
  {
    $action = KInflector::isSingular($this->getView()->getName()) ? 'read' : 'browse';
      
    $result = $this->execute($action, $context);
    
    if(($result instanceof KDatabaseRowInterface) || ($result instanceof KDatabaseRowsetInterface))
      $result = parent::_actionGet($context);
    
    return (string) $result;
  } 
  
  protected function _actionPost(KCommandContext $context)
  {
    $action = $this->getModel()->getState()->isUnique() ? 'edit' : 'add';
    return parent::execute($action, $context);     
  }
  
  protected function _actionPut(KCommandContext $context)
  {   
    $data = $this->getModel()->getItem();

    if($this->getModel()->getState()->isUnique()) 
    { 
      $action = 'add';     
      
      if(!$data->isNew()) {
        $data->reset();
        $action = 'edit';     
      }

      $state = $this->getModel()->getState()->getData(true);
      $data->setData($state);
 
      $data = parent::execute($action, $context); 
    } 
    else $context->setError(new KControllerException(ucfirst('Resource not found', KHttpResponse::BAD_REQUEST)));

    return $data;  
  }               
}