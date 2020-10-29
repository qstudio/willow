<?php

namespace willow\render;

use willow\core;
use willow\core\helper as h;
use willow;
use willow\parse;
use willow\render;

class fields extends willow\render {


    /**
     * Work passed field data into rendering format
     */
    public static function prepare(){

        // check we have fields to loop over ##
        if ( 
            ! self::$fields
            || ! is_array( self::$fields ) 
        ) {

			/// log ##
			h::log( self::$args['task'].'~>e:>Error in $fields array' );

            return false;

		}
		
		// h::log( self::$fields );

        // filter $args now that we have fields data from ACF ##
        self::$args = core\filter::apply([ 
            'parameters'    => [ 'fields' => self::$fields, 'args' => self::$args ], // pass ( $fields, $args ) as single array ##
            'filter'        => 'willow/render/fields/prepare/before/args/'.self::$args['task'], // filter handle ##
            'return'        => self::$args
        ]); 

        // filter all fields before processing ##
        self::$fields = core\filter::apply([ 
            'parameters'    => [ 'fields' => self::$fields, 'args' => self::$args ], // pass ( $fields, $args ) as single array ##
            'filter'        => 'willow/render/fields/prepare/before/fields/'.self::$args['task'], // filter handle ##
            'return'        => self::$fields
        ]); 

		// h::log( self::$fields );
		// h::log( self::$fields_map );
		// h::log( 'hash: '.self::$args['config']['hash'] );

        // start loop ##
        foreach ( self::$fields as $field => $value ) {

            // check field has a value ##
            if ( 
                ! $value 
                || is_null( $value )
            ) {

				// log ##
				h::log( self::$args['task'].'~>n:>Field: "'.$field.'" has no value, check for data issues' );

				// h::log( 'Field empty: '.$field );

                continue;

            }

            // filter field before callback ##
            $field = core\filter::apply([ 
                'parameters'    => [ 'field' => $field, 'value' => $value, 'args' => self::$args, 'fields' => self::$fields ], // params
                'filter'        => 'willow/render/fields/prepare/before/callback/'.self::$args['task'].'/'.$field, // filter handle ##
                'return'        => $field
            ]); 

            // Callback methods on specified field ##
            // Note - field includes a list of standard callbacks, which can be extended via the filter willow/render/callbacks/get ##
            $value = render\callback::field( $field, $value );

            // h::log( 'd:>After callback -- field: '.$field .' With Value:' );
            // h::log( $value );

            // filter field before format ##
            $field = core\filter::apply([ 
                'parameters'    => [ 'field' => $field, 'value' => $value, 'args' => self::$args, 'fields' => self::$fields ], // params
                'filter'        => 'willow/render/fields/prepare/before/format/'.self::$args['task'].'/'.$field, // filter handle ##
                'return'        => $field
			]); 
			
			// h::log( 'd:>Field value: '.$value );

            // Format each field value based on type ( int, string, array, WP_Post Object ) ##
            // each item is filtered as looped over -- q/render/field/GROUP/FIELD - ( $args, $fields ) ##
            // results are saved back to the self::$fields array in String format ##
			render\format::field( $field, $value );
			
		}
		
		// push in new duplicate fields from field_map, required for unique filters on variables ##
		// self::map();

        // filter all fields ##
        self::$fields = core\filter::apply([ 
            'parameters'    => [ 'fields' => self::$fields, 'args' => self::$args ], // pass ( $fields, $args ) as single array ##
            'filter'        => 'willow/render/fields/prepare/after/fields/'.self::$args['task'], // filter handle ##
            'return'        => self::$fields
		]); 

    }



	/**
	 * dupliate fields, for unique filters 
	 * these fields should then run in the next loop, adding required markup ##
	 * 
	 * @since 	1.2.0
	 * @return 	void
	 * 
	*/
	public static function map(){

		// h::log( self::$fields_map );
		// h::log( 'hash: '.self::$args['config']['hash'] );

		// start loop ##
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

				if( ! is_array( self::$fields_map[ self::$args['config']['hash'] ][ $find_field ] ) ){

					// h::log( $find_field.' in fields_maps is not an array, so continuing...' );

				} else {

					foreach( self::$fields_map[ self::$args['config']['hash'] ][ $find_field ] as $map_key => $map_value ){

						// h::log( 'map_value: '.$map_value );
						// h::log( '$find_field: '.$find_field );
						// h::log( self::$args );

						// prepare new key ##
						$new_key = $field.str_replace( $find_field, '', $map_value );

						// h::log( 'New Key: '.$new_key );

						// assign existing key value to new key ##
						self::$fields[$new_key] = $value;

					}

				}

			}

		}

		// h::log( self::$fields );
		
	}


	
	/**
	 * Define $fields args for render methods
	 * 
	 * @since 1.0.0
	*/
	public static function define( $args = null ){

		// h::log( $args );

		// sanity ##
		if (
			! is_null( $args )
			&& is_array( $args )
			&& array_filter( $args ) // not an empty array ##
			&& isset( self::$args )
			&& is_array( self::$args )
			&& isset( self::$args['context'] )
		){

			// we cannot set default fields on buffer runs ##
			if( 'buffer' == self::$args['context'] ){

				// h::log( 'NOT on buffer..' );

			} else {

				// collect entire array for array.X.property access ##
				// h::log( $args );

				reset( $args );
				$first_key = key( $args );
				self::$fields[$first_key] = $args[$first_key];
				// h::log( $first_key );
				// h::log( self::$fields );

			}

		} else {

			// h::log( $args );

			h::log( 'e:>Error in $args ( empty or not an array ) by "'.core\method::backtrace([ 'level' => 4, 'return' => 'class_function' ]).'"' );

			// return false;
			// $args = [];

			if(
				isset( self::$args['config']['default'] )
				&& is_array( self::$args['config']['default'] )
			){

				// h::log( 'config->default is defined' );
				// h::log( self::$args['config']['default'] );

				// define args as config->default ##
				
				// REMOVED, default value is assigned in markup::prepare();
				// $args = self::$args['config']['default'];

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
			self::$fields[$key] = $value;

			// strip ##
			// self::strip();

		}

		return true;

	}	

    
    /**
     * Add $field from self:$fields array
     * 
     */
    public static function set( string $field = null, string $value = null ) {

        // sanity ##
        if ( 
            is_null( $field )
            || is_null( $value ) 
        ) {

			// log ##
			h::log( self::$args['task'].'~>n:>No field or value passed to method.' );

            return false;

		}
		
		// h::log( 'e:>Adding field: '.$field.' by "'.core\method::backtrace([ 'level' => 2, 'return' => 'function' ]).'"' );
		// h::log( $value );

        // add field to array ##
        self::$fields[$field] = $value;

		// log ##
		h::log( self::$args['task'].'~>fields_added:>"'.$field.'" by "'.core\method::backtrace([ 'level' => 2, 'return' => 'function' ]).'"' );

        // positive ##
        return true;

    }



    /**
     * Remove $field from self:$fields array
     * 
     */
    public static function remove( string $field = null ) {

        // sanity ##
        if ( is_null( $field ) ) {

			// log ##
			h::log( self::$args['task'].'~>n:>No field value passed to method.' );

            return false;

        }

        // remove from array ##
        unset( self::$fields[$field] );

        // log ##
		h::log( self::$args['task'].'~>fields_removed:>"'.$field.'" by "'.core\method::backtrace([ 'level' => 2, 'return' => 'function' ]).'"' );

        // positive ##
        return true;

    }


    
    /**
     * Try to get field type from passed key and field name
     * 
     * @return  boolean
     */
    public static function get_type( $field = null ){

		// h::log( self::$args );

		// sanity ##
		if(
			is_null( $field )
		){

			// get caller ##
			$backtrace = core\method::backtrace([ 'level' => 2, 'return' => 'class_function' ]);

			h::log( self::$args['task'].'~>n:>'.$backtrace.' -> No $field passed' );

			return false;

		}

		// h::log( 'd:>Checking Type of Field: "'.$field.'"' );
		// h::log( self::$args );

		// shortcut check for ui\method gather data ##
		if ( 
			isset( self::$args['config']['type'] ) 
			&& array_key_exists( self::$args['config']['type'], render\type::get_allowed() )
		){

			// h::log( 'd:>Shortcut to type passed in args: '.self::$args['config']['type'] );

			return self::$args['config']['type'];

		}

		// sanity ##
		if(
			! isset( self::$fields ) // fields array is only set in "group" context
		){

			// get caller ##
			$backtrace = core\method::backtrace([ 'level' => 2, 'return' => 'class_function' ]);

			h::log( self::$args['task'].'~>n:>'.$backtrace.' -> Field: "'.$field.'" $fields empty' );

			return false;

		}

		// h::log( self::$fields[$field] );
		// check if data is structured as an array of array ##
		if ( 
			isset( self::$fields[$field] )
			&& render\method::is_array_of_arrays( self::$fields[$field] )
		){

			h::log( self::$args['task'].'~>n:>field: "'.$field.'" is an array of arrays, so set to repeater' );

			return 'repeater';

		}

		// sanity ##
		if(
			! isset( self::$args['fields'] ) // fields array is only set in "group" context
		){

			// get caller ##
			$backtrace = core\method::backtrace([ 'level' => 2, 'return' => 'class_function' ]);

			h::log( self::$args['task'].'~>n:>'.$backtrace.' -> Field: "'.$field.'" $args->fields empty' );

			return false;

		}

        if ( 
			$key = core\method::array_search( 'key', 'field_'.$field, self::$args['fields'] )
        ){

            // h::log( self::$args['fields'][$key] );

            if ( 
                isset( self::$args['fields'][$key]['type'] )
            ) {

				// log ##
				h::log( self::$args['task'].'~>n:>Field: "'.$field.'" is Type: "'.self::$args['fields'][$key]['type'].'"' );

                return self::$args['fields'][$key]['type'];

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
    public static function get_callback( $field = null ){

		// sanity ##
		if (
			is_null( $field )
		){

			h::log( 'e:>Error, no $field passed' );

			return false;

		}

		// helper::log( 'Checking Type of Field: "'.$field.'"' );
		
		// sanity ##
		if ( ! isset( self::$args['fields'] ) ) {

			// get caller ##
			$backtrace = core\method::backtrace([ 'level' => 2, 'return' => 'class_function' ]);

			// log ##
			h::log( self::$args['task'].'~>n:>'.$backtrace.' -> "$args[fields]" is not defined' );

			return false;

		}

		// h::log( self::$args['fields'] );
        if ( 
            ! $key = core\method::array_search( 'key', 'field_'.$field, self::$args['fields'] )
        ){

			// log ##
			h::log( self::$args['task'].'~>n:>failed to find Field: "'.$field.'" data in $fields' );

            return false;

        }

        // helper::log( self::$args['fields'][$key] );

        if ( 
            ! isset( self::$args['fields'][$key]['callback'] )
        ) {

			// log ##
			h::log( self::$args['task'].'~>n:>Field: "'.$field.'" has no callback defined' );

            return false;

        }

        // ok - we have a callback, let's check it's formatted correctly ##
        // we need a "method" ##
        // "args" are optional.. I guess, but surely we'd send the field value to the passed method.. or perhaps not ##
        if ( 
            ! is_array( self::$args['fields'][$key]['callback'] )
            || ! isset( self::$args['fields'][$key]['callback']['method'] )
        ) {

			// log ##
			h::log( self::$args['task'].'~>n:>Field: "'.$field.'" has a callback, but it is not correctly formatted - not an array or missing "method" key' );

            return false;

        }

        // ok - we must be good now ##

        // assign to var ##
        $callback = self::$args['fields'][$key]['callback'];

		// log ##
		h::log( self::$args['task'].'~>n:>Field: "'.$field.'" has callback: "'.$callback['method'].'" sending back to caller' );

        // filter ##
        $callback = core\filter::apply([ 
            'parameters'    => [ 'callback' => $callback, 'field' => $field, 'args' => self::$args, 'fiekds' => self::$fields ], // params ##
            'filter'        => 'q/render/fields/get_callback/'.self::$args['task'].'/'.$field, // filter handle ##
            'return'        => $callback
        ]); 

        // return ##
        return self::$args['fields'][$key]['callback'];

    }


}
