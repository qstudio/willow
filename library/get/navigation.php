<?php

namespace willow\get;

// Q ##
use willow\core;
use willow\core\helper as h;
use willow\get;
use willow\strings;

// Q Theme ##
use q\theme;

// Willow ##
// use willow;

class navigation extends \willow\get {


	/**
    * Get Pagination links
    *
    * @since       1.0.2
	* @return      String      HTML
	* @link	https://gist.github.com/mtx-z/f95af6cc6fb562eb1a1540ca715ed928
    */
	public static function pagination( $args = null ) {

		// h::log( $args );

		// sanity ##
		if (
			is_null( $args )
			|| ! is_array( $args )
		){

			h::log( 'e:>Error in pased args' );

			return false;

		}

		if ( 
			isset( $args['query'] )
		) {

			$query = $args['query'];

		// grab some global variables ##
		} else {
			
			// h::log( 'Grabbing global query..' );
			global $wp_query;
			$query = $wp_query;

		}

		// no query, no pagination ##
		if ( ! $query ) {

			h::log( 'e:>Nothing to query here' );

			return false;

		}
		
		// get config ##
		$config = core\config::get([ 'context' => 'navigation', 'task' => 'pagination' ]);

		// h::log( $config );

		// validate config ##
		if ( ! $config ) {

			h::log( 'e:>Error loading config' );

			return false;

		}

		// work out total ##
		$total = $query->max_num_pages;
		// h::log( 'Total: '.$total );

		// append query to pagination links, if set ##
		$fragement = '';

		// args to query WP ##
		$paginate_args = [
			// 'base'         			=> str_replace( 999999999, '%#%', \esc_url( \get_pagenum_link( 999999999 ) ) ),
			'base'                  => @\add_query_arg('paged','%#%'),
			'format'       			=> '?paged=%#%',
			'total'        			=> $total,
			'current'      			=> max( 1, \get_query_var( 'paged' ) ),
			'type'         			=> 'array',
			'show_all'              => false,
			'end_size'		        => $config['end_size'], 
			'mid_size'		        => $config['mid_size'],
			'prev_text'             => $config['prev_text'],
			'next_text'             => $config['next_text'],                   
		];

		// optionally add search query var ##
		if( ! empty( $query->query_vars['s'] ) ) {

			$paginate_args['add_args'] = array( 's' => \get_query_var( 's' ) );
			// $query_args['s'] = \get_query_var( 's' );
			$fragement .= '&s='.\get_query_var( 's' );
			
		}

		// h::log( $query_args );

		// filter args ##
		$paginate_args = \apply_filters( 'q/get/navigation/pagination/args', $paginate_args );

		// get links from WP ##
		$paginate_links = \paginate_links( $paginate_args );

		// check if we got any links ##
		if ( 
			! $paginate_links
			|| 0 == count( $paginate_links )
		) {

			// h::log( 'd:>$paginate_links empty.. bailing' );

			return false;

		}

		// test ##
		// h::log( $pages );
		// h::log( 'd:>paged: '.\get_query_var( 'paged' ) );

		// empty array ##
		$array = [];

		// prepare first item -- unless on first page ##
		if ( 0 != \get_query_var( 'paged' ) ) {
			$link_first = '?paged=1'.$fragement;
			$array[] = '<li class="'.$config['li_class'].'"><a class="'.$config['class_link_first'].'" rel="1" href="'.\esc_url( $link_first ).'">'.$config['first_text'].'</a></li>';
		}

		// merge pagination into links ##
		$array = array_merge( $array, $paginate_links ) ;

		// prepare last item ##
		if ( $total != \get_query_var( 'paged' ) ) {
			$link_last = '?paged='.$total.$fragement;
			$array[] = '<li class="'.$config['li_class'].'"><a class="'.$config['class_link_last'].'" rel="'.$total.'" href="'.\esc_url( $link_last ).'">'.$config['last_text'].'</a></li>';
		}
 
		// test ##
		// h::log( $array );

		// format page items ##
		$items = [];
		// $markup = $config['markup']['template']; // '<li class="%active-class%">%item%</li>' ##
		$i = 0;

		foreach ( $array as $page ) {

			// $row['class_link_item'] = $config['class_link_item'];
			$items[$i]['li_class'] = $config['li_class'];
			$items[$i]['item'] = str_replace( 'page-numbers', $config['class_link_item'], $page );
			$items[$i]['active-class'] = (strpos($page, 'current') !== false ? ' active' : '');

			// iterate ##
			$i ++;

		}

		// filters and checks ##
		$items = get\method::prepare_return( $args, $items );

		// h::log( $items );

		// markup array ##
		$string = \willow\strings\method::markup( $config['markup']['template'], $items, $config['markup'] );

		// h::log( $string );

		// echo ##
		// if ( 'return' == $return ){ 
			
			return $string ;

		// } else {

			// echo $string;

		// }

		// kick back ##
		// return true;
		
	}



	
    /**
     * Get Sibling pages
     *
     * @since       1.0.1
     * @return      string       HTML Menu
     */
    public static function siblings( $args = null )
    {

		// sanity ##
		if (
			is_null( $args )
			|| ! is_array( $args )
		){

			h::log( 'e:>Error in pased args' );

			return false;

		}

		// to make this more generic, we will get the current wp_post, if this is not passed ##
		if (
			! isset( $args['config']['post'] )
		){

			$args['config']['post'] = get\post::object();

		}

		// h::log( $args );

        // meta_query to exclude certain sub pages from desktop on screen sub navigation ##
        $meta_query = []; // nada ##

		// query for child or sibling's post ##
		// @todo - pass arguments from context/navigation
        $wp_args = array(
            'post_type'         => 'page', //$args->post_type,
            'post_parent'       => $args['config']['post']->post_parent,
            'orderby'           => 'menu_order',
            'order'             => 'ASC',
            'posts_per_page'    => -1,//$args->posts_per_page,
            // 'meta_query'        => $meta_query
        );

        #pr( $wp_args );

        $object = new \WP_Query( $wp_args );

        // test returned array ##
        // h::log( $object->posts );

        // nothing cooking ##
        if ( ! $object->have_posts() ) { return false; }

        // $posts array ##
		$array = [];
		
		// iterate ##
		$count = 0;

        // loop over all posts ##
        foreach ( $object->posts as $post ) {

            // make WP functions available ##
			setup_postdata( $post );
			
			// id ##
			$array[$count]['post_id'] = $post->ID ;

            // title ##
            $array[$count]['post_title'] = $post->post_title ;

            // permalink ##
            $array[$count]['post_permalink'] = \get_permalink( $post->ID );

            // class & highlight ##
            $array[$count]['highlight'] = 
                $post->ID === $args['config']['post']->ID ? 
                'active current' : 
                '' ;

            // sort out global $post after WP_Query loop ##
            \wp_reset_postdata();

			// iterate ##
			$count ++;

        }

        // test posts #
        // h::log( $array );

		// return ##
		return get\method::prepare_return( $args, $array );

	}
	



	/**
     * Get children pages
     *
     * @since       1.0.1
     * @return      string       HTML Menu
     */
    public static function children( $args = null ){

		// sanity ##
		if (
			is_null( $args )
			|| ! is_array( $args )
		){

			h::log( 'e:>Error in pased args' );

			return false;

		}

		// to make this more generic, we will get the current wp_post, if this is not passed ##
		if (
			! isset( $args['config']['post'] )
		){

			$args['config']['post'] = get\post::object();

		}

		// h::log( $args );

        // meta_query to exclude certain sub pages from desktop on screen sub navigation ##
        $meta_query = []; // nada ##

		// query for child or sibling's post ##
		// @todo - pass arguments from context/navigation
        $wp_args = array(
            'post_type'         => 'page', // $args->post_type,
            'post_parent'       => $args['config']['post']->ID,
            'orderby'           => 'menu_order',
            'order'             => 'ASC',
            'posts_per_page'    => -1, //$args->posts_per_page,
            // 'meta_query'        => $meta_query
        );

        #pr( $wp_args );

        $object = new \WP_Query( $wp_args );

        // test returned array ##
        // h::log( $object->posts );

        // nothing cooking ##
        if ( ! $object->have_posts() ) { return false; }

        // $posts array ##
		$array = [];
		
		// iterate ##
		$count = 0;

        // loop over all posts ##
        foreach ( $object->posts as $post ) {

            // make WP functions available ##
			\setup_postdata( $post );
			
			// id ##
			$array[$count]['post_id'] = $post->ID ;

            // title ##
			$array[$count]['post_title'] = $post->post_title ;

			// modified ##
			$array[$count]['post_modified_date'] = \get_the_modified_date( '', $post->ID ) ;
			
			// title ##
            $array[$count]['post_excerpt'] =  \willow\get\post::excerpt_from_id( $post->ID, 200 );

            // permalink ##
            $array[$count]['post_permalink'] = \get_permalink( $post->ID );

            // class & highlight ##
            $array[$count]['highlight'] = 
                $post->ID === $args['config']['post']->ID ? 
                'active current' : 
                '' ;

            // sort out global $post after WP_Query loop ##
            \wp_reset_postdata();

			// iterate ##
			$count ++;

        }

        // test posts #
        // h::log( $array );

		// return ##
		return get\method::prepare_return( $args, $array );

    }
	


	
    /**
    * Render nav menu
    *
    * @since       1.3.3
    * @return      string   HTML
	*/
    public static function menu( $args = null, $blog_id = 1 )
    {

		// h::log( $args );

		// sanity ##
		if(
			is_null( $args )
			|| ! is_array( $args )
			|| ! isset( $args['args']['theme_location'] )
		){

			h::log( 'e:>Error in passed args' );

			return false;

		}

		// get context / task ##
		$context = isset( $args['context'] ) ? $args['context'] : 'navigation' ;
		$task = isset( $args['task'] ) ? $args['task'] : 'menu' ;

		// Parse incoming $args into an array and merge it with $defaults ##
		$args = core\method::parse_args( $args['args'], core\config::get([ 'context' => $context, 'task' => $task ])['args'] );
		// h::log( 'e:>MENU: '.$args['theme_location'] );
		
        if ( ! \has_nav_menu( $args['theme_location'] ) ) {
        
            h::log( 'd:>! has nav menu: '.$args['theme_location'] );

            return false;

        }

        if ( 
            ! \is_multisite() 
        ) {

            // h::log( $args );
			$menu = \wp_nav_menu( $args );
			
			// test ##
			// h::log( $menu );

			// return ##
			return $menu;

		}
		
		#global $blog_id;
		$blog_id = \absint( $blog_id );

		// h::log( 'nav_menu - $blog_id: '.$blog_id.' / $origin_id: '.$origin_id );

        \switch_to_blog( $blog_id );
        #h::log( 'get_current_blog_id(): '.\get_current_blog_id()  );
        // h::log( $args );
		$menu = \wp_nav_menu( $args );
		// h::log( $menu );
        \restore_current_blog();

		return $menu;

    }




    /**
    * Get Multisite network nav menus items
    *
    * @link        http://wordpress.stackexchange.com/questions/26367/use-wp-nav-menu-to-display-a-menu-from-another-site-in-a-network-install
    * @global      Integer     $blog_id
    * @param       Array       $args
    * @param       Integer     $origin_id
    * @return      Array
    */
    public static function menu_items( $args = null ) {

		// not ready yet ...
		return false;

		// sanity ##
		if(
			is_null( $args )
			|| ! is_array( $args )
			|| ! isset( $args['args']['theme_location'] )
		){

			h::log( 'e:>Error in passed args' );

			return false;

		}

		/*
		if ( 
			! $locations = \get_nav_menu_locations()
			|| ! isset( $locations[ $args['args']['theme_location'] ] )
			|| ! $menu = \get_term( $locations[ $args['args']['theme_location'] ], 'nav_menu' )
          	|| ! $array = wp_get_nav_menu_items( $menu->term_id )
			// ! \has_nav_menu( $args['args']['theme_location'] ) 
		) {
        
            h::log( 'd:>Unable to locate menu: '.$args['args']['theme_location'] );

            return false;

		}
		*/
		
		if ( 
			! $locations = \get_nav_menu_locations()
		) {
        
            h::log( 'd:>1 Unable to locate menu: '.$args['args']['theme_location'] );

            return false;

		}
		
		if ( 
			! isset( $locations[ $args['args']['theme_location'] ] )
		) {
        
            h::log( 'd:>2 Unable to locate menu: '.$args['args']['theme_location'] );

            return false;

		}
		
		if ( 
			! $menu = \get_term( $locations[ $args['args']['theme_location'] ], 'nav_menu' )
		) {
        
            h::log( 'd:>3 Unable to locate menu: '.$args['args']['theme_location'] );

            return false;

		}
		
		if ( 
          	! $array = wp_get_nav_menu_items( $menu->term_id )
		) {
        
            h::log( 'd:>4 Unable to locate menu: '.$args['args']['theme_location'] );

            return false;

		}

		// h::log( $array );

        // nothing found ##
        if ( 
			! $array 
			|| ! is_array( $array )
		) { 

			h::log( 'd:>Menu returned no items: '.$args['args']['theme_location'] ); // theme_location
			
			return false; 
		
		}

        // return the nav menu items ##
        // return $array;

    }


}
