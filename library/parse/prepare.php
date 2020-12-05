<?php

namespace Q\willow\parse;

use Q\willow;

class prepare {

	private 
		$plugin = false,
		$args = false,
		$process = false
	;
	
	/*
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
	*/

	
	// public static function __run(){

	// 	self::load();

	// }


	
    /**
     * Apply Markup changes to passed template
     * find all placeholders in self::$markup and replace with matching values in self::$fields
	 * most complex and most likely to clash go first, then simpler last ##
     * 
     */
    public function __construct( \Q\willow\plugin $plugin, $args = null, $process = 'secondary' ){

		// grab passed plugin object ## 
		$this->plugin = $plugin;
		$this->process = $process;
		$this->args = $args;

	}
	
	public function hooks(){

		// $this->plugin->log( $this->args );
		// exit;
		// store passed args - context/task ##

		// used for assigning filters ##
		if(
			isset( $this->args )
			&& is_array( $this->args )
			&& isset( $this->args['context'] )
			&& isset( $this->args['task'] )
		){

			$this->plugin->set( '_parse_context', $this->args['context'] );
			$this->plugin->set( '_parse_task', $this->args['task'] );
			// self::$parse_args = $args;

		}

		// context controller ##
		require_once $this->plugin->get_plugin_path( 'library/context/_load.php' );

		// h::log( self::$args['markup'] );

		// prepare arguments object ##
		$arguments = new willow\parse\arguments( $this->plugin, $this->args, $this->process );

		// search for partials in passed markup - these update the markup template with returned markup ##
		$partials = new willow\parse\partials( $this->plugin, $this->args, $this->process );
		$partials->match();

		// pre-format markup to run any >> translatable strings << ##
		// runs early and might be used to return data to arguments ##
		// willow\parse\i18n::prepare( $args, $process );

		// pre-format markup to run any >> php variables << ##
		// runs early and might be used to return data to arguments ##
		// willow\parse\php_variables::prepare( $args, $process );

		// pre-format markup to run any >> functions << ##
		// runs early and might be used to return data to arguments ##
		// willow\parse\php_functions::prepare( $args, $process );

		// pre-format markup to extract daa from willows ##
		// willow\parse\willows::prepare( $args, $process );
		$willows = new willow\parse\willows( $this->plugin, $this->args, $this->process );
		$willows->match();

		// pre-format markup to extract loops ##
		// willow\parse\loops::prepare( $args, $process );
		$loops = new willow\parse\loops( $this->plugin, $this->args, $this->process );
		// $loops->match();

		// pre-format markup to extract comments and place in html ##
		// willow\parse\comments::prepare( $args, $process ); // 

		// pre-format markup to extract variable arguments - 
		// goes last, as other tags might have added new variables to prepare ##
		// willow\parse\variables::prepare( $args, $process );





		// h::log( 't:>THIS breaks many things, but is needed for filters to run and replace correctly.. TODO' );
		// remove all flags before markup is parsed ##
		####// flags::cleanup( $args, $process );

	}




	

}
