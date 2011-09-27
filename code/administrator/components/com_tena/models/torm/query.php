<?php

/**      
 * Friendly Querying interface. The code that drives this is in TOrmTable.    
 *
 * @copyright Copyright (C) 2011 Ken Erickson.
 * @license   GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link      https://github.com/bookworm/com_tena
 */
class TOrmQuery extends TOrmTable
{ 
  /**
   * Holds the associated items. e.g $postmodel->posts;
   *
   * @var array
   **/
  protected $_children;  
   
  /**
   * Holds instantiated models
   *
   * @var array
   **/  
  protected $_instances = array();
   
  /**
   * Constructor
   *
   * @param object An optional KConfig object with configuration options
   */      
  public function __construct(KConfig $config)
  {
    parent::__construct($config);           
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
    parent::_initialize($config);    
  }                                      
  
// ------------------------------------------------------------------------

 /**
  * Empty __call() overload just calls the parent.
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
  * Catches calls to associated items. 
  *        
  * @param string $name The name of the association. E.g $model->posts.
  * @return mixed $this->_children, a state or false.
  */ 
  public function __get($name)
  {
    if(isset($this->associations['many'][$name])) 
    {       
      $fkey = $this->associations['many'][$name]['foreign_key']; 
      $identifier = 'admin::com.' . 'comName' . '.model.' . $this->associations['many'][$name]['class_name'];
      if(!isset($this->_children[$name])) $this->_children[$name] = KFactory::tmp($identifier)->${$fkey}($this->_state->id)->all(); 
      return $this->_children[$name];      
    }
    elseif(isset($this->associations['one'][$name])) 
    {              
      $fkey = $this->associations['one'][$name]['foreign_key']; 
      $identifier = 'admin::com.' . 'comName' . '.model.' . $this->associations['one'][$name]['class_name']; 
      if(!isset($this->_children[$name])) $this->_children[$name] = KFactory::tmp($identifier)->${$fkey}($this->_state->id)->first();       
      return $this->_children[$name];
    }
    return parent::__get($name);
  }   
   
// ------------------------------------------------------------------------

 /**
  * Returns all the items.
  *            
  * @usage $model->all(array('limit' => '20', 'category' => 'bob')); 
  * @param string $states An array of states.
  * @return mixed $this->_items. An array of instantiated models.
  */ 
  public function all($states = array())
  {  
    if(!empty($states))
      $this->setStates($states);
      
    return $this->getItems();
  }     
  
// ------------------------------------------------------------------------

 /**
  * Returns all the items.
  *            
  * @return mixed $this->_items. An array of instantiated models.
  */
  public function getItems()
  {
    $this->getList();
    return $this->instantiateModels();
  }
  
// ------------------------------------------------------------------------

 /**
  * Setter for special states. Will catch special state options and set them otherwise passes off the set.   
  * Handles limit state for now.
  *   
  * @param string $property Name of the property to set.
  * @param mixed  $value The property's value.         
  * @return mixed
  */    
  public function set($property, $value = null)
  {
    parent::set($property, $value);

    // If limit has been changed, adjust offset accordingly
    if($limit = $this->_state->limit) {
      $this->_state->offset = $limit != 0 ? (floor($this->_state->offset / $limit) * $limit) : 0;
    }

    return $this;  
  }      
  
// ------------------------------------------------------------------------

 /**
  * Special state setter for models. Automatically adds a finder method for the state.
  *    
  * @usage $this->state($state, $value)->state($state, $value);
  * @param string $state Name of the state.
  * @param mixed  $value The state's value.      
  * @return this
  */ 
  public function state($state, $value)
  {           
    $this->addFindMethod($state);
    return $this->setState($state, $value);
  } 
  
// ------------------------------------------------------------------------

 /**
  * Sets a state
  *    
  * @param string $state Name of the state.
  * @param mixed  $value The state's value.      
  * @return this
  */ 
  public function setState($state, $value)
  {
    if(isset($this->_state[$state])) {
      $this->_state[$state]->value = $value;   
      return $this;
    } 
    else return false;   
  }
  
// ------------------------------------------------------------------------

 /**
  * Sets states.
  *    
  * @param array $states An array of states and their values; in the form of array('state' => 'value')
  * @return this
  */   
  public function setStates($states=array())
  {  
    foreach($states as $k => $v) {
      $this->setState($k, $v);
    }
    return $this;
  }    
  
// ------------------------------------------------------------------------

 /**
  * Getter for states 
  *   
  * @param string $property Name of the property to set.
  * @param mixed  $default The property's default value.         
  * @return mixed
  */   
  public function get($property = null, $default = null)
  {
    $result = $default;

    if(is_null($property)) {
      $result = $this->_state->getData();
    }
    else {
      if(isset($this->_state->$property)) {
        $result = $this->_state->$property;
      }
    }

    return $result; 
  } 
  
// ------------------------------------------------------------------------

 /**
  * Instantiates models for what is in $this->_list
  *   
  * @return $this->_items
  */     
  public function instantiateModels()
  {  
    foreach($this->_list->toArray() as $item) 
    {    
      $instance = new ${get_class($this)}();
      $instance->key_values = $item;
      $this->_items[] = $instance;
      unset($instance);
    }    
    return $this->_items;
  }  
 
// ------------------------------------------------------------------------

 /**
  * Instantiates an individual model.
  *   
  * @return $this->_instances[$instance->id] An $this model instance.  
  */  
  public function instantiateModel()
  {
    $instance = new ${get_class($this)}();
    $instance->key_values = $this->_item->toArray();      
    if(isset($this->_instances[$instance->id])) $this->_instances[$instance->id] = $instance;
    unset($instance); 
    return $this->_instances[$instance->id];
  } 
  
// ------------------------------------------------------------------------

 /**
  * Returns the first model matching the current query. And then instantiates it.
  *         
  * @param string $states An array of states.                 
  * @return $this->_instance An $this model instance.
  */ 
  public function first($states = array())
  {  
    if(!empty($states))
      $this->setStates($states);
      
    $this->getItem();
    return $this->instantiateModel();
  }
}