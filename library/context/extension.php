<?php

namespace q\willow\context;

use q\core\helper as h;
use q\willow;
use q\willow\context;
use q\extension as extensions;

class extension extends willow\context {

	
	/**
    * Render search extension
    *
    * @since       4.1.0
    */
    public static function search( $args = null ){

        // ##
		return extensions\search\render::module( $args );

	}


}
