<?php

namespace willow\get;

// Q ##
use willow\core;
use willow\core\helper as h;
use willow\get;
use willow\plugin;

class group extends \willow\get {
	
	public static 
		$acf_fields = null, // fields grabbed by acf function ##
		$data = null, // returned field data array ##
		$fields = null // to return to self::$args['fields]
	;

    /**
     * Get group data via meta handler
     *
     * @param       Array       $args
     * @since       1.3.0
	 * @uses		define
     * @return      Array
     */
    public static function fields( $args = null ) {

		// h::log( $args );

        // sanity ##
        if ( 
            is_null( $args ) 
            || ! is_array( $args )
            // || ! isset( $args['fields'] )
        ) {

			// log ##
			h::log( $args['task'].'~>e:Error in passed parameter $args');
			// h::log( 'e:>Error in passed parameter $args');

            return false;

        }

        // Get all ACF field data for this post ##
        if ( ! self::acf_fields( $args ) ) {

            return false;

        }

        // get all fields defined in this group -- pass to $args['fields'] ##
        if ( ! self::group_fields( $args ) ) {

            return false;

        }

        // h::log( $args[ 'fields' ] );

        // get field names from passed $args ##
        $array = array_column( self::$data, 'name' );

		// h::log( $array );

        // sanity ##
        if ( 
            ! $array 
            || ! is_array( $array )
        ) {

			// log ##
			h::log( $args['task'].'~>e:Error extracting field list from passed data');
			// h::log( 'e:>Error extracting field list from passed data');

            return false;

        }

        // h::log( $array );

        // assign class property ##
        self::$data = $array;
		// h::log( self::$data );

        // remove skipped fields, if defined ##
        self::skip( $args );

        // if group specified, get only fields from this group ##
        self::group( $args );

        // h::log( self::$data );

        // we should do a check if $fields is empty after all the filtering ##
        if ( 
            0 == count( self::$data ) 
        ) {

			// log ##
			h::log( $args['task'].'~>n:Fields array is empty, so nothing to process...');
			// h::log( 'e:>:Fields array is empty, so nothing to process...');

            return false;

		}
		
		// h::log( self::$data );
		// h::log( self::$fields );

        // positive ##
        return [
			'fields'	=> self::$fields,
			'data' 		=> self::$data
		];

    }

	

    /**
     * Get ACF Fields
     */
    public static function acf_fields( $args = null ){

		if ( ! function_exists( 'get_fields' ) ) {

			h::log( 'e:>ACF Plugin missing' );

			return false;

		}

        // get fields ##
		$array = 
			\get_fields( 
				isset( $args['config']['post']->ID ) ? 
				$args['config']['post']->ID : 
				false 
			);
		
		// h::log( $array );

        // sanity ##
        if ( 
            ! $array 
            || ! is_array( $array )
        ) {

			// log ##
			h::log( $args['task'].'~>n:Post has no ACF field data or corrupt data returned');
			// h::log( 'd:>Post has no ACF field data or corrupt data returned');

            return false;

        }

        // h::log( $array );

        return self::$acf_fields = $array;

    }



	/**
	 * Get ACF Field Group from passed $group reference
	 */
    public static function group_fields( $args = null ){

        // assign variable ##
        $group = $args['task'];

        // try to get fields ##
        $array = plugin\acf::get_field_group( $group );

        // h::log( $array );

        if ( 
            ! $array
            || ! \is_array( $array )
        ) {

			// log ##
			h::log( $args['task'].'~>e:No valid ACF field group returned for Group: "'.$group.'"');
			// h::log( 'd:>:No valid ACF field group returned for Group: "'.$group.'"');

            return false;

        }

        // filter ##
        $array = core\filter::apply([ 
            'parameters'    => [ 'fields' => $array ], // pass ( $fields ) as single array ##
            'filter'        => 'willow/get/group/'.$group, // filter handle ##
            'return'        => $array
        ]); 

        // assign to class properties ##
		self::$fields = $array; // capture all fields for type and callback lookups ##
		self::$data = $array; // data to return to fields\define ##

        // h::log( $array );

        // return
        return true;

    }



	/**
	 * Skip fields marked to avoid
	 */
    public static function skip( $args = null ){

        // sanity ##
        if ( 
            ! $args 
            || ! is_array( $args )
        ) {

			// log ##
			h::log( $args['task'].'~>e:Error in passed $args');
			// h::log( 'e:>Error in passed $args');

            return false;

        }

        if ( 
            isset( $args['skip'] ) 
            && is_array( $args['skip'] )
        ) {

            // h::log( $args['skip'] );
            self::$data = array_diff( self::$data, $args['skip'] );

        }

    }



	/**
	* Get the fields from the listed ACF group, removing fields returned form acf_fields()
	*/
    public static function group( $args = null ){

        // sanity ##
        if ( 
            ! $args 
            || ! is_array( $args )
            || ! self::$data
            || ! is_array( self::$data )
        ) {

			// log ##
			h::log( $args['task'].'~>e:Error in passed $args or $fields');
			// h::log( 'e:>Error in passed $args or $fields');

            return false;

		}
		
		// h::log( self::$acf_fields );

        if ( 
            isset( $args['task'] )
        ) {

            // h::log( 'Removing fields from other groups... BEFORE: '.count( self::$data ) );
            // h::log( self::$data );

            self::$data = array_intersect_key( self::$acf_fields, array_flip( self::$data ) );

            // h::log( 'Removing fields from other groups... AFTER: '.count( self::$data ) );

        }

        // kick back ##
        return true;

    }

}
