<?php

namespace q\willow\context;

use q\core\helper as h;
// use q\ui;
use q\get;
use q\willow;
use q\willow\context;
use q\willow\render; 
use q\widget as widgets;

class widget extends willow\context {

	
	/**
    * Render nav menu
    *
    * @since       4.1.0
    */
    public static function sharelines( $args = null ){

        // ##
		return widgets\sharelines::module( $args );

	}


}
