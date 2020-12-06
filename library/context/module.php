<?php

namespace Q\willow\context;

use Q\willow\core\helper as h;
use Q\willow;

class module {

	private
		$plugin = null
	;

	/**
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

		// w__log( $args );
		return $this->plugin->get( 'config')->get([ 'context' => $args['context'], 'task' => $args['task'] ]);

	}

}
