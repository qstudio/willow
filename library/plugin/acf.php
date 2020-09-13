<?php

namespace willow\plugin;

use willow\core;
use willow\core\helper as h;

class acf extends \willow {


    /**
    * Add ACF Fields
    *
    * @since    2.0.0
    */
    public static function add_field_groups( Array $groups = null )
    {

        // get all field groups ##
		// $groups = self::get_fields();
		
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {

            h::log( 'ACF Missing, please install or activate: "https://www.advancedcustomfields.com/"' );

            return false;

        }

        if ( 
            ! $groups 
            || ! is_array( $groups )
        ) {

            h::log( 'No groups to load.' );

            return false;

        }

		// loop over gruops ##
        foreach( $groups as $key => $value ) {

			// h::log( 'Filter: '.'q/plugin/acf/add_field_groups/'.$key );

            // filter groups -- NEW ##
			$value = \apply_filters( 'willow/plugin/acf/add_field_groups/'.$key, $value );
			
            // h::log( $value );

            // load them all up ##
            \acf_add_local_field_group( $value );

        }

    }



    /**
     * Get field group
     */
    public static function get_field_group( String $group = null ) {

        // sanity ##
        if ( ! \function_exists('acf_get_field_group') ) {

            h::log( 'Error -> function "acf_get_field_group" not found' );

            return false;

        }

        // check if string passed ##
        if ( is_null( $group ) ) {

            h::log( 'Error -> No "group" string passed to method.' );

            return false;

        }

        // look for field group and return boolen if fails ##
        if ( ! $array = \acf_get_fields( $group ) ) {

            h::log( 'Notice -> Group: "'.$group.'" not found.' );

            return false;

        }

        // filter ##
        $array = \apply_filters( 'willow/plugin/acf/get_field_group/'.$group, $array );

        // return ##
        return $array;

    }   


}
