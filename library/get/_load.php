<?php

namespace willow;

use willow\core;
use willow\core\helper as h;

// load it up ##
\willow\get::__run();

class get extends \willow {

	public static function __run(){

		self::load();

	}

    /**
    * Load Libraries
    *
    * @since        2.0.0
    */
    public static function load(){

		// methods ##
		require_once self::get_plugin_path( 'library/get/method.php' );

		// plugins ##
		require_once self::get_plugin_path( 'library/get/plugin.php' );

		// themes ##
		require_once self::get_plugin_path( 'library/get/theme.php' );

		// WP_Post queries ##
		require_once self::get_plugin_path( 'library/get/query.php' );

		// has::xx queries ##
		require_once self::get_plugin_path( 'library/get/has.php' );

		// post object ##
		require_once self::get_plugin_path( 'library/get/post.php' );

		// post meta ##
		require_once self::get_plugin_path( 'library/get/meta.php' );

		// field group ##
		require_once self::get_plugin_path( 'library/get/group.php' );

		// taxonomy object ##
		require_once self::get_plugin_path( 'library/get/taxonomy.php' );

		// modules ##
		require_once self::get_plugin_path( 'library/get/module.php' );

		// navigation items ##
		require_once self::get_plugin_path( 'library/get/navigation.php' );

		// media objects ##
		require_once self::get_plugin_path( 'library/get/media.php' );
			
    }

}
