<?php

namespace willow\parse;

use willow;

class markup {

	private 
		$plugin = false
	;

	public function __construct( willow\plugin $plugin ){

		// grab passed plugin object ## 
		$this->plugin = $plugin;

	}

    /**
     * Get all tags of defined $type from passed $string 
     *  
     */
    public function get( $string = null, $type = 'variable' ) {
        
        // sanity ##
        if (
			is_null( $string ) 
			|| ! is_string( $string )
			|| is_null( $type )
        ) {

			// w__log( willow\core\backtrace::get(['level' => 2]) );
			// w__log( \willow()->get( '_args' ) );
			$task = \willow()->get( '_args' )['task'] ?? 'unknown' ;

			// log ##
			w__log( $task.'~>e:>No string or type value passed to method' );

            return false;

		}
		
		switch ( $type ) {

			default :
			case "variable" :

				// note, we trim() white space off tags, as this is handled by the regex ##
				$open = trim( \willow()->tags->g( 'var_o' ) );
				$close = trim( \willow()->tags->g( 'var_c' ) );

				// w__log( 'open: '.$open );

				$regex_find = \apply_filters( 
					'willow/parse/variable/get', 
					"~\\$open\s+(.*?)\s+\\$close~" // note:: added "+" for multiple whitespaces.. not sure it's good yet...
				);

			break ;

		}

		// $regex_find = \apply_filters( 'q/render/markup/variables/get', '~\{{\s(.*?)\s\}}~' );
		// if ( ! preg_match_all('~\%(\w+)\%~', $string, $matches ) ) {
        if ( ! preg_match_all( $regex_find, $string, $matches ) ) {

			// log ##
			// w__log( 't:>TODO - if no self::$args - set to buffer' );
			// w__log( \willow()->get( '_args')['task'].'~>n:>No variables found in string.' );
			// w__log( 'd:>No variables found in string.' );
			// w__log( '$string: "'.$string.'"' );

            return false;

        }

        // test ##
        // w__log( $matches[0] );

        // kick back variable array ##
        return $matches[0];

    }



    /**
     * Check if single tag exists 
     * @todo - work on passed params 
     *  
     */
    public function contains( string $variable = null, $markup = null, $field = null ) {
		
		// if no markup passed, use \willow()->get( '_markup') ##
		$markup = is_null( $markup ) ? \willow()->get( '_markup') : $markup ;

		// if $markup template passed, check there, else check \willow()->get( '_markup') ##
		$string = is_null( $field ) ? \willow()->get( '_markup')['template'] : \willow()->get( '_markup')[$field] ;

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
    public function set( string $tag = null, $position = null, $type = 'variable', $process = 'secondary' ) { // , $markup = null

		// w__log( 't:>Position based replacement seems shaky, perhaps move to swap method...' );

        // sanity ##
        if (
			is_null( $type ) 
			|| is_null( $tag ) 
			|| is_null( $position )
		) {

			// log ##
			w__log( \willow()->get( '_args')['task'].'~>e:Error in data passed to method' );

            return false;

		}
		
		// // what type of variable are we adding ##
		switch ( $type ) {

			default :
			case "variable" :

				// check if variable is correctly formatted --> {{ STRING }} ##
				$needle_start = \willow()->tags->g( 'var_o' ); #'{{ ';
				$needle_end = \willow()->tags->g( 'var_c' ); #' }}';

			break ;

		}

        if (
            ! willow\core\strings::starts_with( $tag, $needle_start ) 
			|| ! willow\core\strings::ends_with( $tag, $needle_end ) 
        ) {

			// log ##
			w__log( \willow()->get( '_args')['task'].'~>e:>passed tag: "'.$tag.'" is not correctly formatted - missing {{ at start or }} at end.' );

            return false;

		}
		
		// w__log( 'd:>Setting tag: "'.$tag.'"' );
		// w__log( 'Position: '.$position );

		// find out which markup to affect ##
		switch( $process ){

			default : 
			case "secondary" :

				// w__log( 'd:>Swapping markup in \willow()->get( '_markup')' );

				// add new variable to $template as defined position - don't replace {{ variable }} yet... ##
				$new_template = substr_replace( \willow()->get( '_markup')['template'], $tag, $position, 0 );

				// test ##
				// w__log( 'd:>'.$new_template );

				// push back into main stored markup ##
				\willow()->get( '_markup')['template'] = $new_template; // ."\r\n"

			break ;

			case "primary" :

				// w__log( 'd:>Swapping markup in self::$buffer_markup' );

				// add new variable to $template as defined position - don't replace $from yet... ##
				$new_template = substr_replace( \willow()->get('_buffer_markup'), $tag, $position, 0 );

				// test ##
				// w__log( 'd:>'.$new_template );

				// push back into main stored markup ##
				\willow()->set('_buffer_markup', $new_template ); // ."\r\n"

			break ;

		} 
		
		// w__log( 'd:>'.$new_template );

		// log ##
		// w__log( \willow()->get( '_args')['task'].'~>variable_added:>"'.$tag.'" @position: "'.$position.'" by "'.core\backtrace::get([ 'level' => 2, 'return' => 'function' ]).'"' );

        // positive ##
        return true; 

    }
	
	/**
     * Set {{ variable }} in self:markup['template'] at defined position
     * 
     */
    public function add( string $tag = null, $before = null, $type = 'variable', $process = 'secondary' ) { // , $markup = null

		w__log( 't:>TOOO, __deprecate in 1.5.0' );

        // sanity ##
        if (
			is_null( $type ) 
			|| is_null( $tag ) 
			|| is_null( $before )
		) {

			// log ##
			w__log( \willow()->get( '_args')['task'].'~>e:Error in data passed to method' );

            return false;

		}

		// validate strings ##
		// @todo - perhaps need to be more liberal or restrictive on this.. will see ##
		if(
			'string' == $type
			&& ! is_string( $tag )
		){

			// log ##
			// w__log( \willow()->get( '_args')['task'].'~>e:>tag: "'.$tag.'" is not a correctly formatted '.$type.'' );

			// log ##
			w__log( 'e:>tag: "'.$tag.'" is not a correctly formatted '.$type.'' );

            return false;

		}

		
		// // what type of variable are we adding ##
		switch ( $type ) {

			default :
			case "variable" :

				// check if variable is correctly formatted --> {{ STRING }} ##
				$needle_start = \willow()->tags->g( 'var_o' ); #'{{ ';
				$needle_end = \willow()->tags->g( 'var_c' ); #' }}';

			break ;

			case "string" :

				// check if variable is correctly formatted --> {{ STRING }} ##
				$needle_start = '';
				$needle_end = '';

			break ;

		}

        if (
            ! willow\core\strings::starts_with( $tag, $needle_start ) 
			|| ! willow\core\strings::ends_with( $tag, $needle_end ) 
        ) {

			// log ##
			w__log( \willow()->get( '_args')['task'].'~>e:>passed tag: "'.$tag.'" is not correctly formatted - missing {{ at start or }} at end.' );

            return false;

		}
		
		// find out which markup to affect ##
		switch( $process ){

			default : 
			case "secondary" :

				// get the markup array ##
				$markup = \willow()->get( '_markup');

				// $before is a string, which we need to find in markup 
				if( 
					$position = false === strpos( $markup['template'], $before )
				){

					w__log( 'e:>Cannot locate "'.$before.'" in internal $markup' );

				}

				// add new variable to $template as defined position - don't replace {{ variable }} yet... ##
				$new_template = substr_replace( $markup['template'], $tag, $position );

				$markup['template'] = $new_template."\r\n";

				// @TODO - check this works well and arrange better mether do store keys to arrays ###
				// push back into main stored markup ##
				\willow()->set( '_markup', $markup );

				// log ##
				// w__log( 'd:>Adding tag: "'.$tag.'" @ Position: '.$position.' in internal markup' );

				// test ##
				w__log( 'd:>'.$new_template );

			break ;

			case "primary" :

				$buffer_markup = \willow()->get( '_buffer_markup' );

				// $before is a string, which we need to find in markup 
				if( 
					$position = false === strpos( $buffer_markup, $before )
				){

					w__log( 'e:>Cannot locate "'.$before.'" in buffer $markup: '.$buffer_markup );

				}

				// w__log( 'd:>Swapping markup in $buffer_markup' );

				// add new variable to $template as defined position - don't replace $from yet... ##
				$new_template = substr_replace( $buffer_markup, $tag, $position );

				// test ##
				// w__log( 'd:>'.$new_template );

				// push back into main stored markup ##
				\willow()->set( '_buffer_markup', $new_template."\r\n" );

				// log ##
				// w__log( 'd:>Adding tag: "'.$tag.'" @ Position: '.$position.' in buffer markup' );

				// test ##
				w__log( 'd:>'.$new_template );


			break ;

		} 
		
		// w__log( 'd:>'.$new_template );

		// log ##
		// w__log( \willow()->get( '_args')['task'].'~>variable_added:>"'.$tag.'" @position: "'.$position.'" by "'.core\backtrace::get([ 'level' => 2, 'return' => 'function' ]).'"' );

        // positive ##
        return true; #$markup['template'];

    }
	
	/**
     * Set {{ variable }} in self:markup['template'] at defined position
     * 
     */
    public function swap( string $from = null, string $to = null, $from_type = 'willow', $to_type = 'variable', $process = 'secondary' ) { 

        // sanity ##
        if (
			is_null( $to ) 
			|| is_null( $to_type ) 
			|| is_null( $from )
			|| is_null( $from_type ) 
		) {

			// log ##
			w__log( \willow()->get( '_args')['task'].'~>e:Error in data passed to method' );

            return false;

		}

		// w__log('d:>from: "'.$from.'"');
		// w__log('d:>to: "'.$to.'"');
		
		// validate to type ##
		switch ( $to_type ) {

			default :
			case "variable" :

				// check if variable is correctly formatted --> {{ STRING }} ##
				$needle_start = \willow()->tags->g( 'var_o' ); #'{{ ';
				$needle_end = \willow()->tags->g( 'var_c' ); #' }}';

			break ;

			// allow for string replacements ##
			case "string" :

				// check if variable is correctly formatted --> {{> STRING }} ##
				$needle_start = ''; 
				$needle_end = ''; 

			break;

			case "partial" :

				// check if variable is correctly formatted --> {{> STRING }} ##
				$needle_start = \willow()->tags->g( 'par_o' ); #'{{> ';
				$needle_end = \willow()->tags->g( 'par_c' ); #' <}}';

			break ;

			case "loop" :

				// check if variable is correctly formatted --> {{> STRING }} ##
				$needle_start = \willow()->tags->g( 'loo_o' ); #'{{@ ';
				$needle_end = \willow()->tags->g( 'loo_c' ); #' /@}}';

			break ;

			case "willow" :

				// check if variable is correctly formatted --> {{> STRING }} ##
				$needle_start = \willow()->tags->g( 'wil_o' ); #'{{~ ';
				$needle_end = \willow()->tags->g( 'wil_c' ); #' ~}}';

			break ;

			case "php_function" :

				// check if variable is correctly formatted --> {{> STRING }} ##
				$needle_start = \willow()->tags->g( 'php_fun_o' ); #'<% ';
				$needle_end = \willow()->tags->g( 'php_fun_c' ); #' %>';

			break ;

			case "php_variable" :

				// check if php variable is correctly formatted --> {{> STRING }} ##
				$needle_start = \willow()->tags->g( 'php_var_o' ); // {#
				$needle_end = \willow()->tags->g( 'php_var_c' ); // #}

			break ;

			case "comment" :

				// check if variable is correctly formatted --> {{> STRING }} ##
				$needle_start = \willow()->tags->g( 'com_o' ); #'{{! ';
				$needle_end = \willow()->tags->g( 'com_c' ); #' !}}';

			break ;

		}

		// validate strings ##
		if(
			'string' == $to_type
			&& ! is_string( $to )
		){

			// log ##
			w__log( \willow()->get( '_args')['task'].'~>e:>tag: "'.$to.'" is not a correctly formatted '.$to_type.'' );

			// log ##
			w__log( 'e:>tag: "'.$to.'" is not a correctly formatted '.$to_type.'' );

            return false;

		}

		// trim for regex ##
		$open = trim( $needle_start );
		$close = trim( $needle_end );

		// regex ##
		$regex_find = \apply_filters( 
			'willow/parse/markup/regex/find', 
			"/$open\s+(.*?)\s+$close/s"  // note:: added "+" for multiple whitespaces.. not sure it's good yet...
		);

        if (
			! preg_match( $regex_find, $to )
			&& 'string' != $to_type // we can skip strings, as their format was validated earlier ##
        ) {

			// log ##
			w__log( \willow()->get( '_args')['task'].'~>e:>tag: "'.$to.'" is not a correctly formatted '.$to_type.' - missing "'.$needle_start.'" at start or "'.$needle_end.'" at end.' );

			// log ##
			w__log( 'e:>tag: "'.$to.'" is not a correctly formatted '.$to_type.' - missing "'.$needle_start.'" at start or "'.$needle_end.'" at end.' );

            return false;

		}

		// validate from type ##
		switch ( $from_type ) {

			default :
			case "willow" :

				// check if variable is correctly formatted --> {{> STRING }} ##
				$needle_start = \willow()->tags->g( 'wil_o' ); #'{{~ ';
				$needle_end = \willow()->tags->g( 'wil_c' ); #' ~}}';

			break ;

			// allow for string replacements ##
			case "string" :

				// check if variable is correctly formatted --> {{> STRING }} ##
				$needle_start = '';
				$needle_end = ''; 

			break;

			// allow for string translations ##
			case "i18n" :

				// check if variable is correctly formatted --> {{ STRING }} ##
				$needle_start = \willow()->tags->g( 'i18n_o' ); #'{_ ';
				$needle_end = \willow()->tags->g( 'i18n_c' ); #' _}';

			break;

			case "variable" :

				// check if variable is correctly formatted --> {{ STRING }} ##
				$needle_start = \willow()->tags->g( 'var_o' ); #'{{ ';
				$needle_end = \willow()->tags->g( 'var_c' ); #' }}';

			break ;

			case "partial" :

				// check if variable is correctly formatted --> {{> STRING }} ##
				$needle_start = \willow()->tags->g( 'par_o' ); #'{{> ';
				$needle_end = \willow()->tags->g( 'par_c' ); #' }}';

			break ;

			case "comment" :

				// check if variable is correctly formatted --> {{> STRING }} ##
				$needle_start = \willow()->tags->g( 'com_o' ); #'{{! ';
				$needle_end = \willow()->tags->g( 'com_c' ); #' }}';

			break ;

			case "loop" :

				// check if variable is correctly formatted --> {{> STRING }} ##
				$needle_start = \willow()->tags->g( 'loo_o' ); // '{{@ ';
				$needle_end = \willow()->tags->g( 'loo_c' ); // ' }}';

			break ;

			case "php_function" :

				// check if variable is correctly formatted --> {{> STRING }} ##
				$needle_start = \willow()->tags->g( 'php_fun_o' ); #'<< ';
				$needle_end = \willow()->tags->g( 'php_fun_c' ); #' >>';

			break ;

			case "php_variable" :

				// check if php variable is correctly formatted --> {{> STRING }} ##
				$needle_start = \willow()->tags->g( 'php_var_o' ); // {#
				$needle_end = \willow()->tags->g( 'php_var_c' ); // #}

			break ;

		}

		// trim for regex ##
		$open = trim( $needle_start );
		$close = trim( $needle_end );

		// regex ##
		$regex_find = \apply_filters( 
			'willow/parse/markup/regex/find', 
			"/$open\s+(.*?)\s+$close/s"  // note:: added "+" for multiple whitespaces.. not sure it's good yet...
			// "/{{#(.*?)\/#}}/s" 
		);

        if (
            // ! core\strings::starts_with( $from, $needle_start ) 
			// || ! core\strings::ends_with( $from, $needle_end ) 
			! preg_match( $regex_find, $from )
        ) {

			// log ##
			// w__log( \willow()->get( '_args')['task'].'~>e:>tag: "'.$from.'" is not a correctly formatted '.$from_type.' - missing "'.$needle_start.'" at start or "'.$needle_end.'" at end.' );

			// log ##
			w__log( 'e:>tag: "'.$from.'" is not a correctly formatted '.$from_type.' -> missing "'.$needle_start.'" at start or "'.$needle_end.'" at end.' );

            return false;

		}
		
		// w__log( 'd:>swapping from: "'.$from.'" to: "'.$to.'"' );

		// find out which markup to affect ##
		switch( $process ){

			default : 
			case "secondary" :

				// w__log( 'd:>Swapping markup in $_markup' );

				$_markup = \willow()->get( '_markup' );

				// add new variable to $template as defined position - don't replace $from yet... ##
				$new_template = willow\core\strings::str_replace_first( $from, $to, $_markup['template'] ); // only replaces first occurance ##

				// test ##
				// w__log( 'd:>'.$new_template );

				// set 'template' key with new template markup ##
				$_markup['template'] = $new_template;

				// push back into _markup ##
				\willow()->set( '_markup', $_markup );

			break ;

			case "primary" :

				// w__log( 'd:>Swapping markup in $_buffer_markup' );

				// w__log( 'd:>Swapping markup in self::$buffer_markup' );
				$_buffer_markup = \willow()->get( '_buffer_markup' );

				// add new variable to $template as defined position - don't replace $from yet... ##
				$new_template = str_replace( $from, $to, $_buffer_markup );

				// test ##
				// w__log( 'd:>'.$new_template );

				// push back into main stored markup ##
				\willow()->set( '_buffer_markup', $new_template );

			break ;

		} 

        // positive ##
        return true;

    }

    /**
     * Remove {{ variable }} from self:$args['markup'] array
     * 
     */
    public function remove( string $variable = null, $markup = null, $type = 'variable' ) {

        // sanity ##
        if (
			is_null( $variable ) 
			|| is_null( $markup )
			|| is_null( $type )
		) {

			// log ##
			w__log( \willow()->get( '_args')['task'].'~>e:>No variable or markkup value passed to method' );

            return false;

		}
		
		// w__log( 'remove: '.$variable );

        // check if variable is correctly formatted --> {{ STRING }} ##

		// what type of variable are we adding ##
		switch ( $type ) {

			default :
			case "variable" :

				// check if variable is correctly formatted --> {{ STRING }} ##
				$needle_start = \willow()->tags->g( 'var_o' ); #'{{ ';
				$needle_end = \willow()->tags->g( 'var_c' ); #' }}';

			break ;

			case "comment" :

				// check if variable is correctly formatted --> {{ STRING }} ##
				$needle_start = \willow()->tags->g( 'com_o' ); #'{{ ';
				$needle_end = \willow()->tags->g( 'com_c' ); #' }}';

			break ;

		}

        if (
            ! willow\core\strings::starts_with( $variable, $needle_start ) 
            || ! willow\core\strings::ends_with( $variable, $needle_end ) 
        ) {

			// log ##
			// w__log( \willow()->get( '_args')['task'].'~>e:>Placeholder: "'.$variable.'" is not correctly formatted - missing "{{ " at start or " }}" at end.' );
			w__log( 'e:>Placeholder: "'.$variable.'" is not correctly formatted - missing "{{ " at start or " }}" at end.' );

            return false;

		}
		
		// w__log( 'Removing variable: "'.$variable.'"' );
		// return $markup;

        // remove variable from markup ##
		$markup = 
			str_replace( 
            	$variable, 
            	'', // nada ##
            	$markup
			);
		
		// w__log( 'd:>'.$markup );

		// log ##
		// w__log( \willow()->get( '_args')['task'].'~>variable_removed:>"'.$variable.'" by "'.\willow\core\backtrace::get([ 'level' => 2, 'return' => 'function' ]).'"' );

		// w__log( 'd~>variable_removed:>"'.$variable.'" by "'.\willow\core\backtrace::get([ 'level' => 2, 'return' => 'function' ]).'"' );

        // positive ##
        return $markup;

    }

}
