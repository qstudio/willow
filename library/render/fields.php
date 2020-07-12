<?php

namespace q\willow\render;

// use q\core;
use q\core\helper as h;
use q\view;
use q\get;
use q\willow;
use q\willow\render;

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

        // filter $args now that we have fields data from ACF ##
        self::$args = \q\core\filter::apply([ 
            'parameters'    => [ 'fields' => self::$fields, 'args' => self::$args ], // pass ( $fields, $args ) as single array ##
            'filter'        => 'q/render/fields/prepare/before/args/'.self::$args['task'], // filter handle ##
            'return'        => self::$args
        ]); 

        // filter all fields before processing ##
        self::$fields = \q\core\filter::apply([ 
            'parameters'    => [ 'fields' => self::$fields, 'args' => self::$args ], // pass ( $fields, $args ) as single array ##
            'filter'        => 'q/render/fields/prepare/before/fields/'.self::$args['task'], // filter handle ##
            'return'        => self::$fields
        ]); 

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
            $field = \q\core\filter::apply([ 
                'parameters'    => [ 'field' => $field, 'value' => $value, 'args' => self::$args, 'fields' => self::$fields ], // params
                'filter'        => 'q/render/fields/prepare/before/callback/'.self::$args['task'].'/'.$field, // filter handle ##
                'return'        => $field
            ]); 

            // Callback methods on specified field ##
            // Note - field includes a list of standard callbacks, which can be extended via the filter q/render/callbacks/get ##
            $value = render\callback::field( $field, $value );

            // h::log( 'd:>After callback -- field: '.$field .' With Value:' );
            // h::log( $value );

            // filter field before format ##
            $field = \q\core\filter::apply([ 
                'parameters'    => [ 'field' => $field, 'value' => $value, 'args' => self::$args, 'fields' => self::$fields ], // params
                'filter'        => 'q/render/fields/prepare/before/format/'.self::$args['task'].'/'.$field, // filter handle ##
                'return'        => $field
			]); 
			
			// h::log( 'd:>Field value: '.$value );

            // Format each field value based on type ( int, string, array, WP_Post Object ) ##
            // each item is filtered as looped over -- q/render/field/GROUP/FIELD - ( $args, $fields ) ##
            // results are saved back to the self::$fields array in String format ##
            render\format::field( $field, $value );

        }

        // filter all fields ##
        self::$fields = \q\core\filter::apply([ 
            'parameters'    => [ 'fields' => self::$fields, 'args' => self::$args ], // pass ( $fields, $args ) as single array ##
            'filter'        => 'q/render/fields/prepare/after/fields/'.self::$args['task'], // filter handle ##
            'return'        => self::$fields
        ]); 

    }



	
	/**
	 * Define $fields args for render methods
	 * 
	 * @since 4.0.0
	*/
	public static function define( $args = null ){

		// h::log( $args );

		// sanity ##
		if (
			is_null( $args )
			// || ! is_array( $array )
		){

			h::log( 'e:>Error in passed $args' );

			return false;

		}

		// assign string to key 'value' ##
		if ( is_string( $args ) ){

			h::log( 'e:>Calling fields/define with a string value is __deprectated..' );
			return self::$fields['value'] = $args;

		}

		// h::log( $args );
		// else, loop over array ##
		foreach( $args as $key => $value ) {

			// h::log( 'd:>add field key: '.$key );
			// h::log( $value );

			// add to prop ##
			self::$fields[$key] = $value;

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
		
		$args = [
			'sf.sdfsd' => 'sfsdf' 
		];

        // h::log( 'Adding field: '.$field );

        // add field to array ##
        self::$fields[$field] = $value;

		// log ##
		h::log( self::$args['task'].'~>fields_added:>"'.$field.'" by "'.\q\core\method::backtrace([ 'level' => 2, 'return' => 'function' ]).'"' );

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
		h::log( self::$args['task'].'~>fields_removed:>"'.$field.'" by "'.\q\core\method::backtrace([ 'level' => 2, 'return' => 'function' ]).'"' );

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
			$backtrace = \q\core\method::backtrace([ 'level' => 2, 'return' => 'class_function' ]);

			h::log( self::$args['task'].'~>n:>'.$backtrace.' -> Field: "'.$field.'" $args->fields empty' );

			return false;

		}

        if ( 
			$key = \q\core\method::array_search( 'key', 'field_'.$field, self::$args['fields'] )
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
			$backtrace = \q\core\method::backtrace([ 'level' => 2, 'return' => 'class_function' ]);

			// log ##
			h::log( self::$args['task'].'~>n:>'.$backtrace.' -> "$args[fields]" is not defined' );

			return false;

		}

		// h::log( self::$args['fields'] );
        if ( 
            ! $key = \q\core\method::array_search( 'key', 'field_'.$field, self::$args['fields'] )
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
        $callback = \q\core\filter::apply([ 
            'parameters'    => [ 'callback' => $callback, 'field' => $field, 'args' => self::$args, 'fiekds' => self::$fields ], // params ##
            'filter'        => 'q/render/fields/get_callback/'.self::$args['task'].'/'.$field, // filter handle ##
            'return'        => $callback
        ]); 

        // return ##
        return self::$args['fields'][$key]['callback'];

    }


}
