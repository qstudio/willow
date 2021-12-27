<?php

namespace willow\context;

use willow; 

class group {

	/**
	 * Construct
     */
    public function __construct(){

		// silence ##

	}

	/**
     * Get group data via meta handler
     *
     * @param       Array       $args
     * @since       1.3.0
	 * @param		array
     * @return      Array
     */
    public function get( array $args = null )
	{

		$method = 'fields';

		// Willow needed to run get method ##
		if(
			! method_exists( 'willow\get\group', $method )
			|| ! is_callable([ 'willow\get\group', $method ])
		){

			w__log( 'e:>Class method is not callable: willow\get\group\\'.$method );

			return false;

		}

		// method returns an array with 'data' and 'fields' ##
		if ( 
			// $array = \willow\get\group::fields( $args )
			$array = \willow()->group->fields( $args )
		){
			// w__log( $array['data'] );
			
			// "args->fields" are used for type and callback lookups ##
			$_args = \willow()->get( '_args' );
			$_args['fields'] = $array['fields']; 
			\willow()->set( '_args', $_args );

			// return ##
			return $array['data'];

		}

		return false;

	}

}
