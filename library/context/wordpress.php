<?php

namespace q\willow\context;

use q\willow\core\helper as h;
// use q\get;
use q\willow;
use q\willow\context;
use q\willow\render; 

class wordpress extends willow\context {


	/**
     * Get site option
     *
     * @param       Array       $args
     * @since       1.3.0
     * @return      String
     */
    public static function get_option( $args = null ) {

		return \get_site_option( $args );

    }


}
