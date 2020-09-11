<?php

namespace willow\context;

use willow\core\helper as h;
use willow\core;

class partial extends \willow\context {

	/**
     * Generic Getter - looks for properties in config matching context->task
	 * can be loaded as a string in context/ui file
     *
     * @param       Array       $args
     * @since       1.4.1
	 * @uses		render\fields::define
     * @return      Array
     */
    public static function get( $args = null ) {

		return core\config::get([ 'context' => $args['context'], 'task' => $args['task'] ]);

	}


}
