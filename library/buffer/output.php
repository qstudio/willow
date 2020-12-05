<?php

namespace Q\willow\buffer;

use Q\willow;

class output {

	/**
     * Plugin Instance
     *
     * @var     Object      $plugin
     */
	protected 
		$plugin
		// $is_willow // @TODO
	;

	/**
	 * CLass Constructer 
	*/
	function __construct( $plugin = null ){

		// Log::write( $plugin );

        // grab passed plugin object ## 
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
            || ! ( $this->plugin instanceof \Q\willow\plugin ) 
        ) {

            error_log( 'Error in object instance passed to '.__CLASS__ );

            return false;
        
		}
		
		// $this->plugin->log( 'd:>Buffer hooks run..' );

		// https://stackoverflow.com/questions/38693992/notice-ob-end-flush-failed-to-send-buffer-of-zlib-output-compression-1-in
		\remove_action( 'shutdown', 'wp_ob_end_flush_all', 1 );
		\add_action( 'shutdown', function() {
			while ( @ob_end_flush() );
		} );

		// set _filter var to empty array ##
		$this->plugin->set( '_filter', [] );

		// not on admin ##
		if ( \is_admin() ) {

			// $this->plugin->log( 'd:>Not running on admin' );
			
			return false;

		}

		// \add_action( 'get_header',  [ get_class(), 'ob_start' ], 0 ); // try -- template_redirect.. was init
		\add_action( 'wp',  function(){ 
			
			// if ( 'willow' == \q\view\is::format() ){

				// h::log( 'e:>starting OB, as on a willow template: "'.\q\view\is::format().'"' );
				// h::log( 't:>TODO -- find out why large template content breaks this...??' );
				return ob_start();

			// }

			// h::log( 'e:>not a willow template, so no ob: "'.\q\view\is::format().'"' );

			// return false; 

		}
		, 1 ); 

		\add_action( 'shutdown', function() {

			if ( 'willow' != willow\core\method::template_format() ){

				// h::log( 'e:>No buffer.. so no go' );

				// ob_flush();
				if( ob_get_level() > 0 ) ob_flush();
				
				return false; 
			
			}

			// h::log( 'e:>Doing shutdown buffer' );

			$string = '';
		
			// We'll need to get the number of ob levels we're in, so that we can iterate over each, collecting
			// that buffer's output into the final output.
			$levels = ob_get_level();
			// h::log( $levels );
		
			for ($i = 0; $i < $levels; $i++) {
				$string .= ob_get_clean();
			}

			// h::log( 'e:>String: '.$string );

			// ob_flush();
			if( ob_get_level() > 0 ) ob_flush();

			// Output is directly echoed, once it has been parsed ##
			echo $this->prepare( $string );

			// reset all args ##
			$args = new \Q\willow\render\args( $this->plugin );
			$args->reset();

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
			h::log( 'e:>$buffer is empty, so nothing to render.. stopping here.');

			// kick out ##
			return false;

		}

		// we are passed an html string, captured from output buffering, which we need to parse for tags and process ##
		// h::log( $string );

		// build required args ##
		$this->plugin->set( '_buffer_args', [
			'config'			=> [
				'return' 		=> 'return',
				'debug'			=> false,
			],
			'context'			=> 'primary',
			'task'				=> 'prepare',
		] );

		// take buffer output string as markup->template ##
		$this->plugin->set( '_buffer_markup', $string ); // used for parsers to reference buffer markup template ##
		$this->plugin->set( '_markup_template', $string ); // original markup reference

		// force methods to return for collection by output buffer ##
		$args_default = $this->plugin->get( '_args_default' );
		$args_default['config']['return'] = 'return';
		$this->plugin->set( '_args_default', $args_default );

		// prepare .willow template markup -- affects _buffer_map ##
		$parse = new willow\parse\prepare( $this->plugin, $this->plugin->get( '_buffer_args' ), 'primary' );
		$parse->hooks();

		// h::log( self::$buffer_map );
		$buffer_map = new willow\buffer\map( $this->plugin );
		$this->plugin->set( '_buffer_markup', $buffer_map->prepare() );
		// h::log( self::$buffer_markup );

		// clean up left over tags ##
		// TODO - removed for testing ###
		// new willow\parse\cleanup( $this->plugin, $this->plugin->get( '_buffer_args' ), 'primary' );
		
		// reset properties ##
		$this->plugin->set( '_buffer_map', [] );
		$this->plugin->set( '_buffer_args', null );
		$this->plugin->set( '_filter', null );

		// return to OB to render in template ##
		return $this->plugin->get( '_buffer_markup' );

    }

}
