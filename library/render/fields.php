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

			// w__log( self::$fields );

			// log ##
			w__log( 'e:>Error in $fields array' );
			w__log( $_args['task'].'~>e:>Error in $fields array' );

			// kick out ##
            return false;

		}
		
		// w__log( $_fields );

        // filter $args now that we have fields data from ACF ##
        $_args = $this->plugin->filter->apply([ 
            'parameters'    => [ 'fields' => $_fields, 'args' => $_args ], // pass ( $fields, $args ) as single array ##
            'filter'        => 'willow/render/fields/prepare/before/args/'.$_args['task'], // filter handle ##
            'return'        => $_args
		]); 
		
		// save ##
		$this->plugin->set( '_args', $_args );

        // filter all fields before processing ##
        $_fields = $this->plugin->filter->apply([ 
            'parameters'    => [ 'fields' => $_fields, 'args' => $_args ], // pass ( $fields, $args ) as single array ##
            'filter'        => 'willow/render/fields/prepare/before/fields/'.$_args['task'], // filter handle ##
            'return'        => $_fields
		]); 

		// w__log( $_fields );
		
		// save ##
		$this->plugin->set( '_fields', $_fields );

		// w__log( $_fields );
		// w__log( 'hash: '.$_args['config']['hash'] );

		// push in new duplicate fields from field_map, required for unique filters on variables ##
		$this->map();

		// load again ##
		// $_args = $this->plugin->get( '_args' );
		$_fields = $this->plugin->get( '_fields' );

        // start loop ##
        foreach ( $_fields as $field => $value ) {

            // check field has a value ##
            if ( 
                ! $value 
                || is_null( $value )
            ) {

				// log ##
				w__log( $_args['task'].'~>n:>Field: "'.$field.'" has no value, check for data issues' );

				w__log( 'Field empty: '.$field );

                continue;

            }

            // filter field before callback ##
            $field = $this->plugin->filter->apply([ 
                'parameters'    => [ 'field' => $field, 'value' => $value, 'args' => $_args, 'fields' => $_fields ], // params
                'filter'        => 'willow/render/fields/prepare/before/callback/'.$_args['task'].'/'.$field, // filter handle ##
                'return'        => $field
            ]); 

            // Callback methods on specified field ##
			// Note - field includes a list of standard callbacks, which can be extended via the filter willow/render/callbacks/get ##
            $value = $this->plugin->render->callback->field( $field, $value );

            // w__log( 'd:>After callback -- field: '.$field .' With Value:' );
            // w__log( $value );

            // filter field before format ##
            $field = $this->plugin->filter->apply([ 
                'parameters'    => [ 'field' => $field, 'value' => $value, 'args' => $_args, 'fields' => $_fields ], // params
                'filter'        => 'willow/render/fields/prepare/before/format/'.$_args['task'].'/'.$field, // filter handle ##
                'return'        => $field
			]); 
			
			// w__log( 'd:>Field value: '.$value );
			// w__log( $value );

            // Format each field value based on type ( int, string, array, WP_Post Object ) ##
            // each item is filtered as looped over -- q/render/field/GROUP/FIELD - ( $args, $fields ) ##
			// results are saved back to the $_fields array in String format ##
			$this->plugin->render->format->field( $field, $value );

		}

		// get data a-fresh, for final filter ##
		$_fields = $this->plugin->get( '_fields' );
		
        // filter all fields ##
        $_fields = $this->plugin->filter->apply([ 
            'parameters'    => [ 'fields' => $_fields, 'args' => $_args ], // pass ( $fields, $args ) as single array ##
            'filter'        => 'willow/render/fields/prepare/after/fields/'.$_args['task'], // filter handle ##
            'return'        => $_fields
		]); 

		// store _fields ##
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

		// w__log( $this->plugin->get( '_scope_map' ) );
		// w__log( 'hash: '.$this->plugin->get( '_args' )['config']['hash'] );
		
		// local vars ##
		$_scope_map = $this->plugin->get( '_scope_map' );
		$_fields = $this->plugin->get( '_fields' );

		if ( 
			! $_scope_map
			|| ! is_array( $_scope_map )
			|| ! $_fields
		){

			w__log( 'e:>_fields OR _scope_map empty' );

			// no mapping required ##
			return false;

		}

		foreach( $_fields as $field => $value ){

			// store
			// $field_matches = [];

			// get first part of field key name  - before first dot ##
			$field_key = explode( '.', $field );

			// w__log( 'field: '.$field_key[0] );

			if( array_key_exists( $field_key[0], $_scope_map ) ){

				// w__log( 'scope map includes: '.$field_key[0] );

				foreach( $_scope_map[ $field_key[0] ] as $scope => $hash ){

					// w__log( 'hash: '.$hash );

					// create new field key value ##
					$new_field_key = $field_key[0].'__'.$hash.str_replace( $field_key[0], '', $field );

					// w__log( 'new_field_key: '.$new_field_key );

					// add field ##
					// self::$fields[$new_field_key] = $value;
					$_fields[$new_field_key] = $value;

					// w__log( $_fields );

				}

			}

		}

		// w__log( $_fields );

		// save fields ##
		$this->plugin->set( '_fields', $_fields );

		// w__log( $field_matches );

	}
	
	/**
	 * Define $fields args for render methods
	 * 
	 * @since 1.0.0
	*/
	public function define( $args = null ){

		// w__log( $args );

		// local vars ##
		$_args = $this->plugin->get( '_args' );
		$_fields = $this->plugin->get( '_fields' );
		// w__log( $_fields );

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

				// w__log( 'NOT on buffer..' );

			} else {

				// collect entire array for array.X.property access ##
				// w__log( $args );

				reset( $args );
				$first_key = key( $args );
				$_fields[$first_key] = $args[$first_key];
				// w__log( $first_key );
				// w__log( $_fields );

			}

		} else {

			// w__log( $args );

			// w__log( 'e:>Error in $args ( empty or not an array ) by "'.core\method::backtrace([ 'level' => 4, 'return' => 'class_function' ]).'"' );

			// return false;
			// $args = [];

			if(
				isset( $_args['config']['default'] )
				&& is_array( $_args['config']['default'] )
			){

				// w__log( 'config->default is defined' );
				// w__log( $_args['config']['default'] );

				// define args as config->default ##
				
				// REMOVED, default value is assigned in markup::prepare();
				// $args = $_args['config']['default'];

				// w__log( $args );

			} else {

				// w__log( 'config->default NOT defined, so ending here.' );

				// nothing cooking ##
				return false;

			}

		}

		// w__log( $args );
		// loop over array - saving key + value to self::$fields ##
		foreach( $args as $key => $value ) {

			// w__log( 'd:>add field key: '.$key );
			// w__log( $value );

			// @TODO ##
			// if ( is_string( $value ) ){

				// $value = mb_convert_encoding( $value, 'UTF-8', 'UTF-8' );
				// $value = htmlentities( $value, ENT_QUOTES, 'UTF-8' ); 

				// $value = render\markup::escape( $value );

				// w__log( 'd:>add field key: '.$key );
				// w__log( 'd:> ESCAPED: '.$value );

			// }

			// escape ##
			// $value = self::escape( $key, $value );

			// add to prop ##
			// self::$fields[$key] = $value;
			$_fields[$key] = $value;

			// strip ##
			// self::strip();

		}

		// w__log( $_fields );

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
			w__log( $this->plugin->get( '_args' )['task'].'~>n:>No field or value passed to method.' );

            return false;

		}
		
		// w__log( 'e:>Adding field: '.$field.' by "'.willow\core\method::backtrace([ 'level' => 2, 'return' => 'function' ]).'"' );
		// w__log( $value );

		// add field to array ##
		// self::$fields[$field] = $value;
		$_fields = $this->plugin->get( '_fields' );
		$_fields[$field] = $value;
		$this->plugin->set( '_fields', $_fields );
		// w__log( $this->plugin->get( '_fields' ) );

		// log ##
		w__log( $this->plugin->get( '_args' )['task'].'~>fields:>"'.$field.'"' );
		// w__log( 'e:>field: "'.$field.'"' );
		// w__log( $value );
		// w__log( $this->plugin->get( '_args' )['task'].'~>fields_added:>"'.$field.'" by "'.core\method::backtrace([ 'level' => 2, 'return' => 'function' ]).'"' );

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
			w__log( $this->plugin->get( '_args' )['task'].'~>n:>No field value passed to method.' );

            return false;

        }

        // remove from array ##
		// unset( self::$fields[$field] );
		$_fields = $this->plugin->get( '_fields' );
		unset( $_fields[$field] );
		$this->plugin->set( '_fields', $_fields );

        // log ##
		w__log( $this->plugin->get( '_args' )['task'].'~>fields_removed:>"'.$field.'" by "'.willow\core\method::backtrace([ 'level' => 2, 'return' => 'function' ]).'"' );

        // positive ##
        return true;

    }


    
    /**
     * Try to get field type from passed key and field name
     * 
     * @return  boolean
     */
    public function get_type( $field = null ){

		// w__log( $this->plugin->get( '_args' ) );

		$type_method = new willow\type\method( $this->plugin );
		$_fields = $this->plugin->get( '_fields' );

		// sanity ##
		if(
			is_null( $field )
		){

			// get caller ##
			$backtrace = willow\core\method::backtrace([ 'level' => 2, 'return' => 'class_function' ]);

			w__log( $this->plugin->get( '_args' )['task'].'~>n:>'.$backtrace.' -> No $field passed' );

			return false;

		}

		// w__log( 'd:>Checking Type of Field: "'.$field.'"' );
		// w__log( $this->plugin->get( '_args' ) );

		// shortcut check for ui\method gather data ##
		if ( 
			isset( $this->plugin->get( '_args' )['config']['type'] ) 
			&& array_key_exists( $this->plugin->get( '_args' )['config']['type'], $type_method->get_allowed() )
		){

			// w__log( 'd:>Shortcut to type passed in args: '.$this->plugin->get( '_args' )['config']['type'] );

			return $this->plugin->get( '_args' )['config']['type'];

		}

		// sanity ##
		if(
			! isset( $_fields ) // fields array is only set in "group" context
		){

			// get caller ##
			$backtrace = willow\core\method::backtrace([ 'level' => 2, 'return' => 'class_function' ]);

			w__log( $this->plugin->get( '_args' )['task'].'~>n:>'.$backtrace.' -> Field: "'.$field.'" $_fields empty' );

			return false;

		}

		// w__log( $_fields[$field] );
		// check if data is structured as an array of array ##
		if ( 
			isset( $_fields )
			&& is_array( $_fields )
			&& willow\render\method::is_array_of_arrays( $_fields[$field] )
		){

			w__log( $this->plugin->get( '_args' )['task'].'~>n:>field: "'.$field.'" is an array of arrays, so set to repeater' );

			return 'repeater';

		}

		// sanity ##
		if(
			! isset( $this->plugin->get( '_args' )['fields'] ) // fields array is only set in "group" context
		){

			// get caller ##
			$backtrace = willow\core\method::backtrace([ 'level' => 2, 'return' => 'class_function' ]);

			w__log( $this->plugin->get( '_args' )['task'].'~>n:>'.$backtrace.' -> Field: "'.$field.'" $args->fields empty' );

			return false;

		}

        if ( 
			$key = willow\core\method::array_search( 'key', 'field_'.$field, $this->plugin->get( '_args' )['fields'] )
        ){

            // w__log( $this->plugin->get( '_args' )['fields'][$key] );

            if ( 
                isset( $this->plugin->get( '_args' )['fields'][$key]['type'] )
            ) {

				// log ##
				w__log( $this->plugin->get( '_args' )['task'].'~>n:>Field: "'.$field.'" is Type: "'.$this->plugin->get( '_args' )['fields'][$key]['type'].'"' );

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

			w__log( 'e:>Error, no $field passed' );

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
			w__log( $_args['task'].'~>n:>'.$backtrace.' -> "$args[fields]" is not defined' );

			return false;

		}

		// w__log( $_args['fields'] );
        if ( 
            ! $key = willow\core\method::array_search( 'key', 'field_'.$field, $_args['fields'] )
        ){

			// log ##
			w__log( $_args['task'].'~>n:>failed to find Field: "'.$field.'" data in $fields' );

            return false;

        }

        // helper::log( $_args['fields'][$key] );

        if ( 
            ! isset( $_args['fields'][$key]['callback'] )
        ) {

			// log ##
			w__log( $_args['task'].'~>n:>Field: "'.$field.'" has no callback defined' );

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
			w__log( $_args['task'].'~>n:>Field: "'.$field.'" has a callback, but it is not correctly formatted - not an array or missing "method" key' );

            return false;

        }

        // ok - we must be good now ##

        // assign to var ##
        $callback = $_args['fields'][$key]['callback'];

		// log ##
		w__log( $_args['task'].'~>n:>Field: "'.$field.'" has callback: "'.$callback['method'].'" sending back to caller' );

        // filter ##
        $callback = $this->plugin->filter->apply([ 
            'parameters'    => [ 'callback' => $callback, 'field' => $field, 'args' => $_args, 'fiekds' => $this->plugin->get('_fields') ], // params ##
            'filter'        => 'q/render/fields/get_callback/'.$_args['task'].'/'.$field, // filter handle ##
            'return'        => $callback
        ]); 

        // return ##
        return $_args['fields'][$key]['callback'];

    }

}
