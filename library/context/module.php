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

class module extends willow\context {


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
		return core\config::get([ 'context' => $args['context'], 'task' => $args['task'] ]);

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

		return \q\module\comment::get( $args );

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
