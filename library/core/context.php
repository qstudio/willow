<?php

namespace willow\core;

use willow\core\helper as h;
use willow;

class context {

	public static function get(){

		// sanity ##
		if (
			null === \willow()
			|| null === \willow()->get( '_args' )
			|| ! isset( \willow()->get( '_args' )['context'] )
			|| ! isset( \willow()->get( '_args' )['task'] )
		){

			w__log( 'd:>No context / task available' );

			return false;

		}

		return sprintf( 
			'Context: "%s" Task: "%s"', 
			\willow()->get( '_args' )['context'], 
			\willow()->get( '_args' )['task'] 
		);

	}

}
