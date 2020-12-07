<?php

namespace Q\willow\type;

use Q\willow\core\helper as h;
use Q\willow;

class method {

	private 
		$plugin = false,
		$_type = null
	;

	/**
     */
    public function __construct( \Q\willow\plugin $plugin ){

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
