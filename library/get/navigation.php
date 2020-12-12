<?php

namespace willow\get;

// Q ##
use willow\core\helper as h;
use willow;

class navigation {

	private
		$plugin = null // this
	;

	/**
	 * 
     */
    public function __construct(){

		// grab passed plugin object ## 
		$this->plugin = willow\plugin::get_instance();

	}

	/**
    * Get Pagination links
    *
    * @since       1.0.2
	* @return      String      HTML
	* @link	https://gist.github.com/mtx-z/f95af6cc6fb562eb1a1540ca715ed928
    */
	public function pagination( $args = null ) {

		// w__log( $args );

		// sanity ##
		if (
			is_null( $args )
			|| ! is_array( $args )
		){

			w__log( 'e:>Error in pased args' );

			return false;

		}

		if ( 
			isset( $args['query'] )
		) {

			$query = $args['query'];

		// grab some global variables ##
		} else {
			
			// w__log( 'Grabbing global query..' );
			global $wp_query;
			$query = $wp_query;

		}

		// no query, no pagination ##
		if ( ! $query ) {

			w__log( 'e:>Nothing to query here' );

			return false;

		}
		
		// get config ##
		$config = $this->plugin->config->get([ 'context' => 'navigation', 'task' => 'pagination' ]);

		// w__log( $config );

		// validate config ##
		if ( ! $config ) {

			w__log( 'e:>Error loading config' );

			return false;

		}

		// work out total ##
		$total = $query->max_num_pages;
		// w__log( 'Total: '.$total );

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

		// w__log( $query_args );

		// filter args ##
		$paginate_args = \apply_filters( 'q/get/navigation/pagination/args', $paginate_args );

		// get links from WP ##
		$paginate_links = \paginate_links( $paginate_args );

		// check if we got any links ##
		if ( 
			! $paginate_links
			|| 0 == count( $paginate_links )
		) {

			// w__log( 'd:>$paginate_links empty.. bailing' );

			return false;

		}

		// test ##
		// w__log( $pages );
		// w__log( 'd:>paged: '.\get_query_var( 'paged' ) );

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
		// w__log( $array );

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
		$items = willow\get\method::prepare_return( $args, $items );

		// w__log( $items );

		// markup array ##
		$string = willow\strings\method::markup( $config['markup']['template'], $items, $config['markup'] );

		// w__log( $string );

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
     * Get Sibling page OT posts from same category 
     *
     * @since       1.0.1
     * @return      Mixed|Array|Boolean
     */
    function siblings( $args = null ){

		// sanity ##
		if (
			is_null( $args )
			|| ! is_array( $args )
		){

			w__log( 'e:>Error in pased args' );

			return false;

		}

		// to make this more generic, we will get the current wp_post, if this is not passed ##
		if (
			! isset( $args['config']['post'] )
		){

			$args['config']['post'] = willow\get\post::object();

		}

		// w__log( $args['config']['post'] );
		
		// change behavious, depending on post type ##
		switch( $args['config']['post']->post_type ){

			case "post" :

				$cats = \get_the_category();
				$wp_args = [
					'post_type' 		=> 'post',
					'post__not_in' 		=> [ \get_the_ID() ],
					'posts_per_page' 	=> $args['args']['posts_per_page'],
					'cat'     			=> $cats[0]->term_id
				];

				// filter args ##
				$wp_args = \apply_filters( 'willow/get/siblings/wp_args', $wp_args );

				// run query ##
				$posts = \get_posts( $wp_args );

			break ;

			case "page" :

				// query for sibling's posts ##
				$wp_args = array(
					'post_type'         => $args['args']['post_type'],
					'post_parent'       => $args['config']['post']->post_parent,
					'orderby'           => 'menu_order',
					'order'             => 'ASC',
					'posts_per_page'    => $args['args']['posts_per_page'],
				);
		
				#pr( $wp_args );
				
				// filter args ##
				$wp_args = \apply_filters( 'willow/get/siblings/wp_args', $wp_args );
		
				// get posts ##
				$posts = \get_posts( $wp_args );
		
				// w__log( $posts );
		
				// we need to manipulate the array of post objects a little...
				foreach( $posts as $post => $post_value ){
		
					// class & highlight ##
					$posts[$post]->highlight = ( $post_value->ID === $args['config']['post']->ID ) ? ' active current' : '' ;
		
				}

			break ;

		}

		// w__log( $posts );

		// return posts array to Willow ##
		return $posts;

	}
	
	/**
     * Get children pages
     *
     * @since       1.0.1
     * @return      string       HTML Menu
     */
    function children( $args = null ){

		// h::log( $args );

		// sanity ##
		if (
			is_null( $args )
			|| ! is_array( $args )
		){

			w__log( 'e:>Error in pased args' );

			return false;

		}

		// to make this more generic, we will get the current wp_post, if this is not passed ##
		if (
			! isset( $args['config']['post'] )
		){

			$args['config']['post'] = willow\get\post::object();

		}

		// w__log( $args );

		// query for child posts ##
        $wp_args = array(
            'post_type'         => $args['args']['post_type'],
            'post_parent'       => $args['config']['post']->ID,
            'orderby'           => 'menu_order',
            'order'             => 'ASC',
            'posts_per_page'    => $args['args']['posts_per_page'],
		);
		
		// filter args ##
		$wp_args = \apply_filters( 'willow/get/navigation/wp_args', $wp_args );

		// get posts ##
		$posts = \get_posts( $wp_args );

		// w__log( $posts );

		// return posts array to Willow ##
		return $posts;

    }
	
    /**
    * Render nav menu
    *
    * @since       1.3.3
    * @return      string   HTML
	*/
    public function menu( $args = null, $blog_id = 1 ){

		// w__log( $args );

		// sanity ##
		if(
			is_null( $args )
			|| ! is_array( $args )
			|| ! isset( $args['args']['theme_location'] )
		){

			w__log( 'e:>Error in passed args' );

			return false;

		}

		// get context / task ##
		$context = isset( $args['context'] ) ? $args['context'] : 'navigation' ;
		$task = isset( $args['task'] ) ? $args['task'] : 'menu' ;

		// Parse incoming $args into an array and merge it with $defaults ##
		$args = willow\core\method::parse_args( 
			$args['args'], 
			$this->plugin->config->get([ 'context' => $context, 'task' => $task ])['args'] 
		);
		// w__log( 'e:>MENU: '.$args['theme_location'] );
		
        if ( ! \has_nav_menu( $args['theme_location'] ) ) {
        
            w__log( 'd:>! has nav menu: '.$args['theme_location'] );

            return false;

        }

        if ( 
            ! \is_multisite() 
        ) {

            // w__log( $args );
			$menu = \wp_nav_menu( $args );
			
			// test ##
			// w__log( $menu );

			// return ##
			return $menu;

		}
		
		#global $blog_id;
		$blog_id = \absint( $blog_id );

		// w__log( 'nav_menu - $blog_id: '.$blog_id.' / $origin_id: '.$origin_id );

        \switch_to_blog( $blog_id );
        #w__log( 'get_current_blog_id(): '.\get_current_blog_id()  );
        // w__log( $args );
		$menu = \wp_nav_menu( $args );
		// w__log( $menu );
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

			w__log( 'e:>Error in passed args' );

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
        
            w__log( 'd:>Unable to locate menu: '.$args['args']['theme_location'] );

            return false;

		}
		*/
		
		if ( 
			! $locations = \get_nav_menu_locations()
		) {
        
            w__log( 'd:>1 Unable to locate menu: '.$args['args']['theme_location'] );

            return false;

		}
		
		if ( 
			! isset( $locations[ $args['args']['theme_location'] ] )
		) {
        
            w__log( 'd:>2 Unable to locate menu: '.$args['args']['theme_location'] );

            return false;

		}
		
		if ( 
			! $menu = \get_term( $locations[ $args['args']['theme_location'] ], 'nav_menu' )
		) {
        
            w__log( 'd:>3 Unable to locate menu: '.$args['args']['theme_location'] );

            return false;

		}
		
		if ( 
          	! $array = wp_get_nav_menu_items( $menu->term_id )
		) {
        
            w__log( 'd:>4 Unable to locate menu: '.$args['args']['theme_location'] );

            return false;

		}

		// w__log( $array );

        // nothing found ##
        if ( 
			! $array 
			|| ! is_array( $array )
		) { 

			w__log( 'd:>Menu returned no items: '.$args['args']['theme_location'] ); // theme_location
			
			return false; 
		
		}

        // return the nav menu items ##
        // return $array;

    }


}
