<?php

namespace q\willow\render;

use q\core;
use q\core\helper as h;
// use q\ui;
use q\plugin;
use q\get;
use q\view;
use q\asset;
use q\willow;

class method extends willow\render {


	/**
	 * Prepare $array to be rendered
	 *
	 */
	public static function prepare( $args = null, $array = null ) {

		// get calling method for filters ##
		$method = core\method::backtrace([ 'level' => 2, 'return' => 'function' ]);

		// sanity ##
		if (
			is_null( $args )
			|| ! is_array( $args )
			|| is_null( $array )
			|| ! is_array( $array )
			// || empty( $array )
		) {

			// log ##
			h::log( 'e~>'.$method.':>Error in passed $args or $array' );

			return false;

		}

		// empty results ##
		if (
			empty( $array )
		) {

			// log ##
			h::log( 'e~>'.$method.':>Returned $array is empty' );

			return false;

		}

		// h::log( 'd:>$method: '.$method );
		// h::log( $args );
		// h::log( $array );

		// if no markup sent.. ##
		if ( 
			! isset( $args['markup'] )
			&& is_array( $args ) 
		) {

			// default -- almost useless - but works for single values.. ##
			$args['markup'] = '%value%';

			foreach( $args as $k => $v ) {

				if ( is_string( $v ) ) {

					// take first string value in $args markup ##
					$args['markup'] = $v;

					break;

				}

			}

		}

		// no markup passed ##
		if ( ! isset( $args['markup'] ) ) {

			h::log( 'e~>'.$method.':Missing "markup", returning false.' );

			return false;

		}

		// last filter on array, before applying markup ##
		$array = \apply_filters( 'q/render/prepare/'.$method.'/array', $array, $args );

		// do markup ##
		$string = self::markup( $args['markup'], $array, $args );

		// filter $string by $method ##
		$string = \apply_filters( 'q/render/prepare/'.$method.'/string', $string, $args );

		// filter $array by method/template ##
		if ( $template = view\is::get() ) {

			// h::log( 'Filter: "q/theme/get/string/'.$method.'/'.$template.'"' );
			$string = \apply_filters( 'q/render/prepare/'.$method.'/string/'.$template, $string, $args );

		}

		// test ##
		// h::log( $string );

		// all render methods echo ##
		echo $string ;

		// optional logging to show removals and stats ##
        // render\log::render( $args );

		return true;

	}


	/**
	 * Search string for string passed to wp search query
	*/
	public static function search_the_content( Array $args = null ) {

		// sanity @todo ##
		if (
			is_null( $args )
			|| ! isset( $args['string'] )
		) {

			h::log( 'Error in passed params' );

			return false;

		}

		// get string ##
		$string = $args['string'];

		// get search term ##
		$search = \get_search_query();
		// h::log( $search );

        // $string = $args['string']; #\get_the_content();
        $keys = implode( '|', explode( ' ', $search ) );
		$string = preg_replace( '/(' . $keys .')/iu', '<mark>\0</mark>', $string );

		// get text length limit ##
		$length = isset( $args[ 'length' ] ) ? $args['length'] : 200 ;

		// get first occurance of search string ##
		$position = strpos($string, $search );

		// h::log( 'string pos: '.$position );

		if ( ( $length / 2 ) > $position ) {

			// h::log( 'first search term is less than 100 chars in, so return first 200 chars..' );

			$string = ( strlen( $string ) > 200 ) ? substr( $string,0,200 ).'...' : $string;

		} else {

			// move start point ##
			$string = '...'.substr( $string, $position - ( $length / 2 ), -1 );
			$string = ( strlen( $string ) > 200 ) ? substr( $string,0,200 ).'...' : $string;

		}

		// return ##
		return $string;

    }


	/**
	 * Get string between two placeholders
	 * 
	 * @link 	https://stackoverflow.com/questions/5696412/how-to-get-a-substring-between-two-strings-in-php
	 * @since 4.1.0
	*/
	/*
	public static function string_between( $string, $start, $end, $inclusive = false ){ 
		
		$string = " ".$string; 
		$ini = strpos( $string, $start ); 
		
		if ($ini == 0) {
			return ""; 
		}
		
		if ( ! $inclusive ) {

			$ini += strlen( $start ); 
		
		}
		
		$len = strpos( $string, $end, $ini ) - $ini; 
		
		if ( $inclusive ) {
			
			$len += strlen( $end ); 
		
		}

		$string = substr( $string, $ini, $len ); 

		// trim white spaces ##
		$string = trim( $string );

		// h::log( 'string: '.$string );

		// kick back ##
		return $string;
	
	} 
	*/

	/**
	 * 
	 * 
	 * @link https://stackoverflow.com/questions/27078259/get-string-between-find-all-occurrences-php/27078384#27078384
	*/
	/*
	public static function strings_between( $str, $startDelimiter, $endDelimiter ) {

		$contents = array();
		$startDelimiterLength = strlen($startDelimiter);
		$endDelimiterLength = strlen($endDelimiter);
		$startFrom = $contentStart = $contentEnd = 0;

		while (false !== ($contentStart = strpos($str, $startDelimiter, $startFrom))) {

			$contentStart += $startDelimiterLength;
			$contentEnd = strpos($str, $endDelimiter, $contentStart);
			
			if (false === $contentEnd) {
				break;
			}

			$contents[] = substr($str, $contentStart, $contentEnd - $contentStart);
			$startFrom = $contentEnd + $endDelimiterLength;

		}
	  
		return $contents;

	}
	*/


    /**
     * Format passed date value
     *
     * @since   2.0.0
     * @return  Mixed String
     */
    public static function date( $array = null ){

        // test ##
        #h::log( $array );

        // did we pass anything ##
        if ( ! $array ) {

            #h::log( 'kicked 1' );

            return false;

        }

        $return = false;

        // loop over array of date options ##
        foreach( $array as $key => $value ) {

            #h::log( $value );

            // nothing happening ? ##
            if ( false === $value['date'] ) {

                #h::log( 'kicked 2' );

                continue;

            }

            if ( 'end' == $key ) {

                // h::log( 'Formatting end date: '.$value['date'] );

                // if start date and end date are the same, we need to just return the start date and start - end times ##
                if (
                    // $array['start']['date'] == $array['end']['date']
                    date( $value['format'], strtotime( $array['start']['date'] ) ) == date( $value['format'], strtotime( $array['end']['date'] ) )
                ) {

                    // h::log( 'Start and end dates match, return time' );

                    // use end date ##
                    $date = ' '.date( 'g:i:a', strtotime( $array['start']['date'] ) ) .' - '. date( 'g:i:a', strtotime( $array['end']['date'] ) );

                } else {

                    // h::log( 'Start and end dates do not match..' );

                    // use end date ##
                    $date = ' - '.date( $value['format'], strtotime( $value['date'] ) );

                }

            } else {

                // h::log( 'Formatting start date' );

                $date = date( $value['format'], strtotime( $value['date'] ) );

            }

            // add item ##
            $return .= $date;
            #false === $return ?
            #$date :
            #$date ;

        }

        // kick it back ##
        return $return;

    }





    /**
     * Add http:// if it's not in the URL?
     *
     * @param string $url
     * @return string
     * @link    http://stackoverflow.com/questions/2762061/how-to-add-http-if-its-not-exists-in-the-url
     */
    public static function add_http( $url = null ) {

        if ( is_null ( $url ) ) { return false; }

        if ( ! preg_match("~^(?:f|ht)tps?://~i", $url ) ) {

            $url = "http://" . $url;

        }

        return $url;

	}



    /**
     * Strip <style> tags from post_content
     *
     * @link        http://stackoverflow.com/questions/5517255/remove-style-attribute-from-html-tags
     * @since       0.7
     * @return      string HTML formatted text
     */
    public static function remove_style( $input = null )
    {

        if ( is_null ( $input ) ) { return false; }

        return preg_replace( '/(<[^>]+) style=".*?"/i', '$1', $input );

    }




    public static function rip_tags($string) {

        // ----- remove HTML TAGs -----
        $string = preg_replace ('/<[^>]*>/', ' ', $string);

        // ----- remove control characters -----
        $string = str_replace("\r", '', $string);    // --- replace with empty space
        $string = str_replace("\n", ' ', $string);   // --- replace with space
        $string = str_replace("\t", ' ', $string);   // --- replace with space

        // ----- remove multiple spaces -----
        $string = trim(preg_replace('/ {2,}/', ' ', $string));

        return $string;

    }




    public static function chop( $content, $length = 0, $preprend = '...' )
    {

        if ( $length > 0 ) { // trim required, perhaps ##

            if ( strlen( $content ) > $length ) { // long so chop ##
                return substr( $content , 0, $length ).$preprend;
            } else { // no chop ##
                return $content;
            }

        } else { // send as is ##

            return $content;

        }

    }


	public static function load_google_web_fonts( $fonts, $use_fallback = true, $debug = false )
    {

        // bounce to Google method ##
        return plugin\google::fonts( $fonts, $use_fallback = true, $debug = false );

    }




	/**
     * Markup object based on {{ placeholders }} and template
	 * This feature is not for formatting data, just applying markup to pre-formatted data
     *
     * @since    2.0.0
     * @return   Mixed
     */
	/*
    public static function markup( $markup = null, $data = null )
    {

        // sanity ##
        if (
            is_null( $markup )
            || is_null( $data )
            ||
            (
                ! is_array( $data )
                && ! is_object( $data )
            )
        ) {

            h::log( 'e:>missing parameters' );

            return false;

		}
		
		// capture missing placeholders ##
		// $capture = [];

        // h::log( $data );
		#helper::log( $markup );

		// empty ##
		$return = '';

        // format markup with translated data ##
        foreach( $data as $key => $value ) {

			if (
				is_array( $value )
			){

				// check on the value ##
				// h::log( 'd:>key: '.$key.' is array - going deeper..' );

				$return_inner = $markup;

				foreach( $value as $k => $v ) {

					// $string_inner = $markup;

					// check on the value ##
					// h::log( 'd:>key: '.$k.' / value: '.$v );

					// only replace keys found in markup ##
					if ( false === strpos( $return_inner, '{{ '.$k.' }}' ) ) {

						// h::log( 'd:>skipping '.$k );
		
						continue ;
		
					}

					// template replacement ##
					$return_inner = str_replace( '{{ '.$k.' }}', $v, $return_inner );

				}

				$return .= $return_inner;

				continue;

			}

			// get new markup row ##
			$return .= $markup;

			// check on the value ##
			// h::log( 'd:>key: '.$key.' / value: '.$value );

            // only replace keys found in markup ##
            if ( false === strpos( $return, '{{ '.$key.' }}' ) ) {

                h::log( 'd:>skipping '.$key );

                continue ;

			}

			// template replacement ##
			$return = str_replace( '{{ '.$key.' }}', $value, $return );

		}

		// h::log( $args );

		// wrap string in defined string ?? ##
		if ( isset( $args['wrap'] ) ) {

			// h::log( 'd:>wrapping string before return.' );

			// template replacement ##
			$return = str_replace( '{{ content }}', $return, $args['wrap'] );

		}

        // h::log( $return );

        // return markup ##
        return $return;

	}
	*/



	/**
	 * Extract keys and values from passed array
	 * 
	 * @since 4.1.0
	*/
	public static function extract( $array = null, $prefix = null, $return = null ){

		// @todo -- sanity ##
		if (
			is_null( $array )
			|| ! is_array( $array )
			|| is_null( $prefix )
		){

			h::log( 'e:>Error in passed params' );

			return false;

		}

		// return array ##
		$return_array = [];

		// category will be an array, so create category_title, permalink and slug fields ##
		foreach( $array as $k => $v ){

			$return_array[ $prefix.$k] = $v;

		}

		// how to return data ##
		if ( is_array( $return ) ){

			return array_merge( $return, $return_array );

		}

		// just retrn new values ##
		return $return_array;

	}



	// /**
    //  * Markup object based on %placeholders% and template
    //  *
    //  * @since    2.0.0
    //  * @return   Mixed
    //  */
    // public static function markup_OG( $markup = null, $data = null )
    // {

    //     // sanity ##
    //     if (
    //         is_null( $markup )
    //         || is_null( $data )
    //         ||
    //         (
    //             ! is_array( $data )
    //             && ! is_object( $data )
    //         )
    //     ) {

    //         helper::log( 'e:>missing parameters' );

    //         return false;

    //     }

    //     #helper::log( $data );
	// 	#helper::log( $markup );

	// 	// @todo -- wrapping markup should be applied - $ags['wrap] ##
	// 	h::log( 't:>wrapping markup should be applied from $ags->wrap with placeholder %content%' );

	// 	// @todo -- this should really deal with arrays of data ##
	// 	h::log( 't:>extend to accept and validate arrays of data...' );

	// 	// get the markup ##
	// 	$string = $markup;

    //     // format markup with translated data ##
    //     foreach( $data as $key => $value ) {

	// 		// check on the value ##
	// 		// h::log( 'key: '.$key.' / value: '.$value );

    //         // only replace keys found in markup ##
    //         if ( false === strpos( $string, '%'.$key.'%' ) ) {

    //             #helper::log( 'skipping '.$key );

    //             continue ;

	// 		}

	// 		// template replacement ##
	// 		$string = str_replace( '%'.$key.'%', $value, $string );

	// 	}

    //     // h::log( $string );

    //     // return markup ##
    //     return $string;

	// }
	

	/**
	 * Check if array contains other arrays
	 * 
	 * 
	 * @since 4.1.0
	*/
	public static function is_array_of_arrays( $array = null ) {

		// h::log( $array );

		// sanity ##
		if(
			is_null( $array )
			|| ! is_array( $array )
		){

			h::log( 'e:>Error in passed args or not array' );

			return false;

		}

		if (
			isset( $array[0] )
			&& is_array( $array[0] )
		){

			// h::log( 'd:>is_array' );

			return true;

		}

		// foreach ( $array as $key => $value ) {

		// 	if ( is_array( $value ) ) {

		// 		h::log( 'd:>is_array' );

		// 		return $key;

		// 	}
			  
		// }
		
		return false;
	  
	}



	public static function get_context(){

		// sanity ##
		if (
			! isset( self::$args )
			|| ! isset( self::$args['context'] )
			|| ! isset( self::$args['task'] )
		){

			h::log( 'd:>No context / task available' );

			return false;

		}

		return sprintf( 'Context: "%s" Task: "%s"', self::$args['context'], self::$args['task'] );

	}




    public static function minify( $string = null, $type = 'js' )
    {

        // if debugging, do not minify ##
        if ( 
			class_exists( 'q_theme' )
			&& \q_theme::$debug 
		) {

            return $string;

        }

        switch ( $type ) {

            case "css" :

                $string = asset\minifier::css( $string );

                break ;

            case "js" :
            default :

                $string = asset\minifier::javascript( $string );

                break ;

        }

        // kick back ##
        return $string;

    }




    /**
    * Strip unwated tags and shortcodes from the_content
    *
    * @since       1.4.4
    * @return      String
    */
    public static function clean( $string = null )
    {

        // bypass ##
        return $string;

        // sanity check ##
        if ( is_null ( $string ) ) { return false; }

        // do some laundry ##
        $string = strip_tags( $string, '<a><ul><li><strong><p><blockquote><italic>' );

        // kick back the cleaned string ##
        return $string;

	}
	


	/**
	 * Check if a string starts with a specific string
	 * 
	 * @since 4.1.0
	*/
	/*
	public static function starts_with( $haystack = null, $needle = null ){

		// sanity ##
		if (
			is_null( $haystack )
			|| is_null( $needle )
		){
			
			h::log('e:>Error in passed params');

			return false;

		}

		$length = strlen( $needle );
		
		return ( substr( $haystack, 0, $length ) === $needle );
	 
	}
	*/



	/**
	 * Check if a string ends with a specific string
	 * 
	 * @since 4.1.0
	*/
	/*
	public static function ends_with( $haystack = null, $needle = null ){

		// sanity ##
		if (
			is_null( $haystack )
			|| is_null( $needle )
		){
			
			h::log('e:>Error in passed params');

			return false;

		}

	    $length = strlen( $needle );
		
		if ( $length == 0 ) {

        	return true;
		
		}

		return ( substr( $haystack, -$length ) === $needle );

	}
	*/


	/**
	 * 
	 * @link https://www.php.net/manual/en/function.parse-str.php
	*/
	/*
	public static function parse_str( $string = null ) {

		# result array
		$array = array();
	  
		# split on outer delimiter
		$pairs = explode( '&', $string );
	  
		# loop through each pair
		foreach ( $pairs as $i ) {

			# split into name and value
			list( $key, $value ) = explode( '=', $i, 2 );

			// what about array values ##
			// example -- sm:medium, lg:large
			if( false !== strpos( $value, ':' ) ){

				// temp array ##
				$value_array = [];	

				// split value into an array at "," ##
				$value_pairs = explode( ',', str_replace( ' ', '', $value ) );

				// h::log( $value_pairs );

				# loop through each pair
				foreach ( $value_pairs as $v_pair ) {

					// h::log( $v_pair ); // 'sm:medium'

					# split into name and value
					list( $value_key, $value_value ) = explode( ':', $v_pair, 2 );

					$value_array[ $value_key ] = $value_value;

				}

				// check if we have an array ##
				if ( is_array( $value_array ) ){

					$value = $value_array;

				}

			}
		 
			// $key might be in part__part format, so check ##
			if( false !== strpos( $key, '->' ) ){

				// explode, max 2 parts ##
				$md_key = explode( '->', $key, 2 );

				# if name already exists
				if( isset( $array[ $md_key[0] ][ $md_key[1] ] ) ) {

					# stick multiple values into an array
					if( is_array( $array[ $md_key[0] ][ $md_key[1] ] ) ) {
					
						$array[ $md_key[0] ][ $md_key[1] ][] = $value;
					
					} else {
					
						$array[ $md_key[0] ][ $md_key[1] ] = array( $array[ $md_key[0] ][ $md_key[1] ], $value );
					
					}

				# otherwise, simply stick it in a scalar
				} else {

					$array[ $md_key[0] ][ $md_key[1] ] = $value;

				}

			} else {

				# if name already exists
				if( isset($array[$key]) ) {

					# stick multiple values into an array
					if( is_array($array[$key]) ) {
					
						$array[$key][] = $value;
					
					} else {
					
						$array[$key] = array($array[$key], $value);
					
					}

				# otherwise, simply stick it in a scalar
				} else {

					$array[$key] = $value;

				}
			  
			}
		}
	  
		# return result array
		return $array;

	  }
	  */
	  


}
