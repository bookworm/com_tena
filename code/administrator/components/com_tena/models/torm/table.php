<?php
 
/**      
 * The table stuff.
 *
 * @copyright Copyright (C) 2011 Ken Erickson.
 * @license   GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link      https://github.com/bookworm/com_tena
 */
class TOrmTable extends TOrmCore
{
  /**
   * Constructor
   *
   * @param object An optional KConfig object with configuration options
   */      	
	public function __construct(KConfig $config)
  {
    parent::__construct($config);
    $this->_table = $config->table;
  } 
  
// ------------------------------------------------------------------------
  
  /**
   * A __call() overload just calls the parent __call overload() for now.
   *        
   * @param string $name Method name.
   * @param array  $args The arguments
   * @return mixed 
   */
   public function __call($name, $args)
  {  
    return parent::__call($name, $args);
  }  
     
// ------------------------------------------------------------------------  
  
  /**
   * Get the model data
   * 
   * If the model state is unique this function will call getItem(), otherwise
   * it will call getList().
   *
   * @return KDatabaseRowset or KDatabaseRow
   */      
  public function getData()
  { 
    if($this->_state->isUnique()) {
      $data = $this->getItem();
    } else {
      $data = $this->getList();
    }
    
    return $data;    
  }
    
// ------------------------------------------------------------------------  
  
  /**
   * Method to get a item object which represents a table row
   *
   * If the model state is unique a row is fetched from the database based on the state.
   * If not, an empty row is be returned instead.
   *
   * @return KDatabaseRow
   */ 
	public function getItem()
  {
    if (!isset($this->_item))
    {
      if($this->isConnected())
      {
        $query  = null;

        if($this->_state->isUnique())
        {
          $query = $this->getTable()->getDatabase()->getQuery();

          $this->_buildQueryColumns($query);
          $this->_buildQueryFrom($query);
          $this->_buildQueryJoins($query);
          $this->_buildQueryWhere($query);
          $this->_buildQueryGroup($query);
          $this->_buildQueryHaving($query);   
        }

        $this->_item = $this->getTable()->select($query, KDatabase::FETCH_ROW); 
      }   
    }

    return $this->_item;   
  }     
   
// ------------------------------------------------------------------------  
  
  /**
   * Get a list of items which represents a table rowset
   *
   * @return KDatabaseRowset
   */
  public function getList()
  {
    // Get the data if it doesn't already exist
    if (!isset($this->_list))
    {
      if($this->isConnected())
      {
        $query  = null;

        if(!$this->_state->isEmpty())
        {
          $query = $this->getTable()->getDatabase()->getQuery();

          $this->_buildQueryColumns($query);
          $this->_buildQueryFrom($query);
          $this->_buildQueryJoins($query);
          $this->_buildQueryWhere($query);
          $this->_buildQueryGroup($query);
          $this->_buildQueryHaving($query);
          $this->_buildQueryOrder($query);
          $this->_buildQueryLimit($query);      
        }

        $this->_list = $this->getTable()->select($query, KDatabase::FETCH_ROWSET);   
      }
    }

    return $this->_list;
  }
  
// ------------------------------------------------------------------------  
  
  /**
   * Get the total amount of items
   *
   * @return int
   */
  public function getTotal()
  {
    // Get the data if it doesn't already exist
    if (!isset($this->_total))
    {
      if($this->isConnected())
      {
        //Excplicitly get a count query, build functions can then test if the
        //query is a count query and decided how to build the query.
        $query = $this->getTable()->getDatabase()->getQuery()->count(); 
  
        $this->_buildQueryFrom($query);
        $this->_buildQueryJoins($query);
        $this->_buildQueryWhere($query);

        $total = $this->getTable()->count($query);
        $this->_total = $total;
      }    
    }

    return $this->_total;   
  }
   
// ------------------------------------------------------------------------

	/**
   * Get the distinct values of a column
   *
   * @return object
   */  
  public function getColumn($column)
  {   
    if (!isset($this->_column[$column])) 
    {   
      if($this->isConnected()) 
      {
        $query = $this->getTable()->getDatabase()->getQuery()
            ->distinct()
            ->group('tbl.'.$this->getTable()->mapColumns($column));

        $this->_buildQueryOrder($query);
        
        $this->_column[$column] = $this->getTable()->select($query);    
      }  
    }

    return $this->_column[$column];  
  }
	
// ------------------------------------------------------------------------
  
	/**
   * Builds SELECT columns list for the query.   
   *
   * @param  object $query KDatabaseQuery object
   * @return void
   */
  protected function _buildQueryColumns(KDatabaseQuery $query)
  {
    $query->select(array('tbl.*'));
  }
  
// ------------------------------------------------------------------------

	/**
   * Builds FROM tables list for the query.
   *
   * @param  object $query KDatabaseQuery object
   * @return void
   */ 
  protected function _buildQueryFrom(KDatabaseQuery $query)
  {
    $name = $this->getTable()->getName();
    $query->from($name.' AS tbl');    
  }
  
// ------------------------------------------------------------------------

	/**
   * Builds LEFT JOINS clauses for the query
   *
   * @param  object $query KDatabaseQuery object
   * @return void
   */
  protected function _buildQueryJoins(KDatabaseQuery $query)
  {      
    # has_one maps to left join
    # has_many should be separate query when we hit an object that should many bingo.
    # $query->join('LEFT', 'terms_relations AS terms_relations', 'terms_relations.row       = tbl.'.$table->getIdentityColumn());
  }
  
// ------------------------------------------------------------------------

	/**
   * Builds a WHERE clause for the query.
   *
   * @param  object $query KDatabaseQuery object
   * @return void
   */ 
  protected function _buildQueryWhere(KDatabaseQuery $query)
  {
    //Get only the unique states
    $states = $this->states;
    
    if(!empty($states))   
    {
      foreach($states as $key => $value) {
        $query->where('tbl.'.$key, 'IN', $value);
      }
    }
  }
  
// ------------------------------------------------------------------------

	/**
   * Builds a GROUP BY clause for the query.
   *
   * @param  object $query KDatabaseQuery object
   * @return void
   */   
  protected function _buildQueryGroup(KDatabaseQuery $query)
  {
  }
  
// ------------------------------------------------------------------------

	/**
   * Builds a HAVING clause for the query.
   *
   * @param  object $query KDatabaseQuery object
   * @return void
   */        
  protected function _buildQueryHaving(KDatabaseQuery $query)
  {
  }
       
// ------------------------------------------------------------------------

	/**
   * Builds a generic ORDER BY clause based on the model's state.
   *
   * @param  object $query KDatabaseQuery object
   * @return void
   */ 
  protected function _buildQueryOrder(KDatabaseQuery $query)
  {
    $sort       = $this->_state->sort;
    $direction  = strtoupper($this->_state->direction);

    if($sort) { 
      $query->order($this->getTable()->mapColumns($sort), $direction); 
    } 

    if(array_key_exists('ordering', $this->getTable()->getColumns())) {
      $query->order('tbl.ordering', 'ASC');
    }   
  }
  
// ------------------------------------------------------------------------

	/**
   * Builds LIMIT clause for the query.
   *
   * @param  object $query KDatabaseQuery object
   * @return void
   */ 
  protected function _buildQueryLimit(KDatabaseQuery $query)
  {
    $limit = $this->_state->limit;
    
    if($limit) 
    {
      $offset = $this->_state->offset;
      $total  = $this->getTotal();

      //If the offset is higher than the total recalculate the offset
      if($offset !== 0 && $total !== 0)        
      {
        if($offset >= $total) 
        {
          $offset = floor(($total-1) / $limit) * $limit;    
          $this->_state->offset = $offset;    
        } 
       }
      
       $query->limit($limit, $offset);
    }                                        
  }
}