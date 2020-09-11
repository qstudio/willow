<?php

namespace willow\parse;

use willow;
use willow\core;
use willow\core\helper as h;

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
				$open = trim( willow\tags::g( 'var_o' ) );
				$close = trim( willow\tags::g( 'var_c' ) );

				// h::log( 'open: '.$open );

				$regex_find = \apply_filters( 
					'q/render/parse/variable/get', 
					"~\\$open\s+(.*?)\s+\\$close~" // note:: added "+" for multiple whitespaces.. not sure it's good yet...
				);

			break ;

		}

		// $regex_find = \apply_filters( 'q/render/markup/variables/get', '~\{{\s(.*?)\s\}}~' );
		// if ( ! preg_match_all('~\%(\w+)\%~', $string, $matches ) ) {
        if ( ! preg_match_all( $regex_find, $string, $matches ) ) {

			// log ##
			// h::log( 't:>TODO - if no self::$args - set to buffer' );
			// h::log( self::$args['task'].'~>n:>No variables found in string.' );
			// h::log( 'd:>No variables found in string.' );
			// h::log( '$string: "'.$string.'"' );

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
    public static function contains( string $variable = null, $markup = null, $field = null ) {
		
		// if no markup passed, use self::$markup ##
		$markup = is_null( $markup ) ? self::$markup : $markup ;

		// if $markup template passed, check there, else check self::$markup ##
		$string = is_null( $field ) ? self::$markup['template'] : self::$markup[$field] ;

        if ( ! substr_count( $string, $variable ) ) {

            return false;

        }

        // good ##
        return true;

	}

	

	/**
     * Set {{ variable }} in self:markup['template'] at defined position
     * 
     */
    public static function set( string $tag = null, $position = null, $type = 'variable', $process = 'internal' ) { // , $markup = null

		// h::log( 't:>Position based replacement seems shaky, perhaps move to swap method...' );

        // sanity ##
        if (
			is_null( $type ) 
			|| is_null( $tag ) 
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
				$needle_start = willow\tags::g( 'var_o' ); #'{{ ';
				$needle_end = willow\tags::g( 'var_c' ); #' }}';

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
		
		// h::log( 'd:>Setting tag: "'.$tag.'"' );
		// h::log( 'Position: '.$position );

		// find out which markup to affect ##
		switch( $process ){

			default : 
			case "internal" :

				// h::log( 'd:>Swapping markup in self::$markup' );

				// add new variable to $template as defined position - don't replace {{ variable }} yet... ##
				$new_template = substr_replace( self::$markup['template'], $tag, $position, 0 );

				// test ##
				// h::log( 'd:>'.$new_template );

				// push back into main stored markup ##
				self::$markup['template'] = $new_template; // ."\r\n"

			break ;

			case "buffer" :

				// h::log( 'd:>Swapping markup in self::$buffer_markup' );

				// add new variable to $template as defined position - don't replace $from yet... ##
				$new_template = substr_replace( self::$buffer_markup, $tag, $position, 0 );

				// test ##
				// h::log( 'd:>'.$new_template );

				// push back into main stored markup ##
				self::$buffer_markup = $new_template; // ."\r\n"

			break ;

		} 
		
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
    public static function add( string $tag = null, $before = null, $type = 'variable', $process = 'internal' ) { // , $markup = null

		h::log( 't:>TOOO, __deprecate in 1.5.0' );

        // sanity ##
        if (
			is_null( $type ) 
			|| is_null( $tag ) 
			|| is_null( $before )
		) {

			// log ##
			h::log( self::$args['task'].'~>e:Error in data passed to method' );

            return false;

		}

		// validate strings ##
		// @todo - perhaps need to be more liberal or restrictive on this.. will see ##
		if(
			'string' == $type
			&& ! is_string( $tag )
		){

			// log ##
			// h::log( self::$args['task'].'~>e:>tag: "'.$tag.'" is not a correctly formatted '.$type.'' );

			// log ##
			h::log( 'e:>tag: "'.$tag.'" is not a correctly formatted '.$type.'' );

            return false;

		}

		
		// // what type of variable are we adding ##
		switch ( $type ) {

			default :
			case "variable" :

				// check if variable is correctly formatted --> {{ STRING }} ##
				$needle_start = willow\tags::g( 'var_o' ); #'{{ ';
				$needle_end = willow\tags::g( 'var_c' ); #' }}';

			break ;

			case "string" :

				// check if variable is correctly formatted --> {{ STRING }} ##
				$needle_start = '';
				$needle_end = '';

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
		
		// find out which markup to affect ##
		switch( $process ){

			default : 
			case "internal" :

				// $before is a string, which we need to find in markup 
				if( 
					$position = false === strpos( self::$markup['template'], $before )
				){

					h::log( 'e:>Cannot locate "'.$before.'" in internal $markup' );

				}

				// add new variable to $template as defined position - don't replace {{ variable }} yet... ##
				$new_template = substr_replace( self::$markup['template'], $tag, $position );

				// push back into main stored markup ##
				self::$markup['template'] = $new_template."\r\n";

				// log ##
				// h::log( 'd:>Adding tag: "'.$tag.'" @ Position: '.$position.' in internal markup' );

				// test ##
				h::log( 'd:>'.$new_template );

			break ;

			case "buffer" :

				// $before is a string, which we need to find in markup 
				if( 
					$position = false === strpos( self::$buffer_markup, $before )
				){

					h::log( 'e:>Cannot locate "'.$before.'" in buffer $markup: '.self::$buffer_markup );

				}

				// h::log( 'd:>Swapping markup in self::$buffer_markup' );

				// add new variable to $template as defined position - don't replace $from yet... ##
				$new_template = substr_replace( self::$buffer_markup, $tag, $position );

				// test ##
				// h::log( 'd:>'.$new_template );

				// push back into main stored markup ##
				self::$buffer_markup = $new_template."\r\n";

				// log ##
				// h::log( 'd:>Adding tag: "'.$tag.'" @ Position: '.$position.' in buffer markup' );

				// test ##
				h::log( 'd:>'.$new_template );


			break ;

		} 
		
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
    public static function swap( string $from = null, string $to = null, $from_type = 'willow', $to_type = 'variable', $process = 'internal' ) { 

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

		// h::log('d:>from: "'.$from.'"');
		// h::log('d:>to: "'.$to.'"');
		
		// validate to type ##
		switch ( $to_type ) {

			default :
			case "variable" :

				// check if variable is correctly formatted --> {{ STRING }} ##
				$needle_start = willow\tags::g( 'var_o' ); #'{{ ';
				$needle_end = willow\tags::g( 'var_c' ); #' }}';

			break ;

			// allow for string replacements ##
			case "string" :

				// check if variable is correctly formatted --> {{> STRING }} ##
				$needle_start = ''; 
				$needle_end = ''; 

			break;

			case "partial" :

				// check if variable is correctly formatted --> {{> STRING }} ##
				$needle_start = willow\tags::g( 'par_o' ); #'{{> ';
				$needle_end = willow\tags::g( 'par_c' ); #' <}}';

			break ;

			case "loop" :

				// check if variable is correctly formatted --> {{> STRING }} ##
				$needle_start = willow\tags::g( 'loo_o' ); #'{{@ ';
				$needle_end = willow\tags::g( 'loo_c' ); #' /@}}';

			break ;

			case "willow" :

				// check if variable is correctly formatted --> {{> STRING }} ##
				$needle_start = willow\tags::g( 'wil_o' ); #'{{~ ';
				$needle_end = willow\tags::g( 'wil_c' ); #' ~}}';

			break ;

			case "php_function" :

				// check if variable is correctly formatted --> {{> STRING }} ##
				$needle_start = willow\tags::g( 'php_fun_o' ); #'<% ';
				$needle_end = willow\tags::g( 'php_fun_c' ); #' %>';

			break ;

			case "php_variable" :

				// check if php variable is correctly formatted --> {{> STRING }} ##
				$needle_start = willow\tags::g( 'php_var_o' ); // {#
				$needle_end = willow\tags::g( 'php_var_c' ); // #}

			break ;

			case "comment" :

				// check if variable is correctly formatted --> {{> STRING }} ##
				$needle_start = willow\tags::g( 'com_o' ); #'{{! ';
				$needle_end = willow\tags::g( 'com_c' ); #' !}}';

			break ;

		}

		// validate strings ##
		// @todo - perhaps need to be more liberal or restrictive on this.. will see ##
		if(
			'string' == $to_type
			&& ! is_string( $to )
		){

			// log ##
			h::log( self::$args['task'].'~>e:>tag: "'.$to.'" is not a correctly formatted '.$to_type.'' );

			// log ##
			h::log( 'e:>tag: "'.$to.'" is not a correctly formatted '.$to_type.'' );

            return false;

		}

		// trim for regex ##
		$open = trim( $needle_start );
		$close = trim( $needle_end );

		// regex ##
		$regex_find = \apply_filters( 
			'q/willow/parse/markup/regex/find', 
			"/$open\s+(.*?)\s+$close/s"  // note:: added "+" for multiple whitespaces.. not sure it's good yet...
		);

        if (
            // ! core\method::starts_with( $to, $needle_start ) 
			// || ! core\method::ends_with( $to, $needle_end ) 
			! preg_match( $regex_find, $to )
			&& 'string' != $to_type // we can skip strings, as their format was validated earlier ##
        ) {

			// log ##
			h::log( self::$args['task'].'~>e:>tag: "'.$to.'" is not a correctly formatted '.$to_type.' - missing "'.$needle_start.'" at start or "'.$needle_end.'" at end.' );

			// log ##
			h::log( 'e:>tag: "'.$to.'" is not a correctly formatted '.$to_type.' - missing "'.$needle_start.'" at start or "'.$needle_end.'" at end.' );

            return false;

		}

		// validate from type ##
		switch ( $from_type ) {

			default :
			case "willow" :

				// check if variable is correctly formatted --> {{> STRING }} ##
				$needle_start = willow\tags::g( 'wil_o' ); #'{{~ ';
				$needle_end = willow\tags::g( 'wil_c' ); #' ~}}';

			break ;

			// allow for string replacements ##
			case "string" :

				// check if variable is correctly formatted --> {{> STRING }} ##
				$needle_start = '';
				$needle_end = ''; 

			break;

			case "variable" :

				// check if variable is correctly formatted --> {{ STRING }} ##
				$needle_start = willow\tags::g( 'var_o' ); #'{{ ';
				$needle_end = willow\tags::g( 'var_c' ); #' }}';

			break ;

			case "partial" :

				// check if variable is correctly formatted --> {{> STRING }} ##
				$needle_start = willow\tags::g( 'par_o' ); #'{{> ';
				$needle_end = willow\tags::g( 'par_c' ); #' }}';

			break ;

			case "comment" :

				// check if variable is correctly formatted --> {{> STRING }} ##
				$needle_start = willow\tags::g( 'com_o' ); #'{{! ';
				$needle_end = willow\tags::g( 'com_c' ); #' }}';

			break ;

			case "loop" :

				// check if variable is correctly formatted --> {{> STRING }} ##
				$needle_start = willow\tags::g( 'loo_o' ); // '{{@ ';
				$needle_end = willow\tags::g( 'loo_c' ); // ' }}';

			break ;

			case "php_function" :

				// check if variable is correctly formatted --> {{> STRING }} ##
				$needle_start = willow\tags::g( 'php_fun_o' ); #'<< ';
				$needle_end = willow\tags::g( 'php_fun_c' ); #' >>';

			break ;

			case "php_variable" :

				// check if php variable is correctly formatted --> {{> STRING }} ##
				$needle_start = willow\tags::g( 'php_var_o' ); // {#
				$needle_end = willow\tags::g( 'php_var_c' ); // #}

			break ;

		}

		// trim for regex ##
		$open = trim( $needle_start );
		$close = trim( $needle_end );

		// regex ##
		$regex_find = \apply_filters( 
			'q/willow/parse/markup/regex/find', 
			"/$open\s+(.*?)\s+$close/s"  // note:: added "+" for multiple whitespaces.. not sure it's good yet...
			// "/{{#(.*?)\/#}}/s" 
		);

        if (
            // ! core\method::starts_with( $from, $needle_start ) 
			// || ! core\method::ends_with( $from, $needle_end ) 
			! preg_match( $regex_find, $from )
        ) {

			// log ##
			// h::log( self::$args['task'].'~>e:>tag: "'.$from.'" is not a correctly formatted '.$from_type.' - missing "'.$needle_start.'" at start or "'.$needle_end.'" at end.' );

			// log ##
			h::log( 'e:>tag: "'.$from.'" is not a correctly formatted '.$from_type.' -> missing "'.$needle_start.'" at start or "'.$needle_end.'" at end.' );

            return false;

		}
		
		// h::log( 'd:>swapping from: "'.$from.'" to: "'.$to.'"' );

		// use strpos to get location of {{ variable }} ##
		// $position = strpos( self::$markup, $to );
		// h::log( 'Position: '.$position );

		// find out which markup to affect ##
		switch( $process ){

			default : 
			case "internal" :

				// h::log( 'd:>Swapping markup in self::$markup' );

				// add new variable to $template as defined position - don't replace $from yet... ##
				$new_template = str_replace( $from, $to, self::$markup['template'] );

				// test ##
				// h::log( 'd:>'.$new_template );

				// push back into main stored markup ##
				self::$markup['template'] = $new_template;

			break ;

			case "buffer" :

				// h::log( 'd:>Swapping markup in self::$buffer_markup' );

				// add new variable to $template as defined position - don't replace $from yet... ##
				$new_template = str_replace( $from, $to, self::$buffer_markup );

				// test ##
				// h::log( 'd:>'.$new_template );

				// push back into main stored markup ##
				self::$buffer_markup = $new_template;

			break ;

		} 

        // positive ##
        return true;

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
				$needle_start = willow\tags::g( 'var_o' ); #'{{ ';
				$needle_end = willow\tags::g( 'var_c' ); #' }}';

			break ;

			case "comment" :

				// check if variable is correctly formatted --> {{ STRING }} ##
				$needle_start = willow\tags::g( 'com_o' ); #'{{ ';
				$needle_end = willow\tags::g( 'com_c' ); #' }}';

			break ;

		}

        if (
            ! core\method::starts_with( $variable, $needle_start ) 
            || ! core\method::ends_with( $variable, $needle_end ) 
        ) {

			// log ##
			// h::log( self::$args['task'].'~>e:>Placeholder: "'.$variable.'" is not correctly formatted - missing "{{ " at start or " }}" at end.' );
			h::log( 'e:>Placeholder: "'.$variable.'" is not correctly formatted - missing "{{ " at start or " }}" at end.' );

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
		// h::log( self::$args['task'].'~>variable_removed:>"'.$variable.'" by "'.\q\core\method::backtrace([ 'level' => 2, 'return' => 'function' ]).'"' );

		// h::log( 'd~>variable_removed:>"'.$variable.'" by "'.\q\core\method::backtrace([ 'level' => 2, 'return' => 'function' ]).'"' );

        // positive ##
        return $markup;

    }



}
