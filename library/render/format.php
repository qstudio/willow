<?php

namespace willow\render;

// use q\core;
use willow\core\helper as h;
use q\view;
use q\get;
use willow;
use willow\parse;
use willow\render;

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
    public static function get( $value = null, $field = null ){

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
        $return = \apply_filters( 'willow/render/format/default', 'format_string' ); 

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
        $return = \apply_filters( 'willow/render/format/get/'.self::$args['task'].'/'.$field, $return );

        // kick back ##
        return $return;

    }




    /**
     * Allow text field to be filtered ##
     * 
     */
    public static function apply( $value = null, String $field = null, String $format = null ){

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

        // h::log( 'd:>Checking Format for - Field: "'.$field.'" with method: "'.$format.'"' );

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
     * Format string - allow for external filtering ##
     * 
     */
    public static function format_string( $value = null, $field = null ){

        // h::log( $value );

        return \apply_filters( 'willow/render/format/text/'.self::$args['task'].'/'.$field, $value );

    }


    /**
     * Allow integer field to be filtered ##
     * 
     */
    public static function format_integer( $value = null, $field = null ){

        return \apply_filters( 'willow/render/format/integer/'.self::$args['task'].'/'.$field, $value );

    }



    /**
     * Format Array values
     * These need to be looped over and each value passed back into the format() process
     * 
     * Array data "MIGHT" come from a repeater -- or from another UI method which has gathered data ##
     * which has one single {{ placeholder }} and markup in a property with a name matching key to the field name
     * we need to update the template based on number of array items and defined markup with numbered values ##
     * 
     */
    public static function format_array( $value = null, $field = null ){

        // allow filtering early ##
		$value = \apply_filters( 'willow/render/format/array/'.self::$args['task'].'/'.$field, $value );
		
		// h::log( $value );

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
		self::$markup['template'] = parse\markup::remove( $variable, self::$markup['template'], 'variable' );

        // delete sending field ##
        render\fields::remove( $field, 'Removed by format_array after working' );

        // checkout markup ##
		// h::log( self::$markup['template'] );
		
		// h::log( self::$fields );
		// h::log( self::$markup);

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



    public static function format_array_repeater( $value = null, $field = null ){

        // h::log( 'Formatting repeater array for field: '.$field );
        // h::hard_log( $value );

        // check how many items are in array and format ##
		$count = 0;
		
		// build an array to pass to field setter, to handle repeaters of WP_Post objects ##
		// $array = [];

        // loop over array of arrays, work inner keys and values ## 
        foreach( $value as $r1 => $v1 ) {

            foreach( $v1 as $r2 => $v2 ) {

				// h::log( 'e:>Working "'.$r2.'" Key value: "'.$v2.'"' );
				// h::hard_log( $r2 ); // 'space'
				// h::hard_log( $v2 ); // WP_Post

				// WP_Post Object ##
				if ( $v2 instanceof \WP_Post ) {

					// h::log( 'WP Post Object...' );

					// pass to WP formatter and capture returned array ##
					self::format_object_wp_post( $v2, $field.'.'.$count );

				// WP_Term Object ##
				} elseif ( $v2 instanceof \WP_Term ) {

					// pass to WP formatter ##
					self::format_object_wp_term( $v2, $field.'.'.$count );

				} else {

					// h::log( 'format: '.$v2 );

					// create a new, named and numbered field based on field__COUNT.row_key ##
					// $key_field = $field.'__'.$count.'__'.$r2;
					// render\fields::set( $field.'__'.$count.'__'.$r2, $v2 );
					render\fields::set( $field.'.'.$count.'.'.$r2, $v2 );
				
				}

            }

            // format ran ok ##
			render\markup::set( $field, $count );
			
            // iterate count ##
            $count ++ ;

		}

		// if ( ! empty( $array ) ) {

			// we need to assign the correct key ##
			// h::log( [ $field => $array ] );

			// store array ##
			// self::$fields[$field] = $array;

		// }
		
		// ALSO -- if array only has one row - add key.property fields ##
		if ( 1 === count( $value ) ){

			// h::log( 'e:>'.$field.' is a Single ROW array..' );

			// loop over array of arrays, work inner keys and values ## 
			foreach( $value as $r1 => $v1 ) {

				foreach( $v1 as $r2 => $v2 ) {

					// WP_Post Object ##
					if ( $v2 instanceof \WP_Post ) {

						// h::hard_log( 'WP Post Object...' );

						// pass to WP formatter ##
						self::format_object_wp_post( $v2, $field );

					// WP_Term Object ##
					} elseif ( $v2 instanceof \WP_Term ) {

						// pass to WP formatter ##
						self::format_object_wp_term( $v2, $field );

					} else {
	
						// h::log( 'e:>Working "'.$r2.'" Key value: "'.$v2.'"' );
		
						// create a new, named and numbered field based on field__COUNT.row_key ##
						// render\fields::set( $field.'__'.$count.'__'.$r2, $v2 );
						render\fields::set( $field.'.'.$r2, $v2 );

					}
	
				}
	
				// format ran ok ##
				// render\markup::set( $field, $count );
	
				// iterate count ##
				// $count ++ ;
	
			}

		}

		// h::log( self::$fields );
		// h::log( self::$markup);

        return true;

    }




    /**
     * Format Object values ##
     * Currently, we only support Objects of type WP_Post - so validate with instance of ## 
     * @todo -- Extend this method a lot to deal with extra object types ##
     */
    public static function format_object( $value = null, $field = null ){

        // allow filtering early ##
        $value = \apply_filters( 'willow/render/format/object/'.self::$args['task'].'/'.$field, $value );

        // WP_Post Object ##
        if ( $value instanceof \WP_Post ) {

            // pass to WP formatter ##
            $value = self::format_object_wp_post( $value, $field );

		// WP_Term Object ##
		} elseif ( $value instanceof \WP_Term ) {

            // pass to WP formatter ##
            $value = self::format_object_wp_term( $value, $field );

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
    public static function format_object_wp_post( \WP_Post $wp_post = null, $field = null ): bool {

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

		// h::log( $wp_post );

		// return array of fields ##
		// $array = [];
		
		// h::log( 'Formatting WP Post Object: '.$wp_post->post_title );
		// h::log( 'Field: '.$field ); // whole object ##

        // now, we need to create some new $fields based on each value in self::$wp_post_fields ##
        foreach( self::$wp_post_fields as $wp_post_field ) {
			
			// h::log( 'Working: '.$wp_post_field.' context: '.$context );
			// h::log( 't:>move to object.property - post.title - variable calls..' );

			// start empty ##
			$string = null;

			switch( $wp_post_field ) {

				// post handlers ##	
				// case "ID" : // post special ##
				case substr( $wp_post_field, 0, strlen( 'post_' ) ) === 'post_' :

					$string = render\type::post( $wp_post, $wp_post_field, $field, $context );

				break ;

				// author handlers ##	
				case substr( $wp_post_field, 0, strlen( 'author_' ) ) === 'author_' :

					$string = render\type::author( $wp_post, $wp_post_field, $field, $context );

				break ;

				// taxonomy handlers ##	
				case substr( $wp_post_field, 0, strlen( 'category_' ) ) === 'category_' :
				// case substr( $wp_post_field, 0, strlen( 'term_' ) ) === 'term_' : // @todo ##

					$string = render\type::taxonomy( $wp_post, $wp_post_field, $field, $context );

				break ;

				// post thumbnail ###
				// @todo --- avoid media lookups, when markup does not require them ###
				// this could be done by checking markup, pre-compile - for "src" attr ... ??
				case 'media' : // @todo
	
					// $attachment = \get_post( $attachment_id );

					$string = render\type::media( $wp_post, $wp_post_field, $field, $context );

				break ;

				case 'src' :
		
					// h::log( self::$args );

					$string = render\type::media( $wp_post, $wp_post_field, $field, $context );

				break ;

				case 'meta' :
		
					// h::log( self::$args );

					$string = render\type::meta( $wp_post, $wp_post_field, $field, $context );

				break ;

				// keep this as a string ##
				case 'highlight' :
		
					// h::log( '$wp_post->highlight: '.$wp_post->highlight );

					$string = $wp_post->highlight ?? '';

				break ;

			}

			if ( is_null( $string ) ) {

				// h::log( 'Field: '.$field.' / '.$wp_post_field.' returned an empty string' );

				// log ##
				h::log( self::$args['task'].'~>e:Field: "'.$field.' / '.$wp_post_field.'" returned an empty string');

				// next ... ##
				continue;

			}

			// assign field and value ##
			render\fields::set( $field.'.'.$wp_post_field, $string );

		}

		// filter in custom post field data ##
		$post_type = $wp_post->post_type ?? 'post'; // default to post ##

		// check for defined filter ##
		if ( \has_filter( 'willow/format/wp_post/'.$post_type ) ) {

			// run filter ##
			$array = \apply_filters( 
				'willow/format/wp_post/'.$post_type,
				$field,
				$wp_post
			);

			// validate filter return ##
			if (
				! $array
				|| ! is_array( $array )
			){

				h::log( 'd:>Filter did not return a usable array' );

				return false;

			}

			// h::log( $array );
			// loo over array values ##
			foreach( $array as $key => $value ) {

				// h::log( 'e:>Adding "'.$key.'" with value "'.$value.'"' );

				// validate $value is a string ##
				if( ! is_string( $value ) ){

					h::log( 'e:>"'.$key.'" value is not a string' );

					continue;

				}

				// assign field and value ##
				render\fields::set( $field.'.'.$key, $value );

			}

		}

        // kick back ##
        return true;

    }



	

    /**
     * Format WP_Term Objects
     */
    public static function format_object_wp_term( \WP_Term $wp_term = null, $field = null ) :bool {

        // sanity ##
        if (
            is_null( $wp_term )
            || is_null( $field )
        ) {

			// log ##
			h::log( self::$args['task'].'~>e:>No value or field passed to format_wp_term_object');

            return false;

		}

		// define context ##
		$context = 'WP_Term';
		// $taxonomy = $wp_term->taxonomy ?? 'category'; // default to category ##
		
		// h::log( $wp_term );
		// h::log( '$taxonomy: '.$taxonomy );
		// h::log( 'Formatting WP Term Object: '.$wp_term->name );
		// h::log( $field ); // whole object ##

        // now, we need to create some new $fields based on each value in self::$wp_term_fields ##
        foreach( self::$wp_term_fields as $wp_term_field ) {
			
			// h::log( 'Working: '.$wp_term_field.' context: '.$context );
			// h::log( 't:>move to object.property - post.title - variable calls..' );

			// start empty ##
			$string = null;

			/*
			'term_ID',
			'term_title',
			'term_slug',
			'term_parent',
			'term_permalink',
			'term_taxonomy',
			'term_description',
			'term_parent',
			'term_count'
			*/

			/*
			extend taxonomy term, if there are extra acf fields registered
			*/

			switch( $wp_term_field ) {

				// term_id ##	
				case 'term_ID' :

					$string = $wp_term->term_id;

				break ;

				// term_permalink ##	
				case 'term_permalink' :

					$string = \get_category_link( $wp_term );

				break ;

				// term_title ##	
				case 'term_title' :

					$string = $wp_term->name;

				break ;

				// term_slug ##	
				case 'term_slug' :

					$string = $wp_term->slug;

				break ;

				// term_parent ##	
				case 'term_parent' :

					$string = $wp_term->parent;

				break ;

				// term_count ##	
				case 'term_count' :

					$string = $wp_term->count;

				break ;

				// term_taxonomy ##	
				case 'term_taxonomy' :

					$string = $wp_term->taxonomy;

				break ;

				// term_description ##	
				case 'term_description' :

					$string = $wp_term->description;

				break ;

			}

			if ( is_null( $string ) ) {

				h::log( 'Field: '.$field.' / '.$wp_term_field.' returned an empty string' );

				// log ##
				h::log( self::$args['task'].'~>e:Field: "'.$field.' / '.$wp_term_field.'" returned an empty string');

				// keep moving...
				continue;

			}

			// assign field and value ##
			render\fields::set( $field.'.'.$wp_term_field, $string );

		}

		// filter in custom taxonomy field data ##
		$taxonomy = $wp_term->taxonomy ?? 'category'; // default to category ##

		// check for defined filter ##
		if ( \has_filter( 'willow/format/wp_term/'.$taxonomy ) ) {

			// run filter ##
			$array = \apply_filters( 
				'willow/format/wp_term/'.$taxonomy,
				$field,
				$wp_term
			);

			// validate filter return ##
			if (
				! $array
				|| ! is_array( $array )
			){

				// h::log( 'd:>Filter did not return a usable array' );

				return false;

			}

			// h::log( $array );
			// loo over array values ##
			foreach( $array as $key => $value ) {

				// h::log( 'e:>Adding "'.$key.'" with value "'.$value.'"' );

				// validate $value is a string ##
				if( ! is_string( $value ) ){

					h::log( 'e:>"'.$key.'" value is not a string' );

					continue;

				}

				// assign field and value ##
				render\fields::set( $field.'.'.$key, $value );

			}

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

        return \apply_filters( 'willow/render/format/get_allowed', self::$format );

    }



}
