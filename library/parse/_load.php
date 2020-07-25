<?php

namespace q\willow;

// use q\core;
use q\core\helper as h;
use q\willow\core\helper as helper;
use q\willow;

// load it up ##
\q\willow\parse::run();

class parse extends \q_willow {

	protected static 
		$regex = [
			'clean'	=>"/[^A-Za-z0-9_]/" // clean up string to alphanumeric + _
			// @todo.. move other regexes here ##

		],

		// per match flags ##
		$flags_willow = false,
		$flags_function = false,
		$flags_argument = false,
		$flags_variable = false,
		$flags_comment = false,

		// $parse_args = false,
		$parse_context = false,
		$parse_task = false
		// $flags_function = false,

	;

	
	public static function run(){

		\q\core\load::libraries( self::load() );

	}


    /**
    * Load Libraries
    *
    * @since        2.0.0
    */
    public static function load()
    {

		return $array = [

			// markup methods ##
			'parse_markup' => helper::get( 'parse/markup.php', 'return', 'path' ),

			// flags ##
			'flags' => helper::get( 'parse/flags.php', 'return', 'path' ),

			// find + decode methods for variable + function arguments ##
			'arguments' => helper::get( 'parse/arguments.php', 'return', 'path' ),

			// comments ##
			'comments' => helper::get( 'parse/comments.php', 'return', 'path' ),

			// partials ##
			'partials' => helper::get( 'parse/partials.php', 'return', 'path' ),

			// willows ##
			'willows' => helper::get( 'parse/willows.php', 'return', 'path' ),

			// functions ##
			'php_functions' => helper::get( 'parse/php_functions.php', 'return', 'path' ),

			// sections ##
			// 'sections' => helper::get( 'parse/sections.php', 'return', 'path' ),

			// loops // sections ##
			'loops' => helper::get( 'parse/loops.php', 'return', 'path' ),

			// variables.. ##
			'variables' => helper::get( 'parse/variables.php', 'return', 'path' ),

			// PHP variables.. ##
			'php_variables' => helper::get( 'parse/php_variables.php', 'return', 'path' ),

		];

	}


    /**
     * Apply Markup changes to passed template
     * find all placeholders in self::$markup and replace with matching values in self::$fields
	 * most complex and most likely to clash go first, then simpler last ##
     * 
     */
    public static function prepare( $args = null, $process = 'internal' ){

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

		// search for partials in passed markup ##
		partials::prepare( $args, $process );

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
    public static function cleanup( $args = null, $process = 'internal' ){

		// h::log( self::$args['markup'] );

		// remove all flags ##
		flags::cleanup( $args, $process );

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

		// remove all spare flags ##
		// flags::cleanup();

		// search for config settings passed in markup, such as "src" handle ##
		// argument::cleanup(); // @todo ##

	}

	

}
