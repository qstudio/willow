<?php

namespace willow\render;

use willow\core\helper as h;
use willow;

class format {

	private 
		$plugin = false
	;

	/**
     */
    public function __construct(){

		// grab passed plugin object ## 
		$this->plugin = willow\plugin::get_instance();

	}

    /**
     * Check allowed formats based on passed $value, format and return a string ready for markup  
     * 
     * @return      String
     */
    public function field( String $field = null, $value = null ) {
		
		// w__log( 'd:>Field: '.$field );
		// w__log( $value );
		
		// sanity ##
        if ( is_null( $field ) ) {

			// log ##
			w__log( $this->plugin->get( '_args' )['task'].'~>e:>No field value passed to method.');
			// w__log( 'd:>Field value: '.$value );

            return false;

        }

        // sanity ##
        if ( is_null( $value ) ) {

			// log ##
			w__log( $this->plugin->get( '_args' )['task'].'~>e:>No value passed to method.');
			// w__log( 'd:>Field value: '.$value );

            return false;

        }

        // Check if there are any allowed formats ##
        // Also runs filters to add custom formats ##
        $formats = $this->get_allowed();

        if ( 
            ! $formats
            || ! \is_array( $formats ) 
        ) {

			// log ##
			w__log( $this->plugin->get( '_args' )['task'].'~>e:>No formats allowed in plugin or array corrupt.');
			// w__log( 'e:>No formats allowed in plugin or array corrupt.');

            return false;

		}
		
        // Now check the format of $value - Array requires repeat check on each row ##
		$format = $this->get( $value, $field );
		// w__log( 'Field: '.$field.' --> Format: '.$format );

        // now try to format value ##
		$return = $this->apply( $value, $field, $format );

		// w__log( $this->plugin->get( '_fields' ) );
		
        // self::$fields should all be String values by now, ready for markup ##
        return $return;

	}
	
    /**
     * Get format of $field $value from defined list of allowed formats ##
     * 
     */
    public function get( $value = null, $field = null ){

        // sanity ##
        if ( 
            is_null( $value )
            || is_null( $field )
        ) {

			// log ##
			w__log( $this->plugin->get( '_args' )['task'].'~>e:>Error in parameters passed to check_format');

            return false;

        }

        // get formats ##
        $formats = $this->get_allowed();
        // w__log( $formats );

        // tracker, if we find a match ##
        $tracker = false;

        // assign default in case we don't find a matching type ##
        // this is alterable via a filter ##
        $return = \apply_filters( 'willow/render/format/default', 'format_string' ); 

        // w__log( 'Default method is: '.$return );

        // loop over formats and search for a match ##
        foreach ( $formats as $format => $format_value ){

            // w__log( 'Checking type: '.$format_value['type'] );

            if ( ! function_exists( $format_value['type'] ) ) {

				// log ##
				w__log( $this->plugin->get( '_args' )['task'].'~>n:>Function not found: "'.$format_value['type'].'"');

                continue;

            }

            // w__log( 'd:>function exists: '.$format_value['type'] );

            // boolean check ## is_TYPE === true
            if ( 
				TRUE === call_user_func_array( $format_value['type'], array( $value ) ) 
				// @TODO - use direct class call ##
            ) {

                // log ##
                // w__log( 'd:>Field value: '.$field.' is Type: '.$format_value['type'].' Format with: '.$format_value['method'] );

                // update tracker ##
                $tracker = true;

                // field type assigned ##
                $return = $format_value['method'];

            }

        }

        // note use of default type if no match found ##
        if ( false === $tracker ) {

			// log ##
			w__log( $this->plugin->get( '_args' )['task'].'~>n:>No valid value type found for field: "'.$field.'" so assigned: "'.$return.'"');

        }

        // final filter on field format type ##
        $return = \apply_filters( 'willow/render/format/get/'.$this->plugin->get( '_args' )['task'].'/'.$field, $return );

        // kick back ##
        return $return;

    }

    /**
     * Allow text field to be filtered ##
     * 
     */
    public function apply( $value = null, String $field = null, String $format = null ){

        // sanity ##
        if ( 
            is_null( $value )
            || is_null( $field )
            || is_null( $format )
        ) {

			// log ##
			w__log( $this->plugin->get( '_args' )['task'].'~>e:>Error in parameters passed to "apply", $value returned empty and field removed from $fields');

			w__log( 'e:>Error in parameters passed to "apply", $value returned empty and field removed from $fields');

			// this item needs to be removed from $_fields
            $this->plugin->render->fields->remove( $field );

             // we do not return the $value either ##
            return false;

        }

		// w__log( 'd:>Checking Format for - Field: "'.$field.'" with method: "'.$format.'"' );
		// w__log( $value );

        // we can now distribute the $value to the relevant format method ##
        if (
            ! method_exists( __CLASS__, $format )
            || ! is_callable( array( __CLASS__, $format ) )
        ){

			// log ##
			w__log( $this->plugin->get( '_args' )['task'].'~>e:>handler wrong - class: "'.__CLASS__.'" / method: "'.$format.'"');
			w__log( 'e:>handler wrong - class: "'.__CLASS__.'" / method: "'.$format.'"');

            // this item needs to be removed from self::$fields
            $this->plugin->render->fields->remove( $field );

            // we do not return the $value either ##
            return false; 

		}
		
		// w__log( 'e:>format: '.$format );

		// call method and pass arguments ##
		/*
        $value = call_user_func_array (
            array( $this, $format )
            ,   array( $value, $field )
		);
		*/
		$value = $this->{ $format }( $value, $field );
		
		// w__log( $value );

        if ( ! $value ) {

            // w__log( 'Handler method returned bad OR empty data for Field: '.$field );

            // this item needs to be removed from self::$fields
			// self::remove_field( $field, 'Removed by "apply" due to bad or empty data' );
			
			// @TODO --> VERY BAD <--
			w__log( 'Field value bad: '.$field );

            return false; // we do not return the $value either ##

        }

        // test returned data ##
		// w__log( $this->plugin->get( '_fields' ) );
		// w__log( 'Field value now: '.$value );

        // fields are filtered and saved by each type handler, as new fields might be added or removed internally ##

        // kick back ##
        return true;

    }

    /**
     * Format string - allow for external filtering ##
     * 
     */
    public function format_string( $value = null, $field = null ){

        // w__log( $value );

        return \apply_filters( 'willow/render/format/text/'.$this->plugin->get( '_args' )['task'].'/'.$field, $value );

    }

    /**
     * Allow integer field to be filtered ##
     * 
     */
    public function format_integer( $value = null, $field = null ){

        return \apply_filters( 'willow/render/format/integer/'.$this->plugin->get( '_args' )['task'].'/'.$field, $value );

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
    public function format_array( $value = null, $field = null ){

        // allow filtering early ##
		$value = \apply_filters( 'willow/render/format/array/'.$this->plugin->get( '_args' )['task'].'/'.$field, $value );
		
		// w__log( $value );

        // array of arrays containing named indexes ( not WP_Post Objects ) needs to be be marked up as a block, like an Object ##

        // add check to see if array is a collection of array - as exported by repeater fields ##
        if ( 'repeater' == $this->plugin->render->fields->get_type( $field ) ) {

            // w__log( 'd:>Array is a repeater' );

            $this->format_array_repeater( $value, $field );

        } else {

			// w__log( 'd:>Array is an Array..' );
			// w__log( $value );

            // check how many items are in array and format ##
            $count = 0;

            // we need to loop over the array and check what each the value of each key using self::format()
            // Formats that are not registered in self::$formats will be removed ## 
            foreach( $value as $key ) {

                // w__log( $key );

                // create a new, named and numbered field based on field_COUNT -- empty value ##
				$key_field = $field.'.'.$count;
				/*
				WAS
				$key_field = $field.'_'.$count;
				*/
                $this->plugin->render->fields->set( $key_field, '' );

                // Format each field value based on type ( int, string, array, WP_Post Object ) ##
                // each item is filtered as looped over -- q/render/format/GROUP/FIELD - ( $args, $fields ) ##
                // results are saved back to the self::$fields array in String format ##
                if ( $this->field( $key_field, $key ) ) {

                    // format ran ok ##
                    // w__log( 'd:>format ran ok.. so now we can update markup for field: '.$field );
                    $this->plugin->render->markup->set( $field, $count );

                }

                // iterate count ##
                $count ++ ;

            }

        }

        // remove variable from markup template
		// self::$markup['template'] = render\markup::remove_placeholder( '{{ '.$field.' }}', self::$markup['template'] );

		// get parse_markup object ##
		// $parse_markup = new willow\parse\markup( $this->plugin );

		// get _markup ##
		$_markup = $this->plugin->get( '_markup' );

		$variable = $this->plugin->tags->wrap([ 'open' => 'var_o', 'value' => $field, 'close' => 'var_c' ]);
		$_markup['template'] = $this->plugin->parse->markup->remove( $variable, $_markup['template'], 'variable' );

		// set _markup ##
		$this->plugin->set( '_markup', $_markup );

		// delete sending field ##
        $this->plugin->render->fields->remove( $field, 'Removed by format_array after working' );

        // checkout markup ##
		// w__log( self::$markup['template'] );
		
		// w__log( $this->plugin->get( '_fields' ) );
		// w__log( self::$markup);

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

    public function format_array_repeater( $value = null, $field = null ){

        // w__log( 'Formatting repeater array for field: '.$field );
        // w__log( $value );

        // check how many items are in array and format ##
		$count = 0;
		
		// build an array to pass to field setter, to handle repeaters of WP_Post objects ##
		// $array = [];

        // loop over array of arrays, work inner keys and values ## 
        foreach( $value as $r1 => $v1 ) {

            foreach( $v1 as $r2 => $v2 ) {

				// w__log( 'e:>Working "'.$r2.'" Key value: "'.$v2.'"' );
				// w__log_direct( $r2 ); // 'space'
				// w__log_direct( $v2 ); // WP_Post

				// WP_Post Object ##
				if ( $v2 instanceof \WP_Post ) {

					// w__log( 'WP Post Object...' );

					// pass to WP formatter and capture returned array ##
					$this->format_object_wp_post( $v2, $field.'.'.$count );

				// WP_Term Object ##
				} elseif ( $v2 instanceof \WP_Term ) {

					// pass to WP formatter ##
					$this->format_object_wp_term( $v2, $field.'.'.$count );

				} else {

					// w__log( 'format: '.$v2 );

					// create a new, named and numbered field based on field__COUNT.row_key ##
					// $key_field = $field.'__'.$count.'__'.$r2;
					// render\fields::set( $field.'__'.$count.'__'.$r2, $v2 );
					$this->plugin->render->fields->set( $field.'.'.$count.'.'.$r2, $v2 );
				
				}

            }

            // format ran ok ##
			$this->plugin->render->markup->set( $field, $count );
			
            // iterate count ##
            $count ++ ;

		}

		// if ( ! empty( $array ) ) {

			// we need to assign the correct key ##
			// w__log( [ $field => $array ] );

			// store array ##
			// self::$fields[$field] = $array;

		// }
		
		// ALSO -- if array only has one row - add key.property fields ##
		if ( 1 === count( $value ) ){

			// w__log( 'e:>'.$field.' is a Single ROW array..' );

			// loop over array of arrays, work inner keys and values ## 
			foreach( $value as $r1 => $v1 ) {

				foreach( $v1 as $r2 => $v2 ) {

					// WP_Post Object ##
					if ( $v2 instanceof \WP_Post ) {

						// w__log_direct( 'WP Post Object...' );

						// pass to WP formatter ##
						$this->format_object_wp_post( $v2, $field );

					// WP_Term Object ##
					} elseif ( $v2 instanceof \WP_Term ) {

						// pass to WP formatter ##
						$this->format_object_wp_term( $v2, $field );

					} else {
	
						// w__log( 'e:>Working "'.$r2.'" Key value: "'.$v2.'"' );
		
						// create a new, named and numbered field based on field__COUNT.row_key ##
						// render\fields::set( $field.'__'.$count.'__'.$r2, $v2 );
						$this->plugin->render->fields->set( $field.'.'.$r2, $v2 );

					}
	
				}
	
				// format ran ok ##
				// render\markup::set( $field, $count );
	
				// iterate count ##
				// $count ++ ;
	
			}

		}

		// w__log( $this->plugin->get( '_fields' ) );
		// w__log( self::$markup);

        return true;

    }

    /**
     * Format Object values ##
     * Currently, we only support Objects of type WP_Post - so validate with instance of ## 
     * @todo -- Extend this method a lot to deal with extra object types ##
     */
    public function format_object( $value = null, $field = null ){

		// w__log( 'Formatting object for field: '.$field );

		// local vars ##
		$_args = $this->plugin->get( '_args' );

        // allow filtering early ##
        $value = \apply_filters( 'willow/render/format/object/'.$_args['task'].'/'.$field, $value );

        // WP_Post Object ##
        if ( $value instanceof \WP_Post ) {

			// // w__log( 'Formatting object WP_Post field: '.$field );

            // pass to WP formatter ##
            $value = $this->format_object_wp_post( $value, $field );

		// WP_Term Object ##
		} elseif ( $value instanceof \WP_Term ) {

            // pass to WP formatter ##
            $value = $this->format_object_wp_term( $value, $field );

        // @todo - add more formats here ... ##

        } else {

			// log ##
			w__log( $_args['task'].'~>n:>Object is not of type WP_Post, so emptied, $value returned empty and field removed from $fields');

            // this item needs to be removed from self::$fields
            $this->plugin->render->fields->remove( $field, 'Removed by format_object because Object format is not allowed in $formats' );

            // we do not return the $value either ##
            return false; 

        }

        // delete sending field ##
        $this->plugin->render->fields->remove( $field, 'Removed by format_object after working' );

        // return false will delete the passed field ##
        return true;

    }

    /**
     * Format WP_Post Objects
     */
    public function format_object_wp_post( \WP_Post $wp_post = null, $field = null ): bool {

		// local vars ##
		$_args = $this->plugin->get( '_args' );
		$_wp_post_fields = $this->plugin->get( '_wp_post_fields');

        // sanity ##
        if (
            is_null( $wp_post )
            || is_null( $field )
        ) {

			// log ##
			w__log( $_args['task'].'~>e:>No value or field passed to format_wp_post_object');
			w__log( 'e:>No value or field passed to format_wp_post_object');

            return false;

		}

		// instatiate willow\type object ##
		// $type_get = new willow\type\get( $this->plugin );

		// define context ##
		$context = 'WP_Post';

		// render_fields object ##
		// $render_fields = willow\render\fields( $this->plugin );

		// w__log( $wp_post );

		// return array of fields ##
		// $array = [];
		
		// w__log( 'Formatting WP Post Object: '.$wp_post->post_title );
		// w__log( 'Field: '.$field ); // whole object ##

        // now, we need to create some new $fields based on each value in self::$wp_post_fields ##
        foreach( $_wp_post_fields as $wp_post_field ) {
			
			// w__log( 'Working: '.$wp_post_field.' context: '.$context );
			// w__log( 't:>move to object.property - post.title - variable calls..' );

			// start empty ##
			$string = null;

			switch( $wp_post_field ) {

				// post handlers ##	
				// case "ID" : // post special ##
				case substr( $wp_post_field, 0, strlen( 'post_' ) ) === 'post_' :

					$post = new willow\type\post( $this->plugin );

					$string = $post->format( $wp_post, $wp_post_field, $field, $context, $type = 'post' );

				break ;

				// author handlers ##	
				case substr( $wp_post_field, 0, strlen( 'author_' ) ) === 'author_' :

					$author = new willow\type\author( $this->plugin );

					$string = $author->format( $wp_post, $wp_post_field, $field, $context, $type = 'author' );

				break ;

				// taxonomy handlers ##	
				case substr( $wp_post_field, 0, strlen( 'category_' ) ) === 'category_' :
				// case substr( $wp_post_field, 0, strlen( 'term_' ) ) === 'term_' : // @todo ##

					$taxonomy = new willow\type\taxonomy( $this->plugin );

					$string = $taxonomy->format( $wp_post, $wp_post_field, $field, $context, $type = 'category' );

				break ;

				// post thumbnail ###
				// @todo --- avoid media lookups, when markup does not require them ###
				// this could be done by checking markup, pre-compile - for "src" attr ... ??
				case 'media' : // @todo
	
					// $attachment = \get_post( $attachment_id );
					// w__log( 'Get media gallery...' );

					$media = new willow\type\media( $this->plugin );

					$string = $media->format( $wp_post, $wp_post_field, $field, $context, $type = 'media' );

				break ;

				case 'src' :
		
					// w__log( $_args );

					$media = new willow\type\media( $this->plugin );

					$string = $media->format( $wp_post, $wp_post_field, $field, $context, $type = 'media' );

				break ;

				case 'meta' :
		
					// w__log( $_args );
					$meta = new willow\type\meta( $this->plugin );

					$string = $meta->format( $wp_post, $wp_post_field, $field, $context, $type = 'meta' );

				break ;

				// keep this as a string ##
				case 'highlight' :
		
					// w__log( '$wp_post->highlight: '.$wp_post->highlight );

					$string = $wp_post->highlight ?? '';

				break ;

			}

			if ( is_null( $string ) ) {

				// w__log( 'Field: '.$field.' / '.$wp_post_field.' returned an empty string' );

				// log ##
				w__log( $_args['task'].'~>e:Field: "'.$field.' / '.$wp_post_field.'" returned an empty string');

				// next ... ##
				continue;

			}

			// filter post fields -- global ##
			$string = \apply_filters( 
				'willow/render/type/'.$_args['context'], $string 
			);

			// filter group/field -- field specific ##
			$string = \apply_filters( 
				'willow/render/type/'.$_args['context'].'/'.$_args['task'], $string
			);

			// assign field and value ##
			// willow\render\fields::set( $field.'.'.$wp_post_field, $string );
			$this->plugin->render->fields->set( $field.'.'.$wp_post_field, $string );

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

				w__log( 'd:>Filter did not return a usable array' );

				return false;

			}

			// w__log( $array );
			// loo over array values ##
			foreach( $array as $key => $value ) {

				// w__log( 'e:>Adding key: "'.$key.'" with value: "'.$value.'"' );

				// validate $value is a string ##
				if( ! is_string( $value ) ){

					// w__log( 'e:>"'.$key.'" value is not a string' );

					continue;

				}

				// assign field and value ##
				// willow\render\fields::set( $field.'.'.$key, $value );
				$this->plugin->render->fields->set( $field.'.'.$key, $value );

			}

		}

        // kick back ##
        return true;

    }

    /**
     * Format WP_Term Objects
     */
    public function format_object_wp_term( \WP_Term $wp_term = null, $field = null ) :bool {

		$_args = $this->plugin->get( '_args' );

        // sanity ##
        if (
            is_null( $wp_term )
            || is_null( $field )
        ) {

			// log ##
			w__log( $_args['task'].'~>e:>No value or field passed to format_wp_term_object');

            return false;

		}

		// define context ##
		$context = 'WP_Term';
		// $taxonomy = $wp_term->taxonomy ?? 'category'; // default to category ##
		
		// w__log( $wp_term );
		// w__log( '$taxonomy: '.$taxonomy );
		// w__log( 'Formatting WP Term Object: '.$wp_term->name );
		// w__log( $field ); // whole object ##

        // now, we need to create some new $fields based on each value in self::$wp_term_fields ##
        foreach( $this->plugin->get('_wp_term_fields') as $wp_term_field ) {
			
			// w__log( 'Working: '.$wp_term_field.' context: '.$context );
			// w__log( 't:>move to object.property - post.title - variable calls..' );

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

				w__log( 'Field: '.$field.' / '.$wp_term_field.' returned an empty string' );

				// log ##
				w__log( $this->plugin->get( '_args' )['task'].'~>e:Field: "'.$field.' / '.$wp_term_field.'" returned an empty string');

				// keep moving...
				continue;

			}

			// assign field and value ##
			// willow\render\fields::set( $field.'.'.$wp_term_field, $string );
			$this->plugin->render->fields->set( $field.'.'.$wp_term_field, $string );

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

				// w__log( 'd:>Filter did not return a usable array' );

				return false;

			}

			// w__log( $array );
			// loo over array values ##
			foreach( $array as $key => $value ) {

				// w__log( 'e:>Adding "'.$key.'" with value "'.$value.'"' );

				// validate $value is a string ##
				if( ! is_string( $value ) ){

					w__log( 'e:>"'.$key.'" value is not a string' );

					continue;

				}

				// assign field and value ##
				// willow\render\fields::set( $field.'.'.$key, $value );
				$this->plugin->render->fields->set( $field.'.'.$key, $value );

			}

		}

        // kick back ##
        return true;

    }

    /**
     * Get allowed fomats with filter ##
     * 
     */
    public function get_allowed(){

        return \apply_filters( 'willow/render/format/get_allowed', $this->plugin->get( '_format') );

    }



}
