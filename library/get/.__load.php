<?php

namespace Q\willow;

use Q\willow;

class get {

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

		$this->plugin->log( 'OK from GET class' );

		// methods ##
		// require_once self::get_plugin_path( 'library/get/method.php' );

		// plugins ##
		// require_once self::get_plugin_path( 'library/get/plugin.php' );

		// themes ##
		// require_once self::get_plugin_path( 'library/get/theme.php' );

		// WP_Post queries ##
		// require_once self::get_plugin_path( 'library/get/query.php' );

		// has::xx queries ##
		// require_once self::get_plugin_path( 'library/get/has.php' );

		// post object ##
		// require_once self::get_plugin_path( 'library/get/post.php' );

		// post meta ##
		// require_once self::get_plugin_path( 'library/get/meta.php' );

		// field group ##
		// require_once self::get_plugin_path( 'library/get/group.php' );

		// taxonomy object ##
		// require_once self::get_plugin_path( 'library/get/taxonomy.php' );

		// modules ##
		// require_once self::get_plugin_path( 'library/get/module.php' );

		// navigation items ##
		// require_once self::get_plugin_path( 'library/get/navigation.php' );

		// media objects ##
		// require_once self::get_plugin_path( 'library/get/media.php' );
			
	}
	
}
