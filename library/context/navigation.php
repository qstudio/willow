<?php

namespace q\willow\context;

use q\core\helper as h;
// use q\ui;
use q\get;
use q\willow;
use q\willow\context;
use q\willow\render; 

class navigation extends willow\context {


	/**
    * Render nav menu
    *
    * @since       4.1.0
    */
    public static function menu( $args = null ){

		return [ 'menu' => get\navigation::menu( $args ) ];

	}
	

	/**
    * Render pagination
    *
    * @since       4.1.0
    */
    public static function pagination( $args = null ){

		return [ 'pagination' => get\navigation::pagination( $args ) ];

	}
	

	/**
    * Render siblings
    *
    * @since       4.1.0
    */
    public static function siblings( $args = null ){

		return [ 'siblings' => get\navigation::siblings( $args ) ];

	}
	

	/**
    * Render back_home_next
    *
    * @since       4.1.0
    */
    public static function relative( $args = null ){

		return [ 'relative'	=> get\navigation::relative( $args ) ];

    }


}
