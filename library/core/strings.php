<?php

namespace willow\core;

use willow\core\helper as h;
use willow;

class strings {

	/**
	 * Make a string.. because, sometimes you need to
	 * 
	 * @since 1.5.0
	*/
	public static function make( int $length = 64, string $keyspace = ' 0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ' ): string {

		if ($length < 1) {
			throw new \RangeException("Length must be a positive integer");
		}
		$pieces = [];
		$max = mb_strlen($keyspace, '8bit') - 1;
		for ($i = 0; $i < $length; ++$i) {
			$pieces []= $keyspace[random_int(0, $max)];
		}
		return implode('', $pieces);

	}


    /**
     * Format passed date value
     *
     * @since   2.0.0
     * @return  Mixed String
     */
    public static function date( $array = null ){

        // test ##
        #w__log( $array );

        // did we pass anything ##
        if ( ! $array ) {

            #w__log( 'kicked 1' );

            return false;

        }

        $return = false;

        // loop over array of date options ##
        foreach( $array as $key => $value ) {

            #w__log( $value );

            // nothing happening ? ##
            if ( false === $value['date'] ) {

                #w__log( 'kicked 2' );

                continue;

            }

            if ( 'end' == $key ) {

                // w__log( 'Formatting end date: '.$value['date'] );

                // if start date and end date are the same, we need to just return the start date and start - end times ##
                if (
                    // $array['start']['date'] == $array['end']['date']
                    date( $value['format'], strtotime( $array['start']['date'] ) ) == date( $value['format'], strtotime( $array['end']['date'] ) )
                ) {

                    // w__log( 'Start and end dates match, return time' );

                    // use end date ##
                    $date = ' '.date( 'g:i:a', strtotime( $array['start']['date'] ) ) .' - '. date( 'g:i:a', strtotime( $array['end']['date'] ) );

                } else {

                    // w__log( 'Start and end dates do not match..' );

                    // use end date ##
                    $date = ' - '.date( $value['format'], strtotime( $value['date'] ) );

                }

            } else {

                // w__log( 'Formatting start date' );

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
    public static function remove_style( $input = null ){

        if ( is_null ( $input ) ) { return false; }

        return preg_replace( '/(<[^>]+) style=".*?"/i', '$1', $input );

    }

    public static function rip_tags($string){

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

    public static function chop( $content, $length = 0, $preprend = '...' ){

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

	public static function file_extension( $string = null ){

		// sanity ##
		if( is_null( $string ) ){

			w__log( 'e:>No string passed to method' );

			return false;

		}

		$n = strrpos( $string, "." );

		return ( $n === false ) ? "" : substr( $string, $n+1 );
		
	}

    /**
    * Strip unwated tags and shortcodes from the_content
    *
    * @since       1.4.4
    * @return      String
    */
    public static function clean( string $string = null ){

        // bypass ##
        return $string;

        // sanity check ##
        if ( is_null ( $string ) ) { return false; }

        // do some laundry ##
        $string = strip_tags( $string, '<a><ul><li><strong><p><blockquote><italic>' );

        // kick back the cleaned string ##
        return $string;

	}

	public static function substr_last( string $string = null ){

		// sanity ##
		if ( is_null( $string ) ){ return false; }

		return mb_substr( trim( $string ), -1, 'utf-8' ); 

	}

	public static function substr_first( string $string = null ){

		// sanity ##
		if ( is_null( $string ) ){ return false; }

		return mb_substr( trim( $string ), 0, 1, 'utf-8' ); 

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

			w__log( 'Error in passed params' );

			return false;

		}

		// get string ##
		$string = $args['string'];

		// get search term ##
		$search = \get_search_query();
		// w__log( $search );

        // $string = $args['string']; #\get_the_content();
        $keys = implode( '|', explode( ' ', $search ) );
		$string = preg_replace( '/(' . $keys .')/iu', '<mark>\0</mark>', $string );

		// get text length limit ##
		$length = isset( $args[ 'length' ] ) ? $args['length'] : 200 ;

		// get first occurance of search string ##
		$position = strpos($string, $search );

		// w__log( 'string pos: '.$position );

		if ( ( $length / 2 ) > $position ) {

			// w__log( 'first search term is less than 100 chars in, so return first 200 chars..' );

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
     * Markup object based on {{ placeholders }} and template
	 * This feature is not for formatting data, just applying markup to pre-formatted data
     *
     * @since    2.0.0
     * @return   Mixed
     */
    public static function markup( $markup = null, $data = null, $args = null ){

        // sanity ##
        if (
            is_null( $markup )
            || is_null( $data )
            || (
                ! is_array( $data )
                && ! is_object( $data )
            )
        ) {

            w__log( 'e:>missing parameters' );

            return false;

		}

		if (
			function_exists( 'willow' )
		){

			// variable replacement -- regex way ##
			$open = \willow()->tags->g( 'var_o' );
			$close = \willow()->tags->g( 'var_c' );

		} else {

			\w__log( 'e:>Willow Library Missing, using presumed variable tags {{ xxx }}' );

			$open = '{{ ';
			$close = ' }}';

		}
		
		// capture missing placeholders ##
		// $capture = [];

        // // w__log( $data );
		// w__log( $markup );
		// w__log( $data );
		// w__log( 't:>replace {{ with tag::var_o' );

		// empty ##
		$return = '';

        // format markup with translated data ##
        foreach( $data as $key => $value ) {

			if (
				is_array( $value )
			){

				// check on the value ##
				// w__log( 'd:>key: '.$key.' is array - going deeper..' );

				$return_inner = $markup;

				foreach( $value as $k => $v ) {

					// $string_inner = $markup;

					// check on the value ##
					// w__log( 'd:>key: '.$k.' / value: '.$v );

					// only replace keys found in markup ##
					if ( false === strpos( $return_inner, $open.$k.$close ) ) {

						// w__log( 'd:>skipping '.$k );
		
						continue ;
		
					}

					// template replacement ##
					$return_inner = str_replace( $open.$k.$close, $v, $return_inner );

				}

				$return .= $return_inner;

				continue;

			}

			// get new markup row ##
			$return .= $markup;

			// check on the value ##
			// w__log( 'd:>key: '.$key.' / value: '.$value );

            // only replace keys found in markup ##
            if ( false === strpos( $return, $open.$key.$close ) ) {

                // w__log( 'd:>skipping '.$key );

                continue ;

			}

			// template replacement ##
			$return = str_replace( $open.$key.$close, $value, $return );

		}

		// w__log( $return );

		// wrap string in defined string ?? ##
		if ( isset( $args['wrap'] ) ) {

			// w__log( 'd:>wrapping string before return: '.$args['wrap'] );

			// template replacement ##
			$return = str_replace( $open.'template'.$close, $return, $args['wrap'] );

		}

        // w__log( $return );

        // return markup ##
        return $return;

	}

	public static function str_replace_first( $find = null, $replace = null, $subject = null, $limit = 1 ) {

		// @todo - sanity ##

		// check is $find is in $ubject and return start position ##
		$pos = strpos( $subject, $find );

		// found ##
		if ( $pos !== false ) {

			return substr_replace( $subject, $replace, $pos, strlen( $find ) );

		}
		
		// kick it back ##
		return $subject;

	}

	/**
	 * Make a nice hash ( of it.. )
	 * 
	 * @since 	1.2.0
	 * @return 	String
	*/
	public static function hash( $length = 10 ){
			
		$token = "";
		$codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
		$codeAlphabet.= "0123456789";
		$max = strlen($codeAlphabet); // edited

		for ($i=0; $i < $length; $i++) {
			$token .= $codeAlphabet[ self::hash_helper( 0, $max-1 ) ];
		}

		return $token;

	}

	/**
	 * Hash Helper
	 * 
	 * @since 	1.2.0
	 * @return 	String
	*/
	public static function hash_helper( $min, $max ){

		$range = $max - $min;
		if ($range < 1) return $min; // not so random...
		$log = ceil(log($range, 2));
		$bytes = (int) ($log / 8) + 1; // length in bytes
		$bits = (int) $log + 1; // length in bits
		$filter = (int) (1 << $bits) - 1; // set all lower bits to 1
		do {
			$rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
			$rnd = $rnd & $filter; // discard irrelevant bits
		} while ($rnd > $range);

		return $min + $rnd;

	}
	
	/**
	 * Parse string
	 * 
	 * @link https://www.php.net/manual/en/function.parse-str.php
	*/
	public static function parse( $string = null ){

		// get args ##
		$_args = \willow()->get( '_args' );
		// w__log( $_args );

		// sanity ##
		if(
			is_null( $string )
			|| ! is_string( $string )
		){
			
			w__log( 'e:>Passed string empty or bad format' );

			return $string;

		}

		// w__log($string);

		// delimiters ##
		$operator_assign = '=';
		$operator_array = '->';
		$delimiter_key = ':';
		$delimiter_and_property = ',';
		$delimiter_and_key = '&';

		// check for "=" delimiter ##
		if( false === strpos( $string, $operator_assign ) ){

			w__log( $_args['task'].'~>n:>Passed string format does not include asssignment operator "'.$operator_assign.'" --> '.$string );

			return $string;

		}

		# result array
		$array = [];
	  
		# split on outer delimiter
		$pairs = explode( $delimiter_and_key, $string );

		// w__log( $pairs );
	  
		# loop through each pair
		foreach ( $pairs as $i ) {

			if ( 
				! $i
				|| ! trim( $i )
				// || true !== strpos( $i, $operator_assign ) 
			){
			
				w__log( 'e:>$i is empty ~~ "'.$i.'"' );

				continue;
			
			}

			if ( 
				// ! $i
				// || ! trim( $i )
				false === strpos( $i, $operator_assign ) 
			){
			
				w__log( 'e:>$i does not contain "'.$operator_assign.'" ~~ "'.$i.'"' );

				continue;
			
			}

			// split into key and value ##
			list( $key, $value ) = explode( $operator_assign, $i, 2 );

			// what about array values ##
			// example -- sm:medium, lg:large
			if( false !== strpos( $value, $delimiter_key ) ){

				// temp array ##
				$value_array = [];	

				// preg_match_all( "~\'[^\"]++\"|[^,]++~", $value, $result );
				// w__log( $result );
				$value_pairs = self::quoted_explode( $value, $delimiter_and_property, '"' );
				// w__log( $value_pairs );

				// split value into an array at "," ##
				// $value_pairs = explode( $delimiter_and_property, $value );

				// w__log( $value_pairs );

				# loop through each pair
				foreach ( $value_pairs as $v_pair ) {

					// w__log( 'e:>'.$v_pair ); // 'sm:medium'

					# split into name and value
					list( $value_key, $value_value ) = explode( $delimiter_key, $v_pair, 2 );

					$value_array[ $value_key ] = $value_value;

				}

				// check if we have an array ##
				if ( is_array( $value_array ) ){

					$value = $value_array;

				}

			}
		 
			// $key might be in part__part format, so check ##
			if( false !== strpos( $key, $operator_array ) ){

				// explode, max 2 parts ##
				$md_key = explode( $operator_array, $key, 2 );

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

		// w__log( $array );
	  
		# return result array
		return $array;

	}

	/**
	 * Regex Escape values 
	*/
	public static function regex_escape( $subject ){

		return str_replace( array( '\\', '^', '-', ']' ), array( '\\\\', '\\^', '\\-', '\\]' ), $subject );
	
	}

	/**
	 * Explode string, while respecting delimiters
	 * 
	 * @link https://stackoverflow.com/questions/3264775/an-explode-function-that-ignores-characters-inside-quotes/13755505#13755505
	*/
	public static function quoted_explode( $subject, $delimiter = ',', $quotes = '\"' ){
		$clauses[] = '[^'.self::regex_escape( $delimiter.$quotes ).']';

		foreach( str_split( $quotes) as $quote ) {

			$quote = self::regex_escape( $quote );
			$clauses[] = "[$quote][^$quote]*[$quote]";

		}

		$regex = '(?:'.implode('|', $clauses).')+';
		
		preg_match_all( '/'.str_replace('/', '\\/', $regex).'/', $subject, $matches );

		return $matches[0];

	}
	
	/**
	 * search string by array
	 * 
	 * @link	https://stackoverflow.com/questions/6284553/using-an-array-as-needles-in-strpos
	 */
	public static function strposa( $haystack, $needle, $offset=0 ){
		if( ! is_array( $needle ) ) {
			
			$needle = array($needle);

		}

		foreach( $needle as $query ) {

			// stop on first true result ##
			if( strpos( $haystack, $query, $offset ) !== false) return true;
		
		}

		return false;

	}

	public static function get_acronym( $string = null, $length = 10 ){

		// sanity ##
		if ( is_null( $string ) ) { return false; }

		return 
			self::chop( 
				str_replace(
					[ '-', '_' ], "", // replace ##
					strtolower( 
						array_reduce( 
							str_word_count( $string, 1), function($res , $w){ 
								return $res . $w[0]; 
							} 
						)
					)
				),
				$length, '' // chop ##
			);

	}

	

	/**
	 * Check if a string starts with a specific string
	 * 
	 * @since 4.1.0
	*/
	public static function starts_with( $haystack = null, $needle = null ){

		// sanity ##
		if (
			is_null( $haystack )
			|| is_null( $needle )
		){
			
			w__log('e:>Error in passed params');

			return false;

		}

		$length = strlen( $needle );

		// remove white spaces and line breaks ##
	    // $haystack = preg_replace( '/\s*/m', '', $haystack );
		
		return ( substr( $haystack, 0, $length ) === $needle );
	 
	}

	/**
	 * Check if a string ends with a specific string
	 * 
	 * @since 4.1.0
	*/
	public static function ends_with( $haystack = null, $needle = null ){

		// sanity ##
		if (
			is_null( $haystack )
			|| is_null( $needle )
		){
			
			w__log('e:>Error in passed params');

			return false;

		}

	    $length = strlen( $needle );
		
		if ( $length == 0 ) {

        	return true;
		
		}

		// remove white spaces and line breaks ##
		// $haystack = preg_replace( '/\s*/m', '', $haystack );

		return ( substr( $haystack, -$length ) === $needle );

	}

	/**
	 * 
	 * 
	 * @link https://stackoverflow.com/questions/27078259/get-string-between-find-all-occurrences-php/27078384#27078384
	*/
	public static function strings_between( $str, $startDelimiter, $endDelimiter ){

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

	/**
	 * Get string between two placeholders
	 * 
	 * @link 	https://stackoverflow.com/questions/5696412/how-to-get-a-substring-between-two-strings-in-php
	 * @since 4.1.0
	*/
	public static function between( $string, $start, $end, $inclusive = false ){ 
		
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

		// w__log( 'string: '.$string );

		// kick back ##
		return $string;
	
	} 

	public static function tab2space( $line, $tab = 4, $nbsp = FALSE ){

		while (($t = mb_strpos($line,"\t")) !== FALSE) {
			
			$preTab = $t?mb_substr($line, 0, $t):'';
			$line = $preTab . str_repeat($nbsp?chr(7):' ', $tab-(mb_strlen($preTab)%$tab)) . mb_substr($line, $t+1);
		}
		
		return  $nbsp?str_replace($nbsp?chr(7):' ', '&nbsp;', $line):$line;

	}

}
