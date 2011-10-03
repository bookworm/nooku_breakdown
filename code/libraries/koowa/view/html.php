<?php 

class KViewHtml extends KViewTemplate
{
  protected function _initialize(KConfig $config)
  {
    $config->append(array(
      'mimetype'         => 'text/html',
      'template_filters' => array('form'),
    ));

    parent::_initialize($config); 
  }   
  
  public function display()
  {
    if(empty($this->output))
    {
      $model = $this->getModel();

      $this->assign('state', $model->getState());

      if($this->_auto_assign)
      {
        $name  = $this->getName();

        if(KInflector::isPlural($name)) {
          $this->assign($name,  $model->getList())
            ->assign('total', $model->getTotal());   
        }
        else $this->assign($name, $model->getItem());    
      }  
    }

    return parent::display();  
  }
}