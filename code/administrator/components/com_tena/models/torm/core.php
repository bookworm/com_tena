<?php    

/**      
 * Core Handles stuff like associations and keys/fields.
 *
 * @copyright Copyright (C) 2011 Ken Erickson.
 * @license   GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link      https://github.com/bookworm/com_tena
 */
class TOrmCore extends TOrmDB
{   
  /**
   * The class short name points to $this->getIdentifier()->getName()
   *
   * @var string
   **/
  public $name;   
  
  /**
   * Holds the key and their info's.
   *           
   * @usage $this->key('name', 'type', $options = array());
   * @var arrary
   **/
  public $keys = array();   
  
  /**
   * Find Methods.
   *
   * @note Holds an array of find methods e.g find_by_id or find_by_title.  
   *  These are generated from keys and states.
   *  
   * @usage You can call these using $model->find_by_keyname();
   * @var array
   **/
  public $find_methods = array();    
  
  /**
   * Associations between other models.
   *
   * @note Very query heavy at the moment. Need to re-factor to more efficiently use joins & construct stuff in code.
   *           
   * @var array
   **/
  public $associations = array('one' => array(), 'many' => array());    
  
  /**
   * Constructor
   *
   * @param object An optional KConfig object with configuration options
   */
  public function __construct(KConfig $config = null)
  {
		if(!isset($config)) $config = new KConfig();

		parent::__construct($config);

    $this->_initialize();
  }      
       
// ------------------------------------------------------------------------
  
  /**
   * Initializes the config for the object
   *
   * Called from {@link __construct()} as a first step of object instantiation.
   *
   * @param  object An optional KConfig object with configuration options
   * @return void
   */
  public function _initialize(KConfig $config)
	{
    $this->name = $this->getIdentifier()->getName();      
    parent::_initialize($config);
  }         
  
// ------------------------------------------------------------------------

 /**
  * A __call() overload provides fluent interfaces and find_method magic.
  *        
  * @param string $name Method name.
  * @param array  $args The arguments
  * @return mixed 
  */
  public function __call($name, $args)
  {       
    if($this->find_methods[$name] && !empty($args))
    {             
      $state = explode('_', $name);
      $this->setState($state[2], $args[0]);
      return $this->first();
    }
    elseif(isset($this->_state->$name)) {
      return $this->set($name, $args[0]);
    }                  
    
    return parent::__call($name, $args);
  } 
  
// ------------------------------------------------------------------------

 /**
  * Builds a key. Alias to $this->key
  *   
  * @usage $this->key('name', 'type', $options = array());     
  * @param string $name Name of the key.
  * @param string $type The type.
  * @param array  $options An array of options.
  * @return $this
  */  
  public function field($key, $type, $options = array())
  {
    return $this->key($key, $type, $options);        
  }
                                  
// ------------------------------------------------------------------------

 /**
  * Builds a key
  *   
  * @usage $this->key('name', 'type', $options = array());     
  * @param string $name Name of the key.
  * @param string $type The type.
  * @param array  $options An array of options.
  * @return $this
  */  
  public function key($key, $type, $options = array())
  {         
    if(isset($options['sql_type'])) 
    { 
      $sql_type = $options['sql_type'];                            
    }   
    else 
    {
      if(isset($this->sql_types[$type])) {
  	    $sql_type = $type;  
  	  } 
  	  else 
  	  {    
  	    if(!isset($this->sqltypemap[strtolower($type)])) throw new KException('Non existent type'); 
  	    
  	    if(is_array($this->sqltypemap[$type])) {
  	      $sql_type = $this->sqltypemap[$type]['type']; 
  	      $options = array_merge($options, $this->sqltypemap[$type]['default_options']);
  	    }  
  	    else {
  	      $sql_type = $this->sqltypemap[$type];
  	    }
  	  }  
    }   
      
    if(!isset($options['sql_options'])) $sql_options = '';
    else $sql_options = $options['sql_options'];
    
    $this->keys[$key] = array(
      'key'          => $key,
       $type         => 'type', 
       'options'     => $options,  
       'sql_type'    => $sql_type, 
       'sql_options' => $sql_options
     );   
    if(!isset($options['state'])) $this->addFindMethod($state);  
    return $this;
  }  
   
// ------------------------------------------------------------------------
  
  /**
   * A has_one association. 
   *   
   * @usage $this->has_one('name' $options = array());     
   * @param string $name The name of what is had.
   * @param array  $option An array of options.
   * @return $this
   */   
  public function has_one()
  {
    $args = func_get_args();
    $name = $args[0]; 
    if(isset($args[a])) $options = $args[2];    
    else { $options = array(); }        
       
    if(!isset($options['foreign_key'])) $options['foreign_key'] = strtolower($this->$name) . '_' . 'id'; 
    if(!isset($options['class_name']))  $options['class_name'] = strtoupper($name);   
    
    $this->associations['one'][$name] = array(
      'name'        => $name,  
      'foreign_key' => $options['foreign_key'],
      'class_name'  => $options['class_name'],
    ); 
    
    return $this;       
  }    
  
// ------------------------------------------------------------------------

  /**
   * A has_many association. 
   *   
   * @usage $this->has_one('name' $options = array());     
   * @param string $name The name of what is had.
   * @param array  $option An array of options.
   * @return $this
   */
  public function has_many()
  {
    $args = func_get_args();
    $name = $args[0]; 
    if(isset($args[a])) $options = $args[2];
    else { $options = array(); } 
    
    if(!isset($options['foreign_key'])) $options['foreign_key'] = strtolower($this->name) . '_' . 'id'; 
    if(!isset($options['class_name']))  $options['class_name']  = strtoupper($name);
    
    $this->associations['many'][$name] = array(
      'name'        => $name, 
      'foreign_key' => $options['foreign_key'],
      'class_name'  => $options['class_name'],
    ); 
    
    return $this;
  }     
  
// ------------------------------------------------------------------------

  /**
   * Adds a find method.
   *     
   * @param string $name Short name of find method to add.  
   * @return this
   */
  public function addFindMethod($name)
  {
    $this->find_methods[] = 'find'.'_'.'by'.$name; 
    return $this;
  } 
   
// ------------------------------------------------------------------------

  /**
   * Resets.
   *     
   * @param bool $default Reset to defaults?
   * @return this
   */
  public function reset($default = true)
  {
    unset($this->_list);
    unset($this->_item);
    unset($this->_total);        
    unset($this->_children);
    unset($this->_items);
    
    $this->_state->reset($default);

    return $this;     
  }  
   
// ------------------------------------------------------------------------

  /**
   * __get() overload for convenient aliases like with the model id etc.
   *     
   * @param bool $name Name of the property to get.
   * @return mixed Either the key or parent::__get()
   */  
  public function __get($name)
  {
    if($name == 'id') {
      return $this->key_values[$this->package . '_' . $this->name. '_' . 'id'];
    } 
    return parent::__get($name);
  }
}