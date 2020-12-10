<?php

namespace willow\context;

use willow; 

class group {

	private
		$plugin = null // this 
	;

	/**
	 * 
     */
    public function __construct(){

		// grab passed plugin object ## 
		$this->plugin = willow\plugin::get_instance();

	}

	/**
     * Get group data via meta handler
     *
     * @param       Array       $args
     * @since       1.3.0
	 * @uses		define
     * @return      Array
     */
    public function get( $args = null ) {

		$method = 'fields';

		// Q needed to run get method ##
		if(
			! method_exists( 'willow\get\group', $method )
			|| ! is_callable([ 'willow\get\group', $method ])
		){

			w__log( 'e:>Class method is not callable: willow\get\group\\'.$method );

			return false;

		}

		// build object ##
		$group = new willow\get\group( $this->plugin );

		// method returns an array with 'data' and 'fields' ##
		if ( 
			// $array = \willow\get\group::fields( $args )
			$array = $group->fields( $args )
		){
			// w__log( $array['data'] );
			
			// "args->fields" are used for type and callback lookups ##
			// self::$args['fields'] = $array['fields']; 
			$_args = $this->plugin->get( '_args' );
			$_args['fields'] = $array['fields']; 
			$this->plugin->set( '_args', $_args );

			// return ##
			return $array['data'];

		}

	}

}
