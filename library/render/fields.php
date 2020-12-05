<?php

namespace Q\willow\render;

use Q\willow\core\helper as h;
use Q\willow;

class fields {

	private 
		$plugin = false
	;

	/**
     */
    public function __construct( \Q\willow\plugin $plugin ){

		// grab passed plugin object ## 
		$this->plugin = $plugin;

	}

    /**
     * Work passed field data into rendering format
     */
    public function prepare(){

		// temp vars, updated when changed internally ##
		$_args = $this->plugin->get( '_args' );
		$_fields = $this->plugin->get( '_fields' );

        // check we have fields to loop over ##
        if ( 
            ! $_fields
            || ! is_array( $_fields ) 
        ) {

			// h::log( self::$fields );

			// log ##
			// @todo // log-->h::log( $_args['task'].'~>e:>Error in $fields array' );

			// kick out ##
            return false;

		}
		
		// h::log( self::$fields );

        // filter $args now that we have fields data from ACF ##
        $_args = $this->plugin->get('filter')->apply([ 
            'parameters'    => [ 'fields' => $_fields, 'args' => $_args ], // pass ( $fields, $args ) as single array ##
            'filter'        => 'willow/render/fields/prepare/before/args/'.$_args['task'], // filter handle ##
            'return'        => $_args
		]); 
		
		// save ##
		$this->plugin->set( '_args', $_args );

        // filter all fields before processing ##
        $_fields = $this->plugin->get('filter')->apply([ 
            'parameters'    => [ 'fields' => $_fields, 'args' => $_args ], // pass ( $fields, $args ) as single array ##
            'filter'        => 'willow/render/fields/prepare/before/fields/'.$_args['task'], // filter handle ##
            'return'        => $_fields
		]); 
		
		// save ##
		$this->plugin->set( '_fields', $_fields );

		// load again ##
		$_args = $this->plugin->get( '_args' );
		$_fields = $this->plugin->get( '_fields' );

		// h::log( $_fields );
		// h::log( self::$fields_map );
		// h::log( 'hash: '.$_args['config']['hash'] );

		// push in new duplicate fields from field_map, required for unique filters on variables ##
		$this->map();

        // start loop ##
        foreach ( $_fields as $field => $value ) {

            // check field has a value ##
            if ( 
                ! $value 
                || is_null( $value )
            ) {

				// log ##
				// @todo // log-->h::log( $_args['task'].'~>n:>Field: "'.$field.'" has no value, check for data issues' );

				// h::log( 'Field empty: '.$field );

                continue;

            }

            // filter field before callback ##
            $field = $this->plugin->get('filter')->apply([ 
                'parameters'    => [ 'field' => $field, 'value' => $value, 'args' => $_args, 'fields' => $_fields ], // params
                'filter'        => 'willow/render/fields/prepare/before/callback/'.$_args['task'].'/'.$field, // filter handle ##
                'return'        => $field
            ]); 

            // Callback methods on specified field ##
			// Note - field includes a list of standard callbacks, which can be extended via the filter willow/render/callbacks/get ##
			$render_callback = new willow\render\callback( $this->plugin );
            $value = $render_callback->field( $field, $value );

            // h::log( 'd:>After callback -- field: '.$field .' With Value:' );
            // h::log( $value );

            // filter field before format ##
            $field = $this->plugin->get('filter')->apply([ 
                'parameters'    => [ 'field' => $field, 'value' => $value, 'args' => $_args, 'fields' => $_fields ], // params
                'filter'        => 'willow/render/fields/prepare/before/format/'.$_args['task'].'/'.$field, // filter handle ##
                'return'        => $field
			]); 
			
			// h::log( 'd:>Field value: '.$value );
			// h::log( $value );

            // Format each field value based on type ( int, string, array, WP_Post Object ) ##
            // each item is filtered as looped over -- q/render/field/GROUP/FIELD - ( $args, $fields ) ##
			// results are saved back to the $_fields array in String format ##
			$render_format = new willow\render\format( $this->plugin );
			$render_format->field( $field, $value );

		}
		
        // filter all fields ##
        $_fields = $this->plugin->get('filter')->apply([ 
            'parameters'    => [ 'fields' => $_fields, 'args' => $_args ], // pass ( $fields, $args ) as single array ##
            'filter'        => 'willow/render/fields/prepare/after/fields/'.$_args['task'], // filter handle ##
            'return'        => $_fields
		]); 

		$this->plugin->set( '_fields', $_fields );

    }

	/**
	 * dupliate fields, for unique filters 
	 * these fields should then run in the next loop, adding required markup ##
	 * 
	 * @since 	1.2.0
	 * @return 	void
	 * 
	*/
	public function map(){

		// h::log( self::$scope_map );
		// h::log( 'hash: '.$this->plugin->get( '_args' )['config']['hash'] );
		
		// local vars ##
		$_scope_map = $this->plugin->get( '_scope_map' );
		$_fields = $this->plugin->get( '_fields' );

		if ( 
			! $_scope_map
			|| ! is_array( $_scope_map )
		){

			// no mapping required ##
			return false;

		}

		foreach( $_fields as $field => $value ){

			// store
			$field_matches = [];

			// get first part of field key name  - before first dot ##
			$field_key = explode( '.', $field );

			// h::log( 'field: '.$field_key[0] );

			if( array_key_exists( $field_key[0], self::$scope_map ) ){

				// h::log( 'scope map includes: '.$field_key[0] );

				foreach( $_scope_map[ $field_key[0] ] as $scope => $hash ){

					// h::log( 'hash: '.$hash );

					// create new field key value ##
					$new_field_key = $field_key[0].'__'.$hash.str_replace( $field_key[0], '', $field );

					// h::log( 'new_field_key: '.$new_field_key );

					// add field ##
					// self::$fields[$new_field_key] = $value;
					$_fields[$new_field_key] = $value;

				}

			}

		}

		// save fields ##
		$this->plugin->set( '_fields', $_fields );

		// h::log( $field_matches );

		/*
		// start loop -- this was first patch for field data.. perhaps we'll NOT need it ##
		foreach ( self::$fields as $field => $value ) {

			if( false !== strpos( $field, '.' ) ) {
			
				$field_array = explode( '.', $field );
				$find_field = end( $field_array );
			
			} else {

				$find_field = $field ;

			}
			
			// h::log( 'Search for: '.$field.' in fields_map' );

			if( 
				self::$fields_map
				&& is_array( self::$fields_map )
				&& core\method::array_key_exists( self::$fields_map, $find_field )
			){

				// h::log( 'Found: '.$find_field.' in fields_map' );

				if( ! is_array( self::$fields_map[ $this->plugin->get( '_args' )['config']['hash'] ][ $find_field ] ) ){

					// h::log( $find_field.' in fields_maps is not an array, so continuing...' );

				} else {

					foreach( self::$fields_map[ $this->plugin->get( '_args' )['config']['hash'] ][ $find_field ] as $map_key => $map_value ){

						// h::log( 'map_value: '.$map_value );
						// h::log( '$find_field: '.$find_field );
						// h::log( $this->plugin->get( '_args' ) );

						// prepare new key ##
						$new_key = $field.str_replace( $find_field, '', $map_value );

						// h::log( 'New Key: '.$new_key );

						// assign existing key value to new key ##
						self::$fields[$new_key] = $value;

					}

				}

			}

		}
		*/

		// h::log( self::$fields );
		
	}
	
	/**
	 * Define $fields args for render methods
	 * 
	 * @since 1.0.0
	*/
	public function define( $args = null ){

		// h::log( $args );

		// local vars ##
		$_args = $this->plugin->get( '_args' );
		$_fields = $this->plugin->get( '_fields' );

		// sanity ##
		if (
			! is_null( $args )
			&& is_array( $args )
			&& array_filter( $args ) // not an empty array ##
			&& isset( $_args )
			&& is_array( $_args )
			&& isset( $_args['context'] )
		){

			// we cannot set default fields on buffer runs ##
			if( 'primary' == $_args['context'] ){

				// h::log( 'NOT on buffer..' );

			} else {

				// collect entire array for array.X.property access ##
				// h::log( $args );

				reset( $args );
				$first_key = key( $args );
				$_fields[$first_key] = $args[$first_key];
				// h::log( $first_key );
				// h::log( self::$fields );

			}

		} else {

			// h::log( $args );

			// h::log( 'e:>Error in $args ( empty or not an array ) by "'.core\method::backtrace([ 'level' => 4, 'return' => 'class_function' ]).'"' );

			// return false;
			// $args = [];

			if(
				isset( $_args['config']['default'] )
				&& is_array( $_args['config']['default'] )
			){

				// h::log( 'config->default is defined' );
				// h::log( $_args['config']['default'] );

				// define args as config->default ##
				
				// REMOVED, default value is assigned in markup::prepare();
				// $args = $_args['config']['default'];

				// h::log( $args );

			} else {

				// h::log( 'config->default NOT defined, so ending here.' );

				// nothing cooking ##
				return false;

			}

		}

		// h::log( $args );
		// loop over array - saving key + value to self::$fields ##
		foreach( $args as $key => $value ) {

			// h::log( 'd:>add field key: '.$key );
			// h::log( $value );

			// @TODO ##
			// if ( is_string( $value ) ){

				// $value = mb_convert_encoding( $value, 'UTF-8', 'UTF-8' );
				// $value = htmlentities( $value, ENT_QUOTES, 'UTF-8' ); 

				// $value = render\markup::escape( $value );

				// h::log( 'd:>add field key: '.$key );
				// h::log( 'd:> ESCAPED: '.$value );

			// }

			// escape ##
			// $value = self::escape( $key, $value );

			// add to prop ##
			// self::$fields[$key] = $value;
			$_fields[$key] = $value;

			// strip ##
			// self::strip();

		}

		// save $_fields ##
		$this->plugin->set( '_fields', $_fields );

		// done ##
		return true;

	}	

    
    /**
     * Add $field from self:$fields array
     * 
     */
    public function set( string $field = null, string $value = null ) {

        // sanity ##
        if ( 
            is_null( $field )
            || is_null( $value ) 
        ) {

			// log ##
			h::log( $this->plugin->get( '_args' )['task'].'~>n:>No field or value passed to method.' );

            return false;

		}
		
		// h::log( 'e:>Adding field: '.$field.' by "'.core\method::backtrace([ 'level' => 2, 'return' => 'function' ]).'"' );
		// h::log( $value );

		// add field to array ##
		// self::$fields[$field] = $value;
		$_fields = $this->plugin->get( '_fields' );
		$_fields[$field] = $value;
		$this->plugin->set( '_fields', $_fields );

		// log ##
		// @todo // log-->h::log( $this->plugin->get( '_args' )['task'].'~>fields:>"'.$field.'"' );
		// h::log( $this->plugin->get( '_args' )['task'].'~>fields_added:>"'.$field.'" by "'.core\method::backtrace([ 'level' => 2, 'return' => 'function' ]).'"' );

        // positive ##
        return true;

    }



    /**
     * Remove $field from self:$fields array
     * 
     */
    public function remove( string $field = null ) {

        // sanity ##
        if ( is_null( $field ) ) {

			// log ##
			h::log( $this->plugin->get( '_args' )['task'].'~>n:>No field value passed to method.' );

            return false;

        }

        // remove from array ##
		// unset( self::$fields[$field] );
		$_fields = $this->plugin->get( '_fields' );
		unset( $_fields[$field] );
		$this->plugin->set( '_fields', $_fields );

        // log ##
		// @todo // log-->h::log( $this->plugin->get( '_args' )['task'].'~>fields_removed:>"'.$field.'" by "'.willow\core\method::backtrace([ 'level' => 2, 'return' => 'function' ]).'"' );

        // positive ##
        return true;

    }


    
    /**
     * Try to get field type from passed key and field name
     * 
     * @return  boolean
     */
    public function get_type( $field = null ){

		// h::log( $this->plugin->get( '_args' ) );

		$type_method = new willow\type\method( $this->plugin );
		$_fields = $this->plugin->get( '_fields' );

		// sanity ##
		if(
			is_null( $field )
		){

			// get caller ##
			$backtrace = willow\core\method::backtrace([ 'level' => 2, 'return' => 'class_function' ]);

			h::log( $this->plugin->get( '_args' )['task'].'~>n:>'.$backtrace.' -> No $field passed' );

			return false;

		}

		// h::log( 'd:>Checking Type of Field: "'.$field.'"' );
		// h::log( $this->plugin->get( '_args' ) );

		// shortcut check for ui\method gather data ##
		if ( 
			isset( $this->plugin->get( '_args' )['config']['type'] ) 
			&& array_key_exists( $this->plugin->get( '_args' )['config']['type'], $type_method->get_allowed() )
		){

			// h::log( 'd:>Shortcut to type passed in args: '.$this->plugin->get( '_args' )['config']['type'] );

			return $this->plugin->get( '_args' )['config']['type'];

		}

		// sanity ##
		if(
			! isset( $_fields ) // fields array is only set in "group" context
		){

			// get caller ##
			$backtrace = willow\core\method::backtrace([ 'level' => 2, 'return' => 'class_function' ]);

			h::log( $this->plugin->get( '_args' )['task'].'~>n:>'.$backtrace.' -> Field: "'.$field.'" $_fields empty' );

			return false;

		}

		// h::log( $_fields[$field] );
		// check if data is structured as an array of array ##
		if ( 
			isset( $_fields )
			&& willow\render\method::is_array_of_arrays( $_fields[$field] )
		){

			h::log( $this->plugin->get( '_args' )['task'].'~>n:>field: "'.$field.'" is an array of arrays, so set to repeater' );

			return 'repeater';

		}

		// sanity ##
		if(
			! isset( $this->plugin->get( '_args' )['fields'] ) // fields array is only set in "group" context
		){

			// get caller ##
			$backtrace = willow\core\method::backtrace([ 'level' => 2, 'return' => 'class_function' ]);

			// @todo // log-->h::log( $this->plugin->get( '_args' )['task'].'~>n:>'.$backtrace.' -> Field: "'.$field.'" $args->fields empty' );

			return false;

		}

        if ( 
			$key = willow\core\method::array_search( 'key', 'field_'.$field, $this->plugin->get( '_args' )['fields'] )
        ){

            // h::log( $this->plugin->get( '_args' )['fields'][$key] );

            if ( 
                isset( $this->plugin->get( '_args' )['fields'][$key]['type'] )
            ) {

				// log ##
				h::log( $this->plugin->get( '_args' )['task'].'~>n:>Field: "'.$field.'" is Type: "'.$this->plugin->get( '_args' )['fields'][$key]['type'].'"' );

                return $this->plugin->get( '_args' )['fields'][$key]['type'];

            }

        }
        
        // kick it back ##
        return false;

	}

    /**
     * Get callbacks registered for $field
     * 
     * @return  boolean
     */
    public function get_callback( $field = null ){

		// sanity ##
		if (
			is_null( $field )
		){

			h::log( 'e:>Error, no $field passed' );

			return false;

		}

		// local var ##
		$_args = $this->plugin->get( '_args' );

		// helper::log( 'Checking Type of Field: "'.$field.'"' );
		
		// sanity ##
		if ( ! isset( $_args['fields'] ) ) {

			// get caller ##
			$backtrace = willow\core\method::backtrace([ 'level' => 2, 'return' => 'class_function' ]);

			// log ##
			// @todo // log-->h::log( $_args['task'].'~>n:>'.$backtrace.' -> "$args[fields]" is not defined' );

			return false;

		}

		// h::log( $_args['fields'] );
        if ( 
            ! $key = willow\core\method::array_search( 'key', 'field_'.$field, $_args['fields'] )
        ){

			// log ##
			h::log( $_args['task'].'~>n:>failed to find Field: "'.$field.'" data in $fields' );

            return false;

        }

        // helper::log( $_args['fields'][$key] );

        if ( 
            ! isset( $_args['fields'][$key]['callback'] )
        ) {

			// log ##
			h::log( $_args['task'].'~>n:>Field: "'.$field.'" has no callback defined' );

            return false;

        }

        // ok - we have a callback, let's check it's formatted correctly ##
        // we need a "method" ##
        // "args" are optional.. I guess, but surely we'd send the field value to the passed method.. or perhaps not ##
        if ( 
            ! is_array( $_args['fields'][$key]['callback'] )
            || ! isset( $_args['fields'][$key]['callback']['method'] )
        ) {

			// log ##
			h::log( $_args['task'].'~>n:>Field: "'.$field.'" has a callback, but it is not correctly formatted - not an array or missing "method" key' );

            return false;

        }

        // ok - we must be good now ##

        // assign to var ##
        $callback = $_args['fields'][$key]['callback'];

		// log ##
		h::log( $_args['task'].'~>n:>Field: "'.$field.'" has callback: "'.$callback['method'].'" sending back to caller' );

        // filter ##
        $callback = $this->plugin->get('filter')->apply([ 
            'parameters'    => [ 'callback' => $callback, 'field' => $field, 'args' => $_args, 'fiekds' => $this->plugin->get('_fields') ], // params ##
            'filter'        => 'q/render/fields/get_callback/'.$_args['task'].'/'.$field, // filter handle ##
            'return'        => $callback
        ]); 

        // return ##
        return $_args['fields'][$key]['callback'];

    }

}
