<?php

namespace willow\context;

use willow\core\helper as h;
use willow;

class partial {

	/**
	 * Construct
     */
    public function __construct(){

		// silence ##

	}

	/**
     * Generic Getter - looks for properties in config matching context->task
	 * can be loaded as a string in context/ui file
     *
     * @param       Array       $args
     * @since       1.4.1
     * @return      Array
     */
    public function get( $args = null ) {

		return \willow()->config->get([ 'context' => $args['context'], 'task' => $args['task'] ]);

	}


}
