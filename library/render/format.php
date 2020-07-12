<?php

namespace q\willow\render;

use q\core;
use q\core\helper as h;
use q\view;
use q\get;
use q\willow;
use q\willow\render;

class format extends willow\render {

    /**
     * Check allowed formats based on passed $value, format and return a string ready for markup  
     * 
     * @return      String
     */
    public static function field( String $field = null, $value = null ) {
		
		// h::log( 'd:>Field: '.$field );
		// h::log( $value );
		
		// sanity ##
        if ( is_null( $field ) ) {

			// log ##
			h::log( self::$args['task'].'~>e:>No field value passed to method.');
			// h::log( 'd:>Field value: '.$value );

            return false;

        }

        // sanity ##
        if ( is_null( $value ) ) {

			// log ##
			h::log( self::$args['task'].'~>e:>No value passed to method.');
			// h::log( 'd:>Field value: '.$value );

            return false;

        }

        // Check if there are any allowed formats ##
        // Also runs filters to add custom formats ##
        $formats = self::get_allowed();

        if ( 
            ! $formats
            || ! \is_array( $formats ) 
        ) {

			// log ##
			h::log( self::$args['task'].'~>e:>No formats allowed in plugin or array corrupt.');

            return false;

        }

        // Now check the format of $value - Array requires repeat check on each row ##
        $format = self::get( $value, $field );

        // now try to format value ##
		$return = self::apply( $value, $field, $format );
		
        // self::$fields should all be String values by now, ready for markup ##
        return $return;

	}
	

	
    /**
     * Get format of $field $value from defined list of allowed formats ##
     * 
     */
    public static function get( $value = null, $field = null )
    {

        // sanity ##
        if ( 
            is_null( $value )
            || is_null( $field )
        ) {

			// log ##
			h::log( self::$args['task'].'~>e:>Error in parameters passed to check_format');

            return false;

        }

        // get formats ##
        $formats = self::get_allowed();
        // h::log( $formats );

        // tracker, if we find a match ##
        $tracker = false;

        // assign default in case we don't find a matching type ##
        // this is alterable via a filter ##
        $return = \apply_filters( 'q/render/format/default', 'format_text' ); 

        // h::log( 'Default method is: '.$return );

        // loop over formats and search for a match ##
        foreach ( $formats as $format => $format_value ){

            // h::log( 'Checking type: '.$format_value['type'] );

            if ( ! function_exists( $format_value['type'] ) ) {

				// log ##
				h::log( self::$args['task'].'~>n:>Function not found: "'.$format_value['type'].'"');

                continue;

            }

            // h::log( 'function exists: '.$format_value['type'] );

            // boolean check ## is_TYPE === true
            if ( 
                TRUE === call_user_func_array( $format_value['type'], array( $value ) ) 
            ) {

                // log ##
                // h::log( 'Field value: '.$field.' is Type: '.$format_value['type'].' Format with: '.$format_value['method'] );

                // update tracker ##
                $tracker = true;

                // field type assigned ##
                $return = $format_value['method'];

            }

        }

        // note use of default type if no match found ##
        if ( false === $tracker ) {

			// log ##
			h::log( self::$args['task'].'~>n:>No valid value type found for field: "'.$field.'" so assigned: "'.$return.'"');

        }

        // final filter on field format type ##
        $return = \apply_filters( 'q/render/format/get/'.self::$args['task'].'/'.$field, $return );

        // kick back ##
        return $return;

    }




    /**
     * Allow text field to be filtered ##
     * 
     */
    public static function apply( $value = null, String $field = null, String $format = null )
    {

        // sanity ##
        if ( 
            is_null( $value )
            || is_null( $field )
            || is_null( $format )
        ) {

			// log ##
			h::log( self::$args['task'].'~>e:>Error in parameters passed to "apply", $value returned empty and field removed from $fields');

            // this item needs to be removed from self::$fields
            render\fields::remove( $field );

             // we do not return the $value either ##
            return false;

        }

        // h::log( 'Checking Format for - Field: '.$field.' with method: '.$format );

        // we can now distribute the $value to the relevant format method ##
        if (
            ! method_exists( __CLASS__, $format )
            || ! is_callable( array( __CLASS__, $format ) )
        ){

			// log ##
			h::log( self::$args['task'].'~>e:>handler wrong - class: "'.__CLASS__.'" / method: "'.$format.'"');

            // this item needs to be removed from self::$fields
            render\fields::remove( $field );

            // we do not return the $value either ##
            return false; 

        }

        // call class method and pass arguments ##
        $value = call_user_func_array (
            array( __CLASS__, $format )
            ,   array( $value, $field )
        );

        if ( ! $value ) {

            // h::log( 'Handler method returned bad OR empty data for Field: '.$field );

            // this item needs to be removed from self::$fields
			// self::remove_field( $field, 'Removed by "apply" due to bad or empty data' );
			
			// h::log( 'Field value bad: '.$field );

            return false; // we do not return the $value either ##

        }

        // test returned data ##
		// h::log( self::$fields );
		// h::log( 'Field value now: '.$value );

        // fields are filtered and saved by each type handler, as new fields might be added or removed internally ##

        // kick back ##
        return true;

    }




    /**
     * Format text - allow for external filtering ##
     * 
     */
    public static function format_text( $value = null, $field = null )
    {

        // h::log( $value );

        return \apply_filters( 'q/render/format/text/'.self::$args['task'].'/'.$field, $value );

    }


    /**
     * Allow integer field to be filtered ##
     * 
     */
    public static function format_integer( $value = null, $field = null )
    {

        return \apply_filters( 'q/render/format/integer/'.self::$args['task'].'/'.$field, $value );

    }



    /**
     * Format Array values
     * These need to be looped over and each value passed back into the format() process
     * 
     * Array data "MIGHT" come from a repeater -- or from another UI method which has gather data ##
     * which has one single {{ placeholder }} and markup in a property with a name matching key to the field name
     * we need to update the template based on number of array items and defined markup with numbered values ##
     * 
     */
    public static function format_array( $value = null, $field = null )
    {

        // allow filtering early ##
        $value = \apply_filters( 'q/render/format/array/'.self::$args['task'].'/'.$field, $value );

        // array of arrays containing named indexes ( not WP_Post Objects ) needs to be be marked up as a block, like an Object ##

        // add check to see if array is a collection of array - as exported by repeater fields ##
        if ( 'repeater' == render\fields::get_type( $field ) ) {

            // h::log( 'd:>Array is a repeater' );

            self::format_array_repeater( $value, $field );

        } else {

			// h::log( 'd:>Array is an Array..' );
			// h::log( $value );

            // check how many items are in array and format ##
            $count = 0;

            // we need to loop over the array and check what each the value of each key using self::format()
            // Formats that are not registered in self::$formats will be removed ## 
            foreach( $value as $key ) {

                // h::log( $key );

                // create a new, named and numbered field based on field_COUNT -- empty value ##
				$key_field = $field.'.'.$count;
				/*
				WAS
				$key_field = $field.'_'.$count;
				*/
                render\fields::set( $key_field, '' );

                // Format each field value based on type ( int, string, array, WP_Post Object ) ##
                // each item is filtered as looped over -- q/render/format/GROUP/FIELD - ( $args, $fields ) ##
                // results are saved back to the self::$fields array in String format ##
                if ( self::field( $key_field, $key ) ) {

                    // format ran ok ##
                    // h::log( 'd:>format ran ok.. so now we can update markup for field: '.$field );
                    render\markup::set( $field, $count );

                }

                // iterate count ##
                $count ++ ;

            }

        }

        // remove variable from markup template
		// self::$markup['template'] = render\markup::remove_placeholder( '{{ '.$field.' }}', self::$markup['template'] );
		$variable = willow\tags::wrap([ 'open' => 'var_o', 'value' => $field, 'close' => 'var_c' ]);
		self::$markup['template'] = willow\markup::remove( $variable, self::$markup['template'], 'variable' );

        // delete sending field ##
        render\fields::remove( $field, 'Removed by format_array after working' );

        // checkout markup ##
        // h::log( self::$markup['template'] );

        // returning false will delete the original passed field ##
        return true;

	}
	

	public static function is_associative_array( $array ) { 

		foreach ( $array as $key => $value ) { 
			if ( is_string( $key ) ) {
				return true; 
			}
		} 
		
		return false; 

	}



    public static function format_array_repeater( $value = null, $field = null )
    {

        // h::log( 'Formatting repeater array...' );
        // h::log( $value );

        // check how many items are in array and format ##
        $count = 0;

        // loop over array of arrays, work inner keys and values ## 
        foreach( $value as $r1 => $v1 ) {

            foreach( $v1 as $r2 => $v2 ) {

                // h::log( 'Working "'.$r2.'" Key value: "'.$v2.'"' );

                // create a new, named and numbered field based on field__COUNT.row_key ##
				// $key_field = $field.'__'.$count.'__'.$r2;
				// render\fields::set( $field.'__'.$count.'__'.$r2, $v2 );
                render\fields::set( $field.'.'.$count.'.'.$r2, $v2 );

            }

            // format ran ok ##
            render\markup::set( $field, $count );

            // iterate count ##
            $count ++ ;

        }

        return true;

    }




    /**
     * Format Object values ##
     * Currently, we only support Objects of type WP_Post - so validate with instance of ## 
     * @todo -- Extend this method a lot to deal with extra object types ##
     */
    public static function format_object( $value = null, $field = null )
    {

        // allow filtering early ##
        $value = \apply_filters( 'q/render/format/object/'.self::$args['task'].'/'.$field, $value );

        // WP Object format ##
        if ( $value instanceof \WP_Post ) {

            // pass to WP formatter ##
            $value = self::format_object_wp_post( $value, $field );

        // @todo - add more formats here ... ##

        } else {

			// log ##
			h::log( self::$args['task'].'~>n:>Object is not of type WP_Post, so emptied, $value returned empty and field removed from $fields');

            // this item needs to be removed from self::$fields
            render\fields::remove( $field, 'Removed by format_object because Object format is not allowed in $formats' );

            // we do not return the $value either ##
            return false; 

        }

        // delete sending field ##
        render\fields::remove( $field, 'Removed by format_object after working' );

        // return false will delete the passed field ##
        return true;

    }



    /**
     * Format WP_Post Objects
     */
    public static function format_object_wp_post( \WP_Post $wp_post = null, $field = null ) :bool {

        // sanity ##
        if (
            is_null( $wp_post )
            || is_null( $field )
        ) {

			// log ##
			h::log( self::$args['task'].'~>e:>No value or field passed to format_wp_post_object');

            return false;

		}

		// define context ##
		$context = 'WP_Post';
		
		// h::log( 'Formatting WP Post Object: '.$wp_post->post_title );
		// h::log( 'Field: '.$field ); // whole object ##

        // now, we need to create some new $fields based on each value in self::$type_fields ##
        foreach( self::$type_fields as $type_field ) {
			
			// h::log( 'Working: '.$type_field.' context: '.$context );

			// start empty ##
			$string = null;

			switch( $type_field ) {

				// post handlers ##	
				// case "ID" : // post special ##
				case substr( $type_field, 0, strlen( 'post_' ) ) === 'post_' :

					$string = render\type::post( $wp_post, $type_field, $field, $context );

				break ;

				// author handlers ##	
				case substr( $type_field, 0, strlen( 'author_' ) ) === 'author_' :

					$string = render\type::author( $wp_post, $type_field, $field, $context );

				break ;

				// taxonomy handlers ##	
				case substr( $type_field, 0, strlen( 'category_' ) ) === 'category_' :
				// case substr( $type_field, 0, strlen( 'term_' ) ) === 'term_' : // @todo ##
				// case substr( $type_field, 0, strlen( 'term_' ) ) === 'term_' : // @todo ##

					$string = render\type::taxonomy( $wp_post, $type_field, $field, $context );

				break ;

				// post thumbnail ###
				// @todo --- avoid media lookups, when markups does not require them ###
				// this could be done by checking markup, pre-compile - for "src" attr ... ??
				case 'media' : // @todo
	
					// // note, we pass the attachment ID to src handler ##
					// $attachment_id = \get_post_thumbnail_id( $wp_post );
					// $attachment = \get_post( $attachment_id );

					$string = render\type::media( $wp_post, $type_field, $field, $context );

				break ;

				case 'src' :
		
					// // note, we pass the attachment ID to src handler ##
					// $attachment_id = \get_post_thumbnail_id( $wp_post );
					// $attachment = \get_post( $attachment_id );
					// h::log( self::$args );

					$string = render\type::media( $wp_post, $type_field, $field, $context );

				break ;

			}

			if ( is_null( $string ) ) {

				h::log( 'Field: '.$field.' / '.$type_field.' returned an empty string' );

				// log ##
				h::log( self::$args['task'].'~>e:Field: "'.$field.' / '.$type_field.'" returned an empty string');

				// @@ todo.. do we need to remove field or markup ?? ##

				continue;

			}

			// assign field and value ##
			render\fields::set( $field.'.'.$type_field, $string );
			// render\fields::set( $field.'__'.$type_field, $string );

		}

        // kick back ##
        return true;

    }




    /**
     * Get allowed fomats with filter ##
     * 
     */
    public static function get_allowed()
    {

        return \apply_filters( 'q/render/format/get_allowed', self::$format );

    }



}
