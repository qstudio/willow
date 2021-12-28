<?php

namespace willow\core;


use willow;
use willow\core\helper as h;

class template {

    /**
     * Get Q template name, if set - else return WP global
     * 
     * 
     */
    public static function get(){

        if( ! isset( $GLOBALS['q_template'] ) ) {

            // h::log( 'e:>Page template empty' );
            
			// return false;
			
			// changes to return WP template -- check for introduced issues ##
			return str_replace( [ '.php', '.willow', '.willow.php' ], '', \get_page_template_slug() );

        } else {

            // h::log( 'Page template: '.$GLOBALS['q_template'] );

            return str_replace( [ '.php', '.willow', '.willow.php' ], '', $GLOBALS['q_template'] );        

        }

	}

	/**
     * Get Q template format - normally .php or .willow
     * 
	 * @since 4.1.0
     */
    public static function format(){

        if( ! isset( $GLOBALS['q_template'] ) ) {

			// changed to return WP template -- check for introduced issues ##
			$template = \get_page_template_slug();

        } else {

            // h::log( 'Page template: '.$GLOBALS['q_template'] );

            $template = $GLOBALS['q_template'];        

		}
		
		// h::log( 'e:>Template: "'.$template.'"' );

		$extension = \willow\core\file::extension( $template );

		// h::log( 'e:>Extension: "'.$extension.'"' );

		// kick back ##
		return $extension;

	}

	/**
	 * Check is the current view matches the controller
	 * 
	 * @since 4.0.0
	*/
	public static function showing( $file = null ): bool {

		// h::log( 'd:>temp: '.core\template::get() );
		// h::log( 'd:>file: '.$file  );

		return self::get() == trim( $file ) ;

	}

}
