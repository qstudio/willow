<?php

namespace willow\context;

use willow;
use willow\core\helper as h;

/**
 * Apply Markup changes to passed template
 * find all placeholders in self::$markup and replace with matching values in self::$fields
 * most complex and most likely to clash go first, then simpler last ##
 */
class ui {

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
	 * @uses		render\fields::define
     * @return      Array
     */
    public function get( $args = null ) {

		// w__log( $args );

		return \willow()->config->get([ 'context' => $args['context'], 'task' => $args['task'] ]);

	}


}
