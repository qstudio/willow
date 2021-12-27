<?php

namespace willow\parse;

use willow;
use willow\core\helper as h;

/**
 * Apply Markup changes to passed template
 * find all placeholders in self::$markup and replace with matching values in self::$fields
 * most complex and most likely to clash go first, then simpler last ##
*/
class prepare {

	private 
		$args = false,
		$process = false
	;
	
    
    /**
	 * Construct
     */
    public function __construct(){

		// silence ##

	}
	
	public function hooks( $args = null, $process = 'secondary' ):void
	{

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

			\willow()->set( '_parse_context', $this->args['context'] );
			\willow()->set( '_parse_task', $this->args['task'] );
			// self::$parse_args = $args;

		}

		// search for partials in passed markup - these update the markup template with returned markup ##
		\willow()->parse->partials->match( $this->args, $this->process );

		// pre-format markup to run any >> translatable strings << ##
		// runs early and might be used to return data to arguments ##
		\willow()->parse->i18n->match( $this->args, $this->process );

		// pre-format markup to run any >> functions << ##
		// runs early and might be used to return data to arguments ##
		\willow()->parse->php_functions->match( $this->args, $this->process );

		// pre-format markup to extract data from willows ##
		\willow()->parse->willows->match( $this->args, $this->process );

		// pre-format markup to extract loops ##
		\willow()->parse->loops->match( $this->args, $this->process );

		// pre-format markup to extract comments and place in html ##
		\willow()->parse->comments->match( $this->args, $this->process );

		// pre-format markup to extract variable arguments - 
		// goes last, as other tags might have added new variables to prepare ##
		\willow()->parse->variables->match( $this->args, $this->process );

		// pre-format markup to run any >> php variables << ##
		// runs early and might be used to return data to arguments ##
		// __deprecated__ in 2.0.0 ---------------------
		// willow\parse\php_variables::prepare( $args, $process );

		// __deprecated__ in 2.0.0 ---------------------
		// remove all flags before markup is parsed ##
		// flags::cleanup( $args, $process );

	}

}
