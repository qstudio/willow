<?php

namespace Q\willow\parse;

// use Q\willow\core\helper as h;
use Q\willow;

class cleanup {

	private 
		$plugin = false,
		$args = false,
		$process = false
	;

	/*
	protected 
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
	*/

	public function __construct( \Q\willow\plugin $plugin, $args = null, $process = 'secondary' ){

		// grab passed plugin object ## 
		$this->plugin = $plugin;
		$this->process = $process;
		$this->args = $args;

	}

	/**
     * Apply Markup changes to passed template
     * find all placeholders in self::$markup and replace with matching values in self::$fields
	 * most complex and most likely to clash go first, then simpler last ##
     * 
     */
    public function hooks(){

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
		// willows::cleanup( $args, $process );
		$willows = new willow\parse\willows( $this->plugin );
		$willows->cleanup( $this->args, $this->process );

		// clean up stray section tags ##
		loops::cleanup( $args, $process );

		// clean up stray partial tags ##
		partials::cleanup( $args, $process );

		// clean up stray comment tags ##
		comments::cleanup( $args, $process );

		// remove all spare vars ##
		// variables::cleanup( $args, $process );
		$variables = new willow\parse\variables( $this->plugin );
		$variables->cleanup( $this->args, $this->process );

	}

}
