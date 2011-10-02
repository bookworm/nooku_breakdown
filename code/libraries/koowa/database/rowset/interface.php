<?php

interface KDatabaseRowsetInterface extends KObjectIdentifiable
{
  public function getData($modified = false);
  public function setData( $data, $modified = true );
  public function addData(array $data, $new = true);
  public function getIdentityColumn();
  public function find($needle);
  public function save();
  public function delete();
  public function reset();
  public function insert(KDatabaseRowInterface $row);
  public function extract(KDatabaseRowInterface $row);
  public function isConnected();  
}