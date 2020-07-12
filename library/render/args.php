<?php

namespace q\willow\render;

// use q\core;
use q\willow\core;
use q\core\helper as h;
use q\view;
use q\get;
use q\willow;
use q\willow\render;

class args extends willow\render {

	private static $collect = [];

	/**
	 * Empty all render args
	 * 
	 * @since 4.0.0
	 */ 
	public static function reset(){

		// h::log( 'd:>reset args for: '.self::$args['task'] );

		// passed args ##
        self::$args 	= [
			'fields'	=> []
		];

		self::$output = null; // return string ##
        self::$fields = null; // array of field names and values ##
		self::$markup = null; // array to store passed markup and extra keys added by formatting ##
		self::$log 	= null; // tracking array for feedback ##

	}


	/**
	 * Collect all render args
	 * 
	 * @since 4.0.0
	 */ 
	public static function collect(){

		// h::log( 'd:>collect all args'); ##

		self::$collect['args'] = self::$args;
		self::$collect['output'] = self::$output;
		self::$collect['fields'] = self::$fields;
		self::$collect['markup'] = self::$markup;
		self::$collect['log'] = self::$log;

	}


	/**
	 * Set all render args
	 * 
	 * @since 4.0.0
	 */ 
	public static function set(){

		// h::log( 'd:>set all args'); ##

		self::$args = self::$collect['args'];
		self::$output = self::$collect['output'];
		self::$fields = self::$collect['fields'] ;
		self::$markup = self::$collect['markup'];
		self::$log = self::$collect['log'];

	}



    public static function validate( $args = null ) {

		// h::log( $args );

		// pre-format args to extract markup -- if only one string is passed, this will empty the args ##
		// $args = render\markup::pre_config( $args );

		// get stored config via lookup, fallback 
		// pulls from Q, but available to filter via q/config/load ##
		$config = core\config::get( $args );

		// test ##
		// h::log( $config );

		// Parse incoming $args into an array and merge it with $config defaults ##
		// allows specific calling methods to alter passed $args ##
		if ( $config ) $args = \q\core\method::parse_args( $args, $config );

		// h::log( $args );

        // checks on required fields in $args array ##
        if (
			// ! isset( $args )
			is_null( $args )
            || ! is_array( $args )
        ){

			// log ##
			h::log( self::$args['task'].'~>e:>Missing required args, so stopping here' );
			
			// h::log( 'd:>Kicked here...' );

            return false;

		}

		// h::log( $args['config']['post'] );

		// If posts is passed as an int, then get a matching post Object, as we can use the data later ## 
		// validate passed post ##
		if ( 
			isset( $args['config']['post'] ) 
			// is_int( $args['config']['post'] )
			&& ! $args['config']['post'] instanceof \WP_Post
		) {

			// get new post, if corrupt ##
			$args['config']['post'] = get\post::object( $args['config'] );

			// h::log( 'Post set, but not an Object.. so getting again..: '.$args['config']['post']->ID );

		}

		// no post set ##
		if ( ! isset( $args['config']['post'] ) ) {

			$args['config']['post'] = get\post::object();

			// h::log( 'No post set, so getting: '.$args['config']['post']->ID );

		}

		// last check ##
		if ( ! isset( $args['config']['post'] ) ) {

			// h::log( 'Error with post object, validate - returned as null.' );

			$args['config']['post'] = null;

			// return false;

		}

		// h::log( $args['config']['post']->ID );

        // assign properties with initial filters ##
		$args = self::assign( $args );
		
		// h::log( $args );

        // // check if module asked to run $args['config']['run']
        // if ( 
        //     // isset( $args['config']['run'] )
        //     // && 
        //     false === $args['config']['run']
        // ){

		// 	// log ##
		// 	h::log( self::$args['task'].'~>n:>config->run defined as false for: '.$args['task'].', so stopping here.. ' );

        //     return false;

		// }
		
		// check if module asked to run $args['config']['run']
		if ( 
            // isset( $args['config']['run'] )
			// && 
			isset( $args['config']['run'] )
            && false === $args['config']['run']
        ){

			h::log( $args );

			// log ##
			h::log( $args['task'].'~>n:>config->run defined as false for: '.$args['task'].', so stopping here.. ' );
			h::log( 'd:>config run defined as false... so stop' );

            return false;

        }

        // ok - should be good ##
        return $args;

	}
	



    /**
     * Assign class properties with initial filters, merging in passed $args from calling method
     */
    public static function assign( Array $args = null ) {

        // apply global filter to $args - specific calls should be controlled by parameters included directly ##
        $args = \q\core\filter::apply([
			'filter'        => 'q/render/args',
			'parameters'    => $args,
			'return'        => $args
		]);
		
		// apply template level filter to $args - specific calls should be controlled by parameters included directly ##
        $args = \q\core\filter::apply([
			'filter'        => 'q/render/args/'.view\is::get(),
			'parameters'    => $args,
			'return'        => $args
        ]);

		// h::log( core\config::$config );
			
		// merge CONTEXT->global settings - this allows to pass config per context ##
		if ( $config = core\config::get([ 'context' => $args['context'], 'task' => 'config' ]) ){

			// h::log( 'd:>Merging settings from: '.$args['context'].'->config' );
			$context_config = [ 'config' => $config ];
			// h::log( $context_config );

			// merge in global__CONTEXT settings ##
			$args = \q\core\method::parse_args( $context_config, $args );

			// h::log( $args );

		}

		// grab all passed args and merge with defaults -- this ensures we have config->run, config->debug etc.. ##
		$args = \q\core\method::parse_args( $args, self::$args_default );

		// h::log( $args );

		// assign class property ##
		self::$args = $args;

		// prepare markup, fields and handlers based on passed configuration ##
		// render\parse::prepare( $args );

		// pre-format markup ##
		self::post_config();
		
        // return args for validation ##
        return $args;

	}
	


	/**
	 * Extract data from passed args 
	 * 
	 * @since 4.1.0
	*/
	public static function post_config(){

		// h::log( self::$args['markup'] );

		// post-format markup to extract markup keys collected by config ##
		render\markup::merge();

	}





	/**
	 * Prepare passed args ##
	 *
	 */
	public static function prepare( $args = null ) {

		h::log( 't:>merge with args::validate, just need to get config right..' );

		// sanity ##
		if (
			is_null( $args )
		 	|| ! is_array( $args )
		){

		 	h::log( 'e:>Error in passed args' );

		 	return false;

		}

		// get calling method for filters ##
		$task = core\method::backtrace([ 'level' => 2, 'return' => 'function' ]);

		// define context for all in class -- i.e "group" ##
		if ( ! isset( $args['context'] ) ) {
			$args['context'] = 'global';
		}

		// let's set "task" to calling function, for debugging ##
		if ( ! isset( $args['task'] ) ) {
			$args['task'] = $task;
		}

		// h::log( $args );

		// get stored config via lookup, fallback 
		// pulls from Q, but available to filter via q/config/load ##
		$config = core\config::get( $args );

		// test ##
		// h::log( $config );

		// Parse incoming $args into an array and merge it with $config defaults ##
		// allows specific calling methods to alter passed $args ##
		if ( $config ) $args = core\method::parse_args( $args, $config );

		// h::log( $config );

        // checks on required fields in $args array ##
        if (
			// ! isset( $args )
			is_null( $args )
            || ! is_array( $args )
        ){

			// log ##
			h::log( self::$args['task'].'~>e:>Missing required args, so stopping here' );
			
			// h::log( 'Kicked here...' );

            return false;

		}

		// get stored config - pulls from Q, but available to filter via q/config/get/all ##
		// $config =
		// 	( // force config settings to return by passing "config" -> "property" ##
		// 		isset( $args['config']['load'] )
		// 		&& core\config::get( $args['config']['load'] )
		// 	) ?
		// 	core\config::get( $args['config']['load'] ) :
		// 	core\config::get( $method ) ;

		// // test ##
		// // h::log( $config );

		// // Parse incoming $args into an array and merge it with $config defaults ##
		// // allows specific calling methods to alter passed $args ##
		// if ( $config ) $args = \wp_parse_args( $args, $config );

		// h::log( $config );
		// h::log( $args );

		// merge any default args with any pass args ##
		// if (
		// 	is_null( $args )
		// 	|| ! is_array( $args )
		// ) {

		// 	h::log( 'Error in passed $args' );

		// 	return false;

		// }

		// no post set ##
		if ( ! isset( $args['config']['post'] ) ) {

			$args['config']['post'] = get\post::object();

		}

		// validate passed post ##
		if (
			isset( $args['config']['post'] )
			&& ! $args['config']['post'] instanceof \WP_Post
		) {

			// get new post, if corrupt ##
			$args['config']['post'] = get\post::object( $args );

		}

		// last check ##
		if ( ! $args['config']['post'] ) {

			h::log( 'Error with post object, validate - returned as null.' );

			$args['config']['post'] = null;

			// return false;

		}

		// kick back args ##
		return $args;

	}



	
    public static function is_enabled()
    {

        // sanity ##
        if ( 
            ! self::$args 
            || ! is_array( self::$args )
        ) {

			// log ##
			h::log( self::$args['task'].'~>e:>Error in passed self::$args');

            return false;

        }

        // helper::log( self::$fields );
        // helper::log( 'We are looking for field: '.self::$args['enable'] );

        // check for enabled flag - if none, return true ##
        // we also take one guess at the field name -- if it's not passed in config ##
        if ( 
            ! isset( self::$args['enable'] )
            && ! isset( self::$fields[self::$args['task'].'_enable'] )
        ) {

			// log ##
			h::log( self::$args['task'].'~>n:>No enable defined in $args or enable field found for task: "'.self::$args['task'].'"');

            return true;

        }

        // kick back ##
        if ( 
            (
                isset( self::$args['enable'] )
                && 1 == self::$fields[self::$args['enable']]
            )
			|| 
				isset( self::$fields[self::$args['task'].'_enable'] )
            	&& 1 == self::$fields[self::$args['task'].'_enable']
        ) {

			// log ##
			h::log( self::$args['task'].'~>n:>Field task: "'.self::$args['task'].'" Enabled, continue');

            // helper::log( self::$args['enable'] .' == 1' );

            return true;

        }

		// log ##
		h::log( self::$args['task'].'~>n:>Field Group: "'.self::$args['task'].'" NOT Enabled, stopping.');

        // helper::log( self::$args['enable'] .' != 1' );

        // negative ##
        return false;

    }

     
}
