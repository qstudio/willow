<?php

namespace willow\render;

use willow;

class args {

	private 
		$plugin = false,
		$collect = [] // temp storage ##
	;

	/**
     * Construct
     */
    public function __construct( willow\plugin $plugin ){

		// grab passed plugin object ## 
		$this->plugin = $plugin;

	}

	/**
	 * Empty all render args
	 * 
	 * @since 4.0.0
	 */ 
	public function reset(){

		// w__log( 'd:>reset args for: '.self::$args['task'] );

		// passed args ##
        \willow()->set( '_args', [ 'fields'	=> [] ] ); // default args array ##
		\willow()->set( '_output', null ); // return string ##
        \willow()->set( '_fields', [] ); // array of field names and values ##
		\willow()->set( '_markup', null ); // array to store passed markup and extra keys added by formatting ##
		\willow()->set( '_hash', null ); // hasher ##

	}

	/**
	 * Collect all render args
	 * 
	 * @since 4.0.0
	 */ 
	public function collect(){

		// w__log( 'd:>collect all args'); ##

		$this->collect['_args'] = \willow()->get( '_args' );
		$this->collect['_output'] = \willow()->get( '_output' );
		$this->collect['_fields'] = \willow()->get( '_fields' );
		$this->collect['_markup'] = \willow()->get( '_markup' );
		$this->collect['_hash'] = \willow()->get( '_hash' );

	}

	/**
	 * Set all render args
	 * 
	 * @since 4.0.0
	 */ 
	public function restore(){

		// w__log( 'd:>set all args'); ##

		\willow()->set( '_args', $this->collect['_args'] );
		\willow()->set( '_output', $this->collect['_output'] );
		\willow()->set( '_fields', $this->collect['_fields'] );
		\willow()->set( '_markup', $this->collect['_markup'] );
		\willow()->set( '_hash', $this->collect['_hash'] );

	}

    public function validate( $args = null ) {

		// w__log( $args );

		// get stored config ##
		$config = \willow()->config->get( $args );

		// test ##
		// w__log( $config );

		// Parse incoming $args into an array and merge it with $config defaults ##
		// allows specific calling methods to alter passed $args ##
		if ( $config ) {
		
			$args = willow\core\arrays::parse_args( $args, $config );
		
		}

		// w__log( $args );

        // checks on required fields in $args array ##
        if (
			! isset( $args )
			|| is_null( $args )
            || ! is_array( $args )
        ){

			// log ##
			w__log( \willow()->get('_args')['task'].'~>e:>Missing required args, so stopping here' );
			
			// w__log( 'd:>Kicked here...' );

            return false;

		}

		// w__log( $args['config']['post'] );

		// If posts is passed as an int, then get a matching post Object, as we can use the data later ## 
		// validate passed post ##
		if ( 
			isset( $args['config']['post'] ) 
			// is_int( $args['config']['post'] )
			&& ! $args['config']['post'] instanceof \WP_Post
		) {

			// get new post, if corrupt ##
			$args['config']['post'] = willow\get\post::object( $args['config'] );

			// w__log( 'Post set, but not an Object.. so getting again..: '.$args['config']['post']->ID );

		}

		// no post set ##
		if ( ! isset( $args['config']['post'] ) ) {

			$args['config']['post'] = willow\get\post::object();

			// w__log( 'No post set, so getting: '.$args['config']['post']->ID );

		}

		// last check ##
		if ( ! isset( $args['config']['post'] ) ) {

			// w__log( 'Error with post object, validate - returned as null.' );

			$args['config']['post'] = null;

			// return false;

		}

		// w__log( $args['config']['post']->ID );

		// store current _args stage ##
		\willow()->set( '_args', $args );

        // assign properties with initial filters ##
		$this->assign();

		// get _args again, in case they changed ##
		$_args = \willow()->get( '_args' );
		
		// w__log( $args );

        // // check if module asked to run $args['config']['run']
        // if ( 
        //     // isset( $args['config']['run'] )
        //     // && 
        //     false === $args['config']['run']
        // ){

		// 	// log ##
		// 	w__log( self::$args['task'].'~>n:>config->run defined as false for: '.$args['task'].', so stopping here.. ' );

        //     return false;

		// }
		
		// check if module asked to run $args['config']['run']
		if ( 
            // isset( $args['config']['run'] )
			// && 
			isset( $_args['config']['run'] )
            && false === $_args['config']['run']
        ){

			// w__log( $args );

			// log ##
			w__log( $_args['task'].'~>n:>config->run defined as false for: '.$_args['task'].', so stopping here.. ' );
			// w__log( 'd:>config run defined as false for: '.$args['task'].', so stopping here..' );

            return false;

        }

        // ok - should be good ##
        return true;

	}

    /**
     * Assign class properties with initial filters, merging in passed $args from calling method
	 * 
	 * @since 	1.0.0
	 * @return
     */
    public function assign() {

		// get local copy ##
		$_args = \willow()->get( '_args' );

        // apply global filter to $args - specific calls should be controlled by parameters included directly ##
        $_args = \willow()->filter->apply([
			'filter'        => 'willow/render/args',
			'parameters'    => $_args,
			'return'        => $_args
		]);
		
		// apply template level filter to $args - specific calls should be controlled by parameters included directly ##
        $_args = \willow()->filter->apply([
			'filter'        => 'willow/render/args/'.\willow\core\template::get(),
			'parameters'    => $_args,
			'return'        => $_args
        ]);

		// w__log( core\config::$config );
			
		// merge CONTEXT->global settings - this allows to pass config per context ##
		if ( $config = \willow()->config->get([ 'context' => $_args['context'], 'task' => 'config' ]) ){

			// w__log( 'd:>Merging settings from: '.$_args['context'].'->config' );
			$context_config = [ 'config' => $config ];
			// w__log( $context_config );

			// w__log( $args );

			// merge in global__CONTEXT settings ##
			// w__log( 't:>NOTE, swapped order of merge here to try to give preference to task args over global args... keep an eye' );
			$_args = willow\core\arrays::parse_args( $_args, $context_config );
			// $args = core\arrays::parse_args( $context_config, $args );

			// w__log( $_args );

		}

		// grab all passed args and merge with defaults -- this ensures we have config->run, config->debug etc.. ##
		$_args = willow\core\arrays::parse_args( $_args, \willow()->get( '_args_default' ) );

		// w__log( $_args['markup'] );

		// store object property ##
		\willow()->set( '_args', $_args );

		// post-format markup to extract markup keys collected by config ##
		\willow()->render->markup->merge();
		
        // return ##
        return;

	}

	/**
	 * Define config->default value
	 * 
	 * @since 4.1.0
	*/
	public function default( array $array = null ){

		// sanity ##
		if(
			is_null( $array )
			|| ! is_array( $array )
		){

			w__log( 'Error in passed default parameter' );

			return false;

		}

		$_args = \willow()->get( '_args' );
		$_args['config']['default'] = $array;
		\willow()->set( '_args' , $_args );

		// self::$args['config']['default'] = $array;

		w__log( 'd:>Default value set to: '.var_export( $array, true ) );

		return true;

	}

	/**
	 * Prepare passed args ##
	 *
	 */
	public function prepare( array $args = null ) {

		// w__log( 't:>merge with args::validate, just need to get config right..' );

		// sanity ##
		if (
			is_null( $args )
		 	|| ! is_array( $args )
		){

		 	w__log( 'e:>Error in passed args' );

		 	return false;

		}

		// get calling method for filters ##
		$task = willow\core\backtrace::get([ 'level' => 2, 'return' => 'function' ]);

		// define context for all in class -- i.e "group" ##
		if ( ! isset( $args['context'] ) ) {
			$args['context'] = 'global';
		}

		// let's set "task" to calling function, for debugging ##
		if ( ! isset( $args['task'] ) ) {
			$args['task'] = $task;
		}

		// w__log( $args );

		// get stored config via lookup, fallback 
		// pulls from Q, but available to filter via willow/config/load ##
		$config = \willow()->config->get( $args );

		// test ##
		// w__log( $config );

		// Parse incoming $args into an array and merge it with $config defaults ##
		// allows specific calling methods to alter passed $args ##
		if ( $config ) {

			$args = willow\core\arrays::parse_args( $args, $config );

		}

		// w__log( $config );

        // checks on required fields in $args array ##
        if (
			// ! isset( $args )
			is_null( $args )
            || ! is_array( $args )
        ){

			// log ##
			w__log( 'e:>Missing required args, so stopping here' );
			
			// w__log( 'Kicked here...' );

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
		// // w__log( $config );

		// // Parse incoming $args into an array and merge it with $config defaults ##
		// // allows specific calling methods to alter passed $args ##
		// if ( $config ) $args = \wp_parse_args( $args, $config );

		// w__log( $config );
		// w__log( $args );

		// merge any default args with any pass args ##
		// if (
		// 	is_null( $args )
		// 	|| ! is_array( $args )
		// ) {

		// 	w__log( 'Error in passed $args' );

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

			w__log( 'Error with post object, validate - returned as null.' );

			$args['config']['post'] = null;

			// return false;

		}

		// kick back args ##
		return $args;

	}
	
    public function is_enabled(){

		// local vars ##
		$_args = \willow()->get( '_args' );
		$_fields = \willow()->get( '_fields' );

        // sanity ##
        if ( 
            ! $_args 
            || ! is_array( $_args )
        ) {

			// log ##
			w__log( $_args['task'].'~>e:>Error in passed $_args');

            return false;

        }

        // helper::log( self::$fields );
        // helper::log( 'We are looking for field: '.$_args['enable'] );

        // check for enabled flag - if none, return true ##
        // we also take one guess at the field name -- if it's not passed in config ##
        if ( 
            ! isset( $_args['enable'] )
            && ! isset( $_fields[$_args['task'].'_enable'] )
        ) {

			// log ##
			w__log( $_args['task'].'~>n:>No enable defined in $args NOR enable field found for task: "'.$_args['task'].'"');

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
			w__log( $_args['task'].'~>n:>Field task: "'.$_args['task'].'" Enabled, continue');

            return true;

        }

		// log ##
		w__log( $_args['task'].'~>n:>Field Group: "'.$_args['task'].'" NOT Enabled, stopping.');

        // negative ##
        return false;

    }
     
}
