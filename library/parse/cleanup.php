<?php

namespace willow\parse;

use willow;

class cleanup {

	private 
		$plugin = false
	;

	public function __construct(){

		// grab passed plugin object ## 
		$this->plugin = willow\plugin::get_instance();

	}

	/**
     * Apply Markup changes to passed template
     * find all placeholders in self::$markup and replace with matching values in self::$fields
	 * most complex and most likely to clash go first, then simpler last ##
     * 
     */
    public function hooks( $args = null, $process = 'secondary' ){

		// w__log( self::$args['markup'] );

		// remove all flags ##
		// flags::cleanup( $args, $process ); // @todo -- if required ##

		// remove all spare args... ##
		// arguments::cleanup( $args, $process ); // @todo -- if required ##

		// remove left-over i18n strings
		$this->plugin->parse->i18n->cleanup( $args, $process );

		// remove left-over php variables
		// __deprecated in 2.0.0
		// php_variables::cleanup( $args, $process );

		// clean up stray function tags ##
		$this->plugin->parse->php_functions->cleanup( $args, $process );

		// clean up stray willow tags ##
		$this->plugin->parse->willows->cleanup( $args, $process );

		// clean up stray loop tags ##
		$this->plugin->parse->loops->cleanup( $args, $process );

		// clean up stray partial tags ##
		$this->plugin->parse->partials->cleanup( $args, $process );

		// clean up stray comment tags ##
		$this->plugin->parse->comments->cleanup( $args, $process );

		// remove all spare vars ##
		$this->plugin->parse->variables->cleanup( $args, $process );

	}

}
