<?php

namespace Q\willow\core;

use Q\willow\core;

class method {
    
    /**
     * Sanitize user input data using WordPress functions
     * 
     * @since       0.1
     * @param       string      $value      Value to sanitize
     * @param       string      $type       Type of value ( email, user, int, key, text[default] )
     * @link        http://codex.wordpress.org/Validating_Sanitizing_and_Escaping_User_Data
     * @link        http://wp.tutsplus.com/tutorials/creative-coding/data-sanitization-and-validation-with-wordpress/
     * @return      string      HTML output
     */
    public static function sanitize( $value = null, $type = 'text' ){
        
        // check submitted data ##
        if ( is_null( $value ) ) {
            
            return false;
            
        }
        
        switch ( $type ) {
            
            case( 'email' ):
            
                return \sanitize_email( $value );
                break;
            
            case( 'user' ):
            
                return \sanitize_user( $value );
                break;
            
            case( 'integer' ):
            
                return intval( $value );
                break;
            
            case( 'filename' ):
            
                return \sanitize_file_name( $value );
                break;
            
            case( 'key' ):
            
                return self::sanitize_key( $value ); // altered version of wp sanitize_key
                break;
			
			case( 'php_class' ):

				return self::php_class( $value );
				break;

			case( 'php_namespace' ):

				return self::php_namespace( $value );
				break;

			case( 'php_function' ):

				return self::php_function( $value );
				break;

            case( 'sql' ):
                
                return \esc_sql( $value );
                break;
            
            case( 'stripslashes' ):
                
                return preg_replace("~\\\\+([\"\'\\x00\\\\])~", "$1", $value);
                #stripslashes( $value );
                break;
            
            case( 'none' ):
                
                return $value;
                break;
            
            case( 'text' ):
            default;
                    
                // text validation
                return \sanitize_text_field( $value );
                break;
                
        }
        
	}


	/**
    * Sanitizes a php namespace
    *
    * @since 1.3.0
    * @param string $key String key
    * @return string Sanitized key
    */
    public static function php_namespace( $key = null ){
        
        // sanity check ##
        if ( ! $key ) { return false; }
        
        // scan the key for allowed characters ##
        $key = preg_replace( '^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*(\\\\[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*)*$', '', $key );
        
        // return the key ##
        return $key;
        
	}


	
	/**
    * Sanitizes a php function name
    *
    * @since 1.3.0
    * @param string $key String key
    * @return string Sanitized key
    */
    public static function php_function( $key = null ){
        
        // sanity check ##
        if ( ! $key ) { return false; }
        
        // scan the key for allowed characters ##
        $key = preg_replace( '/[^A-Za-z0-9-_]+/', '', $key );
        
        // return the key ##
        return $key;
        
	}



    /**
    * Sanitizes a php class name
    *
    * @since 1.3.0
    * @param string $key String key
    * @return string Sanitized key
    */
    public static function php_class( $key = null ){
        
        // sanity check ##
        if ( ! $key ) { return false; }
        
        // scan the key for allowed characters ##
        $key = preg_replace( '/[^A-Za-z0-9-\\\\_]+/', '', $key );
        
        // return the key ##
        return $key;
        
	}

    
    
    /**
    * Sanitizes a string key.
    *
    * @since 1.3.0
    * @param string $key String key
    * @return string Sanitized key
    */
    public static function sanitize_key( $key = null ){
        
        // sanity check ##
        if ( ! $key ) { return false; }
        
        // scan the key for allowed characters ##
        $key = preg_replace( '/[^a-zA-Z0-9_\-~!$^+]/', '', $key );
        
        // return the key ##
        return $key;
		
	}

	

	/**
     * Get Q template name, if set - else return WP global
     * 
     * 
     */
    public static function template(){

        if( ! isset( $GLOBALS['q_template'] ) ) {

            // w__log( 'e:>Page template empty' );
            
			// return false;
			
			// changes to return WP template -- check for introduced issues ##
			return str_replace( [ '.php', '.willow' ], '', \get_page_template_slug() );

        } else {

            // w__log( 'Page template: '.$GLOBALS['q_template'] );

            return str_replace( [ '.php', '.willow' ], '', $GLOBALS['q_template'] );        

        }

	}


	/**
     * Get Q template format - normally .php or .willow
     * 
	 * @since 4.1.0
     */
    public static function template_format(){

        if( ! isset( $GLOBALS['q_template'] ) ) {

			// changed to return WP template -- check for introduced issues ##
			$template = \get_page_template_slug();

        } else {

            // w__log( 'Page template: '.$GLOBALS['q_template'] );

            $template = $GLOBALS['q_template'];        

		}
		
		// w__log( 'e:>Template: "'.$template.'"' );

		$extension = self::file_extension( $template );

		// w__log( 'e:>Extension: "'.$extension.'"' );

		// kick back ##
		return $extension;

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


	
	public static function file_put_array( $path, $array ){

		if ( is_array( $array ) ){

			$contents = self::var_export_short( $array, true );
			// $contents = var_export( $array, true );

			// stripslashes ## .. hmmm ##
			$contents = str_replace( '\\', '', $contents );

			// w__log( 'd:>Array data good, saving to file' );

			// save in php as an array, ready to return ##
			file_put_contents( $path, "<?php\n return {$contents};\n") ;
			
			// done ##
			return true;

		}

		w__log( 'e:>Error with data format, config file NOT saved' );
		
		// failed ##
		return false;

	}


	public static function var_export_short( $data, $return = true ){

		$dump = var_export($data, true);

		$dump = preg_replace('#(?:\A|\n)([ ]*)array \(#i', '[', $dump); // Starts
		$dump = preg_replace('#\n([ ]*)\),#', "\n$1],", $dump); // Ends
		$dump = preg_replace('#=> \[\n\s+\],\n#', "=> [],\n", $dump); // Empties

		if (gettype($data) == 'object') { // Deal with object states
			$dump = str_replace('__set_state(array(', '__set_state([', $dump);
			$dump = preg_replace('#\)\)$#', "])", $dump);
		} else { 
			$dump = preg_replace('#\)$#', "]", $dump);
		}

		if ($return===true) {
			return $dump;
		} else {
			echo $dump;
		}

	}



	public static function tab2space( $line, $tab = 4, $nbsp = FALSE ){

		while (($t = mb_strpos($line,"\t")) !== FALSE) {
			
			$preTab = $t?mb_substr($line, 0, $t):'';
			$line = $preTab . str_repeat($nbsp?chr(7):' ', $tab-(mb_strlen($preTab)%$tab)) . mb_substr($line, $t+1);
		}
		
		return  $nbsp?str_replace($nbsp?chr(7):' ', '&nbsp;', $line):$line;

	}



	/**
	 * Get string between two placeholders
	 * 
	 * @link 	https://stackoverflow.com/questions/5696412/how-to-get-a-substring-between-two-strings-in-php
	 * @since 4.1.0
	*/
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

		// w__log( 'string: '.$string );

		// kick back ##
		return $string;
	
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
     * Recursive pass args 
     * 
     * @link    https://mekshq.com/recursive-wp-parse-args-wordpress-function/
     */
    public static function parse_args( &$args, $defaults ){

		// sanity ##
		if(
			! $defaults
		){

			// w__log( 'e:>No $defaults passed to method' );

			return $args; // ?? TODO, is this good ? 

		}

        $args = (array) $args;
        $defaults = (array) $defaults;
        $result = $defaults;
        
        foreach ( $args as $k => &$v ) {
            if ( is_array( $v ) && isset( $result[ $k ] ) ) {
                $result[ $k ] = self::parse_args( $v, $result[ $k ] );
            } else {
                $result[ $k ] = $v;
            }
        }

        return $result;

	}
	


	
	/**
	 * 
	 * @link https://www.php.net/manual/en/function.parse-str.php
	*/
	public static function parse_str( $string = null ){

		// sanity ##
		if(
			is_null( $string )
			|| ! is_string( $string )
		){
			
			w__log( 'e:>Passed string empty or bad format' );

			return false;

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

			w__log( 'e:>Passed string format does not include asssignment operator "'.$operator_assign.'" --> '.$string );

			return false;

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
			
				w__log( '$i is empty ~~ "'.$i.'"' );

				continue;
			
			}

			if ( 
				// ! $i
				// || ! trim( $i )
				false === strpos( $i, $operator_assign ) 
			){
			
				w__log( '$i does not contain "'.$operator_assign.'" ~~ "'.$i.'"' );

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
	 * Debug Calling class + method / function 
	 * 
	 * @since 	4.0.0
	 */
	public static function backtrace( $args = null ){

		// default args ##
		$level = isset( $args['level'] ) ? $args['level'] : 1 ; // direct caller ##

		// check we have a result ##
		$backtrace = debug_backtrace();

		if (
			! isset( $backtrace[$level] )
			// || ! isset( $backtrace[$level]['class'] )
			// || ! isset( $backtrace[$level]['function'] )
		) {

			return false;

		}

		// get defined level of data ##
		$caller = $backtrace[$level];

		// class::function() ##
		if ( 
			isset( $args['return'] ) 
			&& 'class_function' == $args['return'] 
			// && isset( $caller['class'] )
			// && isset( $caller['function'] )
		) {

			return sprintf(
				__( '%s%s()', 'Q' )
				,  	isset($caller['class']) ? $caller['class'].'::' : null
				,   $caller['function']
			);

		}

		// config class_function() ##
		if ( 
			isset( $args['return'] ) 
			&& 'config' == $args['return'] 
			// && isset( $caller['class'] )
			// && isset( $caller['function'] )
		) {

			return sprintf(
				__( '%s%s()', 'Q' )
				,  	isset($caller['class']) ? $caller['class'].'_' : null
				,   $caller['function']
			);

		}

		// file::line() ##
		if ( 
			isset( $args['return'] ) 
			&& 'file_line' == $args['return'] 
			&& isset( $caller['file'] )
			&& isset( $caller['line'] )
		) {

			return sprintf(
				__( '%s:%d', 'Q' )
				,   $caller['file']
				,   $caller['line']
			);

		}

		// specific value ##
		if ( 
			isset( $args['return'] ) 
			&& isset( $caller[$args['return']] )
		) {

			return sprintf(
				__( '%s', 'Q' )
				,  $caller[$args['return']] 
			);

		}

		// default - everything ##
		return sprintf(
			__( '%s%s() %s:%d', 'Q' )
			,   isset($caller['class']) ? $caller['class'].'::' : ''
			,   $caller['function']
			,   isset( $caller['file'] ) ? $caller['file'] : 'n'
			,   isset( $caller['line'] ) ? $caller['line'] : 'x'
		);

	}



	public static function array_search( $field = null, $value = null, $array = null ) {

		// sanity ##
		if (
			is_null( $field )
			|| is_null( $value )
			|| is_null( $array )
			|| ! is_array( $array )
		){

			w__log( 'e:>Error in passed params' );

			return false;

		}

        foreach ( $array as $key => $val ) {
        
            if ( $val[$field] === $value ) {
        
                return $key;
        
            }
        
        }
        
        return null;

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
			render\method::chop( 
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
	 * Check is key_exists in MD array
	 * 
	 * @since 	1.2.0
	 * @return	Boolean
	*/
	public static function array_key_exists( array $array, $key ){

		// is in base array?
		if ( array_key_exists( $key, $array ) ) {
			return true;
		}
	
		// check arrays contained in this array
		foreach ( $array as $element ) {

			if ( is_array( $element ) ) {

				if ( self::array_key_exists( $element, $key ) ) {

					return true;

				}
			
			}
	
		}
	
		return false;
	}


	
    /**
     * Save a value to the options table, either updating or creating a new key
     * 
     * @since       1.5.0
     * @return      Void
     */
    public static function add_update_option( $option_name, $new_value, $deprecated = '', $autoload = 'no' ) 
    {
    
        if ( \get_option( $option_name ) != $new_value ) {

            \update_option( $option_name, $new_value );

        } else {

            \add_option( $option_name, $new_value, $deprecated, $autoload );

        }
    
    }
	


	/**
	 * Convert an array to an object
	 * 
	 * @since 	1.5.0
	 * @return 	Mixed
	*/
    public static function array_to_object( $array ) {
        
        if ( ! is_array( $array ) ) {

            return $array;

        }
    
        $object = new \stdClass();

        if ( is_array( $array ) && count( $array ) > 0 ) {

            foreach ( $array as $name => $value ) {

                $name = strtolower( trim( $name ) );

                if ( ! empty( $name ) ) {

                    $object->$name = self::array_to_object( $value );

                }

            }

            return $object;

        } else {
          
            return false;
        
        }

	}

}
