<?php

namespace willow\type;

use willow\core\helper as h;
use willow;

class method {

	private 
		$plugin = false,
		$_type = null
	;

	/**
     */
    public function __construct( \willow\plugin $plugin ){

		// grab passed plugin object ## 
		$this->plugin = $plugin;

	}

	
    /**
     * Get allowed fomats with filter ##
     * 
     */
    public function get_allowed(){

		// cache ##
		if ( $this->_type ) { 
		
			// w__log( 'Type set..' );
			// w__log( $this->_type );

			return $this->_type; 
		
		} 

		// via filter ##
        return $this->_type = \apply_filters( 'willow/render/type/get', $this->plugin->get( '_type' ) );

    }


}
