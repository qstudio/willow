<?php

namespace willow;

// use q\core\core as core;
use willow\core\helper as h;

// load it up ##
\willow\core::__run();

class core extends \willow {

    public static function __run(){

        // load templates ##
		self::load_libraries();
		
    }


    /**
    * Load Libraries
    *
    * @since        2.0.0
    */
    private static function load_libraries(){

		// lirbaries ##
		require_once self::get_plugin_path( 'library/core/helper.php' );
		require_once self::get_plugin_path( 'library/core/config.php' );
		require_once self::get_plugin_path( 'library/core/method.php' );
		require_once self::get_plugin_path( 'library/core/function.php' );
		require_once self::get_plugin_path( 'library/core/filter.php' );
		require_once self::get_plugin_path( 'library/core/log.php' );

    }

}
