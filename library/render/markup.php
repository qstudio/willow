<?php

namespace willow\render;

use willow;

class markup {

	private 
		$plugin = false
		// $wrapped = false
	;

	/**
     * Construct class
     * 
     */
    public function __construct( \willow\plugin $plugin ){

		// grab passed plugin object ## 
		$this->plugin = $plugin;

	}

	/**
	 * filter passed args for markup - accepts an array of keys+values or a string
	 * 
	 * @since 4.1.0
	*/
	public function pre_validate( $args = null ){

		// sanity ##
		if (
			is_null( $args )
		){

			w__log( 'd:>No $args sent from calling method' );

			return false;

		}
		
        // test args sent from view caller ##
		// w__log( $args );

		// empty stored markup ##
		$this->plugin->set( '_markup', [] );
		// $_markup = $this->plugin->get( '_markup' );
		$_markup = []; // OR [] ##

		// $for = '';#' - '.$args['context'].'_'.$args['task'];

		// if "markup" set in args, take this ##
		if ( 
			is_array( $args )
			&& isset( $args['markup'] ) 
		){

			// passed markup is an array - so take all values ##
			if ( 
				is_array( $args['markup'] ) 
				// && isset( $args['markup']['template'] ) // we can't validate "template" yet, as it might be pulled from config
			) {

				// w__log('d:>Using array markup' );
				// w__log( $args['markup'] );

				$this->plugin->set( '_markup', $args['markup'] );

				return true;

			} else {

				// w__log('d:>Using single markup' );
				// w__log( $args['markup'] );

				$_markup['template'] = $args['markup'];

				$this->plugin->set( '_markup', $_markup );

				// w__log( $this->plugin->get( '_markup' ) );

				return true;

			}

		}

		// convert string passed args, presuming it to be markup...??... ##
		if ( is_string( $args ) ) {

			// w__log('d:>Using string markup:' );
			// w__log( $args );

			$_markup['template'] = $args;

			// set markup ##
			$this->plugin->set( '_markup', $_markup );

			// add markup->template ##
			return $_markup;

			/*
			return self::$markup = [
				'template' => $args
			];
			*/

		} 

		// w__log( $this->plugin->get( '_markup' ) );
		// w__log( 'NO markup set...' );

		// kick back ##
		return false;

	}

	/**
	 * $markup is set, so now we need to merge in any new markup values returned from get::config()
	 * 
	 * @since 4.1.0
	*/
	public function merge(){

		$_markup = $this->plugin->get( '_markup' );
		$_fields = $this->plugin->get( '_fields' );
		$_args = $this->plugin->get('_args');

		// sanity ##
		if (
			is_null( $_args )
		){

			w__log( 'd:>No $args available or corrupt' );

			return false;

		}
		
		// test ##
		// w__log( $_args['markup'] );

		// make an array ##
		if (
			! $_markup
			|| ! isset( $_markup )
			|| empty( $_markup )
			|| ! is_array( $_markup )
		){
			
			// w__log( 'd:>Create empty _markup array...' );

			// self::$markup = []; 
			// $this->plugin->set( '_markup', [] );

			$_markup = [];
	
		}

		// get fresh ##
		// $_markup = $this->plugin->get( '_markup' );

		// for ##
		$for = ' for: '.willow\render\method::get_context();

		// we only accept correctly formatted markup from config ##
		if (
			isset( $_args['markup'] ) 
		) {

			// config has a single markup value, take ##
			if (
				is_string( $_args['markup'] )
			){

				// w__log( 'adding additional single markup from config'.$for );
				// w__log( $_args['markup'] );
				// w__log( $_markup );

				// take as main template ##
				$_markup['template'] = $_args['markup'];

			}

			// config passed an array fo values ##
			if ( is_array( $_args['markup'] ) ) {

				// w__log( 'adding additional array of markup from config'.$for );
				// w__log( self::$args['markup'] );
				// w__log( self::$markup );

				// take array or markup ##
				$_markup = $_args['markup'];

			}

			// merge into defaults -- view passed markup takes preference ##
			// $_markup_get = $this->plugin->get( '_markup' );
			// $_markup_merge = willow\core\method::parse_args( $_markup_get, $_markup );
			$this->plugin->set( '_markup', $_markup );
			// self::$markup = core\method::parse_args( self::$markup, $markup );

			// test ##
			// w__log( $_markup );

			// return true;

		}

		// get fresh ##
		$_markup = $this->plugin->get( '_markup' );

		// @todo no additional markup passes from config.. so we should check if we actually have a markup->template
		if (
			! $_markup
			|| ! is_array( $_markup )
			|| ! isset( $_markup['template'] )
			// || null == self::$markup['template']
		){

			// w__log( 'e:>Creating backup markup...' );
			// w__log( $this->plugin->get( '_args' ) );

			$backup_markup = '';

			// default -- almost useless - but works for single values.. ##
			// $markup = willow\tags::wrap([ 'open' => 'var_o', 'value' => 'value', 'close' => 'var_c' ]); // OLD WAY ##
			$backup_markup = $this->plugin->tags->wrap([ 
				'open' 	=> 'var_o', 
				'value' => $this->plugin->get( '_args' )['task'], // takes "task" as default ##
				'close' => 'var_c' 
			]);

			// w__log( $backup_markup );

			// filter ##
			$backup_markup = \apply_filters( 'willow/render/markup/default', $backup_markup );

			// w__log( $_markup );

			// note ##
			w__log( $this->plugin->get( '_args' )['task'].'~>n:>Using default markup'.$for.' : '.$backup_markup );

			// last validation ##
			if ( 
				is_null( $backup_markup ) 
				// || ! is_array( $_markup ) 
			){ 
				w__log( 'e:>ERORR: _markup empty still..' );
				$backup_markup = ''; 
			}

			// store _markup ##
			$_markup['template'] = $backup_markup;
			$this->plugin->set( '_markup', $_markup );

			// w__log( $this->plugin->get( '_markup' ) );

		}

		// remove markup from args ##
		// w__log( $this->plugin->get( '_markup' ) );
		unset( $_args['markup'] );
		// w__log( $_args );
		$this->plugin->set( '_args', $_args );

		// kick back ##
		return true;

	}

	/**
     * Apply Markup changes to passed template
     * find all variables in $_markup and connected values in $_fields
     * 
	 * @since
	 * @return
     */
    public function prepare(){

		// reset ##
		// $this->wrapped = false;

		$_markup = $this->plugin->get( '_markup' );
		$_fields = $this->plugin->get( '_fields' );
		$_args = $this->plugin->get('_args');

        // sanity checks ##
        if (
            ! isset( $_fields )
            || ! is_array( $_fields )
			|| ! isset( $_markup )
			|| ! is_array( $_markup )
			|| ! isset( $_markup['template'] ) // default markup property ##
        ) {

			// log ##
			w__log( $_args['task'].'~>e:>Error with passed $args');

            return false;

		}
		
        // test ##
        // w__log( $_fields );
		// w__log( $_markup );
		// w__log( $_args );

        // new string to hold output ## 
		$string = $_markup['template'];
		// $string = core\method::string_between( self::$args['config']['tag'], trim( $this->plugin->tags->g( 'arg_o' )), trim( $this->plugin->tags->g( 'arg_c' )) );

		// w__log( '$string: '.$string );
		// w__log( self::$args );
		
        // loop over each field, replacing variables with values ##
        foreach( $_fields as $key => $value ) {

			// w__log( $value );

			// cast booleans to integer ##
			if ( \is_bool( $value ) ) {

				// @todo - is this required ?? ##
				// $value = (int) $value;

			}

            // we only want integer or string values here -- so check and remove, as required ##
            if ( 
				! \is_string( $value ) 
				&& ! \is_int( $value ) 
			) {

				if( 
					isset( $_args['config']['default'] ) 
					&& is_array( $_args['config']['default'] )
					&& (
						isset( $_args['config']['default'][$key] )
						||
						isset( $_args['config']['default']['all'] )
					)
				){

					// start empty ##	
					$default_value = false;

					// take specific key default value ##
					if ( isset( $_args['config']['default'][$key] ) ) {
						
						$default_value = $_args['config']['default'][$key];
					
					// take catch-all default value #
					} elseif ( isset( $_args['config']['default']['all'] ) ) {
						
						$default_value = $_args['config']['default']['all'] ;

					}

					// check we actually have a value ##
					if( 
						! $default_value 
						|| ! is_string( $default_value ) // and that it is a string ##
					) {

						// w__log( 'd:>NO Default value set or NOT a string' );
						w__log( 'd:>"'.$_args['context'].'->'.$_args['task'].'->'.$key.'" is not a string or integer and Willow did not find a default value.' );

						unset( $_fields[$key] );

						continue;

					}

					w__log( 'd:>Value for "'.$_args['context'].'->'.$_args['task'].'->'.$key.'" set to "'.$default_value.'"' );

					// set value and continue ##
					$value = $default_value;

				} else {

					// log ##
					// w__log( $_args['task'].'~>n:>The value of: "'.$key.'" is not a string or integer - so it will be skipped and removed from markup...');
					w__log( 'd:>"'.$_args['context'].'->'.$_args['task'].'->'.$key.'" cannot be printed, it will be skipped and removed from markup.');

					unset( $_fields[$key] );

					// save updated _fields object ##
					// $this->plugin->set( '_fields', $_fields ); // PROBLEMO... ####

					continue;

				}

			}
			
			// save updated _fields object ##
			$this->plugin->set( '_fields', $_fields );

			// w__log( 'working key: '.$key.' with value: '.$value );
			
			// markup string, with filter and wrapper lookup ##
			$string = $this->string([ 'key' => $key, 'value' => $value, 'string' => $string ]);

		}

		// w__log( $string );

		// w__log( self::$fields );
		
		// optional wrapper, html passed in markup->wrap with {{ template }} variable ##
		$string = $this->wrap([ 'string' => $string ]);

        // w__log( $string );

        if ( 
			// $placeholders = placeholder::get( $string ) 
			$variables = $this->plugin->parse->markup->get( $string, 'variable' ) 
        ) {

			// log ##
			w__log( $_args['task'].'~>n:>"'.count( $variables ) .'" variables found in formatted string - these will be removed');

			// w__log( $variables );
			
			// w__log( 't:>moved from loop removal to regex model, make sure this does not cause other problems ##');
			$this->plugin->parse->variables->cleanup();

            // remove any leftover variables in string ##
            // foreach( $variables as $key => $value ) {
            
				// $string = placeholder::remove( $value, $string );
				// $string = render\tag::remove( $value, $string, 'variable' );
            
            // }

        }

        // filter ##
        $string = $this->plugin->filter->apply([ 
            'parameters'    => [ 'string' => $string ], // pass ( $string ) as single array ##
            'filter'        => 'q/render/markup/'.$_args['task'], // filter handle ##
            'return'        => $string
        ]); 

        // check ##
        // w__log( 'd:>'.$string );

		// apply to class property ##
		$this->plugin->set( '_output', $string );

		return $string; 
        // return self::$output = $string;

        // return ##
        // return true;

	}

	public function string( $args = null ){

		$_markup = $this->plugin->get( '_markup' );
		$_args = $this->plugin->get('_args');

		// w__log( $args );

		// sanity ##
		if (  
			is_null( $args )
			|| ! isset( $args['key'] )
			|| ! isset( $args['value'] )
			|| ! isset( $args['string'] )
		){

			w__log( $_args['task'].'~>e:>Error in passed args to "string" method' );

			return false;

		}

		// get string ##
		$string = $args['string'];
		$value = $args['value'];
		$key = $args['key'];

		// w__log( 'key: "'.$key.'" - value: "'.$value.'" - string: "'.$string.'"' );

		// string needs to be a... string.. so check ##
		if( ! is_string( $string ) ){

			w__log( $_args['task'].'~>e:>Error in passed args. "string" is not a string' );
			// w__log( 'e:>Error in passed args. "string" is not a string' );
			// w__log( $string );

			return false;

		}

		// filter ##
		$string = $this->plugin->get('filter')->apply([ 
             'parameters'    => [ 'string' => $string ], // pass ( $string ) as single array ##
             'filter'        => 'willow/render/markup/string/before/'.$_args['task'].'/'.$key, // filter handle ##
             'return'        => $string
		]); 

		// key might be in object.iterator.property format - we only need the property for filters ##
		$filter_key = $key;

		// $regex = \apply_filters( 'willow/render/markup/string', "~\\$open(?:\s*\[[^][{}]*])?\s*$key\s*\\$close~" ); 
		if( false !== strpos( $key, '.' ) ){ 
		
			$filter_keys = explode( '.', $key ); 

			// w__log( $filter_keys );
			
			$filter_key = end( $filter_keys ); 

			// $regex = \apply_filters( 'willow/render/markup/string', "~\\$open(?:\s*\[[^][{}]*])?\s*$filter_key\s*\\$close~" ); 
		
		}

		// w__log( 'e:>Filter Key: '.$filter_key.' ~ '.$key );
		// w__log( self::$fields );

		// filters ##
		// we need to find each {{ variable }} in the passed string which matches the current "key"
		if ( 
            $variables = $this->plugin->parse->markup->get( $string, 'variable' ) 
        ) {

			// check variables ##
			// w__log( $variables );

			foreach( $variables as $var_key => $var_value ){

				// strip filter flags ##
				$filters = willow\core\method::string_between( 
					$var_value, 
					trim( $this->plugin->tags->g( 'fla_o' )), 
					trim( $this->plugin->tags->g( 'fla_c' )), 
					true 
				);

				// w__log( '$filters: '.$filters );

				if( ! $filters ){

					// w__log( 'd:>No filters found in variable: '.$var_value );

					continue;

				}

				// strip variable tags ##
				$raw_var_value = str_replace( 
					[ 
						$filters, 
						trim( $this->plugin->tags->g( 'var_o' )), 
						trim( $this->plugin->tags->g( 'var_c' )) 
					], 
					'', // with nada ## 
					$var_value 
				);

				// clean up - with trim ##
				$raw_var_value = trim( $raw_var_value );

				// w__log( 'd:>$raw_var_value: "'.$raw_var_value.'"' );

				// check if raw_var_value matches current $key - if not, skip ##
				if( $raw_var_value != $key ){

					// w__log( 'd:>$raw_var_value != $key: '.$raw_var_value .' - '.$key );

					continue;

				}

				// grab filters again, this time without tags ##
				$filters = willow\core\method::string_between( 
					$var_value, 
					trim( $this->plugin->tags->g( 'fla_o' )), 
					trim( $this->plugin->tags->g( 'fla_c' )), 
					false 
				);

				// w__log( $filters );

				// get filters ##
				$filters = $this->plugin->filter_method->prepare([ 'filters' => $filters ]);

				// w__log( $filters );

				// store pre-filter value ##
				$pre_value = $value; 

				// run filters ##
				$filter_value = $this->plugin->filter_method->process([ 
					'filters'	=> $filters,
					'string' 	=> $value, 
					'use' 		=> 'variable', // for filters ##
				]);

				// compare pre and post filter values ##
				if( $filter_value != $pre_value ){

					// w__log( 'd:>Filtered value is different: '.$filter_value );
					// w__log( 'd:>Replace: "'.$var_value.'" with "'.$filter_value.'"' );

					// run unique str_replace on whole variable ##
					$string = str_replace( $var_value, $filter_value, $string );

				}

			}

		}

		// filter variable ##
		// $pre_filter = $value;
		// instead of a filter, let's run this directly as a command ##
		// $value = apply_filters( 'willow/render/markup/variable', $value, $filter_key );
		/*
		$value = willow\filter\method::apply([ 
			'string' 	=> $value, 
			'use' 		=> 'variable', // for filters ##
		]);
		*/
		// if ( $value != $pre_filter ) w__log( 'value after filter: '.$value );
		// w__log( 'string before regex: '.$string );

		// variable replacement -- regex way ##
		$open = trim( $this->plugin->tags->g( 'var_o' ) );
		$close = trim( $this->plugin->tags->g( 'var_c' ) );

		// $regex = \apply_filters( 'q/render/markup/string', "~\{{\s+$key\s+\}}~" ); // '~\{{\s(.*?)\s\}}~' 
		$regex = \apply_filters( 'willow/render/markup/string', "~\\$open(?:\s*\[[^][{}]*])?\s*$key\s*\\$close~" ); 
		
		// REGEX respects flags in {{ [flags] variable }} - so, the whole variable is replaced with $value ##
		$string = preg_replace( $regex, $value, $string ); 

		// w__log( 'string after regex: '.$string );

		// filter ##
		$string = $this->plugin->get('filter')->apply([ 
			'parameters'    => [ 'string' => $string ], // pass ( $string ) as single array ##
			'filter'        => 'willow/render/markup/string/after/'.$_args['task'].'/'.$key, // filter handle ##
			'return'        => $string
		]); 

		// filter whole tag markup, filters are extraced earlier from the {~ Willow ~} ##
		$string = \apply_filters( 'willow/render/markup/tag', $string, $filter_key );

		// return ##
		return $string;

	}

	public function wrap( $args = null ){

		// w__log( $args['key'] );

		$_markup = $this->plugin->get( '_markup' );
		$_args = $this->plugin->get( '_args' );

		// sanity ##
		if (  
			is_null( $args )
			// || ! isset( $args['key'] )
			// || ! isset( $args['value'] )
			|| ! isset( $args['string'] )
		){

			w__log( $_args['task'].'~>e:>Error in passed args to "wrap" method' );
			// w__log( 'e:>Error in passed args to "wrap" method' );

			return false;

		}

		// w__log( 'd:>hello 2...' );

		// get string ##
		$_wrapped = $args['string'];
		// $value = $args['value'];
		// $key = $args['key'];

		// w__log( 'key: "'.$key.'" - value: "'.$value.'"' );

		// look for wrapper in markup ##
		// and wrap once ..
		if ( 
			isset( $_markup['wrap'] ) 
			// && ! self::$wrapped
		) { 

			// w__log( 'd:>hello 3...' );

			// $markup = self::$args[ $key ];
			$_wrap = $_markup[ 'wrap' ];

			// w__log( 'd:>wrap string in: '.$_wrap );
			// w__log( $string );

			// filter ##
			$_wrap = $this->plugin->filter->apply([ 
				'parameters'    => [ 'wrap' => $_wrap ], // pass ( $string ) as single array ##
				'filter'        => 'q/render/markup/wrap/'.$_args['context'].'/'.$this->plugin->get('_args')['task'], // filter handle ##
				'return'        => $_wrap
			]); 

			// w__log( $_wrap );

			// w__log( 'found: '.$markup );

			// w__log( 'wrap: '.$this->plugin->tags->wrap([ 'open' => 'var_o', 'value' => 'template', 'close' => 'var_c' ]) );

			// wrap key value in found markup ##
			// example: markup->wrap = '<h2 class="mt-5">{{ template }}</h2>' ##
			$_wrapped = str_replace( 
				// '{{ template }}', 
				$this->plugin->tags->wrap([ 'open' => 'var_o', 'value' => 'template', 'close' => 'var_c' ]), 
				$_wrapped, 
				$_wrap 
			);

			// w__log( $_wrapped );

			// track ##
			// self::$wrapped = true;

		}

		// filter ##
		$_wrapped = $this->plugin->filter->apply([ 
             'parameters'    => [ 'string' => $_wrapped ], // pass ( $string ) as single array ##
             'filter'        => 'willow/render/markup/string/wrap/'.$_args['context'].'/'.$_args['task'], // filter handle ##
             'return'        => $_wrapped
        ]); 

		// template replacement ##
		// $string = str_replace( '{{ '.$key.' }}', $value, $string );
		// w__log( $_wrapped );

		// // regex way ##
		// $regex = \apply_filters( 'q/render/markup/string', "~\{{\s+$key\s+\}}~" ); // '~\{{\s(.*?)\s\}}~' 
		// $string = preg_replace( $regex, $value, $string ); 

		// // filter ##
		// $string = core\filter::apply([ 
        //      'parameters'    => [ 'string' => $string ], // pass ( $string ) as single array ##
        //      'filter'        => 'q/render/markup/string/after/'.$this->plugin->get('_args')['task'].'/'.$key, // filter handle ##
        //      'return'        => $string
        // ]); 

		// return ##
		return $_wrapped;

	}

    /**
     * Update Markup base for passed field ##
     * 
     */
    public function set( string $field = null, $count = null ){

		// make some local vars ##
		$_markup = $this->plugin->get( '_markup' );
		$_args = $this->plugin->get('_args');

        // sanity ##
        if ( 
            is_null( $field )
            || is_null( $count )
        ) {

			// log ##
			w__log( $_args['task'].'~>n:>No field value or count iterator passed to method');

            return false;

		}
		
        // check ##
        // w__log( 'Update template markup for field: '.$field.' @ count: '.$count );

        // look for required markup ##
		// w__log( $this->plugin->get ( '_markup' ) );
		// w__log( '$field: '.$field );
		if ( ! isset( $_markup[$field] ) ) {

			// log ##
			w__log( $_args['task'].'~>n:>Field: "'.$field.'" does not have required markup defined in "$markup->'.$field.'"' );
			// w__log( 'e:>Field: "'.$field.'" does not have required markup defined in "$markup->'.$field.'"' );

            // bale if not found ##
            return false;

        }

        // w__log( $_markup[$field] );

        // get target variable ##
		$tag = $this->plugin->tags->wrap([ 'open' => 'var_o', 'value' => $field, 'close' => 'var_c' ]);
        if ( 
			! $this->contains( $tag )
        ) {

			// log ##
			w__log( $_args['task'].'~>n:>Tag: "'.$tag.'" is not in the passed markup template' );
			w__log( 'd:>Tag: "'.$tag.'" is not in the passed markup template' );

            return false;

        }

        // so, we have the repeater markup to copy, variable in template to locate new markup ... 
        // && we need to find all variables in markup and append field__X__VARIABLE

        // get all variables from markup->$field ##
        if ( 
			! $variables = $this->plugin->parse->markup->get( $_markup[$field], 'variable' ) 
        ) {

			// log ##
			w__log( $_args['task'].'~>n:>No variables found in passed string' );
			w__log( 'e:>No variables found in passed string' );

            return false;

        }

        // test ##
		// w__log( $variables );
		
		// w__log( 'hash: '.self::$args['config']['hash'] );

        // iterate over {{ variables }} adding prefix ##
        $new_variables = [];
        foreach( $variables as $key => $value ) {

			// w__log( 'e:>Working variable: '.$value );

			// get flags ##
			$flags = ''; // nada ##
			
			if(
				willow\core\method::string_between( 
					$value, 
					trim( $this->plugin->tags->g( 'fla_o' ) ), 
					trim( $this->plugin->tags->g( 'fla_c' ) ) 
				)
			){

				// store flags ##
				$flags = trim(
					willow\core\method::string_between( 
						$value, 
						trim( $this->plugin->tags->g( 'fla_o' ) ), 
						trim( $this->plugin->tags->g( 'fla_c' ) ) 
					)
				);

				// wrap stored flag in flag tags ##
				$flags = $this->plugin->tags->g( 'fla_o' ).$flags.$this->plugin->tags->g( 'fla_c' ).' ';

				// remove flags from worked string $value ##
				$value = str_replace( $flags, "", $value ); 

			}

			// w__log( 'Flagless variable: '.$value );

			// var open and close, with and without whitespace  at start and end ##
			$array_replace = [
				$this->plugin->tags->g( 'var_o' ),
				trim( $this->plugin->tags->g( 'var_o' ) ),
				$this->plugin->tags->g( 'var_c' ),
				trim( $this->plugin->tags->g( 'var_c' ) )
			];

			// new variable --- complex... ##
			$new = $this->plugin->tags->g( 'var_o' ).$flags.trim($field).'.'.trim($count).'.'.trim( str_replace( $array_replace, '', trim($value) ) ).$this->plugin->tags->g( 'var_c' );

			// single whitespace max ## @might be needed ##
			// $new = preg_replace( '!\s+!', ' ', $new );	

			// w__log( 'e:>new_variable: '.$new );

			$new_variables[] = $new;

            // $new_placeholders[] = '{{ '.trim($field).'__'.trim($count).'__'.str_replace( [ '{{', '{{ ', '}}', ' }}' ], '', trim($value) ).' }}';

        } 

        // test new variables ##
        // w__log( $new_variables );

        // generate new markup from template with new_variables ##
        $new_markup = str_replace( $variables, $new_variables, $_markup[$field] );

		// w__log( $_markup[$field] );
        // w__log( $new_markup );

        // use strpos to get location of {{ variable }} ##
        $position = strpos( $_markup['template'], $tag );
        // helper::log( 'Position: '.$position );

        // add new markup to $template as defined position - don't replace {{ variable }} yet... ##
        $new_template = substr_replace( $_markup['template'], $new_markup, $position, 0 );

        // test ##
        // w__log( $new_template );

        // push back into main stored markup ##
		$_markup['template'] = $new_template;
		$this->plugin->set( '_markup', $_markup );

        // kick back ##
        return true;

	}
	
	/**
     * Check if single tag exists 
     * @todo - work on passed params 
     *  
     */
    public function contains( string $variable = null, $field = null ) {
		
		// if $markup template passed, check there, else check self::$markup ##
		$markup = is_null( $field ) ? $this->plugin->get( '_markup' )['template'] : $this->plugin->get( '_markup' )[$field] ;

        if ( ! substr_count( $markup, $variable ) ) {

            return false;

        }

        // good ##
        return true;

	}


}
