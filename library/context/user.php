<?php

namespace Q\willow\context;

use Q\willow\core\helper as h;
use Q\willow;

class user {

	private
		$plugin = null // this
	;

	/**
	 * 
	 */
	public function __construct( \Q\willow\plugin $plugin ){

		// grab passed plugin object ## 
		$this->plugin = $plugin;

	}


}
