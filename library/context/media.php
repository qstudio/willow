<?php

namespace q\willow\context;

use q\core\helper as h;
use q\get;
use q\willow;
use q\willow\context;
use q\willow\render; 

class media extends willow\context {


	/**
     * Src image - this requires a post_id / attachment_id to be bassed ##
     *
     * @param       Array       $args
     * @since       1.3.0
     * @return      String
     */
    public static function src( $args = null ) {

		// returns array with key 'src', 'srcset', 'alt' etc.... ##
		render\fields::define(
			get\media::src( $args )
		);

	}


	/**
     * lookup thumbnail image, this implies we are working with the current post
     *
     * @param       Array       $args
     * @since       1.3.0
     * @return      String
     */
    public static function thumbnail( $args = null ) {

		// h::log( self::$args );

		// returns array with key 'src', 'srcset', 'alt' etc.... ##
		render\fields::define(
			get\media::thumbnail( $args )
		);

	}
	


	/**
     * Get page Avatar style and placement
     *
     * @since       1.0.1
     * @return      Mixed       string HTML || Boolean false
     */
    public static function avatar( $args = array() )
    {

		/*
        // grab avatar object ##
        // if ( ! $object = self::get_avatar( $args ) ) { return false; }

		// <a class="circle <?php echo $object->class; ?>"><img src="<?php echo $object->src; ?>" /></a>
		*/
		
		// returns array with key 'src', 'srcset', 'alt' etc.... ##
		render\fields::define(
			get\media::avatar( $args )
		);

    }


}
