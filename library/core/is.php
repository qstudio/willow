<?php

namespace willow\core;

use willow\core;

class is {

    /**
	 * Check if a string is JSON
	 * 
	 * @since 2.0.2
	*/
	public static function json( $string )
	{

		// if it's not a string, false ##
		if( ! is_string( $string ) ) {

			return false;

		}
	
		json_decode( $string );

		if ( json_last_error() === JSON_ERROR_NONE ){

			return true;

		}

		return false;
	
	}

}
