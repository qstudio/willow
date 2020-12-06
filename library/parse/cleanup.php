<?php

namespace Q\willow\parse;

use Q\willow;

class cleanup {

	private 
		$plugin = false,
		$args = false,
		$process = false
	;

	public function __construct( \Q\willow\plugin $plugin ){

		// grab passed plugin object ## 
		$this->plugin = $plugin;

	}

	/**
     * Apply Markup changes to passed template
     * find all placeholders in self::$markup and replace with matching values in self::$fields
	 * most complex and most likely to clash go first, then simpler last ##
     * 
     */
    public function hooks( $args = null, $process = 'secondary' ){

		// assign ##
		$this->process = $process;
		$this->args = $args;

		// w__log( self::$args['markup'] );

		// remove all flags ##
		// flags::cleanup( $args, $process ); // @todo -- if required ##

		// remove all spare args... ##
		// arguments::cleanup( $args, $process ); // @todo -- if required ##

		// remove left-over i18n strings
		// i18n::cleanup( $args, $process );
		$willows = new willow\parse\i18n( $this->plugin );
		$i18n->cleanup( $this->args, $this->process );

		// remove left-over php variables
		// __deprecated in 2.0.0
		// php_variables::cleanup( $args, $process );

		// clean up stray function tags ##
		$php_functions = new willow\parse\php_functions( $this->plugin );
		$php_functions->cleanup( $this->args, $this->process );

		// clean up stray willow tags ##
		$willows = new willow\parse\willows( $this->plugin );
		$willows->cleanup( $this->args, $this->process );

		// clean up stray loop tags ##
		$loops = new willow\parse\loops( $this->plugin );
		$loops->cleanup( $this->args, $this->process );

		// clean up stray partial tags ##
		$partials = new willow\parse\partials( $this->plugin );
		$partials->cleanup( $this->args, $this->process );

		// clean up stray comment tags ##
		$comments = new willow\parse\comments( $this->plugin );
		$comments->cleanup( $this->args, $this->process );

		// remove all spare vars ##
		$variables = new willow\parse\variables( $this->plugin );
		$variables->cleanup( $this->args, $this->process );

	}

}
