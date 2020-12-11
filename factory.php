<?php

namespace willow;

// import classes ##
use willow;
use willow\plugin as plugin;
use willow\core\helper as h;

// If this file is called directly, Bulk!
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

/*
* Factory Class
*/
final class factory {

	private $plugin;

    /**
     * Class constructor to define object props --> empty
     * 
     * @since   0.0.1
     * @return  void
    */
    function __construct() {

        $this->plugin = plugin::get_instance(); 
		
	}
	
	public function hooks(){

		// kick off extend and store object ##
		$this->plugin->set( 'extend', new willow\context\extend() );

		// kick off filter and store object ##
		$this->plugin->set( 'filter', new willow\core\filter() );

		// build helper object ##
		$this->plugin->set( 'helper', new willow\core\helper() );

		// kick off tags and store object ##
		$this->plugin->set( 'tags', new willow\core\tags() );

		// prepare filters ##
		$this->plugin->set( 'filter_method', new willow\filter\method() );

	}

}
