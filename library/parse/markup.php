<?php

namespace Q\willow\parse;

use Q\willow;

class markup {

	private 
		$plugin = false
	;

	/**
	 * Scan for partials in markup and convert to variables and $fields
	 * 
	 * @since 4.1.0
	*/
	public function __construct( \Q\willow\plugin $plugin ){

		// grab passed plugin object ## 
		$this->plugin = $plugin;

	}

    /**
     * Get all tags of defined $type from passed $string 
     *  
     */
    public function get( string $string = null, $type = 'variable' ) {
        
        // sanity ##
        if (
			is_null( $string ) 
			|| is_null( $type )
        ) {

			// log ##
			$this->plugin->log( $this->plugin->get( '_args')['task'].'~>e:>No string or type value passed to method' );

            return false;

		}
		
		switch ( $type ) {

			default :
			case "variable" :

				// note, we trim() white space off tags, as this is handled by the regex ##
				$open = trim( $this->plugin->get('tags')->g( 'var_o' ) );
				$close = trim( $this->plugin->get('tags')->g( 'var_c' ) );

				// $this->plugin->log( 'open: '.$open );

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
			// $this->plugin->log( 't:>TODO - if no self::$args - set to buffer' );
			// $this->plugin->log( $this->plugin->get( '_args')['task'].'~>n:>No variables found in string.' );
			// $this->plugin->log( 'd:>No variables found in string.' );
			// $this->plugin->log( '$string: "'.$string.'"' );

            return false;

        }

        // test ##
        // $this->plugin->log( $matches[0] );

        // kick back variable array ##
        return $matches[0];

    }



    /**
     * Check if single tag exists 
     * @todo - work on passed params 
     *  
     */
    public function contains( string $variable = null, $markup = null, $field = null ) {
		
		// if no markup passed, use $this->plugin->get( '_markup') ##
		$markup = is_null( $markup ) ? $this->plugin->get( '_markup') : $markup ;

		// if $markup template passed, check there, else check $this->plugin->get( '_markup') ##
		$string = is_null( $field ) ? $this->plugin->get( '_markup')['template'] : $this->plugin->get( '_markup')[$field] ;

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

		// $this->plugin->log( 't:>Position based replacement seems shaky, perhaps move to swap method...' );

        // sanity ##
        if (
			is_null( $type ) 
			|| is_null( $tag ) 
			|| is_null( $position )
		) {

			// log ##
			$this->plugin->log( $this->plugin->get( '_args')['task'].'~>e:Error in data passed to method' );

            return false;

		}
		
		// // what type of variable are we adding ##
		switch ( $type ) {

			default :
			case "variable" :

				// check if variable is correctly formatted --> {{ STRING }} ##
				$needle_start = $this->plugin->get('tags')->g( 'var_o' ); #'{{ ';
				$needle_end = $this->plugin->get('tags')->g( 'var_c' ); #' }}';

			break ;

		}

        if (
            ! willow\core\method::starts_with( $tag, $needle_start ) 
			|| ! willow\core\method::ends_with( $tag, $needle_end ) 
        ) {

			// log ##
			$this->plugin->log( $this->plugin->get( '_args')['task'].'~>e:>passed tag: "'.$tag.'" is not correctly formatted - missing {{ at start or }} at end.' );

            return false;

		}
		
		// $this->plugin->log( 'd:>Setting tag: "'.$tag.'"' );
		// $this->plugin->log( 'Position: '.$position );

		// find out which markup to affect ##
		switch( $process ){

			default : 
			case "secondary" :

				// $this->plugin->log( 'd:>Swapping markup in $this->plugin->get( '_markup')' );

				// add new variable to $template as defined position - don't replace {{ variable }} yet... ##
				$new_template = substr_replace( $this->plugin->get( '_markup')['template'], $tag, $position, 0 );

				// test ##
				// $this->plugin->log( 'd:>'.$new_template );

				// push back into main stored markup ##
				$this->plugin->get( '_markup')['template'] = $new_template; // ."\r\n"

			break ;

			case "primary" :

				// $this->plugin->log( 'd:>Swapping markup in self::$buffer_markup' );

				// add new variable to $template as defined position - don't replace $from yet... ##
				$new_template = substr_replace( $this->plugin->get('_buffer_markup'), $tag, $position, 0 );

				// test ##
				// $this->plugin->log( 'd:>'.$new_template );

				// push back into main stored markup ##
				$this->plugin->set('_buffer_markup', $new_template ); // ."\r\n"

			break ;

		} 
		
		// $this->plugin->log( 'd:>'.$new_template );

		// log ##
		// $this->plugin->log( $this->plugin->get( '_args')['task'].'~>variable_added:>"'.$tag.'" @position: "'.$position.'" by "'.core\method::backtrace([ 'level' => 2, 'return' => 'function' ]).'"' );

        // positive ##
        return true; 

    }




	
	/**
     * Set {{ variable }} in self:markup['template'] at defined position
     * 
     */
    public function add( string $tag = null, $before = null, $type = 'variable', $process = 'secondary' ) { // , $markup = null

		$this->plugin->log( 't:>TOOO, __deprecate in 1.5.0' );

        // sanity ##
        if (
			is_null( $type ) 
			|| is_null( $tag ) 
			|| is_null( $before )
		) {

			// log ##
			$this->plugin->log( $this->plugin->get( '_args')['task'].'~>e:Error in data passed to method' );

            return false;

		}

		// validate strings ##
		// @todo - perhaps need to be more liberal or restrictive on this.. will see ##
		if(
			'string' == $type
			&& ! is_string( $tag )
		){

			// log ##
			// $this->plugin->log( $this->plugin->get( '_args')['task'].'~>e:>tag: "'.$tag.'" is not a correctly formatted '.$type.'' );

			// log ##
			$this->plugin->log( 'e:>tag: "'.$tag.'" is not a correctly formatted '.$type.'' );

            return false;

		}

		
		// // what type of variable are we adding ##
		switch ( $type ) {

			default :
			case "variable" :

				// check if variable is correctly formatted --> {{ STRING }} ##
				$needle_start = $this->plugin->get('tags')->g( 'var_o' ); #'{{ ';
				$needle_end = $this->plugin->get('tags')->g( 'var_c' ); #' }}';

			break ;

			case "string" :

				// check if variable is correctly formatted --> {{ STRING }} ##
				$needle_start = '';
				$needle_end = '';

			break ;

		}

        if (
            ! willow\core\method::starts_with( $tag, $needle_start ) 
			|| ! willow\core\method::ends_with( $tag, $needle_end ) 
        ) {

			// log ##
			$this->plugin->log( $this->plugin->get( '_args')['task'].'~>e:>passed tag: "'.$tag.'" is not correctly formatted - missing {{ at start or }} at end.' );

            return false;

		}
		
		// find out which markup to affect ##
		switch( $process ){

			default : 
			case "secondary" :

				// get the markup array ##
				$markup = $this->plugin->get( '_markup');

				// $before is a string, which we need to find in markup 
				if( 
					$position = false === strpos( $markup['template'], $before )
				){

					$this->plugin->log( 'e:>Cannot locate "'.$before.'" in internal $markup' );

				}

				// add new variable to $template as defined position - don't replace {{ variable }} yet... ##
				$new_template = substr_replace( $markup['template'], $tag, $position );

				$markup['template'] = $new_template."\r\n";

				// @TODO - check this works well and arrange better mether do store keys to arrays ###
				// push back into main stored markup ##
				$this->plugin->set( '_markup', $markup );

				// log ##
				// $this->plugin->log( 'd:>Adding tag: "'.$tag.'" @ Position: '.$position.' in internal markup' );

				// test ##
				$this->plugin->log( 'd:>'.$new_template );

			break ;

			case "primary" :

				$buffer_markup = $this->plugin->get( '_buffer_markup' );

				// $before is a string, which we need to find in markup 
				if( 
					$position = false === strpos( $buffer_markup, $before )
				){

					$this->plugin->log( 'e:>Cannot locate "'.$before.'" in buffer $markup: '.$buffer_markup );

				}

				// $this->plugin->log( 'd:>Swapping markup in $buffer_markup' );

				// add new variable to $template as defined position - don't replace $from yet... ##
				$new_template = substr_replace( $buffer_markup, $tag, $position );

				// test ##
				// $this->plugin->log( 'd:>'.$new_template );

				// push back into main stored markup ##
				$this->plugin->set( '_buffer_markup', $new_template."\r\n" );

				// log ##
				// $this->plugin->log( 'd:>Adding tag: "'.$tag.'" @ Position: '.$position.' in buffer markup' );

				// test ##
				$this->plugin->log( 'd:>'.$new_template );


			break ;

		} 
		
		// $this->plugin->log( 'd:>'.$new_template );

		// log ##
		// $this->plugin->log( $this->plugin->get( '_args')['task'].'~>variable_added:>"'.$tag.'" @position: "'.$position.'" by "'.core\method::backtrace([ 'level' => 2, 'return' => 'function' ]).'"' );

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
			$this->plugin->log( $this->plugin->get( '_args')['task'].'~>e:Error in data passed to method' );

            return false;

		}

		// $this->plugin->log('d:>from: "'.$from.'"');
		// $this->plugin->log('d:>to: "'.$to.'"');
		
		// validate to type ##
		switch ( $to_type ) {

			default :
			case "variable" :

				// check if variable is correctly formatted --> {{ STRING }} ##
				$needle_start = $this->plugin->get('tags')->g( 'var_o' ); #'{{ ';
				$needle_end = $this->plugin->get('tags')->g( 'var_c' ); #' }}';

			break ;

			// allow for string replacements ##
			case "string" :

				// check if variable is correctly formatted --> {{> STRING }} ##
				$needle_start = ''; 
				$needle_end = ''; 

			break;

			case "partial" :

				// check if variable is correctly formatted --> {{> STRING }} ##
				$needle_start = $this->plugin->get('tags')->g( 'par_o' ); #'{{> ';
				$needle_end = $this->plugin->get('tags')->g( 'par_c' ); #' <}}';

			break ;

			case "loop" :

				// check if variable is correctly formatted --> {{> STRING }} ##
				$needle_start = $this->plugin->get('tags')->g( 'loo_o' ); #'{{@ ';
				$needle_end = $this->plugin->get('tags')->g( 'loo_c' ); #' /@}}';

			break ;

			case "willow" :

				// check if variable is correctly formatted --> {{> STRING }} ##
				$needle_start = $this->plugin->get('tags')->g( 'wil_o' ); #'{{~ ';
				$needle_end = $this->plugin->get('tags')->g( 'wil_c' ); #' ~}}';

			break ;

			case "php_function" :

				// check if variable is correctly formatted --> {{> STRING }} ##
				$needle_start = $this->plugin->get('tags')->g( 'php_fun_o' ); #'<% ';
				$needle_end = $this->plugin->get('tags')->g( 'php_fun_c' ); #' %>';

			break ;

			case "php_variable" :

				// check if php variable is correctly formatted --> {{> STRING }} ##
				$needle_start = $this->plugin->get('tags')->g( 'php_var_o' ); // {#
				$needle_end = $this->plugin->get('tags')->g( 'php_var_c' ); // #}

			break ;

			case "comment" :

				// check if variable is correctly formatted --> {{> STRING }} ##
				$needle_start = $this->plugin->get('tags')->g( 'com_o' ); #'{{! ';
				$needle_end = $this->plugin->get('tags')->g( 'com_c' ); #' !}}';

			break ;

		}

		// validate strings ##
		if(
			'string' == $to_type
			&& ! is_string( $to )
		){

			// log ##
			$this->plugin->log( $this->plugin->get( '_args')['task'].'~>e:>tag: "'.$to.'" is not a correctly formatted '.$to_type.'' );

			// log ##
			$this->plugin->log( 'e:>tag: "'.$to.'" is not a correctly formatted '.$to_type.'' );

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
			$this->plugin->log( $this->plugin->get( '_args')['task'].'~>e:>tag: "'.$to.'" is not a correctly formatted '.$to_type.' - missing "'.$needle_start.'" at start or "'.$needle_end.'" at end.' );

			// log ##
			$this->plugin->log( 'e:>tag: "'.$to.'" is not a correctly formatted '.$to_type.' - missing "'.$needle_start.'" at start or "'.$needle_end.'" at end.' );

            return false;

		}

		// validate from type ##
		switch ( $from_type ) {

			default :
			case "willow" :

				// check if variable is correctly formatted --> {{> STRING }} ##
				$needle_start = $this->plugin->get('tags')->g( 'wil_o' ); #'{{~ ';
				$needle_end = $this->plugin->get('tags')->g( 'wil_c' ); #' ~}}';

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
				$needle_start = $this->plugin->get('tags')->g( 'i18n_o' ); #'{_ ';
				$needle_end = $this->plugin->get('tags')->g( 'i18n_c' ); #' _}';

			break;

			case "variable" :

				// check if variable is correctly formatted --> {{ STRING }} ##
				$needle_start = $this->plugin->get('tags')->g( 'var_o' ); #'{{ ';
				$needle_end = $this->plugin->get('tags')->g( 'var_c' ); #' }}';

			break ;

			case "partial" :

				// check if variable is correctly formatted --> {{> STRING }} ##
				$needle_start = $this->plugin->get('tags')->g( 'par_o' ); #'{{> ';
				$needle_end = $this->plugin->get('tags')->g( 'par_c' ); #' }}';

			break ;

			case "comment" :

				// check if variable is correctly formatted --> {{> STRING }} ##
				$needle_start = $this->plugin->get('tags')->g( 'com_o' ); #'{{! ';
				$needle_end = $this->plugin->get('tags')->g( 'com_c' ); #' }}';

			break ;

			case "loop" :

				// check if variable is correctly formatted --> {{> STRING }} ##
				$needle_start = $this->plugin->get('tags')->g( 'loo_o' ); // '{{@ ';
				$needle_end = $this->plugin->get('tags')->g( 'loo_c' ); // ' }}';

			break ;

			case "php_function" :

				// check if variable is correctly formatted --> {{> STRING }} ##
				$needle_start = $this->plugin->get('tags')->g( 'php_fun_o' ); #'<< ';
				$needle_end = $this->plugin->get('tags')->g( 'php_fun_c' ); #' >>';

			break ;

			case "php_variable" :

				// check if php variable is correctly formatted --> {{> STRING }} ##
				$needle_start = $this->plugin->get('tags')->g( 'php_var_o' ); // {#
				$needle_end = $this->plugin->get('tags')->g( 'php_var_c' ); // #}

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
            // ! core\method::starts_with( $from, $needle_start ) 
			// || ! core\method::ends_with( $from, $needle_end ) 
			! preg_match( $regex_find, $from )
        ) {

			// log ##
			// $this->plugin->log( $this->plugin->get( '_args')['task'].'~>e:>tag: "'.$from.'" is not a correctly formatted '.$from_type.' - missing "'.$needle_start.'" at start or "'.$needle_end.'" at end.' );

			// log ##
			$this->plugin->log( 'e:>tag: "'.$from.'" is not a correctly formatted '.$from_type.' -> missing "'.$needle_start.'" at start or "'.$needle_end.'" at end.' );

            return false;

		}
		
		// $this->plugin->log( 'd:>swapping from: "'.$from.'" to: "'.$to.'"' );

		// use strpos to get location of {{ variable }} ##
		// $position = strpos( $this->plugin->get( '_markup'), $to );
		// $this->plugin->log( 'Position: '.$position );

		// find out which markup to affect ##
		switch( $process ){

			default : 
			case "secondary" :

				$markup = $this->plugin->get( '_markup');

				// $this->plugin->log( 'd:>Swapping markup in $this->plugin->get( '_markup')' );

				// add new variable to $template as defined position - don't replace $from yet... ##
				// $new_template = str_replace( $from, $to, $this->plugin->get( '_markup')['template'] );
				$new_template = \willow\render\method::str_replace_first( $from, $to, $markup['template'] ); // only replaces first occurance ##

				// test ##
				// $this->plugin->log( 'd:>'.$new_template );
				$markup['template'] = $new_template;

				// push back into main stored markup ##
				$this->plugin->set( '_markup', $markup );

			break ;

			case "primary" :

				// $this->plugin->log( 'd:>Swapping markup in self::$buffer_markup' );

				// add new variable to $template as defined position - don't replace $from yet... ##
				$new_template = str_replace( $from, $to, $this->plugin->get('_buffer_markup') );

				// test ##
				// $this->plugin->log( 'd:>'.$new_template );

				// push back into main stored markup ##
				$this->plugin->set( '_buffer_markup', $new_template );

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
			$this->plugin->log( $this->plugin->get( '_args')['task'].'~>e:>No variable or markkup value passed to method' );

            return false;

		}
		
		// $this->plugin->log( 'remove: '.$variable );

        // check if variable is correctly formatted --> {{ STRING }} ##

		// what type of variable are we adding ##
		switch ( $type ) {

			default :
			case "variable" :

				// check if variable is correctly formatted --> {{ STRING }} ##
				$needle_start = $this->plugin->get('tags')->g( 'var_o' ); #'{{ ';
				$needle_end = $this->plugin->get('tags')->g( 'var_c' ); #' }}';

			break ;

			case "comment" :

				// check if variable is correctly formatted --> {{ STRING }} ##
				$needle_start = $this->plugin->get('tags')->g( 'com_o' ); #'{{ ';
				$needle_end = $this->plugin->get('tags')->g( 'com_c' ); #' }}';

			break ;

		}

        if (
            ! core\method::starts_with( $variable, $needle_start ) 
            || ! core\method::ends_with( $variable, $needle_end ) 
        ) {

			// log ##
			// $this->plugin->log( $this->plugin->get( '_args')['task'].'~>e:>Placeholder: "'.$variable.'" is not correctly formatted - missing "{{ " at start or " }}" at end.' );
			$this->plugin->log( 'e:>Placeholder: "'.$variable.'" is not correctly formatted - missing "{{ " at start or " }}" at end.' );

            return false;

		}
		
		// $this->plugin->log( 'Removing variable: "'.$variable.'"' );
		// return $markup;

        // remove variable from markup ##
		$markup = 
			str_replace( 
            	$variable, 
            	'', // nada ##
            	$markup
			);
		
		// $this->plugin->log( 'd:>'.$markup );

		// log ##
		// $this->plugin->log( $this->plugin->get( '_args')['task'].'~>variable_removed:>"'.$variable.'" by "'.\q\core\method::backtrace([ 'level' => 2, 'return' => 'function' ]).'"' );

		// $this->plugin->log( 'd~>variable_removed:>"'.$variable.'" by "'.\q\core\method::backtrace([ 'level' => 2, 'return' => 'function' ]).'"' );

        // positive ##
        return $markup;

    }



}
