<?php

namespace q\plugin;

use q\core;
use q\core\helper as h;
// use q\wordpress as wordpress;

// load it up ##
\q\plugin\advanced_forms::run();

class advanced_forms extends \Q {

    public static function run()
    {

		if ( ! \is_admin() ) {

			// filter to remove ACF scripts on front-end ##
			\add_filter( 'q/tab/special/script', [ get_class(), 'form_remove_enqueues' ], 10, 2 );
		
		}

    }



    /**
    * Add ACF Fields
    *
    * @since    2.0.0
    */
    public static function form_remove_enqueues() {

		// Stylized select (including user and post fields)
		wp_dequeue_script( 'select2' );
		wp_dequeue_style( 'select2' );
	  
		// Date picker
		wp_dequeue_script( 'jquery-ui-datepicker' );
		wp_dequeue_style( 'acf-datepicker' );
	  
		// Date and time picker
		wp_dequeue_script( 'acf-timepicker' );
		wp_dequeue_style( 'acf-timepicker' );
	  
		// Color picker
		wp_dequeue_script( 'wp-color-picker' );
		wp_dequeue_style( 'wp-color-picker' );
	
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
