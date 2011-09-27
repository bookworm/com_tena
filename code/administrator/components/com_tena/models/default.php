<?php 

// Requires
require_once 'torm' . DS . 'db.php'; 
require_once 'torm' . DS . 'core.php';         
require_once 'torm' . DS . 'table.php';      
require_once 'torm' . DS . 'query.php';      
require_once 'torm' . DS . 'torm.php';      

/**      
 * A better default model     
 *
 * @copyright Copyright (C) 2011 Ken Erickson.
 * @license   GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link      https://github.com/bookworm/com_tena
 */
 class ComTenaModelDefault extends TORm
 {
 /**
      * Constructor
      *
      * @param   object  An optional KConfig object with configuration options
      */
     public function __construct(KConfig $config)
     {
         parent::__construct($config);

         // Set the static states
         #$this->_state->limit = KFactory::get('lib.joomla.application')->getCfg('list_limit');
     }
 }