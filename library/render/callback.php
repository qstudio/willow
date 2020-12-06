<?php

namespace Q\willow\render;

// use willow\core;
use Q\willow\core\helper as h;
// use q\view;
// use q\get;
use Q\willow;
// use willow\render;

class callback {

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
     * Run defined callbacks on specific field ##
     * Return alters the static class property $args
     * 
     */
    public function field( String $field = null, $value = null ){

		$_args = $this->plugin->get( '_args' );
		$_fields = $this->plugin->get( '_fields' );

        // sanity ##
        if ( is_null( $field ) ) {

			// self::$log['error'][] = 'No field value passed to method.';
			
			// log ##
			w__log( $_args['task'].'~>e:>No field value passed to method.');

            return $value;

        }

        // sanity ##
        if ( is_null( $value ) ) {

			// self::$log['error'][] = 'No value passed to method.';
			
			// log ##
			w__log( $_args['task'].'~>e:>No value passed to method.');

            return $value;

        }

        // Check if there are any allowed callbacks ##
        // Also runs filters to add custom callbacks ##
        $callbacks = $this->get_callbacks();

        if ( 
            ! $callbacks
            || ! \is_array( $callbacks ) 
        ) {

			// self::$log['error'][] = 'No callbacks allowed in plugin';
			
			// log ##
			w__log( $_args['task'].'~>e:>No callbacks allowed in plugin.');

            return $value;

        }

		// w__log( 'Looking for callback for field: "'.$field.'" in self::$fields' );
		
		$render_fields = new willow\render\fields( $this->plugin );
        if ( ! $field_callback = $render_fields->get_callback( $field ) ) {

			// self::$log['error'][] = 'No callbacks found for Field: "'.$field.'"';

            return $value;

        }

        // w__log( $field_callback );

        // assign method to variable ##
        $method = $field_callback['method'];
        $field_value = $_fields[$field];

        // Check we have a real field value to work with ##
        if ( ! $field_value ) {

			// self::$log['notice'][] = 'No field value found, stopping callback';
			
			// log ##
			w__log( $_args['task'].'~>n:>No field value found, stopping callback: "'.$field.'"');

            return $value;

        }

        // args is an optional param = we default to an array containing the field value ##
        $args = [ $field_value ];

        // if the callback passed args ( or if they were added by a filter ) - let's process them ##
        if ( 
            isset( $field_callback['args'] ) 
        ) {

			// w__log( 't:>TODO - check if this callback {{ value }} reference is out of date' );

            // Clean up args, with actual passed value ##
            $field_callback['args'] = str_replace( 
                '{{ value }}',  // @TODO - this looks like an out-of-date markup tag ??
                $field_value, 
                $field_callback['args'] 
            );

            // assign args ##
            $args = $field_callback['args'];

        }

        // w__log( $method );
        // w__log( $args );

        // check if field callback is listed in the allowed array of callbacks ##
        if ( ! array_key_exists( $method, $callbacks ) ) {

			// self::$log['notice'][] = 'Cannot find callback: "'.$method.'"';
			
			// log ##
			w__log( $_args['task'].'~>n:>Cannot find callback: "'.$method.'"');

            return $value;

        }

        // Check if the method is usable ##
        if (
            // ! method_exists( $args->view, $args->method )
            // || 
            ! is_callable( $method )
        ){

			// self::$log['notice'][] = 'Method is not callable: "'.$method.'"';
			
			// log ##
			w__log( $_args['task'].'~>n:>Method is not callable: "'.$method.'"');

            return $value;

        }

        // checks over ##
        // w__log( 'Field: "'.$field.'" has a valid callback: "'.$method.'"');

        // $filter = 'q/field/callback/field/before/'.$method.'/'.$field;
        // w__log( 'Filter: '.$filter );

        // filter field callback value ( $args ) before callback ##
        $args = $this->plugin->get( 'filter')->apply([ 
            'parameters'    => [ 'args' => $args, 'field' => $field, 'value' => $value, 'fields' => $_fields ], // params ##
            'filter'        => 'willow/render/callback/field/before/'.$method.'/'.$field, // filter handle ##
            'return'        => $args
        ]); 

        // w__log( $args );

        // run callback using original value of field ##
        $data = call_user_func (
            $method,
            $args
        );

        // Opps ##
        if ( ! $data ) {

			// self::$log['notice'][] = 'Method returned bad data..';
			
			// log ##
			w__log( $_args['task'].'~>n:>Method return bad data...');

            return $value;

        }

        // check ##
        // w__log( $data );

        // now add new data to class property $fields ##
		$_fields[$field] = $data;
		$this->plugin->set( '_fields', $_fields );

        // done ##
        return $data;

    }


    /**
     * Run defined callbacks on fields ##
     * 
     */
    public function get_callbacks(){

        return \apply_filters( 'willow/render/callbacks/get', $this->plugin->get( '_callbacks') );

    }

}
