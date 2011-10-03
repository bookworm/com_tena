<?php 

/**      
 * Stuff that takes place directly on the DB. Generate schema etc.  
 *
 * @copyright Copyright (C) 2011 Ken Erickson.
 * @license   GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link      https://github.com/bookworm/com_tena
 */
abstract class TOrmDB extends KObject implements KObjectIdentifiable        
{     
  /**
	 * Model column data
	 *
	 * @var mixed
	 */
  protected $_column; 
  
  /**
	 * Model table data
	 *
	 * @var mixed
	 */
   public $_table = false;     
	
	/**
	 * Model state
	 *
	 * @var mixed
	 */
  protected $_state;
  
  /**
	 * Model item data
	 *
	 * @var mixed
	 */   
  protected $_item;  
  
  /**
	 * Model list data
	 *
	 * @var array
	 */
	public $_list; 
	
	/**
	 * Model items data
	 *
	 * @var array
	 */
  public $_items;  
  
  /**
	 * List total
	 *
	 * @var integer
	 */
  protected $_total; 
  
  public $sql_types = array(    
    'BOOLEAN', 'CHAR', 'VARCHAR', 'CLOB', 'TINYINT', 'SMALLINT', 'INT',
    'BIGINT', 'FLOAT', 'NUMERIC', 'DATETIME', 'BLOB', 'TINYTEXT', 'TEXT', 'MEDIUMTEXT', 'LONGTEXT',
    'MEDIUMBLOB', 'LONGBLOB', 'DATE', 'TIME', 'TIMESTAMP', 'SERIAL', 'SET'
  );   
   
  /**
	 * Maps key types to their actual SQL types.
	 *
	 * @var array
	 */
  public $sqltypemap = array(
    'string'      => array('type' => 'VARCHAR', 'default_options' => array('sql_args' => 255)), 
    'foreign_key' => 'FOREIGN KEY',
    'integer'     => 'INT',
    'int'         => 'INT'
  );
   
  /**
	 * Constructor
	 *
	 * @param object An optional KConfig object with configuration options
	 */
	public function __construct(KConfig $config = null)
	{
    if(!isset($config)) $config = new KConfig();    
    
    parent::__construct($config);   
    $this->_state = $config->state;  
	}  
 
// ------------------------------------------------------------------------
 
	/**
	 * Initializes the options for the object
	 *
	 * Called from {@link __construct()} as a first step of object instantiation.
	 *
	 * @param 	object 	An optional KConfig object with configuration options
	 * @return  void
	 */
	protected function _initialize(KConfig $config)
	{                                   
  	$config->append(array(
      'state'  => KFactory::tmp('lib.koowa.model.state'),
    ));   
    
   	parent::_initialize($config);    
  }
      
// ------------------------------------------------------------------------
  
	/**
	 * Get the object identifier
	 *
	 * @return KIdentifier
	 * @see KObjectIdentifiable
	 */
	public function getIdentifier()
	{
		return $this->_identifier;
	}  

// ------------------------------------------------------------------------
  
  /**
   * Set the model state properties
   *
   * This function overrides the KObject::set() function and only acts on state properties.
   *
   * @param   string|array|object	The name of the property, an associative array or an object
   * @param   mixed  The value of the property
   * @return	KModelAbstract
   */
  public function set($property, $value = null)
  {
    if(is_object($property)) {
      $property = (array) KConfig::toData($property);
    }

    if(is_array($property)) {
    	$this->_state->setData($property);
    } 
    else {
    	$this->_state->$property = $value;
    }

    return $this;  
  }    
  
// ------------------------------------------------------------------------
	
	/**
	 * Test the connected status of the row.
	 *
	 * @return boolean Returns TRUE if we have a reference to a live KDatabaseTableAbstract object.
	 */
  public function isConnected()
	{
    return (bool) $this->getTable();
	}   

// ------------------------------------------------------------------------
	 
	/**
   * Method to get state object
   *
   * @return object The state object
   */
	public function getState()
  {
    return $this->_state;
  } 
   
// ------------------------------------------------------------------------
  
  /**
   * Method to get a item
   *
   * @return object
   */
  public function getItem()
  {
    return $this->_item;
  }
  
// ------------------------------------------------------------------------
  
  /**
   * Get a list of items
   *
   * @return object
   */
  public function getList()
  {
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
    return $this->_total;
  }

// ------------------------------------------------------------------------
  
  /**
   * Generates the schema
   *
   * @return $this
   */
  public function genSchema()
  {           
    $table = $this->getTable();       
		$db    = $table->getDatabase();  
 		
		$columns = $table->getColumns();
		
		$query = "CREATE TABLE IF NOT EXISTS `#__" . $this->getIdentifier()->package . "_$this->name` (";
		
    foreach($this->keys as $k => $key) 
    {   
      if(isset($columns[$k]))
        if($columns[$k]->type == $key['sql_type']) continue; 
      
      $query .= "`$k` ";
      $query .= $key['sql_type'];
      if(isset($key['sql_args'])) $query .= '(' . $key['sql_args'] . ') '; 
      $query .= $key['sql_options'] . ',';
    } 
    $query .= ');'; 
    
    file_put_contents('query.txt', $query);
        
    # $db->execute($query);
    return $this;
  } 
}