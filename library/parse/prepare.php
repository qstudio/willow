<?php

namespace Q\willow\parse;

use Q\willow;
use Q\willow\core\helper as h;

class prepare {

	private 
		$plugin = false,
		$args = false,
		$process = false
	;
	
    /**
     * Apply Markup changes to passed template
     * find all placeholders in self::$markup and replace with matching values in self::$fields
	 * most complex and most likely to clash go first, then simpler last ##
     * 
     */
    public function __construct( \Q\willow\plugin $plugin ){

		// grab passed plugin object ## 
		$this->plugin = $plugin;

	}
	
	public function hooks( $args = null, $process = 'secondary' ){

		// add __MAGIC__ context loader ##
		require_once $this->plugin->get_plugin_path( '/library/context/_load.php' ); 

		// assign ##
		$this->process = $process;
		$this->args = $args;

		// w__log( $this->args );
		// w__log( 'process: '.$this->process );

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

		// w__log( self::$args['markup'] );

		// prepare arguments object ##
		$arguments = new willow\parse\arguments( $this->plugin );

		// search for partials in passed markup - these update the markup template with returned markup ##
		$partials = new willow\parse\partials( $this->plugin );
		$partials->match( $this->args, $this->process );

		// pre-format markup to run any >> translatable strings << ##
		// runs early and might be used to return data to arguments ##
		$i18n = new willow\parse\i18n( $this->plugin );
		$i18n->match( $this->args, $this->process );

		// pre-format markup to run any >> php variables << ##
		// runs early and might be used to return data to arguments ##
		// __deprecated__ in 2.0.0
		// willow\parse\php_variables::prepare( $args, $process );

		// pre-format markup to run any >> functions << ##
		// runs early and might be used to return data to arguments ##
		$php_functions = new willow\parse\php_functions( $this->plugin );
		$php_functions->match( $this->args, $this->process );

		// pre-format markup to extract daa from willows ##
		$willows = new willow\parse\willows( $this->plugin );
		$willows->match( $this->args, $this->process );

		// pre-format markup to extract loops ##
		$loops = new willow\parse\loops( $this->plugin );
		$loops->match( $this->args, $this->process );

		// pre-format markup to extract comments and place in html ##
		$comments = new willow\parse\comments( $this->plugin );
		$comments->match( $this->args, $this->process );

		// pre-format markup to extract variable arguments - 
		// goes last, as other tags might have added new variables to prepare ##
		$variables = new willow\parse\variables( $this->plugin );
		$variables->match( $this->args, $this->process );

		// w__log( 't:>THIS is removed and does not seem required... TODO' );
		// remove all flags before markup is parsed ##
		####// flags::cleanup( $args, $process );

	}




	

}
