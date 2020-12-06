<?php

namespace Q\willow\get;

// Q ##
use Q\willow;
use Q\willow\core\helper as h;

class theme {

	private
		$plugin = null // this
	;

	/**
	 * 
     */
    public function __construct( \Q\willow\plugin $plugin ){

		// grab passed plugin object ## 
		$this->plugin = $plugin;

	}
		
	/**
     * Get body classes
     *
     * @since       1.0.2
     * @return      string   HTML
     */
    public static function body_class( $args = array() ){

        // set-up new array ##
        $array = [];

        // get page template ##
        $template =
            \get_body_class() ?
            (array) \get_body_class() :
            array( "home" ) ;

        // add to array ##
        array_push( $array, $template[0] );

        // add page-$post_name ##
        if ( $post_name = ( isset( $the_post ) && \get_post_type() == "page" ) ? "page-{$the_post->post_name}" : false ) {

            // add to array ##
            array_push( $array, $post_name );

        }

        // added passed element, if ! is_null ##
        if ( isset ( $args['class'] ) ) {

            // w__log( 'Adding passed class..' );

            if ( is_array( $args['class'] ) ) {
                
                $args['class'] = implode( array_filter( $args['class'] ), ' ' ) ;

            } 

            // add it in ##
            array_push( $array, $args['class'] );

        }

		// w__log( $array );
		
        // check if we've got an array - if so filter and implode it ##
        $string =
            is_array( $array ) ?
            implode( array_filter( $array ), ' ' ) :
            $array ;

        // kick it back ##
        return $string;

	}

    /**
    * Get installed theme data
    *
    * @return  Object
    * @since   0.3
    */
    public static function data( $refresh = false ){

       if ( $refresh ) {

           #echo 'refrshing stored theme data<br />'; ##
           \delete_site_option( 'q_theme_data' ); // delete option ##

       }

       // declare global variable ##
       global $q_theme_data;

       $array = \get_site_option( 'q_theme_data' );

       if ( ! \get_site_option( 'q_theme_data' ) ) {

           #echo 'stored theme option empty<br />';
           #$array = @file_get_contents( q_get_option("uri_parent")."library/version/");

           if( function_exists( 'wp_get_theme' ) ) {
               $array = \wp_get_theme( \get_site_option( 'template' ));
               #$theme_version = $theme_data->Version;
           } else {
               $array = \get_theme_data( \get_template_directory() . '/style.css');
               #$theme_version = $theme_data['Version'];
           }
           #$theme_base = get_option('template');

           if ( $array ) {

               willow\core\method::add_update_option( 'q_theme_data', $array, '', 'yes' );
               #echo 'stored fresh theme data<br />';

           }

       }

       return willow\core\method::array_to_object( $array );

    }

	public static function is_child(){

		$theme = \wp_get_theme(); // gets the current theme
		// w__log( $theme );

		if ( $theme->template ) {
			return true;
		}

		return false;

	}

	public static function is_parent(){

		$theme = \wp_get_theme(); // gets the current theme
		
		if ( ! $theme->parent_theme ) {
			return true;
		}

		return false;

	}


}	
