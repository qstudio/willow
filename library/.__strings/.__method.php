<?php

namespace willow\strings;

use willow\core\helper as h;

class method {


	public static function substr_last( string $string = null ){

		// sanity ##
		if ( is_null( $string ) ){ return false; }

		return mb_substr( trim( $string ), -1, 'utf-8' ); 

	}

	public static function substr_first( string $string = null ){

		// sanity ##
		if ( is_null( $string ) ){ return false; }

		return mb_substr( trim( $string ), 0, 1, 'utf-8' ); 

	}

}
