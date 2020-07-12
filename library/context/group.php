<?php

namespace q\willow\context;

use q\core;
use q\core\helper as h;
use q\get;
use q\willow;
use q\willow\render; 

class group extends willow\context {


	/**
     * Get group data via meta handler
     *
     * @param       Array       $args
     * @since       1.3.0
	 * @uses		define
     * @return      Array
     */
    public static function get( $args = null ) {

		// h::log( $args );
		// h::log( self::$markup );

		// get title - returns array with key 'title' ##
		// $args['field'] = $args['task']; // make sure field value is set ##
		
		// method returns an array with 'data' and 'fields' ##
		if ( 
			$array = get\group::fields( $args )
		){
			// h::log( $array );
			
			// "args->fields" are used for type and callback lookups ##
			self::$args['fields'] = $array['fields']; 

			// define "fields", passing returned data ##
			render\fields::define(
				$array['data']
			);

		}

	}
	


	/**
     * Get group data via meta handler
     *
     * @param       Array       $args
     * @since       1.3.0
	 * @uses		define
     * @return      Array
     */
    public static function get_NO( $args = null ) {

        // sanity ##
        if ( 
            is_null( self::$args ) 
            || ! is_array( self::$args )
            // || ! isset( render::$args['fields'] )
        ) {

			// log ##
			h::log( self::$args['task'].'~>e:Error in passed parameter $args');

            return false;

        }

        // Get all ACF field data for this post ##
        if ( ! self::acf_fields() ) {

            return false;

        }

        // get all fields defined in this group -- pass to $args['fields'] ##
        if ( ! self::group_fields() ) {

            return false;

        }

        // h::log( render::$args[ 'fields' ] );

        // get field names from passed $args ##
        $array = array_column( self::$args[ 'fields' ], 'name' );

        // sanity ##
        if ( 
            ! $array 
            || ! is_array( $array )
        ) {

			// log ##
			h::log( self::$args['task'].'~>e:Error extracting field list from passed data');

            return false;

        }

        // h::log( $array );

        // assign class property ##
        self::$fields = $array;
		// h::log( self::$fields );

        // remove skipped fields, if defined ##
        self::skip();

        // if group specified, get only fields from this group ##
        self::group();

        // check if feature is enabled ##
        if ( ! render\args::is_enabled() ) {

            return false;

       }    

        // h::log( self::$fields );

        // we should do a check if $fields is empty after all the filtering ##
        if ( 
            0 == count( self::$fields ) 
        ) {

			// log ##
			h::log( self::$args['task'].'~>n:Fields array is empty, so nothing to process...');

            return false;

		}
		
		h::log( self::$fields );

        // positive ##
        return true;

    }

	

    /**
     * Get ACF Fields
     */
    public static function acf_fields(){

		if ( ! function_exists( 'get_fields' ) ) {

			h::log( 'ACF Plugin missing' );

			return false;

		}

        // get fields ##
		$array = 
			\get_fields( 
				isset( render::$args['config']['post']->ID ) ? 
				render::$args['config']['post']->ID : 
				false 
			);
		
		// h::log( $array );

        // sanity ##
        if ( 
            ! $array 
            || ! is_array( $array )
        ) {

			// log ##
			h::log( render::$args['task'].'~>n:Post has no ACF field data or corrupt data returned');

            return false;

        }

        // h::log( $array );

        return self::$acf_fields = $array;

    }



	/**
	 * Get ACF Field Group from passed $group reference
	 */
    public static function group_fields(){

        // assign variable ##
        $group = render::$args['task'];

        // try to get fields ##
        $array = plugin\acf::get_field_group( $group );

        // h::log( $array );

        if ( 
            ! $array
            || ! \is_array( $array )
        ) {

			// log ##
			h::log( render::$args['task'].'~>e:No valid ACF field group returned for Group: "'.$group.'"');

            return false;

        }

        // filter ##
        $array = core\filter::apply([ 
            'parameters'    => [ 'fields' => $array ], // pass ( $fields ) as single array ##
            'filter'        => 'q/render/get/group_fields/'.$group, // filter handle ##
            'return'        => $array
        ]); 

        // assign to class property ##
        render::$args['fields'] = $array;

        // h::log( $array[0] );

        // return
        return true;

    }



	/**
	 * Skip fields marked to avoid
	 */
    public static function skip(){

        // sanity ##
        if ( 
            ! render::$args 
            || ! is_array( render::$args )
        ) {

			// log ##
			h::log( render::$args['task'].'~>e:Error in passed $args');

            return false;

        }

        if ( 
            isset( render::$args['skip'] ) 
            && is_array( render::$args['skip'] )
        ) {

            // h::log( render::$args['skip'] );
            self::$fields = array_diff( self::$fields, render::$args['skip'] );

        }

    }



	/**
	* Get the fields from the listed ACF group, removing fields returned form acf_fields()
	*/
    public static function group(){

        // sanity ##
        if ( 
            ! render::$args 
            || ! is_array( render::$args )
            || ! self::$fields
            || ! is_array( self::$fields )
        ) {

			// log ##
			h::log( render::$args['task'].'~>e:Error in passed $args or $fields');

            return false;

		}
		
		// h::log( self::$acf_fields );

        if ( 
            isset( render::$args['task'] )
        ) {

            // h::log( 'Removing fields from other groups... BEFORE: '.count( self::$fields ) );
            // h::log( self::$fields );

            self::$fields = array_intersect_key( self::$acf_fields, array_flip( self::$fields ) );

            // h::log( 'Removing fields from other groups... AFTER: '.count( self::$fields ) );

        }

        // kick back ##
        return true;

    }


}
