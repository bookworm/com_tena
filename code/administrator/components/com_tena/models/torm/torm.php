<?php

/**      
 * The instantiated model stuff. save, update etc.
 *
 * @copyright Copyright (C) 2011 Ken Erickson.
 * @license   GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link      https://github.com/bookworm/com_tena
 */
class TOrm extends TOrmQuery
{                
  /**
   * Holds the keys and their values
   *
   * @var array
   */
  public $key_values;
  
  /**
   * Keys with special objects. Hit when you access a key using a method.
   *
   * @var array
   */     
  public $key_objects;   
  
  /**
   * Initializes the config for the object
   *
   * Called from {@link __construct()} as a first step of object instantiation.
   *
   * @param  object An optional KConfig object with configuration options
   * @return void
   */   
  public function __initialize($value='')
  {
    $this->key($this->getIdentifier()->package . '_' . $this->name. '_' . 'id', 'SERIAL');
    parent::_initialize($config);       
  }  
  
// ------------------------------------------------------------------------   
  
  /**
   * __get() overload for getting keys.
   *     
   * @param bool $name Name of the property to get.
   * @return mixed Either the key or parent::__get()
   */
  public function __get($name)
  { 
    if(isset($this->key_values[$name])) return $this->key_values[$name];
    else return parent::__get($name);
  }  
  
// ------------------------------------------------------------------------
  
  /**
   * Used for overloading the call to keys. e.g $model->date()->date_sunset(); 
   *        
   * @param string $name Method name.
   * @param array  $args The arguments
   * @return mixed Either the Field object, false, or parent::__call
   */
  public function __call($name, $args)
  {
    if(isset($this->key_objects[$name])) return $this->key_objects[$name];  
    elseif(empty($args) && isset($this->key_values[$name]))
    { 
      $class_name = 'TField'.strtoupper(self::$keys[$name]['type']);
      if(class_exists($class_name)) {
        $this->key_objects[$name] = new ${$class_name}($this->key_values[$name]); 
        return $this->key_objects[$name];
      }
      else return false; 
    }  
    else return parent::__call($name, $args);
  }   
  
// ------------------------------------------------------------------------

  /**
   * Updates the keys and their values and then saves.
   *        
   * @param array $keys_and_values An array of keys and their new values e.g array('title' => 'Model Post Title');s
   * @return $thiss
   */  
  public function update($keys_and_values = array())
  {
    $this->updateAttributes($keys_and_values = array());
    $this->save();
  }  
  
// ------------------------------------------------------------------------

  /**
   * Alias to $this->set(). Sets a key and its value
   *
   * @param string $key Name of the key to set.
   * @param mixed  $value The key's value.         
   * @return mixed
   */     
  public function __set($key, $value)
  {  
    return $this->set($key, $value);
  }  
 
// ------------------------------------------------------------------------

  /**
   * Sets a key and its value
   *
   * @param string $key Name of the key to set.
   * @param mixed  $value The key's value.         
   * @return mixed $this or parent::set()
   */  
  public function set($key, $value = null)
  {   
    if(isset($this->key_values[$key])) { 
      $this->key_values[$key] = $value; 
      return $this;
    }   
    return parent::set($key, $value);
  }  
   
// ------------------------------------------------------------------------

  /**
   * Updates the keys and their values.
   *        
   * @param array $keys_and_values An array of keys and their new values e.g array('title' => 'Model Post Title');s
   * @return $this
   */ 
  public function updateAttributes($keys_and_values = array())
  {
    $this->key_values = array_merge($this->key_values, $keys_and_values);
    return $this;
  }
 
// ------------------------------------------------------------------------

  /**
   * Updates the data and saves.
   *        
   * @return $this
   */  
  public function save()
  {
    $this->getItem()->setData($this->keys_and_values)->save();
    return $this;
  }
}    