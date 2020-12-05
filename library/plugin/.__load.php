<?php

namespace willow;

use willow\core;
use willow\core\helper as h;

// load it up ##
\willow\plugin::__run();

class plugin extends \willow {

    public static function __run()
    {

        self::load();

    }


    /**
    * Load Libraries
    *
    * @since        2.0.0
    */
    private static function load()
    {

		require_once self::get_plugin_path( 'library/plugin/acf.php' );

    }

}
