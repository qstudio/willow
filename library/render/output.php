<?php

namespace willow\render;

use willow\core\helper as h;
use willow;

class output {

	/**
	 * Construct
     */
    public function __construct(){

		// silence ##

	}

    public function prepare() {

		// local var ##
		$_output = \willow()->get( '_output' );

		// w__log( 'e:>'.$_output );

		// sanity ##
		if ( 
			! isset( $_output )
			|| is_null( $_output )
		){

			// log ##
			w__log( \willow()->get( '_args' )['task'].'~>e:>$_output is empty, so nothing to render.. stopping here.');
			// w__log( 'e:>$_output is empty, so nothing to render.. stopping here.');

			// kick out ##
			return false;

		}

        // filter output ##
        $_output = \willow()->filter->apply([ 
            'parameters'    => [ // pass ( $fields, $args, $output ) as single array ##
                'fields'    => \willow()->get( '_fields' ), 
                'args'      => \willow()->get( '_args' ), 
				'output'    => $_output 
			], 
            'filter'        => 'willow/render/output/'.\willow()->get( '_args' )['task'], // filter handle ##
            'return'        => $_output
		]); 

		// store _output ##
		\willow()->set( '_output', $_output );

        // w__log( self::$output );

        // either return or echo ##
        if ( 
			isset( \willow()->get( '_args' )['config']['return'])
			&& 'echo' === \willow()->get( '_args' )['config']['return'] 
		) {

			// w__log( self::$output );

			// echo ##
			echo $_output;

			// reset all args ##
			\willow()->render->args->reset();

			// stop here ##
            return true;

        } else {

			$_hash = \willow()->get( '_hash' );
			// w__log( $_hash );

			// build return array ##
			$return = [ 
				'hash'		=> $_hash['hash'],
				'tag'		=> $_hash['tag'],
				'output' 	=> $_output,
				'parent'	=> $_hash['parent'],
			];

			// w__log( $return );

			$_buffer_map = \willow()->get( '_buffer_map' );

			// add data to buffer_map ##
			$_buffer_map[] = [
				'hash'		=> $_hash['hash'],
				'tag'		=> $_hash['tag'],
				'output'	=> $_output, // $return['output'],
				'parent'	=> $_hash['parent'],
			];

			// set buffer map ##
			\willow()->set( '_buffer_map', $_buffer_map );

			// reset all args ##
			\willow()->render->args->reset();

			// return ##
            return $return;

        }

    }

}
