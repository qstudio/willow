<?php

namespace Q\willow;

use Q\willow;

class core {

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
	 * callback method for class instantiation
	 *
	 * @since   0.0.2
	 * @return  void
	 */
	public function hooks() {

		// sanity ##
		if( 
			is_null( $this->plugin )
			|| ! ( $this->plugin instanceof plugin ) 
		) {

			error_log( 'Error in object instance passed to '.__CLASS__ );

			return false;
		
		}

		// $this->plugin->log( 'OK from CORE class' );

		// exit;

		// get core object ##
		// $core = $this->plugin->get( 'core' ) ?? new stdClass();
		// $this->plugin->log( $this->plugin );

		// kick off config and store object ##
		$config = new willow\core\config( $this->plugin );
		$config->hooks();

		// store config object ##
		$this->plugin->set( 'config', $config );
		
		// load static methods -- perhaps this will magically load ?? ##
		require_once $this->plugin->get_plugin_path( 'library/core/method.php' );

		// load global functions ##
		require_once $this->plugin->get_plugin_path( 'library/core/function.php' );

		// filters -- should load magically when called ##
		// require_once self::get_plugin_path( 'library/core/filter.php' );

		

    }

}
