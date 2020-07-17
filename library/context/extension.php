<?php

namespace q\willow\context;

use q\core\helper as h;
use q\willow;
use q\willow\render;
// use q\willow\context;
use q\extension as extensions;

class extension extends willow\context {

	
	/**
    * Render search extension
    *
    * @since       4.1.0
    */
    public static function search( $args = null ){

		// h::log( $args );

        // ##
		// return extensions\search\render::ui( $args );

		//  ##
		// render\fields::define(
		return [
			'search' => extensions\search\render::ui( $args )
		];
		// );

	}


}
