<?php

namespace willow\render;

use willow;

class callback {

	private 
		$plugin = false
	;

	/**
     */
    public function __construct( \willow\plugin $plugin ){

		// grab passed plugin object ## 
		$this->plugin = $plugin;

	}

    /**
     * Run defined callbacks on specific field ##
     * Return alters the static class property $args
     * 
     */
    public function field( String $field = null, $value = null ){

		// w__log( 'Field: '.$field );

		// local vars ##
		$_args = $this->plugin->get( '_args' );
		$_fields = $this->plugin->get( '_fields' );

        // sanity ##
        if ( is_null( $field ) ) {

			// w__log['error'][] = 'No field value passed to method.';
			
			// log ##
			w__log( $_args['task'].'~>e:>No field value passed to method.');

            return $value;

        }

        // sanity ##
        if ( is_null( $value ) ) {

			// w__log['error'][] = 'No value passed to method.';
			
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

			// w__log['error'][] = 'No callbacks allowed in plugin';
			
			// log ##
			w__log( $_args['task'].'~>e:>No callbacks allowed in plugin.');

            return $value;

        }

		// w__log( 'Looking for callback for field: "'.$field.'" in self::$fields' );
		
        if ( ! $field_callback = $this->plugin->render->fields->get_callback( $field ) ) {

			// w__log['error'][] = 'No callbacks found for Field: "'.$field.'"';
			// w__log( 'd:>No callbacks found for Field: "'.$field.'"' );

            return $value;

        }

        // w__log( $field_callback );

        // assign method to variable ##
        $method = $field_callback['method'];
        $field_value = $_fields[$field];

        // Check we have a real field value to work with ##
        if ( ! $field_value ) {

			// w__log['notice'][] = 'No field value found, stopping callback';
			
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

			// w__log['notice'][] = 'Cannot find callback: "'.$method.'"';
			
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

			// w__log['notice'][] = 'Method is not callable: "'.$method.'"';
			
			// log ##
			w__log( $_args['task'].'~>n:>Method is not callable: "'.$method.'"');

            return $value;

        }

        // checks over ##
        // w__log( 'Field: "'.$field.'" has a valid callback: "'.$method.'"');

        // $filter = 'q/field/callback/field/before/'.$method.'/'.$field;
        // w__log( 'Filter: '.$filter );

        // filter field callback value ( $args ) before callback ##
        $args = $this->plugin->filter->apply([ 
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
