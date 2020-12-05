<?php

namespace Q\willow\type;

use Q\willow\core\helper as h;
use Q\willow;

class method {

	private 
		$plugin = false
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

        return \apply_filters( 'willow/render/type/get', $this->plugin->get( '_type' ) );

    }


}
