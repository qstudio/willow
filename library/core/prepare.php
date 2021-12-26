<?php

namespace willow\core;

use willow;
use willow\core\helper as h;

class prepare {

	/**
	 * Prepare $array of data to be returned to render
	 *
	 */
	public static function return( $args = null, $array = null ) {

		// get calling method for filters ##
		$method = willow\core\backtrace::get([ 'level' => 2, 'return' => 'function' ]);

		// sanity ##
		if (
			is_null( $args )
			|| ! is_array( $args )
			|| is_null( $array )
			|| ! is_array( $array )
		) {

			w__log( 'e~>'.$method.':>Error in passed $args or $array' );

			return false;

		}

		// w__log( $args );
		// w__log( $array );

		// run global filter on $array by $method ##
		$array = \apply_filters( 'willow/get/'.$method.'/array', $array, $args );

		// run template specific filter on $array by $method ##
		if ( $template = willow\core\template::get() ) {

			// w__log( 'Filter: "q/ui/get/array/'.$method.'/'.$template.'"' );

			$array = \apply_filters( 'willow/get/'.$method.'/array/'.$template, $array, $args );

		}

		// another sanity check after filters... ##
		if (
			is_null( $array )
			|| ! is_array( $array )
		) {

			w__log( 'e~>'.$method.':>Error in returned $array' );

			return false;

		}

		// w__log( $array );

		// return all arrays ##
		return $array ;

	}


}
