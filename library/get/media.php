<?php

namespace willow\get;

use willow;
use willow\core\helper as h;

class media {

	private 
		$plugin = false,
		$type_method = false
	;

	/**
     */
    public function __construct( willow\plugin $plugin ){

		// grab passed plugin object ## 
		$this->plugin = $plugin;

	}
	
	/**
     * Check for a return post thumbnail images and exif-data baed on passed settings ##
     *
     */
    public function thumbnail( $args = null ){

		// w__log( \willow::$args );

		// sanity ##
		if (
			is_null( $args )
			// || ! isset( $args['post'] )
			// // || ! isset( $args['handle'] )
			// || ! $args['post'] instanceof \WP_Post
		){

			w__log( 'e:>Error in passed args' );

			return false;

		}

		// to make this more generic, we will get the current wp_post, if this is not passed ##
		if (
			! isset( $args['config']['post'] )
		){

			$args['post'] = willow\get\post::object();

		} else {

			$args['post'] = $args['config']['post'];

		}

		// let's also pass the handle, if set ##
		if (
			isset( $args['config']['handle'] )
		){

			$args['handle'] = $args['config']['handle'];

		}

        // test incoming args ##
        // w__log( $args );

		// check for post thumbnail ##
        if ( ! \has_post_thumbnail( $args['post']->ID ) ) { 
			
			// w__log( 'd:>'.$args['post']->post_type.' "'.$args['post']->post_title.'" does not have a thumbnail' );

			return false; 
		
		}

		// get thumbnail ID ##
		if ( 
			! $args['attachment_id'] = \get_post_thumbnail_id( $args['post']->ID ) 
		){

			w__log('d:>get_post_thumbnail_id() for '.$args['post']->post_type.' "'.$args['post']->post_title.'" returned false' );

			return false;

		}

		// w__log( $args );
		
		// bounce on to get src attrs ##
		$array = $this->src( $args );

        // if we do not have a src, perhaps we should stop?? ##
		// if ( ! $array['src'] ) { return false; }
		
		// test ##
		// w__log( $array );

        // kick back array ##
		// return $array;
		
		// return ##
		return willow\core\prepare::return( $args, $array );

	}

	/**
     * Check for a return post thumbnail images and exif-data baed on passed settings ##
     *
     */
    public function gallery( $args = null ){

		// w__log( $args );

		// sanity ##
		if (
			is_null( $args )
			|| ! is_array( $args )
		){

			w__log( 'e:>Error in passed args' );

			return false;

		}

		// to make this more generic, we will get the current wp_post, if this is not passed ##
		if (
			! isset( $args['post'] )
		){

			$args['post'] = willow\get\post::object();

		}

        // test incoming args ##
        // w__log( $args );

		// check for post thumbnail ##
        if ( 
			! \get_field( 'media_gallery', $args['post']->ID ) 
			|| ! is_array( \get_field( 'media_gallery', $args['post']->ID ) )
		) { 
			
			// w__log( 'd:>Post does not have gallery images' );

			return false; 
		
		}

		$get_field = \get_field( 'media_gallery', $args['post']->ID );
		// w__log( $get_field );

		// gallery returns an array of IDs, so let's loop over each getting src_large and src_small from passed handles
		$return = []; // new array ##
		$count = 0;

		foreach( $get_field as $key => $value ){

			if ( 
				$small = $this->src([ 
					'handle' 		=> isset( $args['config']['handle']['small'] ) ? $args['config']['handle']['small'] : false,
					'attachment_id'	=> $value,
					'post'			=> $args['post']
				]) 
			){

				$return[$count]['small'] = $small['src'];
				$return[$count]['small_width'] = $small['src_width'];
				$return[$count]['small_height'] = $small['src_height'];
				$return[$count]['small_alt'] = $small['src_alt'];
				// $return[$count]['small_alt'] = $small['src_alt']; // srcset @todo ##
				// $return[$count]['small_alt'] = $small['src_alt']; // meta @todo ##

			}

			if ( 
				$large = $this->src([ 
					'handle' 		=> isset( $args['config']['handle']['large'] ) ? $args['config']['handle']['large'] : false,
					'attachment_id'	=> $value,
					'post'			=> $args['post']
				]) 
			){

				$return[$count]['large'] = $large['src'];
				$return[$count]['large_width'] = $large['src_width'];
				$return[$count]['large_height'] = $large['src_height'];
				$return[$count]['large_alt'] = $large['src_alt'];
				// $return[$count]['small_alt'] = $large['src_alt']; // srcset @todo ##
				// $return[$count]['small_alt'] = $large['src_alt']; // meta @todo ##

			}

			// iterate ##
			$count ++;

		}

        // if we do not have a src, perhaps we should stop?? ##
		// if ( ! $array['src'] ) { return false; }
		
		// test ##
		// w__log( $return );

		// return ##
		return willow\core\prepare::return( $args, [ 'gallery' => $return ] );

    }

    /**
     * Check for a return src and exif-data from attachment ID ##
     *
     */
    public function src( $args = null ){

		// sanity ##
		if (
			is_null( $args )
			|| ! isset( $args['attachment_id'] )
		){

			w__log( 'e:>Error in passed args' );

			return false;

		}

		// check and assign ##
		// w__log( \willow::$args );
		// w__log( $args );
		// handle could be assigned in a few ways -- so, let's check them all in specific to generic order ##
		// from passed args ##
		if ( 
			isset( $args['handle'] ) 
		){

			// nothing to do ##
			// w__log( 'e:> Handle passed $args->handle: '.$args['handle'] );

		// handle filtered into config by markup pre-processor at field level ##
		} else if ( 
			function_exists( 'willow' )
			// && isset( \willow::$args )
			&& \willow()->get( '_args' )
			&& isset( $args['field'] )
			// && isset( \willow::$args[ $args['field'] ]['config']['handle'] ) 
			&& isset( \willow()->get( '_args' )[ $args['field'] ]['config']['handle'] )
		){

			$args['handle'] = \willow()->get( '_args' )[ $args['field'] ]['config']['handle'];

			// w__log( 'e:> Handle grabbed from field specific... config->handle: '.$args['handle'] );

		// handle filtered into config by markup pre-processor at global level ##
		} else if ( 
			function_exists( 'willow' )
			// && isset( \willow::$args )
			&& \willow()->get( '_args' )
			// && isset( $args['field'] )
			// && isset( \willow::$args['config']['handle'] ) 
			&& isset( \willow()->get( '_args' )['config']['handle'] )
		){

			$args['handle'] = \willow()->get( '_args' )['config']['handle'];

			// w__log( 'e:> Handle grabbed from global args... config->handle: '.$args['handle'] );

		// filterable default ##
		} else {

			$args['handle'] = \apply_filters( 'q/get/media/src/handle', 'medium' );

			// w__log( 'e:> Handle set by filterable default: '.$args['handle'] );

		}

        // $args['handle'] = 
        //     isset( self::$args['field']['src']['handle'] ) ?
        //     self::$args['field']['src']['handle'] : // get handle defined in calling args ##
        //     \apply_filters( 'q/render/type/src/handle', 'medium' ); // filterable default ##

		// Testing feedback ##
		// w__log( 'e:>Handle: '.$args['handle'] );
		// w__log( \wp_get_attachment_image_src( $args['attachment_id'], $args['handle'] ) );
		/*
		w__log( \get_intermediate_image_sizes() );
		global $_wp_additional_image_sizes;
		if ( isset( $_wp_additional_image_sizes[ $args['handle'] ] ) ) {
			w__log( $_wp_additional_image_sizes[ $args['handle'] ] ); 
		}
		*/

        // test incoming args ##
        // w__log( \willow::$args[ $args['field'] ] );

        // set-up a new array ##
        $array = [];

        // self::log( 'Handle: '.$args['handle'] );
		if ( ! $src = \wp_get_attachment_image_src( $args['attachment_id'], $args['handle'] ) ){

			w__log( \willow()->get( '_args' )['task'].'~>n wp_get_attachment_image_src did not return data' );

			return false;

		}

		// w__log( $src );
		
		// take array items ##
		$array['src'] = $src[0];
		$array['src_width'] = $src[1];
		$array['src_height'] = $src[2];

		$array['src_alt'] = 
			\get_post_meta( $args['attachment_id'], '_wp_attachment_image_alt', true ) ?
			\get_post_meta( $args['attachment_id'], '_wp_attachment_image_alt', true ) :
			\get_the_title( $args['post'] );

		// image found ? ##
		if ( ! $array['src'] ) { 
		
			w__log( 'd:>array->src missing, so cannot continue...' );

			return false; 
		
		}
		
		// conditional -- add img caption ##
		if ( 
			// set locally..
			(
				function_exists( 'willow' )
				// && isset( \willow::$args['config']['meta'] )
				&& isset( \willow()->get( '_args' )['config']['meta'] )
				// && true === \willow::$args['config']['meta'] 
				&& true === \willow()->get( '_args' )['config']['meta']
			)
			/*
			||
			// OR, set globally ##
			(
				function_exists( 'willow' )
				&& isset( willow()->config->get([ 'context' => 'media', 'task' => 'config' ])['meta'] )
				&& true == willow()->config->get([ 'context' => 'media', 'task' => 'config' ])['meta']
			)
			*/
		) {

			// w__log( 'd:>Adding media meta' );

			// add caption values ##
			$array = array_merge( 
				$this->meta( $args ), 
				$array
			);
		
		}

		// w__log( 't:>global / local logic is wrong, as global always overrules local... look into that..' );
		// conditional -- add img meta values ( sizes ) and srcset ##
        if ( 
			// set locally..
			(	
				function_exists( 'willow' )
				// && isset( \willow::$args['config']['srcset'] )
				&& isset( \willow()->get( '_args' )['config']['srcset'] )
				// && true === \willow::$args['config']['srcset'] 
				&& true === \willow()->get( '_args' )['config']['srcset']
			)
			/*
			||
			// OR, set globally ##
			(
				function_exists( 'willow' )
				&& isset( willow()->config->get([ 'context' => 'media', 'task' => 'config' ])['srcset'] )
				&& true == willow()->config->get([ 'context' => 'media', 'task' => 'config' ])['srcset']
			)
			*/
        ) {

			// w__log( 'd:>Adding srcset' );

			// add srcset values ##
			$array = array_merge( 
				$this->srcset( $args ), 
				$array
			);

		}

        // image found ? ##
		// if ( ! $array['src'] ) { return false; }
		
		// w__log( $array );

        // kick back array ##
        return $array;

    }



	/**
	 * Get srcset and additional attachment meta info
	 * 
	 * @since 4.1.0
	 */
	public function srcset( $args = null ): Array {

		// sanity ##
		if (
			is_null( $args )
			|| ! isset( $args['attachment_id'] )
			|| filter_var( $args['attachment_id'], FILTER_VALIDATE_INT) === false
			// ! is_int( $args['attachment_id'] ) // filter_var($int, FILTER_VALIDATE_INT) !== false
			|| ! isset( $args['handle'] )
		){

			w__log( 'e:>Error in passed params' );

			return [];

		}

		$array = [];

		// $id = \get_post_thumbnail_id( $wp_post );
		$array['src_srcset'] = \wp_get_attachment_image_srcset( $args['attachment_id'], $args['handle'] );
		$array['src_sizes'] = \wp_get_attachment_image_sizes( $args['attachment_id'], $args['handle'] );
		
		// markup tag attributes ##
		// $srcset = '" srcset="'.\esc_attr($srcset).'"'; 
		// $sizes = ' sizes="'.\esc_attr($sizes).'"'; 
		// $alt = ' alt="'.\esc_attr($alt).'"'; 

		return $array;

	}



	/**
	 * Get attachment meta data
	 * 
	 * @since 4.1.0
	 */
	public function meta( $args = null ): Array {

		// sanity ##
		if (
			is_null( $args )
			|| ! isset( $args['attachment_id'] )
			|| filter_var( $args['attachment_id'], FILTER_VALIDATE_INT) === false
			// || ! is_int( $args['attachment_id'] )
		){

			w__log( 'e:>Error in passed params' );

			return [];

		}

		$array = [];

		$image = \get_post( $args['attachment_id'] );

		if ( $image ) {
		
			$array['src_title'] = $image->post_title;
			$array['src_caption'] = $image->post_excerpt;
			$array['src_description'] = $image->post_content;

		}

		/*
		$metadata = \wp_get_attachment_metadata( $args['attachment_id'] );
		if ( $metadata ) {

			w__log( 'd:>Adding metadata from: '.$args['attachment_id'] );
			w__log( $metadata );
			
			$array['caption'] = $metadata['image_meta']['caption'];
			$array['credit'] = $metadata['image_meta']['credit'];
			$array['copyright'] = $metadata['image_meta']['copyright'];
			$array['title'] = $metadata['image_meta']['title'];
			// $array['copyright'] = $metadata['image_meta']['copyright'];

		}
		*/

		return $array;

	}




    /**
     * Get post avatar parts
     *
     * @since       1.0.1
     * @return      Mixed       Object || Boolean false
     */
    public function avatar( $args = array() ){

		w__log( 't:>@todo..' );
		return false;

        // get the_post ##
        if ( ! $the_post = willow\get\post::object( $args ) ) { return false; }

        // set-up new object ##
        $object = new \stdClass;

		// Parse incoming $args into an array and merge it with $defaults - caste to object ##
		// @todo - get config from Willow ##
        // $args = ( object )wp_parse_args( $args, \q_theme::$the_avatar );

        // add post ID, if not passed ##
        $args->post = isset ( $args->post ) ? $args->post : $the_post->ID ;

        // test args ##
        #pr( $args );

        // holder ##
        $object->src = $args->holder;

        // class ##
        $object->class = $args->class;

        // if taxonomy archive ##
        if ( $args->style == 'tax' ) {

            // category ##
            $object->category = \wp_get_post_terms( $args->post, 'category' );
            #pr( $object->category );

            // categories have a smaller holder image ##
            $object->src = h::get( "theme/images/global/102x102.png", 'return' );

            if ( isset( $object->category[0] ) ) {

                // check for image ##
                if ( $image_src = \get_field( 'category_image', 'category_'.$object->category[0]->term_id ) ) {

                    // get attached image src ##
                    $image_src = \wp_get_attachment_image_src( $image_src, 'circle-small' );
                    #pr( $image_src );
                    $object->src = $image_src[0]; // take first array item ##

                }

            }

            // css ##
            #$object->class = 'circle-small';

        // single post ##
        } else {

            $image = \wp_get_attachment_image_src( \get_post_thumbnail_id( $args->post ), 'circle-large' ) ;

            if ( $image ) {

                $object->src = $image[0];

            }

            // css ##
            #$object->class = 'circle-large';

        }

        // kick back colour ##
        return $object;

    }

    /**
    * Get Video URL from oEmbed field in ACF
    *
    * @since		1.4.5
    * @return		String		Video URL
    */
    public static function video_thumbnail_uri( $video_uri = null ){

        $thumbnail_uri = '';

        // determine the type of video and the video id
        if ( ! $video = self::parse_video_uri( $video_uri ) ) { return false; }

        // get youtube thumbnail
        if ( $video['type'] == 'youtube' ) {
            $thumbnail_uri = 'https://img.youtube.com/vi/' . $video['id'] . '/mqdefault.jpg';
        }

        // get vimeo thumbnail
        if( $video['type'] == 'vimeo' ) {

            $thumbnail_uri = self::get_vimeo_thumbnail_uri( $video['id'] );

        // get default/placeholder thumbnail ##
        } else if( ! $thumbnail_uri || \is_wp_error( $thumbnail_uri ) ) {

            return false;

        }

        //return thumbnail uri
        return $thumbnail_uri;

    }

    /**
    * Parse the video uri/url to determine the video type/source and the video id
    *
    * @since		1.4.5
    * @return		Array
    */
    public static function parse_video_uri( $url ) {

        // Parse the url
        $parse = parse_url( $url );

        // Set blank variables
        $video_type = '';
        $video_id = '';

        // Url is http://youtu.be/xxxx
        if ( $parse['host'] == 'youtu.be' ) {

            $video_type = 'youtube';
            $video_id = ltrim( $parse['path'],'/' );

        }

        // Url is http://www.youtube.com/watch?v=xxxx
        // or http://www.youtube.com/watch?feature=player_embedded&v=xxx
        // or http://www.youtube.com/embed/xxxx
        if ( ( $parse['host'] == 'youtube.com' ) || ( $parse['host'] == 'www.youtube.com' ) ) {

            $video_type = 'youtube';

            parse_str( $parse['query'] );

            $video_id = $v;

            if ( !empty( $feature ) )
                $video_id = end( explode( 'v=', $parse['query'] ) );

            if ( strpos( $parse['path'], 'embed' ) == 1 )
                $video_id = end( explode( '/', $parse['path'] ) );

        }

        // Url is http://www.vimeo.com
        if ( ( $parse['host'] == 'vimeo.com' ) || ( $parse['host'] == 'www.vimeo.com' ) ) {

            $video_type = 'vimeo';
            $video_id = ltrim( $parse['path'],'/' );

        }

        $host_names = explode(".", $parse['host'] );
        $rebuild = ( ! empty( $host_names[1] ) ? $host_names[1] : '') . '.' . ( ! empty($host_names[2] ) ? $host_names[2] : '');

        // Url is an oembed url wistia.com ##
        if ( ( $rebuild == 'wistia.com' ) || ( $rebuild == 'wi.st.com' ) ) {

            $video_type = 'wistia';

            if ( strpos( $parse['path'], 'medias' ) == 1 ) {

                $video_id = end( explode( '/', $parse['path'] ) );

            }

        }

        // If recognised type return video array
        if ( ! empty( $video_type ) ) {

            return array(
                'type' => $video_type,
                'id' => $video_id
            );

        } else {

            return false;

        }

    }

    /**
    * Takes a Vimeo video/clip ID and calls the Vimeo API v2 to get the large thumbnail URL.
    *
    * @since		1.4.5
    * @return		String		Video Thumbnail Src
    */
    public static function vimeo_thumbnail_uri( $clip_id = null ){

        // sanity check ##
        if ( is_null( $clip_id ) ) return false;

        $vimeo_api_uri = 'http://vimeo.com/api/v2/video/' . $clip_id . '.php';
        $vimeo_response = \wp_remote_get( $vimeo_api_uri );

        if( \is_wp_error( $vimeo_response ) ) {

            return $vimeo_response;

        } else {

            $vimeo_response = unserialize( $vimeo_response['body'] );
            return $vimeo_response[0]['thumbnail_large'];

        }

    }

}
