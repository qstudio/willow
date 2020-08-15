<?php

namespace q\willow;

// use q\core\core as core;
use q\core\helper as h;

// load it up ##
\q\willow\core::__run();

class core extends \q_willow {

    public static function __run()
    {

        // load templates ##
		self::load_libraries();
		
		h::log( 't:>@TODO - remove dependency on Q.. move required methods into Willow' );

    }


    /**
    * Load Libraries
    *
    * @since        2.0.0
    */
    private static function load_libraries()
    {

		// lirbaries ##
		require_once self::get_plugin_path( 'library/core/helper.php' );
		require_once self::get_plugin_path( 'library/core/config.php' );
		require_once self::get_plugin_path( 'library/core/method.php' );

    }

}
