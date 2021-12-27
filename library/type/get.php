<?php

namespace willow\type;

use willow\core\helper as h;
use willow;

class get {

	private 
		$_type = null
	;

	/**
	 * Construct
     */
    public function __construct(){

		// silence ##

	}
	
    /**
     * Get allowed fomats with filter ##
     * 
     */
    public function allowed(){

		// cache ##
		if ( $this->_type ) { 
		
			// w__log( 'Type set..' );
			// w__log( $this->_type );

			return $this->_type; 
		
		} 

		// via filter ##
        return $this->_type = \apply_filters( 'willow/render/type/get', \willow()->get( '_type' ) );

    }


}
