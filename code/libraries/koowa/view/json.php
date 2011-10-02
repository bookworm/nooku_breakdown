<?php 

class KViewJson extends KViewAbstract
{
  protected $_padding; 
  
  public function __construct(KConfig $config)
  {
    parent::__construct($config);

    if(empty($config->padding) && $config->padding !== false)
    {
      $state = $this->getModel()->getState();

      if(isset($state->callback) && (strlen($state->callback) > 0)) 
        $config->padding = $state->callback;      
    }

    $this->_padding = $config->padding;   
  }          
  
  protected function _initialize(KConfig $config)
  {
    $config->append(array(
      'mimetype' => 'application/json',
      'padding'	 => ''
    ));

    parent::_initialize($config);  
  }     
  
  public function display()
  {
    if(empty($this->output))
    {
      $model = $this->getModel();

      if(KInflector::isPlural($this->getName()))
        $data = array_values($model->getList()->toArray());
      else 
        $data = $model->getItem()->toArray();

      $this->output = $data;
    }

    if(!is_string($this->output)) 
      $this->output = json_encode($this->output);

    if(!empty($this->_padding))
      $this->output = $this->_padding.'('.$this->output.');';

    return parent::display();
  } 
}