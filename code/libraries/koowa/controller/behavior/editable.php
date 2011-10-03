<?php

class KControllerBehaviorEditable extends KControllerBehaviorAbstract
{
  public function __construct(KConfig $config)
  { 
    parent::__construct($config);

    $this->registerCallback('before.read' , array($this, 'setReferrer'));
    $this->registerCallback('after.save'  , array($this, 'unsetReferrer'));
    $this->registerCallback('after.cancel', array($this, 'unsetReferrer'));

    $this->registerCallback('after.read'  , array($this, 'lockResource'));
    $this->registerCallback('after.save'  , array($this, 'unlockResource'));
    $this->registerCallback('after.cancel', array($this, 'unlockResource'));

    $this->setRedirect(KRequest::referrer());      
  }  
  
  public function getReferrer()
  {
    $identifier = $this->getMixer()->getIdentifier();

    $referrer = KFactory::tmp('lib.koowa.http.url', 
      array('url' => KRequest::get('cookie.referrer_'.md5(KRequest::referrer()), 'url'))
    );

    return $referrer; 
  }       
  
  public function setReferrer()
  {                  
    $identifier = $this->getMixer()->getIdentifier();

    if(!KRequest::has('cookie.referrer_'.md5(KRequest::referrer())))
    {
      $referrer = KRequest::referrer();
      $request  = KRequest::url();

      if(!isset($referrer) || ((string) $referrer == (string) $request))
      {  
        $option = 'com_'.$identifier->package;
        $view   = KInflector::pluralize($identifier->name);
        $url    = 'index.php?option='.$option.'&view='.$view;

        $referrer = KFactory::tmp('lib.koowa.http.url',array('url' => $url));
      }

      KRequest::set('cookie.referrer_'.md5(KRequest::url()), (string) $referrer);  
    } 
  }   
  
  public function unsetReferrer()
  {                 
    $identifier = $this->getMixer()->getIdentifier();
    KRequest::set('cookie.referrer_'.md5(KRequest::referrer()), null);  
  }
  
  public function lockResource(KCommandContext $context)
  {               
    if($context->result instanceof KDatabaseRowInterface) 
    {
      $view = $this->getView();

      if($view instanceof KViewTemplate) {
        if($view->getLayout() == 'form' && $context->result->isLockable())
          $context->result->lock();
      }
    }    
  }
  
  public function unlockResource(KCommandContext $context)
  {                 
    if($context->result instanceof KDatabaseRowInterface && $context->result->isLockable())
      $context->result->unlock();
  }   
  
  protected function _actionSave(KCommandContext $context)
  {
    $action = $this->getModel()->getState()->isUnique() ? 'edit' : 'add';
    $data   = $context->caller->execute($action, $context);
      
    $this->setRedirect($this->getReferrer());
    
    return $data;
  } 
  
  protected function _actionApply(KCommandContext $context)
  {
    $action = $this->getModel()->getState()->isUnique() ? 'edit' : 'add';
    $data   = $context->caller->execute($action, $context);

    $url  = clone KRequest::url();

    if($this->getModel()->getState()->isUnique())
    {
      $url    = clone KRequest::url();
      $states = $this->getModel()->getState()->getData(true);

      foreach($states as $key => $value) {
        $url->query[$key] = $data->get($key);
      }   
    }
    else $url->query[$data->getIdentityColumn()] = $data->get($data->getIdentityColumn());

    $this->setRedirect($url);

    return $data;  
  }
  
  protected function _actionCancel(KCommandContext $context)
  {
    $data = $context->caller->execute('read', $context);
    $this->setRedirect($this->getReferrer());
  
    return $data;
  }
}