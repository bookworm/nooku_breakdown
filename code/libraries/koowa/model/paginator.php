<?php

class KModelPaginator extends KModelState
{
  public function __construct(KConfig $config)
  {
    parent::__construct($config);

    $this->insert('total' , 'int')
      ->insert('limit'    , 'int', 20)
      ->insert('offset'   , 'int', 0)
      ->insert('count'    , 'int')
      ->insert('current'  , 'int')
      ->insert('display'  , 'int', 4);     
  }
  
  public function setData(array $data)
  {
    parent::setData($data);

    if($this->total == 0)
    {
      $limit   = 0;
      $offset  = 0;
      $count   = 0;
      $current = 0; 
    } 
    else
    {
      $total  = (int) $this->total;
      $limit  = (int) max($this->limit, 1);
      $offset = (int) max($this->offset, 0);

      if($limit > $total) $offset = 0;

      if(!$this->limit) {
        $offset = 0;
        $limit  =  $total;  
      }

      $count  = (int) ceil($total / $limit);

      if($offset > $total) $offset = ($count-1) * $limit;

      $current = (int) floor($offset / $limit) + 1;  
    }

    $this->limit   = $limit;
    $this->offset  = $offset;
    $this->count   = $count;
    $this->current = $current;

    return $this;   
  }   
  
  public function getList()
  {
    $elements  = array();
    $prototype = new KObject();
    $current   = ($this->current - 1) * $this->limit;

    # First
    $page    = 1;
    $offset  = 0;
    $active  = $offset != $this->offset;
    $props   = array('page' => 1, 'offset' => $offset, 'limit' => $this->limit, 'current' => false, 'active' => $active );
    $element = clone $prototype;
    $elements['first'] = $element->set($props);

    # Previous
    $offset  = max(0, ($this->current - 2) * $this->limit);
    $active  = $offset != $this->offset;
    $props   = array('page' => $this->current - 1, 'offset' => $offset, 'limit' => $this->limit, 'current' => false, 'active' => $active);
    $element = clone $prototype;
    $elements['previous'] = $element->set($props);

    # Pages
    $elements['pages'] = array();
    foreach($this->_getOffsets() as $page => $offset)
    {
      $current = $offset == $this->offset;
      $props = array('page' => $page, 'offset' => $offset, 'limit' => $this->limit, 'current' => $current, 'active' => !$current);
      $element    = clone $prototype;
      $elements['pages'][] = $element->set($props);  
    }

    # Next
    $offset  = min(($this->count-1) * $this->limit, ($this->current) * $this->limit);
    $active  = $offset != $this->offset;
    $props   = array('page' => $this->current + 1, 'offset' => $offset, 'limit' => $this->limit, 'current' => false, 'active' => $active);
    $element = clone $prototype;
    $elements['next'] = $element->set($props);

    # Last
    $offset  = ($this->count - 1) * $this->limit;
    $active  = $offset != $this->offset;
    $props   = array('page' => $this->count, 'offset' => $offset, 'limit' => $this->limit, 'current' => false, 'active' => $active);
    $element = clone $prototype;
    $elements['last'] = $element->set($props);

    return $elements;
  }  
  
  
  protected function _getOffsets()
  {
    if($display = $this->display)
    {
      $start  = (int) max($this->current - $display, 1);
      $start  = min($this->count, $start);
      $stop   = (int) min($this->current + $display, $this->count);   
    }
    else {
      $start = 1;
      $stop = $this->count;       
    }

    $result = array();
    if($start > 0)
    {
      foreach(range($start, $stop) as $pagenumber) {
        $result[$pagenumber] =  ($pagenumber-1) * $this->limit;
      }
    }

    return $result; 
  }
}