<?php

namespace Q\willow\context;

use Q\willow\core\helper as h;
use Q\willow;

class wordpress {

	private
		$plugin = null // this
	;

	/**
	 * 
     */
    public function __construct( \Q\willow\plugin $plugin ){

		// grab passed plugin object ## 
		$this->plugin = $plugin;

	}

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
	


	/**
     * wp_enqueue_script
     *
     * @param       Array       $args
     * @since       1.3.0
     * @return      String
     */
    public static function enqueue_script( $args = null ) {

		// w__log( $args );

		// check if we have a valid script to enquque ##
		// \wp_enqueue_script( $args[''] );

		return [  ];

	}


}
