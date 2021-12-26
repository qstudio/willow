<?php

namespace willow\buffer;

use willow;

class output {

	/**
     * Plugin Instance
     *
     * @var     Object      $plugin
     */
	protected 
		$plugin = false
		// $is_willow // @TODO
	;

	/**
	 * CLass Constructer 
	*/
	function __construct( \willow\plugin $plugin ){

        // grab plugin instance ## 
		$this->plugin = $plugin;

	}

	/**
     * callback method for class instantiation
	 * Check for view template and start OB, if correct
     *
     * @since   0.0.2
     * @return  void
     */
	public function hooks() {

		// sanity ##
        if( 
            is_null( $this->plugin )
            || ! ( $this->plugin instanceof \willow\plugin ) 
        ) {

            error_log( 'Error in object instance passed to '.__CLASS__ );

            return false;
        
		}
		
		// w__log( 'd:>Buffer hooks run..' );

		// https://stackoverflow.com/questions/38693992/notice-ob-end-flush-failed-to-send-buffer-of-zlib-output-compression-1-in
		\remove_action( 'shutdown', 'wp_ob_end_flush_all', 1 );
		\add_action( 'shutdown', function() {
			while ( @ob_end_flush() );
		} );

		// set _filter var to empty array ##
		\willow()->set( '_filter', [] );

		// not on admin ##
		if ( \is_admin() ) {

			// w__log( 'd:>Not running on admin' );
			
			return false;

		}

		// \add_action( 'get_header',  [ get_class(), 'ob_start' ], 0 ); // try -- template_redirect.. was init
		\add_action( 'wp',  function(){ 

			// hook up filters ##
			\willow()->filter->hooks();
			
			// if ( 'willow' == willow\core\template::format() ){

				// w__log( 'e:>starting OB, as on a willow template: "'.\willow\core\template::format().'"' );
				// w__log( 't:>TODO -- find out why large template content breaks this...??' );
				return ob_start();

			// }

			// w__log( 'e:>not a willow template, so no ob: "'.\willow\core\template::format().'"' );

			// return false; 

		}
		, 1 ); 

		\add_action( 'shutdown', function() {

			if ( 'willow' != willow\core\template::format() ){

				// w__log( 'e:>No buffer.. so no go' );

				// ob_flush();
				if( ob_get_level() > 0 ) {
					ob_flush();
				}
				
				return false; 
			
			}

			// w__log( 'e:>Doing shutdown buffer' );

			$string = '';
		
			// We'll need to get the number of ob levels we're in, so that we can iterate over each, collecting
			// that buffer's output into the final output.
			$levels = ob_get_level();
			// w__log( $levels );
		
			for ($i = 0; $i < $levels; $i++) {
				$string .= ob_get_clean();
			}

			// w__log( 'e:>String: '.$string );

			// ob_flush();
			if( ob_get_level() > 0 ) {
				ob_flush();
			}

			// Output is directly echoed, once it has been parsed ##
			echo $this->prepare( $string );

			// reset all args ##
			\willow()->render->args->reset();

		}, 0 );

	}

	/**
	 * Prepare output for Buffer
	 * 
	 * @since 4.1.0
	*/
    public function prepare( String $string = null ) {

		// sanity ##
		if ( 
			is_null( $string )
		){

			// log ##
			w__log( 'e:>$buffer is empty, so nothing to render.. stopping here.');

			// kick out ##
			return false;

		}

		// build factory objects ##
		// \willow()->factory( $this->plugin );

		// we are passed an html string, captured from output buffering, which we need to parse for tags and process ##
		// w__log( $string );

		// build required args ##
		\willow()->set( '_buffer_args', [
			'config'			=> [
				'return' 		=> 'return',
				'debug'			=> false,
			],
			'context'			=> 'primary',
			'task'				=> 'prepare',
		] );

		// take buffer output string as markup->template ##
		\willow()->set( '_buffer_markup', $string ); // used for parsers to reference buffer markup template ##
		\willow()->set( '_markup_template', $string ); // original markup reference

		// force methods to return for collection by output buffer ##
		$_args_default = \willow()->get( '_args_default' );
		$_args_default['config']['return'] = 'return';
		\willow()->set( '_args_default', $_args_default );

		// prepare .willow template markup -- affects _buffer_map ##
		\willow()->parser->hooks( \willow()->get( '_buffer_args' ), 'primary' );

		// w__log( \willow()->get( '_buffer_map' ) );
		$_buffer_map = \willow()->buffer_map->prepare();
		\willow()->set( '_buffer_markup', $_buffer_map );
		// w__log( \willow()->get( '_buffer_markup' ) );

		// clean up left over tags ##
		\willow()->parse->cleanup->hooks( \willow()->get( '_buffer_args' ), 'primary' ); // @TODO - removed for testing ##
		
		// reset properties ##
		\willow()->set( '_buffer_map', [] );
		\willow()->set( '_buffer_args', null );
		\willow()->set( '_filter', null );

		// return to OB to render in template ##
		return \willow()->get( '_buffer_markup' );

    }

}
