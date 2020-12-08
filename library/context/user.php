<?php

namespace willow\context;

use willow\core\helper as h;
use willow;

class user {

	private
		$plugin = null // this
	;

	/**
	 * 
	 */
	public function __construct( \willow\plugin $plugin ){

		// grab passed plugin object ## 
		$this->plugin = $plugin;

	}


}
