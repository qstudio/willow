<?php

namespace q\willow;

// use q\core;
use q\willow\core\helper as h;

// load it up ##
\q\willow\get::run();

class get extends \q_willow {

	public static function run(){

		\q\core\load::libraries( self::load() );

	}

    /**
    * Load Libraries
    *
    * @since        2.0.0
    */
    public static function load()
    {

		return $array = [

			// methods ##
			// 'method' => h::get( 'get/method.php', 'return', 'path' ),

			// taxonomy object ##
			// 'plugin' => h::get( 'get/plugin.php', 'return', 'path' ),

			// taxonomy object ##
			// 'theme' => h::get( 'get/theme.php', 'return', 'path' ),

			// WP_Post queries ##
			// 'query' => h::get( 'get/query.php', 'return', 'path' ),

			// has::xx queries ##
			// 'has' => h::get( 'get/has.php', 'return', 'path' ),

			// post object ##
			'post' => h::get( 'get/post.php', 'return', 'path' ),

			// post meta ##
			// 'meta' => h::get( 'get/meta.php', 'return', 'path' ),

			// field group ##
			// 'group' => h::get( 'get/group.php', 'return', 'path' ),

			// taxonomy object ##
			// 'taxonomy' => h::get( 'get/taxonomy.php', 'return', 'path' ),

			// modules ##
			// 'module' => h::get( 'get/module.php', 'return', 'path' ),

			// navigation items ##
			// 'navigation' => h::get( 'get/navigation.php', 'return', 'path' ),

			// media objects ##
			// 'media' => h::get( 'get/media.php', 'return', 'path' ),
			
		];

    }

}
