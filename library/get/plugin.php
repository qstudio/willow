<?php

namespace willow\get;

use willow;
use willow\core\helper as h;

class plugin {

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
	
    /**
     * Check if a plugin is active
     * 
     * @since       2.0.0
     * @return      Boolean
     */
    public static function is_active( $plugin ){
        
        return in_array( $plugin, (array) \get_site_option( 'active_plugins', [] ) );
    
    }
	
    /**
    * Get Q Plugin data
    *
    * @return   Object
    * @since    0.3
    */
    public static function data( $option = 'q_plugin_data', $refresh = false ){

        if ( $refresh ) {

            #echo 'refrshing stored framework data<br />'; ##
            \delete_site_option( $option ); // delete option ##

        }

        if ( ! $array = \get_site_option( $option ) ) {

            $array = array (
                'version'       => self::version // \Q::version
            );

            if ( $array ) {

                willow\core\method::add_update_option( $option, $array, '', 'yes' );

            }

        }

        return willow\core\method::array_to_object( $array );

	}
	
}	
