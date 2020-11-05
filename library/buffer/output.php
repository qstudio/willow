<?php

namespace willow\buffer;

use willow\core;
use willow\core\helper as h;
use willow;
use willow\render;
use willow\buffer;

\willow\buffer\output::__run();

class output extends willow\buffer {

	// protected static $is_willow;

	/**
	 * Check for view template and start OB, if correct
	*/
	public static function __run(){

		// https://stackoverflow.com/questions/38693992/notice-ob-end-flush-failed-to-send-buffer-of-zlib-output-compression-1-in
		\remove_action( 'shutdown', 'wp_ob_end_flush_all', 1 );
		\add_action( 'shutdown', function() {
			while ( @ob_end_flush() );
		} );

		// start here ##
		self::$filter = [];

		// not on admin ##
		if ( \is_admin() ) return false;

		// \add_action( 'get_header',  [ get_class(), 'ob_start' ], 0 ); // try -- template_redirect.. was init
		\add_action( 'wp',  function(){  // was 'wp'
			
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

			if ( 'willow' != core\method::template_format() ){

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

			// HTML <head> ##
			// willow\context::ui__head();
		
			// Output is directly echoed, once it has been parsed ##
			echo self::prepare( $string );

			// Footer, basically just wp_footer() + closing </body> / </html> tags  ##
			// willow\context::ui__footer();

			// reset all args ##
			render\args::reset();

		}, 0 );

	}


	/**
	 * Prepare output for Buffer
	 * 
	 * @since 4.1.0
	*/
    public static function prepare( String $string = null ) {

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
		self::$buffer_args 		= [
			'config'			=> [
				'return' 		=> 'return',
				'debug'			=> false,
			],
			'context'			=> 'primary',
			'task'				=> 'prepare',
		];

		// take buffer output string as markup->template ##
		self::$buffer_markup = $string; // used for parsers to reference buffer markup template ##
		self::$markup_template = $string; // original markup reference

		// force methods to return for collection by output buffer ##
		self::$args_default['config']['return'] = 'return';

		// prepare .willow template markup -- affects self::$buffer_map ##
		willow\parse::prepare( self::$buffer_args, 'primary' );

		// h::log( self::$buffer_map );
		self::$buffer_markup = buffer\map::prepare();
		// h::log( self::$buffer_markup );

		// clean up left over tags ##
		willow\parse::cleanup( self::$buffer_args, 'primary' );
		
		// reset properties ##
		self::$buffer_map = [];
		self::$buffer_args = null;
		self::$filter = null;

		// return to OB to render in template ##
		return self::$buffer_markup;

    }

}
