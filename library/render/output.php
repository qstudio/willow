<?php

namespace willow\render;

use willow\core;
use willow\core\helper as h;
use willow;
use willow\render;

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
        self::$output = core\filter::apply([ 
            'parameters'    => [ // pass ( $fields, $args, $output ) as single array ##
                'fields'    => self::$fields, 
                'args'      => self::$args, 
				'output'    => self::$output 
			], 
            'filter'        => 'willow/render/output/'.self::$args['task'], // filter handle ##
            'return'        => self::$output
		]); 
		
        // h::log( self::$output );

        // either return or echo ##
        if ( 
			isset( self::$args['config']['return'])
			&& 'echo' === self::$args['config']['return'] 
		) {

			// h::log( self::$output );

			// echo ##
			echo self::$output;

			// reset all args ##
			render\args::reset();

			// stop here ##
            return true;

        } else {

			// build return array ##
			$return = [ 
				'hash'		=> self::$hash['hash'],
				'tag'		=> self::$hash['tag'],
				'output' 	=> self::$output,
				'parent'	=> self::$hash['parent'],
			];

			// h::log( $return );

			// add data to buffer_map ##
			self::$buffer_map[] = [
				'hash'		=> self::$hash['hash'],
				'tag'		=> self::$hash['tag'],
				'output'	=> $return['output'],
				'parent'	=> self::$hash['parent'],
			];

			// reset all args ##
			render\args::reset();

			// return ##
            return $return;

        }

    }

}
