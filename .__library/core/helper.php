<?php

// namespace ##
namespace q\willow\core;

// piggyback Q ##
use q\core;


/**
 * helper Class
 * @package   q_willow\core
 */
class helper extends \q_willow {

/**
    * check if a file exists with environmental fallback
    * first check the active theme ( pulling info from "device-theme-switcher" ), then the plugin
    *
    * @param    $include        string      Include file with path ( from library/  ) to include. i.e. - templates/loop-nothing.php
    * @param    $return         string      return method ( echo, return, require )
    * @param    $type           string      type of return string ( url, path )
    * @param    $path           string      path prefix
    * 
    * @since 0.1
    */
    public static function get( $include = null, $return = 'echo', $type = 'url', $path = "library/" )
    {

        // use Q helper, but pass class name for plugin URL and PATH tests ##
        return core\helper::get( $include, $return, $type, $path, get_parent_class() );

    }



    /**
     * Write to WP Error Log
     *
     * @since       1.5.0
     * @return      void
     */
    public static function log( $log )
    {

        return core\helper::log( $log );

    }



    /**
    * Get current device type from "Device Theme Switcher"
    *
    * @since       0.1
    * @return      string      Device slug
    */
    public static function device()
    {

        return core\helper::device();

    }


}
