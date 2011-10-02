<?php 

class KDatabaseBehaviorSluggable extends KDatabaseBehaviorAbstract
{  
  protected $_columns;
  protected $_separator;
  protected $_length;
  protected $_updatable;
  protected $_unique;      
  
  public function __construct( KConfig $config = null)
  {
    parent::__construct($config);

    foreach($config as $key => $value) {
      $this->{'_'.$key} = $value;
    }         
  }
  
  protected function _initialize(KConfig $config)
  {
    $config->append(array(
      'columns'   => array('title'),
      'separator' => '-',
      'updatable' => true,
      'length'    => null,
      'unique'    => null 
    ));

    parent::_initialize($config);         
  }    
  
  public function getMixableMethods(KObject $mixer = null)
  {
    $methods = array();

    if(isset($mixer->slug))
      $methods = parent::getMixableMethods($mixer);

    return $methods;  
  }         
  
  protected function _afterTableInsert(KCommandContext $context)
  {
    $this->_createSlug();
    $this->save();      
  } 
  
  protected function _beforeTableUpdate(KCommandContext $context)
  {
    if($this->_updatable) $this->_createSlug();
  }   
  
  protected function _createFilter()
  {
    $config = array();
    $config['separator'] = $this->_separator;

    if(!isset($this->_length))
      $config['length'] = $this->getTable()->getColumn('slug')->length;
    else
      $config['length'] = $this->_length;

    $filter = KFactory::tmp('lib.koowa.filter.slug', $config);
    return $filter;  
  }      
  
  protected function _createSlug()
  {
    $filter = $this->_createFilter();

    if(empty($this->slug))
    {
      $slugs = array();
      foreach($this->_columns as $column) {
        $slugs[] = $filter->sanitize($this->$column);
      }

      $this->slug = implode($this->_separator, $slugs);

      $this->_canonicalizeSlug();  
    }
    else
    {
      if(in_array('slug', $this->getModified())) {
        $this->slug = $filter->sanitize($this->slug);
        $this->_canonicalizeSlug();
      } 
    }    
  }  
  
  protected function _canonicalizeSlug()
  {
    $table = $this->getTable();

    if(is_null($this->_unique))
      $this->_unique = $table->getColumn('slug', true)->unique;

    if($this->_unique && $table->count(array('slug' => $this->slug))) 
    {   
      $db    = $table->getDatabase();
      $query = $db->getQuery()
                ->select('slug')
                ->where('slug', 'LIKE', $this->slug.'-%');          

      $slugs = $table->select($query, KDatabase::FETCH_FIELD_LIST);

      $i = 1;
      while(in_array($this->slug.'-'.$i, $slugs)) {
        $i++;
      }

      $this->slug = $this->slug.'-'.$i;      
    }     
  }
}