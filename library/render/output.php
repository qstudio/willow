<?php

namespace Q\willow\render;

use Q\willow\core\helper as h;
use Q\willow;

class output {

	private 
		$plugin = false
	;

	/**
     */
    public function __construct( \Q\willow\plugin $plugin ){

		// grab passed plugin object ## 
		$this->plugin = $plugin;

	}

    public function prepare() {

		// local var ##
		$_output = $this->plugin->get( '_output' );

		// w__log( 'e:>'.$_output );

		// sanity ##
		if ( 
			! isset( $_output )
			|| is_null( $_output )
		){

			// log ##
			w__log( $this->plugin->get( '_args' )['task'].'~>e:>$_output is empty, so nothing to render.. stopping here.');
			// w__log( 'e:>$_output is empty, so nothing to render.. stopping here.');

			// kick out ##
			return false;

		}

        // filter output ##
        $_output = $this->plugin->filter->apply([ 
            'parameters'    => [ // pass ( $fields, $args, $output ) as single array ##
                'fields'    => $this->plugin->get( '_fields' ), 
                'args'      => $this->plugin->get( '_args' ), 
				'output'    => $_output 
			], 
            'filter'        => 'willow/render/output/'.$this->plugin->get( '_args' )['task'], // filter handle ##
            'return'        => $_output
		]); 

		// store _output ##
		$this->plugin->set( '_output', $_output );

        // w__log( self::$output );

        // either return or echo ##
        if ( 
			isset( $this->plugin->get( '_args' )['config']['return'])
			&& 'echo' === $this->plugin->get( '_args' )['config']['return'] 
		) {

			// w__log( self::$output );

			// echo ##
			echo $_output;

			// reset all args ##
			$this->plugin->render->args->reset();

			// stop here ##
            return true;

        } else {

			$_hash = $this->plugin->get( '_hash' );
			// w__log( $_hash );

			// build return array ##
			$return = [ 
				'hash'		=> $_hash['hash'],
				'tag'		=> $_hash['tag'],
				'output' 	=> $_output,
				'parent'	=> $_hash['parent'],
			];

			// w__log( $return );

			$_buffer_map = $this->plugin->get( '_buffer_map' );

			// add data to buffer_map ##
			$_buffer_map[] = [
				'hash'		=> $_hash['hash'],
				'tag'		=> $_hash['tag'],
				'output'	=> $_output, // $return['output'],
				'parent'	=> $_hash['parent'],
			];

			// set buffer map ##
			$this->plugin->set( '_buffer_map', $_buffer_map );

			// reset all args ##
			$this->plugin->render->args->reset();

			// return ##
            return $return;

        }

    }

}
