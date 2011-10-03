<?php

class KDatabaseSchemaTable extends KObject
{
  public $name; 
  public $engine;
  public $type;
  public $length;
  public $autoinc;
  public $collation;
  public $description;
  public $columns = array();
  public $behaviors = array();
  public $indexes = array();
}