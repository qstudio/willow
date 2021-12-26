<?php

namespace willow\get;

use willow;

class group {
	
	private
		$plugin = null, // this 
		$acf_fields = null, // fields grabbed by acf function ##
		$data = null, // returned field data array ##
		$fields = null // to return to self::$args['fields]
	;

	/**
	 * 
     */
    public function __construct( willow\plugin $plugin ){

		// grab passed plugin object ## 
		$this->plugin = $plugin;

	}

    /**
     * Get group data via meta handler
     *
     * @param       Array       $args
     * @since       1.3.0
	 * @uses		define
     * @return      mixed
     */
    public function fields( array $args = null ):?array
	{

		// w__log( $args );

        // sanity ##
        if ( 
            is_null( $args ) 
            || ! is_array( $args )
        ) {

			// log ##
			w__log( $args['task'].'~>e:Error in passed parameter $args');
			// w__log( 'e:>Error in passed parameter $args');

            return null;

        }

        // Get all ACF field data for this post ##
        if ( ! $this->acf_fields( $args ) ) {

            return null;

        }

        // get all fields defined in this group -- pass to $args['fields'] ##
        if ( ! $this->group_fields( $args ) ) {

            return null;

        }

        // w__log( $args[ 'fields' ] );

        // get field names from passed $args ##
        $array = array_column( $this->data, 'name' );

		// w__log( $array );

        // sanity ##
        if ( 
            ! $array 
            || ! is_array( $array )
        ) {

			// log ##
			w__log( $args['task'].'~>e:Error extracting field list from passed data');
			// w__log( 'e:>Error extracting field list from passed data');

            return null;

        }

        // w__log( $array );

        // assign class property ##
        $this->data = $array;
		// w__log( self::$data );

        // remove skipped fields, if defined ##
        $this->skip( $args );

        // if group specified, get only fields from this group ##
        $this->group( $args );

        // w__log( self::$data );

        // we should do a check if $fields is empty after all the filtering ##
        if ( 
            0 == count( $this->data ) 
        ) {

			// log ##
			w__log( $args['task'].'~>n:Fields array is empty, so nothing to process...');
			// w__log( 'e:>:Fields array is empty, so nothing to process...');

            return null;

		}
		
		// w__log( self::$data );
		// w__log( self::$fields );

        // positive ##
        return [
			'fields'	=> \willow()->get( '_fields' ), // self::$fields
			'data' 		=> $this->data
		];

    }

    /**
     * Get ACF Fields data
	 * 
	 * @since 	2.1.0
	 * @param	array
	 * @return	mixed
     */
    public function acf_fields( array $args = null ):?array
	{

		if ( ! function_exists( 'get_fields' ) ) {

			w__log( 'e:>ACF Plugin missing' );

			return false;

		}

        // get fields ##
		$array = 
			\get_fields( 
				$args['config']['post']->ID ?? false 
			);
		
		// w__log( $array );

        // sanity ##
        if ( 
            ! $array 
            || ! is_array( $array )
        ) {

			// log ##
			w__log( $args['task'].'~>n:Post has no ACF field data or corrupt data returned');
			// w__log( 'd:>Post has no ACF field data or corrupt data returned');

            return null;

        }

        // w__log( $array );

        return $this->acf_fields = $array;

    }

	/**
	 * Get ACF Field Group from passed $group reference
	 * 
	 * @since 	2.1.0
	 * @param	array
	 * @return	bool
	 */
    public function group_fields( array $args = null ):bool
	{

        // assign variable ##
		$group = $args['task'];

        // try to get fields ##
        $array = willow\plugin\acf::get_field_group( $group );

        // w__log( $array );

        if ( 
            ! $array
            || ! \is_array( $array )
        ) {

			// log ##
			w__log( $args['task'].'~>e:No valid ACF field group returned for Group: "'.$group.'"');
			// w__log( 'd:>:No valid ACF field group returned for Group: "'.$group.'"');

            return false;

        }

        // filter ##
        $array = \willow()->filter->apply([ 
            'parameters'    => [ 'fields' => $array ], // pass ( $fields ) as single array ##
            'filter'        => 'willow/get/group/'.$group, // filter handle ##
            'return'        => $array
        ]); 

        // assign to class properties ##
		// self::$fields = $array; // capture all fields for type and callback lookups ##
		$_args = \willow()->get( '_args' );
		$_args['fields'] = array_merge( $_args['fields'] ?? [], $array );
		\willow()->set( '_args', $_args ); 
		$this->data = $array; // data to return to fields\define ##

        // w__log( $array );

        // return
        return true;

    }

	/**
	 * Skip fields marked to avoid
	 * 
	 * @since 	2.1.0
	 * @param	array
	 * @return	__bool
	 */
    public function skip( array $args = null ):bool
	{

        // sanity ##
        if ( 
            ! $args 
            || ! is_array( $args )
        ) {

			// log ##
			w__log( $args['task'].'~>e:Error in passed $args');
			// w__log( 'e:>Error in passed $args');

            return false;

        }

        if ( 
            isset( $args['skip'] ) 
            && is_array( $args['skip'] )
        ) {

            // w__log( $args['skip'] );
            $this->data = array_diff( $this->data, $args['skip'] );

        }

		return true;

    }

	/**
 	 * Get the fields from the listed ACF group, removing fields returned form acf_fields()
	 * 
	 * @since 	2.1.0
	 * @param	array
	 * @return	__bool
	 */
    public function group( array $args = null ):bool
	{

        // sanity ##
        if ( 
            ! $args 
            || ! is_array( $args )
            || ! $this->data
            || ! is_array( $this->data )
        ) {

			// log ##
			w__log( $args['task'].'~>e:Error in passed $args or $fields');
			// w__log( 'e:>Error in passed $args or $fields');

            return false;

		}
		
		// w__log( $this->acf_fields );

        if ( 
            isset( $args['task'] )
        ) {

            // w__log( 'Removing fields from other groups... BEFORE: '.count( $this->data ) );
            // w__log( $this->data );

            $this->data = array_intersect_key( $this->acf_fields, array_flip( $this->data ) );

            // w__log( 'Removing fields from other groups... AFTER: '.count( $this->data ) );

        }

        // kick back ##
        return true;

    }

}
