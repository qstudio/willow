<?php

namespace willow\core;

use willow\core;
use willow\core\helper as h;

/**
 * WP plugin update filters
*/
class update {

	/**
	 * Construct
     */
    public function __construct(){

		return $this;

	}

	/**
	 * Kick off 
	*/
	public function hooks():void
	{	

		// add updata check for 3rd party repo ##
		\add_filter( 'q/update/repos', function( array $repos = null ):array
		{
	
			return array_merge( $repos, [
				'willow' => [
					'slug' => 'willow',
					'path' => \willow()::get_plugin_path( 'willow.php' )
				]
			]);
	
		}, 10, 1 );

	}

}
