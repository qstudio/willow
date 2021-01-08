<?php

namespace willow\view;

use willow;
use willow\core\helper as h;

class filter {
    
	private 

		// Array of custom templates to add ##
		$view_custom = [
			/*
			'dashboard'       		=> [ 
				'template'			=> 'dashboard.willow',
				'title'  			=> 'User : Dashboard',
				'class'  			=> '\q_user' // if empty, checks child theme then parent theme in 'view/' path, else indicates plugin class ##
			],
			*/
		],

		$view_custom_filtered = false,

		// Array of Native templates to override ##
		$view_native = [
			/*
			'single-post'   => [
				'function'  => 'is_singular',
				'argument'  => 'post',
				'template'  => 'single-post.php',
				'class'     => '', // if empty, checks child theme then parent theme in 'view/' path, else indicates plugin class ##
			],
			*/
		],

		$view_native_filtered = false,

		// default page template - WP default ##
		$view_default = 'index.php'

	;

	// tracker ##
	private static $view_tracker = null;
	
	function __construct(){

		// empty ##

	}

    /**
    * Kick things off
    */
    function hooks(){

		// we need to filter $view_custom to allow plugins to inject extra templates ##

        // Add a filter to the attributes metabox to inject template into the cache.
        if ( version_compare( floatval( \get_bloginfo( 'version' ) ), '4.7', '<' ) ) {

            // 4.6 and older
            \add_filter( 'page_attributes_dropdown_pages_args', array( $this, 'register_view_custom' ) );

        } else {

            // Add a filter to the wp 4.7 version attributes metabox
            \add_filter( 'theme_page_templates', array( $this, 'add_view_custom' ) );

        }

        // Add a filter to the save post to inject our template into the page cache
        \add_filter( 'wp_insert_post_data', array( $this, 'register_view_custom' ) );

        // Add a filter to the template include to determine if the page has our 
        // template assigned and return it's path
        \add_filter( 'template_include', array( $this, 'check_custom_template' ), 3, 1 );

        // override native templates ##
        \add_filter( 'template_include', array( $this, 'add_view_native' ), 2, 1 );

	}
	
	/**
	 * Allow array of custom templates to be filtered 
	 * 
	 * @since	2.0.5
	 * @return	Mixed
	*/
	function get_view_custom() {

		// this filter only runs once - check if run first ##
		if( 
			$this->view_custom
			&& $this->view_custom_filtered 
		){

			return $this->view_custom;

		}

		// filter list - allow new items to be added ##
		$this->view_custom = \apply_filters( 'willow/view/custom', $this->view_custom );

		// update tracker ## 
		$this->view_custom_filtered = true;

		// return array ##
		return $this->view_custom;

	}
	
	/**
	 * Allow array of native templates to be filtered 
	 * 
	 * @since	2.0.5
	 * @return	Mixed
	*/
	function get_view_native() {

		// this filter only runs once - check if run first ##
		if( 
			$this->view_native
			&& $this->view_native_filtered 
		){

			return $this->view_native;

		}

		// filter list - allow new items to be added ##
		$this->view_native = \apply_filters( 'willow/view/native', $this->view_native );

		// update tracker ## 
		$this->view_native_filtered = true;

		// return array ##
		return $this->view_native;

    }

    /**
     * Add custom templates defined in external plugins
     */
    function custom( $array, $templates = null, $class = null ){

        // let's check if we have any items to add, and format them as required ##
        if ( 
            ! isset( $templates ) 
            || ! is_array( $templates ) 
            || count( array_filter( $templates ) ) == 0
        ) {

            // h::log( 'No custom templates to add to array' );

            return $array;

        }

        // h::log( 'we have '.count( $templates ).' custom templates to add' );
            
        // define a new array ##
        $new_array = [];

        // loop over each item ##
        foreach( $templates as $key => $value ){

            $new_array[$key] = [
                'name'      => $value,
                'class'     => $class, // q_theme -- works as this class extends the parent implicitly ##
                'template'  => $key
            ];

        }

        // we are passed an array, let's peek at it ##
        // h::log( $array );

        // test ##
        // h::log( $new_array );

        // merge array - adding new items to the end and overwritting shared keys ##
        $return_array = array_merge( $array, $new_array );

        // test ##
        // h::log( $return_array );

        // kick back ##
        return $return_array;

    }

    /**
     * Add native templates defined in external plugins
     */
    function native( $array, $templates = null, $class = null ){

        // let's check if we have any items to add, and format them as required ##
        if ( 
            ! isset( $templates ) 
            || ! is_array( $templates ) 
            || count( array_filter( $templates ) ) == 0
        ) {

            // h::log( 'No native templates to add to array' );

            return $array;

        }

        // h::log( 'we have '.count( $this->view_native ).' native templates to add' );
            
        // define a new array ##
        $new_array = $templates;

        // loop over each item -- add class identifier ##
        foreach( $templates as $key => $value ){

            $new_array[$key]['class'] = $class; // q_theme -- works as this class extends the parent implicitly ##

        }

        // we are passed an array, let's peek at it ##
        // h::log( $array );

        // test ##
        // h::log( $new_array );

        // merge array - adding new items to the end and overwritting shared keys ##
        $return_array = array_merge( $array, $new_array );

        // test ##
        // h::log( $return_array );

        // kick back ##
        return $return_array;

    }

    /**
     * Default template
     * 
     * @since 	2.0.5
	 * @return	String
     */
    function get_view_default( $template ){
        
        // look for default template - in q_theme
        $template =  
            h::get( 'view/'.$this->view_default, 'return', 'path' ) ? 
            $this->view_default = h::get( 'view/'.$this->view_default, 'return', 'path' ) : 
            $template ;

        // filter ##
        $template = \apply_filters( 'q/view/default', $template );

        // return ##
        return $template;

    }

	/**
	 * Helper function to validate format of array holding custom templates
	 * 
	 * @since 	2.0.5
	 * @return	Mixed		
	*/
    function format_view_custom(){

		// make sure custom template filter runs ##
		$this->get_view_custom();

        // sanity ##
        if ( 
            is_null( $this->view_custom ) 
			|| ! is_array( $this->view_custom )
			|| count( array_filter( $this->view_custom ) ) == 0
        ) {

            h::log( 'There are no custom templates to add' );

            return false;

        }

        // loop over each item and return in required format ##
        // [ 'file.php' => 'Name', ];
        $array = [];
        foreach( $this->view_custom as $key => $value ) {

			// h::log( $key );

			// $array[ $key ] = $value['name'];
			$array[ $value['template'] ] = $value['name'];

        }

        // test ##
        // h::log( $array );

        // kick it back ##
		return $array;
		// $this->view_custom = $array;

    }

    /**
	 * Adds our template to the WP admin dropdown for v4.7+
	 *
     * @since       0.1.0
     * @return      Array
	 */
	function add_view_custom( $templates ){

        // filter in external custom templates ##
        // We also need to format the templates to what WP expects [ 'file.php' => 'Name', ]; ##
        // $this->view_custom = 
            // $this->format_view_custom( $this->get_view_custom() );
		// );
		
		// h::log( $this->get_view_custom() );

		// make sure custom template filter runs ##
		$this->get_view_custom();

		// template are stored in key => name format 
		/*
		[
			'frontpage.php' => 'Frontpage',
			'page.php'		=> 'Page'
		]
		*/

		// Format the templates to what WP expects ##
		$custom_templates = $this->format_view_custom(); 

		// h::log( $custom_templates );
        
        // merge into known list ##
    	$templates = array_merge( $templates, $custom_templates ); // $this->view_custom
        
        // return ##
        return $templates;

	}

    /**
    * Register extra templates to the WP cache
    *
    * @since        0.1.0
    * @return       Array
    */
    function register_view_custom( $atts ) {

        // Create the key used for the themes cache
        $cache_key = 'page_templates-' . md5( \get_theme_root() . '/' . \get_stylesheet() );

        // Retrieve the cache list. 
        // If it doesn't exist, or it's empty prepare an array
        $templates = wp_get_theme()->get_page_templates();
        if ( empty( $templates ) ) {
            $templates = array();
        } 

        // New cache, therefore remove the old one
        \wp_cache_delete( $cache_key , 'themes');

        // filter in external custom templates ##
        // We also need to format the templates to what WP expects [ 'file.php' => 'Name', ]; ##
        // $this->view_custom = 
            // $this->format_view_custom( \apply_filters( 'q/view/custom', $this->view_custom )
		// );

		// Format the templates to what WP expects [ 'file.php' => 'Name', ]; ##
		$custom_templates = $this->format_view_custom();
		
		// h::log( $custom_templates );

        // Now add our template to the list of templates by merging our templates
        // with the existing templates array from the cache.
        $templates = array_merge( $templates, $custom_templates ); // $this->view_custom

        // Add the modified cache to allow WordPress to pick it up for listing
        // available templates
        \wp_cache_add( $cache_key, $templates, 'themes', 1800 );

        // kick it back ##
        return $atts;

    }

    /**
    * Checks if the template is assigned to the page
    *
    * @since        0.1.0
    * @return       String
    */
    function check_custom_template( $template ) {
        
        // force default template --- @TESTING ##
		// $template = $this->get_view_default( $template );
		
        // check tracker ##
        if ( self::track() ) {

            h::log( 'e:>Template already defined: '.self::track('get') );

            return self::track('get');

        }

        // test ##
        // h::log( 'e:>Template at start: '.$template );

        // Get global post
        global $post;

        // Return template if post is empty
        if ( 
            ! $post 
            || \is_search()
            || \is_404()
        ) {

            // h::log( 'e:>No post, is_search or is_404 matched - using '.$template );

            return $template;

        }

        // get template ##
        if ( ! $_wp_page_template = \get_post_meta( $post->ID, '_wp_page_template', true ) ) {

            // h::log( 'e:>No stored match in _wp_page_template - using: '.$template );

            return $template;

        }

        // we need to check if this is empty ##
        // h::log( 'e:>$_wp_page_template: '.$_wp_page_template );

        // filter in external custom templates ##
        // remember that the format has changed ##
        // $this->view_custom = \apply_filters( 'q/view/custom', $this->view_custom );

        // Return default template if we don't have a custom one defined
        if ( ! isset( $this->get_view_custom()[ $_wp_page_template ] ) ) {

            // h::log( 'e:>No matching custom template found, so kicking back default: '.$template );

            return $template;

        } 

        // we need to include the 'class' parameter, if the templates has this defined by an extended plugin ##
        $class = 
            (
                isset( $this->get_view_custom()[ $_wp_page_template ]['class'] )
                && ! is_null( $this->get_view_custom()[ $_wp_page_template ]['class'] )
            ) ?
            $this->get_view_custom()[ $_wp_page_template ]['class'] :
            null ;

        // look for file - fallback to default if not found ##
        if ( 
            $file = h::get( 
                'view/'.$_wp_page_template, 
                'return', 
                'path',
                'library/', // standard base library path ##
                $class // variable class ##
            ) 
        ) {

            // h::log( 'e:>template file found and set to: '.$file );

            // add global ##
            $GLOBALS['q_template'] = $_wp_page_template;

            // update tracker ##
            self::track( 'set', $file );

            // kick it back ##
            return $file;

        }

        // test ##
        // h::log( 'e:>custom file not found, kicking back default template: '.$template );

        // return ##
        return $template;

    }

    /**
    * Template loader.
    *
    * The template loader will check if WP is loading a template
    * for a specific Post Type and will try to load the template
    * from out 'templates' directory.
    *
    * @since 1.0.0
    *
    * @param	string	$template	Template file that is being loaded.
    * @return	string				Template file that should be loaded.
    */
    function add_view_native( $template ) {

        // h::log( 'Native: '.$template );

        // check tracker ##
        if ( self::track() ) {

            h::log( 'Template already defined: '.self::track( 'get' ) );

            return self::track( 'get' );

        }
        
        // force default template ##
        $template = $this->get_view_default( $template );

        // filter in external native templates ##
        // remember that the format has changed ##
        // $this->view_native = \apply_filters( 'q/view/native', $this->view_native );

        if ( 
            ! array_filter( $this->get_view_native() ) 
        ) {

            // h::log( 'not filtering any native templates.' );

            return $template;

        }

        foreach( $this->get_view_native() as $key => $item ) {

            // we need to include the 'class' parameter, if the templates has this defined by an extended plugin ##
            $class = 
				(
					isset( $item['class'] )
					&& ! is_null( $item['class'] )
				) ?
				$item['class'] :
				null ;
             
            // h::log( 'template: '.$item["template"].' / rule: '.$item["function"].' / class: '.$class );

            if ( ! function_exists( $item["function"] ) ) {

				// nothing cooking -- kick back orginal ##
				// h::log( 'function DOES NOT exists: '.$item['function'] );

                // return $template;
                continue;

            }

            // h::log( 'function exists: '.$item['function'] );

            if ( 
                FALSE === call_user_func_array( $item["function"], array( $item["argument"] ) ) 
            ) {

                // nothing cooking -- kick back orginal ##
                // return $template;
                continue;

            }

            // h::log( 'function matched: '.$item["function"] );

            if ( 
                $template = h::get( 'view/'.$item["template"], 
                    'return', 
                    'path',
                    'library/', // standard base library path ##
                    $class // variable class ##
                ) ) {
                
                // $template = h::get( 'theme/view/'.$item["template"], 'return', 'path' );

                // h::log( 'New template loaded: '.$item["template"] );

                // add global ##
                $GLOBALS['q_template'] = $item["template"];

                // update tracker ##
                self::track( 'set', $template );

                // kick it back ##
                return $template;

            }

        }

        // h::log( 'return default template: '.$template );

        // nothing cooking -- kick back orginal ##
        return $template;

	}
	
	/**
     * Tracking Method
     */
    public static function track( $option = 'status', $template = null ){

        switch ( $option ) {

            case "status" :

                // h::log( 'Checking Tracker: '.( ! is_null( self::$view_tracker ) ? self::$view_tracker : 'null' ) );

                // check on tracker status ##
                return ( true === self::$view_tracker ) ? true : false ;

            break ;

            case "reset" :

                // h::log( 'Reset Tracker' );

                // empty stored template ##
                return self::$view_tracker = null ;

            break ;

            case "set" :

                // h::log( 'Set Tracker: '.$template );

                // set stored template ##
                return self::$view_tracker = $template;

            break ;

            case "get" :

                // h::log( 'Get Tracker: '.self::$view_tracker );

                // returned stored template ##
                return self::$view_tracker;

            break ;

        }

    }

}
