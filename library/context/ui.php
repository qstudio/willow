<?php

namespace q\willow\context;

use q\core\helper as h;
// use q\ui;
use q\get;
use q\willow;
use q\willow\core;
use q\willow\context;
use q\willow\render; 

// Q Theme ##
use q\theme;

class ui extends willow\context {


	/**
     * Generic Getter - looks for properties in config matching context->task
	 * can be loaded as a string in context/ui file
     *
     * @param       Array       $args
     * @since       1.4.1
	 * @uses		render\fields::define
     * @return      Array
     */
    public static function get( $args = null ) {

		// h::log( $args );

		// look for property "args->task" in config ##
		if ( 
			$config = core\config::get([ 'context' => $args['context'], 'task' => $args['task'] ])
		){
			// h::log( $config );
			
			// "args->fields" are used for type and callback lookups ##
			// self::$args['fields'] = $array['fields']; 

			// define "fields", passing returned data ##
			render\fields::define(
				$config
			);

		}

	}
	

	/**
     * get_header
     *
     * @since       1.0.2
     * @return      string   HTML
     */
    public static function header( $args = null )
    {

		$name = null;
		if ( isset( $args['name'] ) ) {
			$name = $args['name'];
		}
		\do_action( 'get_header', $name );

		return theme\view\ui\header::render( $args );
		// return render\fields::define([
		// 	'header' => '' // hack.. nothing to pass here ##
		// ]);

	}



	/**
     * get_footer
     *
     * @since       1.0.2
     * @return      string   HTML
     */
    public static function footer( $args = null )
    {

		$name = null;
		if ( isset( $args['name'] ) ) {
			$name = $args['name'];
		}
		\do_action( 'get_footer', $name );

		return theme\view\ui\footer::render( $args );
		// return render\fields::define([
		// 	'footer' => '' // hack.. nothing to pass here ##
		// ]);

	}



	/**
     * Open .content HTML
     *
     * @since       1.0.2
     * @return      string   HTML
     */
    public static function open( $args = null )
    {

		return render\fields::define([
			'classes' => get\theme::body_class( $args ) // grab classes ##
		]);

	}

	

	/**
     * Open .content HTML
     *
     * @since       1.0.2
     * @return      string   HTML
     */
    public static function close( $args = null )
    {

        // set-up new array -- nothing really to do ##
		// grab classes ##
		return render\fields::define([
			'oh' => '' // hack.. nothing to pass here ##
		]);

	}


	
	/**
     * comment_template
     *
	 * @todo 		allow for passing markup
     * @since       1.0.2
     * @return      string   HTML
     */
    public static function comment( $args = null )
    {

		return theme\view\ui\comment::render( $args );

	}



	/**
	 * @todo --- if really required ??
	 * 
	*/
	/*
    public static function password_form()
    {

?>
        <div class="password" style="text-align: center; margin: 20px;">
            <?php echo \get_the_password_form(); ?>
        </div>
<?php

	}
	*/


}
