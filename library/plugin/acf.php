<?php

namespace q\plugin;

use q\core;
use q\core\helper as h;
// use q\wordpress as wordpress;

// load it up ##
\q\plugin\acf::run();

class acf extends \Q {

    public static function run()
    {

        // // filter q/tab/special/script ##
        // \add_filter( 'q/tab/special/script', [ get_class(), 'tab_special_script' ], 10, 2 );

    }



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
			$value = \apply_filters( 'q/plugin/acf/add_field_groups/'.$key, $value );
			
            // h::log( $value );

            // load them all up ##
            \acf_add_local_field_group( $value );

        }

    }



    // /**
    //  * Get field group
    //  */
    // public static function get_field_group( String $group = null ) {

    //     // @todo -- sanity ##
    //     if ( ! \function_exists('acf_get_field_group') ) {

    //         h::log( 'function "acf_get_field_group" not found' );

    //         return false;

    //     }

    //     // @todo -- check if string passed ##

    //     // @todo -- look for field group and return boolen if fails ##

    //     return \acf_get_field_group( $group );

    // }   



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

        // the $group string might be passed without the prefix "group_" - if it's missing, add it ##
        if ( 'group_' !== substr( $group, 0, 6 ) ) {

            // h::log( 'e:>Notice -> "group" "group_" prefix REMOVED...' );

            // $group = "group_".$group;

        }

        // look for field group and return boolen if fails ##
        if ( ! $array = \acf_get_fields( $group ) ) {

            h::log( 'Notice -> Group: "'.$group.'" not found.' );

            return false;

        }

        // filter ##
        $array = \apply_filters( 'q/plugin/acf/get_field_group/'.$group, $array );

        // return ##
        return $array;

    }   


}
