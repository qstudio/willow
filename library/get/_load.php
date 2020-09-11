<?php

namespace q\willow;

use q\willow\core;
use q\willow\core\helper as h;

// load it up ##
\q\willow\get::__run();

class get extends \willow {

	public static function __run(){

		self::load();

	}

    /**
    * Load Libraries
    *
    * @since        2.0.0
    */
    public static function load()
    {

		// methods ##
		// require_once self::get_plugin_path( 'get/method.php' );

		// taxonomy object ##
		// require_once self::get_plugin_path( 'get/plugin.php' );

		// taxonomy object ##
		// require_once self::get_plugin_path( 'get/theme.php' );

		// WP_Post queries ##
		// require_once self::get_plugin_path( 'get/query.php' );

		// has::xx queries ##
		// require_once self::get_plugin_path( 'get/has.php' );

		// post object ##
		require_once self::get_plugin_path( 'library/get/post.php' );

		// post meta ##
		// require_once self::get_plugin_path( 'get/meta.php' );

		// field group ##
		// require_once self::get_plugin_path( 'get/group.php' );

		// taxonomy object ##
		// require_once self::get_plugin_path( 'get/taxonomy.php' );

		// modules ##
		// require_once self::get_plugin_path( 'get/module.php' );

		// navigation items ##
		// require_once self::get_plugin_path( 'get/navigation.php' );

		// media objects ##
		require_once self::get_plugin_path( 'library/get/media.php' );
			
    }

}
