<?php

namespace q\willow;

use q\willow;
use q\willow\core;
use q\core\helper as h;

// use q\ui;
use q\render; // @TODO

class markup extends willow\parse {

    /**
     * Get all tags of defined $type from passed $string 
     *  
     */
    public static function get( string $string = null, $type = 'variable' ) {
        
        // sanity ##
        if (
			is_null( $string ) 
			|| is_null( $type )
        ) {

			// log ##
			h::log( self::$args['task'].'~>e:>No string or type value passed to method' );

            return false;

		}
		
		switch ( $type ) {

			default :
			case "variable" :

				// note, we trim() white space off tags, as this is handled by the regex ##
				$open = trim( tags::g( 'var_o' ) );
				$close = trim( tags::g( 'var_c' ) );

				// h::log( 'open: '.$open );

				$regex_find = \apply_filters( 
					'q/render/parse/variable/get', 
					// '~\{{\s(.*?)\s\}}~' 
					"~\\$open\s+(.*?)\s+\\$close~" // note:: added "+" for multiple whitespaces.. not sure it's good yet...
				);

			break ;

		}

		// $regex_find = \apply_filters( 'q/render/markup/variables/get', '~\{{\s(.*?)\s\}}~' );
		// if ( ! preg_match_all('~\%(\w+)\%~', $string, $matches ) ) {
        if ( ! preg_match_all( $regex_find, $string, $matches ) ) {

			// log ##
			h::log( self::$args['task'].'~>n:>No extra variables found in string to clean up - good!' );

            return false;

        }

        // test ##
        // h::log( $matches[0] );

        // kick back variable array ##
        return $matches[0];

    }



    /**
     * Check if single tag exists 
     * @todo - work on passed params 
     *  
     */
    public static function contains( string $variable = null, $field = null ) {
		
		// if $markup template passed, check there, else check self::$markup ##
		$markup = is_null( $field ) ? self::$markup['template'] : self::$markup[$field] ;

        if ( ! substr_count( $markup, $variable ) ) {

            return false;

        }

        // good ##
        return true;

	}



	/**
     * Markup object based on {{ placeholders }} and template
	 * This feature is not for formatting data, just applying markup to pre-formatted data
     *
     * @since    2.0.0
     * @return   Mixed
     */
    public static function string( $markup = null, $data = null, $args = null )
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
		h::log( 't:>replace {{ with tag::var_o' );

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

		h::log( $args );

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



	/**
     * Edit {{ variable }} in self:$args['markup']
     * 
     */
	/*
    public static function edit( string $from = null, $to = null, $from_type = 'function', $to_type = 'variable' ) {

        // sanity ##
        if (
			is_null( $variable ) 
			|| is_null( $new_variable )
			// || is_null( $type )
		) {

			// log ##
			h::log( self::$args['task'].'~>e:>No variable or new_variable value passed to method' );

            return false;

		}

		// check if variable is correctly formatted --> {{ STRING }} ##
		// $needle_start = '{{ ';
		// $needle_end = ' }}';

		// // what type of variable are we adding ##
		// switch ( $type ) {

		// 	default :
		// 	case "variable" :

				// check if variable is correctly formatted --> {{ STRING }} ##
				$needle_start = tags::g( 'var_o' ); #'{{ ';
				$needle_end = tags::g( 'var_c' ); #' }}';

		// 	break ;

		// }
		
		if (
			! core\method::starts_with( $variable, $needle_start ) 
			|| ! core\method::ends_with( $variable, $needle_end ) 
			|| ! core\method::starts_with( $new_variable, $needle_start ) 
			|| ! core\method::ends_with( $new_variable, $needle_end ) 
        ) {

			// log ##
			h::log( self::$args['task'].'~>e:>Placeholder is not correctly formatted - missing {{ at start or }} at end.' );
			// h::log( 'd:>Placeholder is not correctly formatted - missing {{ at start or end }}.' );

            return false;

		}
		
		// ok - we should be good to search and replace old for new ##
		$string = str_replace( $variable, $new_variable, self::$markup['template'] );

		// test new string ##
		// h::log( 'd:>'.$string );

		// overwrite markup property ##
		self::$markup['template'] = $string;

		// kick back ##
		return true;

	}
	*/
	
	

	/**
     * Set {{ variable }} in self:markup['template'] at defined position
     * 
     */
    public static function set( string $tag = null, $position = null, $type = 'variable' ) { // , $markup = null

		// h::log( 't:>Position based replacement seems shaky, perhaps move to swap method...' );

        // sanity ##
        if (
			is_null( $type ) 
			|| is_null( $tag ) 
			// || is_null( $markup )
			|| is_null( $position )
		) {

			// log ##
			h::log( self::$args['task'].'~>e:Error in data passed to method' );

            return false;

		}
		
		// // what type of variable are we adding ##
		switch ( $type ) {

			default :
			case "variable" :

				// check if variable is correctly formatted --> {{ STRING }} ##
				$needle_start = tags::g( 'var_o' ); #'{{ ';
				$needle_end = tags::g( 'var_c' ); #' }}';

			break ;

		}

        if (
            ! core\method::starts_with( $tag, $needle_start ) 
			|| ! core\method::ends_with( $tag, $needle_end ) 
        ) {

			// log ##
			h::log( self::$args['task'].'~>e:>passed tag: "'.$tag.'" is not correctly formatted - missing {{ at start or }} at end.' );

            return false;

		}
		
		// h::log( 'd:>Adding tag: "'.$tag.'"' );
		// h::log( 'Position: '.$position );

		// todo - sanitize tag value ##

		// add new variable to $template as defined position - don't replace {{ variable }} yet... ##
		$new_template = substr_replace( self::$markup['template'], $tag, $position, 0 );

		// test ##
		// h::log( 'd:>'.$new_template );

		// push back into main stored markup ##
		self::$markup['template'] = $new_template;
		
		// h::log( 'd:>'.$new_template );

		// log ##
		// h::log( self::$args['task'].'~>variable_added:>"'.$tag.'" @position: "'.$position.'" by "'.core\method::backtrace([ 'level' => 2, 'return' => 'function' ]).'"' );

        // positive ##
        return true; #$markup['template'];

    }



	
	/**
     * Set {{ variable }} in self:markup['template'] at defined position
     * 
     */
    public static function swap( string $from = null, string $to = null, $from_type = 'function', $to_type = 'variable' ) { // , $markup = null

        // sanity ##
        if (
			is_null( $to ) 
			|| is_null( $to_type ) 
			|| is_null( $from )
			|| is_null( $from_type ) 
		) {

			// log ##
			h::log( self::$args['task'].'~>e:Error in data passed to method' );

            return false;

		}
		
		// validate to type ##
		switch ( $to_type ) {

			default :
			case "variable" :

				// check if variable is correctly formatted --> {{ STRING }} ##
				$needle_start = tags::g( 'var_o' ); #'{{ ';
				$needle_end = tags::g( 'var_c' ); #' }}';

			break ;

			case "partial" :

				// check if variable is correctly formatted --> {{> STRING }} ##
				$needle_start = tags::g( 'par_o' ); #'{{ ';
				$needle_end = tags::g( 'par_c' ); #' }}';

			break ;

			case "section" :

				// check if variable is correctly formatted --> {{> STRING }} ##
				$needle_start = tags::g( 'sec_o' ); #'{{ ';
				$needle_end = tags::g( 'sec_c' ); #' }}';

			break ;

			case "function" :

				// check if variable is correctly formatted --> {{> STRING }} ##
				$needle_start = tags::g( 'fun_o' ); #'{{ ';
				$needle_end = tags::g( 'fun_c' ); #' }}';

			break ;

			case "comment" :

				// check if variable is correctly formatted --> {{> STRING }} ##
				$needle_start = tags::g( 'com_o' ); #'{{ ';
				$needle_end = tags::g( 'com_c' ); #' }}';

			break ;

		}

        if (
            ! core\method::starts_with( $to, $needle_start ) 
			|| ! core\method::ends_with( $to, $needle_end ) 
        ) {

			// log ##
			h::log( self::$args['task'].'~>e:>tag: "'.$to.'" is not correctly formatted - missing "'.$needle_start.'" at start or "'.$needle_end.'" at end.' );

            return false;

		}

		// validate from type ##
		switch ( $from_type ) {

			default :
			case "variable" :

				// check if variable is correctly formatted --> {{ STRING }} ##
				$needle_start = tags::g( 'var_o' ); #'{{ ';
				$needle_end = tags::g( 'var_c' ); #' }}';

			break ;

			case "partial" :

				// check if variable is correctly formatted --> {{> STRING }} ##
				$needle_start = tags::g( 'par_o' ); #'{{ ';
				$needle_end = tags::g( 'par_c' ); #' }}';

			break ;

			case "comment" :

				// check if variable is correctly formatted --> {{> STRING }} ##
				$needle_start = tags::g( 'com_o' ); #'{{ ';
				$needle_end = tags::g( 'com_c' ); #' }}';

			break ;

			case "section" :

				// check if variable is correctly formatted --> {{> STRING }} ##
				$needle_start = tags::g( 'sec_o' ); #'{{ ';
				$needle_end = tags::g( 'sec_c' ); #' }}';

			break ;

			case "function" :

				// check if variable is correctly formatted --> {{> STRING }} ##
				$needle_start = tags::g( 'fun_o' ); #'{{ ';
				$needle_end = tags::g( 'fun_c' ); #' }}';

			break ;

		}

        if (
            ! core\method::starts_with( $from, $needle_start ) 
			|| ! core\method::ends_with( $from, $needle_end ) 
        ) {

			// log ##
			h::log( self::$args['task'].'~>e:>tag: "'.$from.'" is not correctly formatted - missing "'.$needle_start.'" at start or "'.$needle_end.'" at end.' );

            return false;

		}
		
		// h::log( 'd:>swapping from: "'.$from.'" to: "'.$to.'"' );

		// use strpos to get location of {{ variable }} ##
		// $position = strpos( self::$markup, $to );
		// h::log( 'Position: '.$position );

		// add new variable to $template as defined position - don't replace $from yet... ##
		$new_template = str_replace( $from, $to, self::$markup['template'] );

		// test ##
		// h::log( 'd:>'.$new_template );

		// push back into main stored markup ##
		self::$markup['template'] = $new_template;
		
		// h::log( 'd:>'.$markup );

		// log ##
		// h::log( self::$args['task'].'~>variable_added:>"'.$to.'" @position: "'.$position.'" by "'.core\method::backtrace([ 'level' => 2, 'return' => 'function' ]).'"' );

        // positive ##
        return true; #$markup['template'];

    }



    /**
     * Remove {{ variable }} from self:$args['markup'] array
     * 
     */
    public static function remove( string $variable = null, $markup = null, $type = 'variable' ) {

        // sanity ##
        if (
			is_null( $variable ) 
			|| is_null( $markup )
			|| is_null( $type )
		) {

			// log ##
			h::log( self::$args['task'].'~>e:>No variable or markkup value passed to method' );

            return false;

		}
		
		// h::log( 'remove: '.$variable );

        // check if variable is correctly formatted --> {{ STRING }} ##

		// what type of variable are we adding ##
		switch ( $type ) {

			default :
			case "variable" :

				// check if variable is correctly formatted --> {{ STRING }} ##
				$needle_start = tags::g( 'var_o' ); #'{{ ';
				$needle_end = tags::g( 'var_c' ); #' }}';

			break ;

		}

        if (
            ! core\method::starts_with( $variable, $needle_start ) 
            || ! core\method::ends_with( $variable, $needle_end ) 
        ) {

			// log ##
			h::log( self::$args['task'].'~>e:>Placeholder: "'.$variable.'" is not correctly formatted - missing "{{ " at start or " }}" at end.' );

            return false;

		}
		
		// h::log( 'Removing variable: "'.$variable.'"' );
		// return $markup;

        // remove variable from markup ##
		$markup = 
			str_replace( 
            	$variable, 
            	'', // nada ##
            	$markup
			);
		
		// h::log( 'd:>'.$markup );

		// log ##
		h::log( self::$args['task'].'~>variable_removed:>"'.$variable.'" by "'.\q\core\method::backtrace([ 'level' => 2, 'return' => 'function' ]).'"' );

        // positive ##
        return $markup;

    }



}
