<?php

namespace q\willow\render;

// use q\core;
use q\core\helper as h;
use q\ui;
use q\willow;
use q\willow\render;

class output extends willow\render {

    
    public static function prepare() {

		// sanity ##
		if ( 
			! isset( self::$output )
			|| is_null( self::$output )
		){

			// log ##
			h::log( self::$args['task'].'~>e:>$output is empty, so nothing to render.. stopping here.');

			// kick out ##
			return false;

		}

        // filter output ##
        self::$output = \q\core\filter::apply([ 
            'parameters'    => [ // pass ( $fields, $args, $output ) as single array ##
                'fields'    => self::$fields, 
                'args'      => self::$args, 
				'output'    => self::$output 
			], 
            'filter'        => 'q/render/output/'.self::$args['task'], // filter handle ##
            'return'        => self::$output
		]); 
		
        // helper::log( self::$output );

        // either return or echo ##
        if ( 
			isset( self::$args['config']['return'])
			&& 'echo' === self::$args['config']['return'] 
		) {

			// h::log( self::$output );

			echo self::$output;

			// reset all args ##
			render\args::reset();

			// stop here ##
            return true;

        } else {

			// grab ##
			$return = self::$output;

			// h::log( $return );

			// h::log( 'buffering: '.self::$buffering );

			// hash should be context__task ##
			$hash = 
				isset( self::$args['config']['hash'] ) ? 
				self::$args['config']['hash'] : 
				self::$args['context'].'__'.self::$args['task'] ;

			// if ( isset( self::$args['config']['hash'] ) ) h::log( 'd:>hash via config->hash' );
			// h::log( 'd:>hash set to: '.$hash );

			// store in buffer also -- 
			// @todo, make this conditional on buffering running.. perhaps just knowing we are returning data is enough to presume it's buffering?? ##
			self::$buffer[ $hash ] = $return;

			// reset all args ##
			render\args::reset();

			// return ##
            return $return;

        }

    }

}
