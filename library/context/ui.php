<?php

namespace Q\willow\context;

use Q\willow;
use Q\willow\core\helper as h;

class ui {

	private 
		$plugin = false
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

	/**
     * Generic Getter - looks for properties in config matching context->task
	 * can be loaded as a string in context/ui file
     *
     * @param       Array       $args
     * @since       1.4.1
	 * @uses		render\fields::define
     * @return      Array
     */
    public function get( $args = null ) {

		// h::log( $args );

		return $this->plugin->get('config')->get([ 'context' => $args['context'], 'task' => $args['task'] ]);

	}


}
