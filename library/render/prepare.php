<?php

namespace willow\render;

use willow\core\helper as h;
use willow;

class prepare {

	/**
	 * Prepare $array to be rendered
	 */
	public static function array( $args = null, $array = null )
	{

		// get calling method for filters ##
		$method = willow\core\backtrace::get([ 'level' => 2, 'return' => 'function' ]);

		// sanity ##
		if (
			is_null( $args )
			|| ! is_array( $args )
			|| is_null( $array )
			|| ! is_array( $array )
			// || empty( $array )
		) {

			// log ##
			w__log( 'e~>'.$method.':>Error in passed $args or $array' );

			return false;

		}

		// empty results ##
		if (
			empty( $array )
		) {

			// log ##
			w__log( 'e~>'.$method.':>Returned $array is empty' );

			return false;

		}

		// w__log( 'd:>$method: '.$method );
		// w__log( $args );
		// w__log( $array );

		// if no markup sent.. ##
		if ( 
			! isset( $args['markup'] )
			&& is_array( $args ) 
		) {

			// default -- almost useless - but works for single values.. ##
			$args['markup'] = '%value%';

			foreach( $args as $k => $v ) {

				if ( is_string( $v ) ) {

					// take first string value in $args markup ##
					$args['markup'] = $v;

					break;

				}

			}

		}

		// no markup passed ##
		if ( ! isset( $args['markup'] ) ) {

			w__log( 'e~>'.$method.':Missing "markup", returning false.' );

			return false;

		}

		// last filter on array, before applying markup ##
		$array = \apply_filters( 'willow/render/prepare/'.$method.'/array', $array, $args );

		// do markup ##
		$string = self::markup( $args['markup'], $array, $args );

		// filter $string by $method ##
		$string = \apply_filters( 'willow/render/prepare/'.$method.'/string', $string, $args );

		// filter $array by method/template ##
		if ( $template = willow\core\template::get() ) {

			// w__log( 'Filter: "q/theme/get/string/'.$method.'/'.$template.'"' );
			$string = \apply_filters( 'willow/render/prepare/'.$method.'/string/'.$template, $string, $args );

		}

		// test ##
		// w__log( $string );

		// all render methods echo ##
		echo $string ;

		// optional logging to show removals and stats ##
        // render\log::render( $args );

		return true;

	}

}
