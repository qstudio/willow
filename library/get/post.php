<?php

namespace Q\willow\get;

use Q\willow;
use Q\willow\core\helper as h;
use Q\willow\strings;

class post {

	private 
		$plugin = false
	;

	/**
     */
    public function __construct( \Q\willow\plugin $plugin ){

		// grab passed plugin object ## 
		$this->plugin = $plugin;

	}

    /**
     * Method to clean up calling and checking for the global $post object
     * Allows $post to be passed
     *
     * @param       Mixed       $post       post ID or $post object
     * @since       1.0.7
     * @return      Object      WP_Post object
     */
    public static function object( $args = null ){

        // w__log( $args );

        // let's try and get a $post from the passed $args ##
        if ( ! is_null ( $args ) && isset( $args ) ) {

            if ( is_array( $args ) && isset( $args["post"] ) ) {

				$post = $args["post"];
				// w__log( 'Post ID sent: '.$post );

            } else if ( is_object ( $args ) && isset ( $args->post ) ) {

                $post = $args->post;

            } else if ( is_integer( $args ) ) {

                $post = $args;

            }

        }

        // w__log( $post );

        // first let's see if anything was set ##
        if ( isset ( $post ) ) {

			// w__log( gettype( $post ) );

			// if ( ! is_object ( $post ) && is_int( $post ) ) {
            if ( is_string ( $post ) || is_int( $post ) ) {

                if ( $object = \get_post( $post ) ) {

					// w__log( 'got post: '.$object->ID );
					
					// pre cache post meta ##
					$object->meta = \get_post_meta( $object->ID );

                    return (object) $object;

                }

            } else if ( is_object ( $post ) ) {

				// pre cache post meta ##
				$post->meta = \get_post_meta( $post->ID );

                return $post;

            }

		}
		
        // next, let's try the global scope -- this might be empty, but will return false ##
		global $post;
		
		if( $post ){

			// pre cache post meta ##
			$post->meta = \get_post_meta( $post->ID );

		}

        // kick it back ##
        return $post;

    }

    /**
     * Get post object terms
     *
     * @since       4.0.0
     */
    public static function object_terms( $args = null ){

		// sanity ##
		if (
			is_null( $args )
			|| ! is_array( $args )
			// || ! isset( $args['taxonomy'] )
		){

			w__log( 'e:>Error in passed args' );

			return false;

		}

		// taxonomy -- defaults to category ##
		$taxonomy = isset( $args['taxonomy'] ) ? $args['taxonomy'] : 'category' ; 
		// w__log( 'd:>'.$taxonomy );

		// post ID ##
		$post_id = isset( $args['config']['post'] ) ? $args['config']['post']->ID : null ;
		// w__log( 'd:>post_id: '.$post_id );

		// $args ##
		$args = isset( $args['args'] ) ? $args['args'] : null ;
		// w__log( $args );

		// get field ##
		$array = \wp_get_post_terms( $post_id, $taxonomy, $args );
		// $array = \wp_the_terms( $post_id, $taxonomy );

		// we expect an array with 1 key [0] of WP_Term object - validate ##
		if (
			! is_array( $array )
			|| is_wp_error( $array )
		){

			w__log( 'e:>Error in returned terms data' );

			return false;

		}

		// w__log( $array );
		
		// return
		return willow\get\method::prepare_return( $args, $array );

	}
	
    /**
     * Generic H1 title tag
     *
     * @param       Array       $args
     * @since       1.3.0
     * @return      String
     */
    public function title( $args = null ) {

		// w__log( $args );

		// sanity ##
		if (
			is_null( $args )
			|| ! is_array( $args )
		){

			w__log( 'e:>Error in passed args' );

			return false;

		}

        // w__log( $args );

        // set-up new array ##
		$array = [];

        // type ##
        $type = 'page';

        // get the title ##
        if (
            \is_home() )
        {

            $the_post = \get_option( 'page_for_posts' );
            // w__log( 'Loading home title: '.$the_post );

            // type ##
            $type = 'is_home';

            // add the title ##
			$array['title'] = \get_the_title( $the_post );
			// $array['permalink'] = \get_permalink( $the_post );

        } else if (

            \is_404()

        ){

            // type ##
            $type = 'is_404';

            // w__log('Loading archive title');
			$array['title'] = \__('404 ~ Oops! It looks like you\'re lost');
			// $array['permalink'] = \get_permalink( \get_site_option( 'page_on_front' ) );

        } else if (

            \is_search()

        ){

            // w__log( 'is_search' );

            // type ##
            $type = 'is_search';

            // w__log('Loading archive title');
			$array['title'] = \sprintf( 'Search results for "%s"', $_GET['s'] );
			// $array['permalink'] = \get_permalink( \get_site_option( 'page_on_front' ) );

        } else if (

                \is_author()
                || \is_tax()
                || \is_category()
                || \is_archive()

        ) {

            // type ##
            $type = 'is_archive';

            // w__log('Loading archive title');
			$array['title'] = \get_the_archive_title();
			// $array['permalink'] = \get_permalink( \get_site_option( 'page_on_front' ) );

        } else {

			$type = 'is_single';

            // w__log('Loading post title');

            // $the_post = $the_post->ID;

            // add the title ##
			$array['title'] = \get_the_title();
			// $array['permalink'] = \get_permalink( $the_post );

        }

		// return ##
		return willow\get\method::prepare_return( $args, $array );

	}
	
    /**
     * Get Post excerpt and return it in an HTML element with classes
     *
     * @since       1.0.7
     */
    public function excerpt( $args = null ){

		// sanity ##
		if (
			is_null( $args )
			|| ! is_array( $args )
		){

			w__log( 'e:>Error in passed args' );

			return false;

		}


        // set-up new array ##
		$array = [];

        // get the post ##
        if ( \is_home() ) {

            // w__log('Loading home excerpt');

            $array['content'] = self::excerpt_from_id( intval( \get_option( 'page_for_posts' ) ), intval( isset( $args['limit'] ) ? $args['limit'] : 200 ) );

        } else if (
            \is_author()
        ) {

            // w__log('Loading author excerpt');

            $array['content'] =
                \get_the_author_meta( 'description' ) ?
                willow\strings\method::chop( nl2br( \get_the_author_meta( 'description' ), intval( isset( $args['limit'] ) ? $args['limit'] : 200 ) ) ) :
                self::excerpt_from_id( intval( \get_option( 'page_for_posts' ) ), intval( isset( $args['limit'] ) ? $args['limit'] : 200 ) );

        } else if (
            \is_tax()
            || \is_category()
            || \is_archive()
        ) {

            // w__log('Loading category excerpt');
            // w__log( category_description() );

            $array['content'] =
                \category_description() ?
                willow\strings\method::chop( nl2br( \category_description(), intval( isset( $args['limit'] ) ? $args['limit'] : 200 ) ) ) :
                self::excerpt_from_id( intval( \get_option( 'page_for_posts' ) ), intval( isset( $args['limit'] ) ? $args['limit'] : 200 ) );

        } else {

            // w__log('Loading other excerpt');

            $array['content'] = self::excerpt_from_id( willow\get\post::object(), intval( isset( $args['limit'] ) ? $args['limit'] : 200 ) );

		}
		
		// return ##
		return willow\get\method::prepare_return( $args, $array );

	}



	/**
     * Gets the excerpt of a specific post ID or object
     *
     * @param   $post       object/int  the ID or object of the post to get the excerpt of
     * @param   $length     int         the length of the excerpt in words
     * @param   $tags       string      the allowed HTML tags. These will not be stripped out
     * @param   $extra      string      text to append to the end of the excerpt
     *
     * @link    http://pippinsplugins.com/a-better-wordpress-excerpt-by-id-function/        Reference
     *
     * @since 0.1
     */
    public static function excerpt_from_id( $post = null, $length = 155, $tags = null, $extra = '&hellip;' ){

		// null post ##
		if ( is_null( $post ) ) {

			$post = self::the_post();

		}

        if( is_int( $post) ) {
            $post = \get_post( $post );
        } elseif( ! is_object( $post ) ) {
            // var_dump( 'no $post' );
            return false;
        }

        if( \has_excerpt( $post->ID ) ) {
            $the_excerpt = $post->post_excerpt;
        } else {
            $the_excerpt = $post->post_content;
        }

        $the_excerpt = \strip_shortcodes( strip_tags( $the_excerpt, $tags ) );
        #pr( $length );

        if ( $length > 0 && strlen( $the_excerpt ) > $length ) { // length set and excerpt too long so chop ##
            $the_excerpt = substr( $the_excerpt, 0, $length ).$extra;
        }

        // var_dump( $the_excerpt );

        return \apply_filters( 'q/get/post/excerpt_from_id', $the_excerpt );

	}

    /**
    * Return the_content with basic filters applied
    *
    * @since       1.0.1
    * @return      string       HTML
    */
    public function content( $args = null ){

		// w__log( 'e:>post->content hit..' );

		// sanity ##
		if (
			is_null( $args )
			|| ! is_array( $args )
		){

			w__log( 'Error in passed args' );

			return false;

		}

        // set-up new array ##
		$array = [];

		// w__log( \get_post_field( 'post_content', $args['config']['post'] ) );

		// get the post_content with filters applied ##
		$array['content'] = \apply_filters( 'the_content', willow\strings\method::clean( \get_post_field( 'post_content', $args['config']['post'] ) ) );

		// w__log( $array );

		// return ##
		return willow\get\method::prepare_return( $args, $array );

	}

	/**
	 * Get current Post object data to render
     *
     * @since       1.6.2
     * @return      Array       
     */
    public function this( $args = null ){

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
			|| ! ( $args['config']['post'] instanceof \WP_Post )
		){

			$post = willow\get\post::object();

		} else {

			$post = $args['config']['post'];

		}

		// w__log( $post );

		// return post object to Willow ##
		return $post;

	}



	/**
	 * Returns the permalink for a page based on the incoming slug.
	 *
	 * @param   string  $slug   The slug of the page to which we're going to link.
	 * @return  string          The permalink of the page
	 * @since   1.0
	 */
	public function permalink_by_slug( $slug = null ){

		// sanity ##
		if(
			is_null( $slug )
		){

			w__log( 'e:>Error in passed $slug' );

			return false;

		}

		// ... TODO ##

	}


}
