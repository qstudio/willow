<?php

namespace Q\willow\render;

use Q\willow;

class args {

	private 
		$plugin = false,
		$collect = [] // temp storage ##
	;

	/**
     * @todo
     * 
     */
    public function __construct( \Q\willow\plugin $plugin ){

		// grab passed plugin object ## 
		$this->plugin = $plugin;

	}

	/**
	 * Empty all render args
	 * 
	 * @since 4.0.0
	 */ 
	public function reset(){

		// $this->plugin->log( 'd:>reset args for: '.self::$args['task'] );

		// passed args ##
        $this->plugin->set( '_args', [ 'fields'	=> [] ] );

		$this->plugin->set( '_output', null ); // return string ##
        $this->plugin->set( '_fields', null ); // array of field names and values ##
		$this->plugin->set( '_markup', null ); // array to store passed markup and extra keys added by formatting ##
		$this->plugin->set( '_hash', null ); // hasher ##

	}

	/**
	 * Collect all render args
	 * 
	 * @since 4.0.0
	 */ 
	public function collect(){

		// $this->plugin->log( 'd:>collect all args'); ##

		$this->collect['args'] = $this->plugin->get('_args');
		$this->collect['output'] = $this->plugin->get('_output');
		$this->collect['fields'] = $this->plugin->get('_fields');
		$this->collect['markup'] = $this->plugin->get('_markup');
		$this->collect['hash'] = $this->plugin->get('_hash');

	}

	/**
	 * Set all render args
	 * 
	 * @since 4.0.0
	 */ 
	public function restore(){

		// $this->plugin->log( 'd:>set all args'); ##

		$this->plugin->get('_args', $this->collect['args'] );
		$this->plugin->get('_args', $this->collect['output'] );
		$this->plugin->get('_args', $this->collect['fields'] );
		$this->plugin->get('_args', $this->collect['markup'] );
		$this->plugin->get('_args', $this->collect['hash'] );

	}

    public function validate( $args = null ) {

		// $this->plugin->log( $args );

		// pre-format args to extract markup -- if only one string is passed, this will empty the args ##
		// $args = render\markup::pre_config( $args );

		// get stored config via lookup, fallback 
		// pulls from Q, but available to filter via willow/config/load ##
		$config = $this->plugin->get('config')->get( $args );

		// test ##
		// $this->plugin->log( $config );

		// Parse incoming $args into an array and merge it with $config defaults ##
		// allows specific calling methods to alter passed $args ##
		if ( $config ) $args = willow\core\method::parse_args( $args, $config );

		// $this->plugin->log( $args );

        // checks on required fields in $args array ##
        if (
			// ! isset( $args )
			is_null( $args )
            || ! is_array( $args )
        ){

			// log ##
			$this->plugin->log( $this->plugin->get('_args')['task'].'~>e:>Missing required args, so stopping here' );
			
			// $this->plugin->log( 'd:>Kicked here...' );

            return false;

		}

		// $this->plugin->log( $args['config']['post'] );

		// If posts is passed as an int, then get a matching post Object, as we can use the data later ## 
		// validate passed post ##
		if ( 
			isset( $args['config']['post'] ) 
			// is_int( $args['config']['post'] )
			&& ! $args['config']['post'] instanceof \WP_Post
		) {

			// get new post, if corrupt ##
			$args['config']['post'] = willow\get\post::object( $args['config'] );

			// $this->plugin->log( 'Post set, but not an Object.. so getting again..: '.$args['config']['post']->ID );

		}

		// no post set ##
		if ( ! isset( $args['config']['post'] ) ) {

			$args['config']['post'] = willow\get\post::object();

			// $this->plugin->log( 'No post set, so getting: '.$args['config']['post']->ID );

		}

		// last check ##
		if ( ! isset( $args['config']['post'] ) ) {

			// $this->plugin->log( 'Error with post object, validate - returned as null.' );

			$args['config']['post'] = null;

			// return false;

		}

		// $this->plugin->log( $args['config']['post']->ID );

        // assign properties with initial filters ##
		$args = $this->assign( $args );
		
		// $this->plugin->log( $args );

        // // check if module asked to run $args['config']['run']
        // if ( 
        //     // isset( $args['config']['run'] )
        //     // && 
        //     false === $args['config']['run']
        // ){

		// 	// log ##
		// 	$this->plugin->log( self::$args['task'].'~>n:>config->run defined as false for: '.$args['task'].', so stopping here.. ' );

        //     return false;

		// }
		
		// check if module asked to run $args['config']['run']
		if ( 
            // isset( $args['config']['run'] )
			// && 
			isset( $args['config']['run'] )
            && false === $args['config']['run']
        ){

			// $this->plugin->log( $args );

			// log ##
			$this->plugin->log( $args['task'].'~>n:>config->run defined as false for: '.$args['task'].', so stopping here.. ' );
			// $this->plugin->log( 'd:>config run defined as false for: '.$args['task'].', so stopping here..' );

            return false;

        }

        // ok - should be good ##
        return $args;

	}

    /**
     * Assign class properties with initial filters, merging in passed $args from calling method
     */
    public function assign( Array $args = null ) {

		$filter = new willow\core\filter( $this->plugin );

        // apply global filter to $args - specific calls should be controlled by parameters included directly ##
        $args = $filter->apply([
			'filter'        => 'willow/render/args',
			'parameters'    => $args,
			'return'        => $args
		]);
		
		// apply template level filter to $args - specific calls should be controlled by parameters included directly ##
        $args = $filter->apply([
			'filter'        => 'willow/render/args/'.\q\view\is::get(),
			'parameters'    => $args,
			'return'        => $args
        ]);

		// $this->plugin->log( core\config::$config );
			
		// merge CONTEXT->global settings - this allows to pass config per context ##
		if ( $config = $this->plugin->get('config')->get([ 'context' => $args['context'], 'task' => 'config' ]) ){

			// $this->plugin->log( 'd:>Merging settings from: '.$args['context'].'->config' );
			$context_config = [ 'config' => $config ];
			// $this->plugin->log( $context_config );

			// $this->plugin->log( $args );

			// merge in global__CONTEXT settings ##
			// $this->plugin->log( 't:>NOTE, swapped order of merge here to try to give preference to task args over global args... keep an eye' );
			$args = willow\core\method::parse_args( $args, $context_config );
			// $args = core\method::parse_args( $context_config, $args );

			// $this->plugin->log( $args );

		}

		// grab all passed args and merge with defaults -- this ensures we have config->run, config->debug etc.. ##
		$args = willow\core\method::parse_args( $args, $this->plugin->get( '_args_default' ) );

		// $this->plugin->log( $args );

		// assign class property ##
		// self::$args = $args;
		$this->plugin->set( '_args', $args );

		// prepare markup, fields and handlers based on passed configuration ##
		// render\parse::prepare( $args );

		// pre-format markup ##
		$this->post_config();
		
        // return args for validation ##
        return $args;

	}
	
	/**
	 * Extract data from passed args 
	 * 
	 * @since 4.1.0
	*/
	public function post_config(){

		// $this->plugin->log( self::$args['markup'] );

		// post-format markup to extract markup keys collected by config ##
		$markup = new willow\render\markup( $this->plugin );
		$markup->merge();

	}

	/**
	 * Define config->default value
	 * 
	 * @since 4.1.0
	*/
	public function default( $array = null ){

		// sanity ##
		if(
			is_null( $array )
			|| ! is_array( $array )
		){

			$this->plugin->log( 'Error in passed default parameter' );

			return false;

		}

		$args = $this->plugin->get('_args');
		$args['config']['default'] = $array;
		$this->plugin->set( '_args' , $args );

		// self::$args['config']['default'] = $array;

		$this->plugin->log( 'd:>Default value set to: '.var_export( $array, true ) );

		return true;

	}

	/**
	 * Prepare passed args ##
	 *
	 */
	public function prepare( $args = null ) {

		// $this->plugin->log( 't:>merge with args::validate, just need to get config right..' );

		// sanity ##
		if (
			is_null( $args )
		 	|| ! is_array( $args )
		){

		 	$this->plugin->log( 'e:>Error in passed args' );

		 	return false;

		}

		// get calling method for filters ##
		$task = willow\core\method::backtrace([ 'level' => 2, 'return' => 'function' ]);

		// define context for all in class -- i.e "group" ##
		if ( ! isset( $args['context'] ) ) {
			$args['context'] = 'global';
		}

		// let's set "task" to calling function, for debugging ##
		if ( ! isset( $args['task'] ) ) {
			$args['task'] = $task;
		}

		// $this->plugin->log( $args );

		// get stored config via lookup, fallback 
		// pulls from Q, but available to filter via willow/config/load ##
		$config = $this->plugin->get('config')->get( $args );

		// test ##
		// $this->plugin->log( $config );

		// Parse incoming $args into an array and merge it with $config defaults ##
		// allows specific calling methods to alter passed $args ##
		if ( $config ) $args = willow\core\method::parse_args( $args, $config );

		// $this->plugin->log( $config );

        // checks on required fields in $args array ##
        if (
			// ! isset( $args )
			is_null( $args )
            || ! is_array( $args )
        ){

			// log ##
			$this->plugin->log( self::$args['task'].'~>e:>Missing required args, so stopping here' );
			
			// $this->plugin->log( 'Kicked here...' );

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
		// // $this->plugin->log( $config );

		// // Parse incoming $args into an array and merge it with $config defaults ##
		// // allows specific calling methods to alter passed $args ##
		// if ( $config ) $args = \wp_parse_args( $args, $config );

		// $this->plugin->log( $config );
		// $this->plugin->log( $args );

		// merge any default args with any pass args ##
		// if (
		// 	is_null( $args )
		// 	|| ! is_array( $args )
		// ) {

		// 	$this->plugin->log( 'Error in passed $args' );

		// 	return false;

		// }

		// no post set ##
		if ( ! isset( $args['config']['post'] ) ) {

			$args['config']['post'] = willow\get\post::object();

		}

		// validate passed post ##
		if (
			isset( $args['config']['post'] )
			&& ! $args['config']['post'] instanceof \WP_Post
		) {

			// get new post, if corrupt ##
			$args['config']['post'] = willow\get\post::object( $args );

		}

		// last check ##
		if ( ! $args['config']['post'] ) {

			$this->plugin->log( 'Error with post object, validate - returned as null.' );

			$args['config']['post'] = null;

			// return false;

		}

		// kick back args ##
		return $args;

	}
	
    public function is_enabled(){

		// local vars ##
		$_args = $this->plugin->get( '_args' );
		$_fields = $this->plugin->get( '_fields' );

        // sanity ##
        if ( 
            ! $_args 
            || ! is_array( $_args )
        ) {

			// log ##
			$this->plugin->log( $_args['task'].'~>e:>Error in passed $_args');

            return false;

        }

        // helper::log( self::$fields );
        // helper::log( 'We are looking for field: '.$_args['enable'] );

        // check for enabled flag - if none, return true ##
        // we also take one guess at the field name -- if it's not passed in config ##
        if ( 
            ! isset( $_args['enable'] )
            && ! isset( self::$fields[$_args['task'].'_enable'] )
        ) {

			// log ##
			$this->plugin->log( $_args['task'].'~>n:>No enable defined in $args NOR enable field found for task: "'.$_args['task'].'"');

            return true;

        }

        // kick back ##
        if ( 
            (
                isset( $_args['enable'] )
                && 1 == $_fields[$_args['enable']]
            )
			|| 
				isset( $_fields[$_args['task'].'_enable'] )
            	&& 1 == $_fields[$_args['task'].'_enable']
        ) {

			// log ##
			$this->plugin->log( $_args['task'].'~>n:>Field task: "'.$_args['task'].'" Enabled, continue');

            return true;

        }

		// log ##
		$this->plugin->log( $_args['task'].'~>n:>Field Group: "'.$_args['task'].'" NOT Enabled, stopping.');

        // negative ##
        return false;

    }
     
}
