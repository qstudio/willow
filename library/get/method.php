<?php

namespace Q\willow\get;

use Q\willow;
use Q\willow\core\helper as h;

class method {

	/**
	 * Prepare $array of data to be returned to render
	 *
	 */
	public static function prepare_return( $args = null, $array = null ) {

		// get calling method for filters ##
		$method = willow\core\method::backtrace([ 'level' => 2, 'return' => 'function' ]);

		// sanity ##
		if (
			is_null( $args )
			|| ! is_array( $args )
			|| is_null( $array )
			|| ! is_array( $array )
		) {

			h::log( 'e~>'.$method.':>Error in passed $args or $array' );

			return false;

		}

		// h::log( $args );
		// h::log( $array );

		// run global filter on $array by $method ##
		$array = \apply_filters( 'willow/get/'.$method.'/array', $array, $args );

		// run template specific filter on $array by $method ##
		if ( $template = willow\view\is::get() ) {

			// h::log( 'Filter: "q/ui/get/array/'.$method.'/'.$template.'"' );

			$array = \apply_filters( 'willow/get/'.$method.'/array/'.$template, $array, $args );

		}

		// another sanity check after filters... ##
		if (
			is_null( $array )
			|| ! is_array( $array )
		) {

			h::log( 'e~>'.$method.':>Error in returned $array' );

			return false;

		}

		// h::log( $array );

		// return all arrays ##
		return $array ;

	}


}
