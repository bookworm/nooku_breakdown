<?php 

class KControllerBehaviorPersistable extends KControllerBehaviorAbstract
{
  protected function _beforeBrowse(KCommandContext $context)
  {
    $identifier = $this->getModel()->getIdentifier().'.'.$context->action;
    $state      = KRequest::get('session.'.$identifier, 'raw', array());
      
    $this->getRequest()->append($state);
    $this->getModel()->set($this->getRequest());
  }
  
  protected function _afterBrowse(KCommandContext $context)
  {
    $model = $this->getModel();
    $state = $model->get();

    $identifier = $model->getIdentifier().'.'.$context->action;
    
    KRequest::set('session.'.$identifier, $state);
  }
}