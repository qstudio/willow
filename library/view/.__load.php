<?php

namespace Q\willow;

use Q\willow\core;
// use willow\core\helper as h;

// load it up ##
// \willow\view::__run();

class view {

	/**
     * Plugin Instance
     *
     * @var     Object      $plugin
     */
	protected 
		$plugin
	;

	/**
	 * CLass Constructer 
	*/
	function __construct( $plugin = null ){

		// Log::write( $plugin );

		// grab passed plugin object ## 
		$this->plugin = $plugin;
		
	}

	/**
	* Load Libraries
	*
	* @since        2.0.0
	*/
	public function hook(){

		// is methods ##
		require_once $this->plugin->get_plugin_path( 'library/view/is.php' );

		// filters ##
		// 'view' => self::get_plugin_path( 'library/view/filter.php' ),

	}

}

