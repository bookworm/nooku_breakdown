<?php 

class KDatabaseBehaviorOrderable extends KDatabaseBehaviorAbstract
{
  public function getMixableMethods(KObject $mixer = null)
  {
    $methods = array();

    if(isset($mixer->ordering))
      $methods = parent::getMixableMethods($mixer);

    return $methods;     
  }      
  
  public function _buildQueryWhere(KDatabaseQuery $query) { } 
  
  public function order($change)
  {
    settype($change, 'int');

    if($change !== 0)
    {
      $old = (int) $this->ordering;
      $new = $this->ordering + $change;
      $new = $new <= 0 ? 1 : $new;

      $table = $this->getTable();
      $db    = $table->getDatabase();
      $query = $db->getQuery();
      
      //Build the where query
      $this->_buildQueryWhere($query);

      $update =  'UPDATE `#__'.$table->getBase().'` ';
      if($change < 0) 
      {
        $update .= 'SET ordering = ordering+1 ';
        $query->where('ordering', '>=', $new)
          ->where('ordering', '<', $old);
      } 
      else 
      {
        $update .= 'SET ordering = ordering-1 ';
        $query->where('ordering', '>', $old)
          ->where('ordering', '<=', $new);
      }
      
      $update .= (string) $query;
      $db->execute($update);

      $this->ordering = $new;
      $this->save();
      $this->reorder();
    }

    return $this->_mixer;
  }      
  
  public function reorder($base = 0)
  {
    settype($base, 'int');

    $table  = $this->getTable();
    $db     = $table->getDatabase();
    $query  = $db->getQuery();

    $this->_buildQueryWhere($query);

    if($base) $query->where('ordering', '>=', (int) $base);

    $db->execute("SET @order = $base");
    $db->execute(
      'UPDATE #__'.$table->getBase().' '
      .'SET ordering = (@order := @order + 1) '
      .(string) $query.' '
      .'ORDER BY ordering ASC' 
    );

    return $this;    
  }       
  
  protected function getMaxOrdering() 
  {
    $table  = $this->getTable();
    $db     = $table->getDatabase();
    $query  = $db->getQuery();

    $this->_buildQueryWhere($query);

    $select = 'SELECT MAX(ordering) FROM `#__'.$table->getName().'`';
    $select .= (string) $query;
    
    return  (int) $db->select($select, KDatabase::FETCH_FIELD);        
  }        
  
  protected function _beforeTableInsert(KCommandContext $context)
  {
    if(isset($this->ordering))
    {
      $max = $this->getMaxOrdering();

      if ($this->ordering <= 0)
        $this->ordering = $max + 1;
      else 
        $this->reorder($this->ordering);     
    }      
  }   
  
  protected function _beforeTableUpdate(KCommandContext $context)
  {
    if(isset($this->order) && isset($this->ordering))
      $this->order($this->order);    
  }            
  
  protected function _afterTableDelete(KCommandContext $context)
  {
    $this->reorder();
  }
}