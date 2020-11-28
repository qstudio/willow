<?php

namespace willow;

use willow\core\helper as h;
use willow;

// load it up ##
\willow\parse::__run();

class parse extends \willow {

	protected static 
		$regex = [
			'clean'	=>"/[^A-Za-z0-9_]/" // clean up string to alphanumeric + _
			// @todo.. move other regexes here ##

		],

		// per match flags ##
		$flags_willow = false,
		$flags_argument = false,
		$flags_variable = false,
		$flags_comment = false,
		$flags_php_function = false,
		$flags_php_variable = false,

		// $parse_args = false,
		$parse_context = false,
		$parse_task = false

	;

	
	public static function __run(){

		self::load();

	}


    /**
    * Load Libraries
    *
    * @since        2.0.0
    */
    public static function load(){

		// markup methods ##
		require_once self::get_plugin_path( 'library/parse/markup.php' );

		// flags - used to indicate filters and pre-processors ##
		require_once self::get_plugin_path( 'library/parse/flags.php' );

		// find + decode methods for variable + function arguments ##
		require_once self::get_plugin_path( 'library/parse/arguments.php' );

		// string translation ##
		require_once self::get_plugin_path( 'library/parse/i18n.php' );

		// comments ##
		require_once self::get_plugin_path( 'library/parse/comments.php' );

		// partials ##
		require_once self::get_plugin_path( 'library/parse/partials.php' );

		// willows ##
		require_once self::get_plugin_path( 'library/parse/willows.php' );

		// functions ##
		require_once self::get_plugin_path( 'library/parse/php_functions.php' );

		// loops // sections ##
		require_once self::get_plugin_path( 'library/parse/loops.php' );

		// variables.. ##
		require_once self::get_plugin_path( 'library/parse/variables.php' );

		// PHP variables.. ##
		require_once self::get_plugin_path( 'library/parse/php_variables.php' );

	}



	
    /**
     * Apply Markup changes to passed template
     * find all placeholders in self::$markup and replace with matching values in self::$fields
	 * most complex and most likely to clash go first, then simpler last ##
     * 
     */
    public static function prepare( $args = null, $process = 'secondary' ){

		// h::log( $args );
		// store passed args - context/task ##

		// used for assigning filters ##
		if(
			$args
			&& isset( $args['context'] )
			&& isset( $args['task'] )
		){

			self::$parse_context = $args['context'];
			self::$parse_task = $args['task'];
			// self::$parse_args = $args;

		}

		// h::log( self::$args['markup'] );

		// search for partials in passed markup - these update the markup template with returned markup ##
		partials::prepare( $args, $process );

		// pre-format markup to run any >> translatable strings << ##
		// runs early and might be used to return data to arguments ##
		i18n::prepare( $args, $process );

		// pre-format markup to run any >> php variables << ##
		// runs early and might be used to return data to arguments ##
		php_variables::prepare( $args, $process );

		// pre-format markup to run any >> functions << ##
		// runs early and might be used to return data to arguments ##
		php_functions::prepare( $args, $process );

		// pre-format markup to extract daa from willows ##
		willows::prepare( $args, $process );

		// pre-format markup to extract loops ##
		loops::prepare( $args, $process );

		// pre-format markup to extract comments and place in html ##
		comments::prepare( $args, $process ); // 

		// pre-format markup to extract variable arguments - 
		// goes last, as other tags might have added new variables to prepare ##
		variables::prepare( $args, $process );

		// h::log( 't:>THIS breaks many things, but is needed for filters to run and replace correctly.. TODO' );
		// remove all flags before markup is parsed ##
		// flags::cleanup( $args, $process );

	}



	/**
     * Apply Markup changes to passed template
     * find all placeholders in self::$markup and replace with matching values in self::$fields
	 * most complex and most likely to clash go first, then simpler last ##
     * 
     */
    public static function cleanup( $args = null, $process = 'secondary' ){

		// h::log( self::$args['markup'] );

		// remove all flags ##
		// flags::cleanup( $args, $process ); // @todo -- if required ##

		// remove all spare args... ##
		// arguments::cleanup( $args, $process ); // @todo -- if required ##

		// remove left-over i18n strings
		i18n::cleanup( $args, $process );

		// remove left-over php variables
		php_variables::cleanup( $args, $process );

		// clean up stray function tags ##
		php_functions::cleanup( $args, $process );

		// clean up stray willow tags ##
		willows::cleanup( $args, $process );

		// clean up stray section tags ##
		loops::cleanup( $args, $process );

		// clean up stray partial tags ##
		partials::cleanup( $args, $process );

		// clean up stray comment tags ##
		comments::cleanup( $args, $process );

		// remove all spare vars ##
		variables::cleanup( $args, $process );

	}

	

}
