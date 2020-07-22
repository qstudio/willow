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
			$return = [ 
				'output' 	=> self::$output,
				'hash'		=> self::$hash['hash'],
				'tag'		=> self::$hash['tag'],
				'markup'	=> self::$hash['markup'],
				'parent'	=> self::$hash['parent'],
				// 'position'	=> self::$hash['position']
			];

			// h::log( $return );

			// h::log( 'buffering: '.self::$buffering );

			// hash should be context__task ##
			/*
			$hash = 
				isset( self::$args['config']['hash'] ) ? 
				self::$args['config']['hash'] : 
				self::$args['context'].'__'.self::$args['task'] ;
			*/

			// if ( isset( self::$args['config']['hash'] ) ) h::log( 'd:>hash via config->hash' );
			// h::log( 'd:>hash set to: '.self::$hash['hash'] );
			// h::log( $return );

			// h::log( self::$hash );
			self::$buffer_map[] = [ // was self::$hash['tag']
				'hash'		=> self::$hash['hash'],
				// 'count'		=> self::$buffer_count,
				// 'position'	=> self::$hash['position'],
				'output'	=> $return['output'],
				'tag'		=> self::$hash['tag'],
				'parent'	=> self::$hash['parent'],
			];

			// iterate count ##
			self::$buffer_count ++;

			// store in buffer also ##
			self::$buffer_fields[ self::$hash['hash'] ] = $return['output'];

			// reset all args ##
			render\args::reset();

			// return ##
            return $return;

        }

    }

}
