<?php

namespace q\willow\render;

use q\core;
use q\core\helper as h;
use q\view;
use q\get;
use q\willow;

class markup extends willow\render {

	// track wrapping ##
	protected static $wrapped = false;

    /**
     * Apply Markup changes to passed template
     * find all variables in self::$markup and connected values in self::$fields
     * 
     */
    public static function prepare(){

		// reset ##
		self::$wrapped = false;

        // sanity checks ##
        if (
            ! isset( self::$fields )
            || ! is_array( self::$fields )
			|| ! isset( self::$markup )
			|| ! is_array( self::$markup )
			|| ! isset( self::$markup['template'] ) // default markup property ##
        ) {

			// log ##
			h::log( self::$args['task'].'~>e:>Error with passed $args');

            return false;

		}
		
        // test ##
        // helper::log( self::$fields );
		// helper::log( self::$markup );
		
		// pre-format markup to extract comments ##
		// self::comments();

        // new string to hold output ## 
		$string = self::$markup['template'];
		
        // loop over each field, replacing variables with values ##
        foreach( self::$fields as $key => $value ) {

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

				h::log( 'The value of: '.$key.' is not a string or integer - so we cannot render it' );

				// log ##
				h::log( self::$args['task'].'~>n:>The value of: "'.$key.'" is not a string or integer - so it will be skipped and removed from markup...');

                unset( self::$fields[$key] );

                continue;

            }

			// h::log( 'working key: '.$key.' with value: '.$value );
			
			// markup string, with filter and wrapper lookup ##
			$string = self::string([ 'key' => $key, 'value' => $value, 'string' => $string ]);

		}
		
		// optional wrapper, html passed in markup->wrap with {{ content }} variable ##
		$string = self::wrap([ 'string' => $string ]);

        // helper::log( $string );

        // check for any left over variables - remove them ##
        if ( 
			// $placeholders = placeholder::get( $string ) 
			$variables = willow\markup::get( $string, 'variable' ) 
        ) {

			// log ##
			h::log( self::$args['task'].'~>n:>"'.count( $variables ) .'" variables found in formatted string - these will be removed');

			// h::log( $variables );
			
			// h::log( 't:>moved from loop removal to regex model, make sure this does not cause other problems ##');
			willow\variables::cleanup();

            // remove any leftover variables in string ##
            // foreach( $variables as $key => $value ) {
            
				// $string = placeholder::remove( $value, $string );
				// $string = render\tag::remove( $value, $string, 'variable' );
            
            // }

        }

        // filter ##
        $string = core\filter::apply([ 
            'parameters'    => [ 'string' => $string ], // pass ( $string ) as single array ##
            'filter'        => 'q/render/markup/'.self::$args['task'], // filter handle ##
            'return'        => $string
        ]); 

        // check ##
        // h::log( 'd:>'.$string );

        // apply to class property ##
        return self::$output = $string;

        // return ##
        // return true;

	}



	// @todo - escape ## per call, or globally ## ??
	public static function escape( $value ) {

		// h::log( self::$args );

		// return $value;

		if ( isset( self::$args['config']['escape'] ) ){

			// h::log( 'd:>escaping value: '.self::$args['config']['hash'] );

			$value = mb_convert_encoding( $value, 'UTF-8', 'UTF-8' );
			$value = htmlentities( $value, ENT_QUOTES, 'UTF-8' ); 

		}

		return $value;

	}



	// @todo - escape ## per call, or globally ## ??
	public static function strip( $value ) {

		// h::log( self::$args );

		// return $value;

		if ( isset( self::$args['config']['strip'] ) ){

			// h::log( 'd:>stripping tags from value: '.$value );

			$value = strip_tags( $value );
			// $value = htmlentities( $value, ENT_QUOTES, 'UTF-8' ); 

		}

		return $value;

	}



	/**
	 * filter passed args for markup
	 * 
	 * @since 4.1.0
	*/
	public static function pre_validate( $args = null ){

		// sanity ##
		if (
			is_null( $args )
		){

			h::log( 'd:>No $args sent from calling method' );

			return false;

		}
		
        // test args sent from view caller ##
		// h::log( $args );

		// empty stored markup ##
		self::$markup = [];

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

				// h::log('d:>Using array markup' );
				// h::log( $args['markup'] );

				return self::$markup = $args['markup'];

			} else {

				// h::log('d:>Using single markup' );
				// h::log( $args['markup'] );

				return self::$markup['template'] = $args['markup'];

			}

		}

		// convert string passed args, presuming it to be markup...??... ##
		if ( is_string( $args ) ) {

			// create args array ##
			// $args = [];

			// h::log('d:>Using string markup:' );
			h::log( $args );

			// add markup->template ##
			return self::$markup = [
				// 'markup' => [
					'template' => $args
				// ]
			];

		} 
		
		// if(
		// 	is_array( $args )
		// 	&& isset( $args['markup'] )
		// ) {

		// 	self::$markup = $args['markup'];

		// }

		// h::log( self::$markup );

		// kick back ##
		return false;

	}

	

	/**
	 * $markup is set, so now we need to merge in any new markup values returned from get::config()
	 * 
	 * @since 4.1.0
	*/
	public static function merge(){

		// sanity ##
		if (
			is_null( self::$args )
			// || is_array( self::$args )
		){

			h::log( 'd:>No $args available or corrupt' );

			return false;

		}
		
        // test ##
		// h::log( self::$args );

		// make an array ##
		if (
			! self::$markup
			|| ! isset( self::$markup )
			|| empty( self::$markup )
			|| ! is_array( self::$markup )
		){
			
			// h::log( 'd:>Create markup array...' );

			self::$markup = []; 
	
		}

		// for ##
		$for = ' for: '.method::get_context();

		// we only accept correctly formatted markup from config ##
		if (
			isset( self::$args['markup'] ) 
		) {

			// config has a single markup value, take ##
			if (
				is_string( self::$args['markup'] )
			){

				// h::log( 'adding additional single markup from config'.$for );
				// h::log( self::$args['markup'] );
				// h::log( self::$markup );

				// take as main template ##
				$markup['template'] = self::$args['markup'];

			}

			// config passed an array fo values ##
			if ( is_array( self::$args['markup'] ) ) {

				// h::log( 'adding additional array of markup from config'.$for );
				// h::log( self::$args['markup'] );
				// h::log( self::$markup );

				// take array or markup ##
				$markup = self::$args['markup'];

			}

			// merge into defaults -- view passed markup takes preference ##
			self::$markup = core\method::parse_args( self::$markup, $markup );

			// test ##
			// h::log( self::$markup );

			// return true;

		}

		// @todo no additional markup passes from config.. so we should check if we actually have a markup->template
		if (
			! isset( self::$markup['template'] )
			// || null == self::$markup['template']
		){

			// default -- almost useless - but works for single values.. ##
			$markup = willow\tags::wrap([ 'open' => 'var_o', 'value' => 'value', 'close' => 'var_c' ]);

			// filter ##
			$markup = \apply_filters( 'q/render/markup/default', $markup );

			// note ##
			// h::log('e:>NOTE: Using default markup'.$for );

			// assign ##
			self::$markup['template'] = $markup;

		}

		// remove markup from args ##
		unset( self::$args['markup'] );

		// kick back ##
		return true;

	}




	public static function string( $args = null ){

		// h::log( $args['key'] );

		// sanity ##
		if (  
			is_null( $args )
			|| ! isset( $args['key'] )
			|| ! isset( $args['value'] )
			|| ! isset( $args['string'] )
		){

			h::log( self::$args['task'].'~>e:>Error in passed args to "string" method' );

			return false;

		}

		// get string ##
		$string = $args['string'];
		$value = $args['value'];
		$key = $args['key'];

		// h::log( 'key: "'.$key.'" - value: "'.$value.'"' );

		// look for wrapper in markup ##
		// if ( isset( self::$args[$key] ) ) {
		// if ( isset( self::$markup[$key] ) ) { // ?? @todo -- how is this working ?? -- surely, this should look for 'wrap'
		// wrap once ..
		// if ( 
		// 	isset( self::$markup['wrap'] ) 
		// 	&& ! self::$wrapped
		// ) { 

		// 	// h::log( 't:>@todo.. string wrap logic...' );

		// 	// $markup = self::$args[ $key ];
		// 	$markup = self::$markup[ 'wrap' ];

		// 	// filter ##
		// 	$string = core\filter::apply([ 
		// 		'parameters'    => [ 'string' => $string ], // pass ( $string ) as single array ##
		// 		'filter'        => 'q/render/markup/wrap/'.self::$args['task'].'/'.$key, // filter handle ##
		// 		'return'        => $string
		// 	]); 

		// 	// h::log( 'found: '.$markup );

		// 	// wrap key value in found markup ##
		// 	// example: markup->wrap = '<h2 class="mt-5">{{ content }}</h2>' ##
		// 	$value = str_replace( 
		// 		// '{{ content }}', 
		// 		willow\tags::wrap([ 'open' => 'var_o', 'value' => 'content', 'close' => 'var_c' ]), 
		// 		$value, 
		// 		$markup 
		// 	);

		// 	// track ##
		// 	self::$wrapped = true;

		// }

		// filter ##
		$string = core\filter::apply([ 
             'parameters'    => [ 'string' => $string ], // pass ( $string ) as single array ##
             'filter'        => 'q/render/markup/string/before/'.self::$args['task'].'/'.$key, // filter handle ##
             'return'        => $string
        ]); 

		// variable replacement -- regex way ##
		$open = trim( willow\tags::g( 'var_o' ) );
		$close = trim( willow\tags::g( 'var_c' ) );

		// h::log( 'open: '.$open );
		// "~\\$open\s+(.*?)\s+\\$close~" // note:: added "+" for multiple whitespaces.. not sure it's good yet...

		// $regex = \apply_filters( 'q/render/markup/string', "~\{{\s+$key\s+\}}~" ); // '~\{{\s(.*?)\s\}}~' 
		$regex = \apply_filters( 'q/render/markup/string', "~\\$open\s+$key\s+\\$close~" ); // '~\{{\s(.*?)\s\}}~' 
		$string = preg_replace( $regex, $value, $string ); 

		// filter ##
		$string = core\filter::apply([ 
             'parameters'    => [ 'string' => $string ], // pass ( $string ) as single array ##
             'filter'        => 'q/render/markup/string/after/'.self::$args['task'].'/'.$key, // filter handle ##
             'return'        => $string
		]); 
		
		h::log( 't:>Move pre-render formats to some sort of system / class, add filters and allow for extensions' );

		// h::log( 'd:>'.$string );

		// escape ##
		$string = self::escape( $string );

		// strip ##
		$string = self::strip( $string );

		// return ##
		return $string;

	}



	public static function wrap( $args = null ){

		// h::log( $args['key'] );
		// h::log( 'd:>hello...' );

		// sanity ##
		if (  
			is_null( $args )
			// || ! isset( $args['key'] )
			// || ! isset( $args['value'] )
			|| ! isset( $args['string'] )
		){

			h::log( self::$args['task'].'~>e:>Error in passed args to "wrap" method' );

			return false;

		}

		// h::log( 'd:>hello 2...' );

		// get string ##
		$string = $args['string'];
		// $value = $args['value'];
		// $key = $args['key'];

		// h::log( 'key: "'.$key.'" - value: "'.$value.'"' );

		// look for wrapper in markup ##
		// and wrap once ..
		if ( 
			isset( self::$markup['wrap'] ) 
			// && ! self::$wrapped
		) { 

			// h::log( 'd:>hello 3...' );

			// $markup = self::$args[ $key ];
			$markup = self::$markup[ 'wrap' ];

			// h::log( 'd:>wrap string in: '.$markup );

			// filter ##
			$markup = core\filter::apply([ 
				'parameters'    => [ 'markup' => $markup ], // pass ( $string ) as single array ##
				'filter'        => 'q/render/markup/wrap/'.self::$args['context'].'/'.self::$args['task'], // filter handle ##
				'return'        => $markup
			]); 

			// h::log( 'found: '.$markup );

			// wrap key value in found markup ##
			// example: markup->wrap = '<h2 class="mt-5">{{ content }}</h2>' ##
			$string = str_replace( 
				// '{{ content }}', 
				willow\tags::wrap([ 'open' => 'var_o', 'value' => 'content', 'close' => 'var_c' ]), 
				$string, 
				$markup 
			);

			// track ##
			// self::$wrapped = true;

		}

		// filter ##
		$string = core\filter::apply([ 
             'parameters'    => [ 'string' => $string ], // pass ( $string ) as single array ##
             'filter'        => 'q/render/markup/string/wrap/'.self::$args['context'].'/'.self::$args['task'], // filter handle ##
             'return'        => $string
        ]); 

		// template replacement ##
		// $string = str_replace( '{{ '.$key.' }}', $value, $string );
		// h::log( $string );

		// // regex way ##
		// $regex = \apply_filters( 'q/render/markup/string', "~\{{\s+$key\s+\}}~" ); // '~\{{\s(.*?)\s\}}~' 
		// $string = preg_replace( $regex, $value, $string ); 

		// // filter ##
		// $string = core\filter::apply([ 
        //      'parameters'    => [ 'string' => $string ], // pass ( $string ) as single array ##
        //      'filter'        => 'q/render/markup/string/after/'.self::$args['task'].'/'.$key, // filter handle ##
        //      'return'        => $string
        // ]); 

		// return ##
		return $string;

	}



    /**
     * Update Markup base for passed field ##
     * 
     */
    public static function set( string $field = null, $count = null ){

        // sanity ##
        if ( 
            is_null( $field )
            || is_null( $count )
        ) {

			// log ##
			h::log( self::$args['task'].'~>n:>No field value or count iterator passed to method');

            return false;

        }

        // check ##
        // h::log( 'Update template markup for field: '.$field.' @ count: '.$count );

        // look for required markup ##
        // if ( ! isset( self::$args[$field] ) ) {
		// h::log( self::$markup );
		if ( ! isset( self::$markup[$field] ) ) {

			// log ##
			h::log( self::$args['task'].'~>n:>Field: "'.$field.'" does not have required markup defined in "$markup->'.$field.'"' );

            // bale if not found ##
            return false;

        }

        // get markup ##
        // $markup = self::$args[$field];

        // get target variable ##
		// $placeholder = '{{ '.$field.' }}';
		$tag = willow\tags::wrap([ 'open' => 'var_o', 'value' => $field, 'close' => 'var_c' ]);
        if ( 
			// ! placeholder::exists( $placeholder )
			! self::contains( $tag )
        ) {

			// log ##
			h::log( self::$args['task'].'~>n:>Tag: "'.$tag.'" is not in the passed markup template' );

            return false;

        }

        // so, we have the repeater markup to copy, variable in template to locate new markup ... 
        // && we need to find all variables in markup and append field__X__VARIABLE

        // get all variables from markup->$field ##
        if ( 
			// ! $placeholders = placeholder::get( self::$markup[$field] ) 
			! $variables = willow\markup::get( self::$markup[$field], 'variable' ) 
        ) {

			// log ##
			h::log( self::$args['task'].'~>n:>No variables found in passed string' );

            return false;

        }

        // test ##
        // helper::log( $variables );

        // iterate over {{ variables }} adding prefix ##
        $new_variables = [];
        foreach( $variables as $key => $value ) {

			// h::log( 'Working variable: '.$value );
			// h::log( 'variable_open: '.willow\tags::g( 'var_o' ) );

			// var open and close, with and without whitespace ##
			$array_replace = [
				willow\tags::g( 'var_o' ),
				trim( willow\tags::g( 'var_o' ) ),
				willow\tags::g( 'var_c' ),
				trim( willow\tags::g( 'var_c' ) )
			];
			// new variable ##
			// h::log( 't:>todo.. make this new field name more reliable' );
			$new = willow\tags::g( 'var_o' ).trim($field).'.'.trim($count).'.'.trim( str_replace( $array_replace, '', trim($value) ) ).willow\tags::g( 'var_c' );

			/*
			WAS

			$new = willow\tags::g( 'var_o' ).trim($field).'__'.trim($count).'__'.trim( str_replace( $array_replace, '', trim($value) ) ).willow\tags::g( 'var_c' );
			*/

			// single whitespace max ## @might be needed ##
			// $new = preg_replace( '!\s+!', ' ', $new );	

			// h::log( 'new_variable: '.$new );

			$new_variables[] = $new;

            // $new_placeholders[] = '{{ '.trim($field).'__'.trim($count).'__'.str_replace( [ '{{', '{{ ', '}}', ' }}' ], '', trim($value) ).' }}';

        } 

        // test new variables ##
        // h::log( $new_variables );

        // generate new markup from template with new_variables ##
        $new_markup = str_replace( $variables, $new_variables, self::$markup[$field] );

        // helper::log( $new_markup );

        // use strpos to get location of {{ variable }} ##
        $position = strpos( self::$markup['template'], $tag );
        // helper::log( 'Position: '.$position );

        // add new markup to $template as defined position - don't replace {{ variable }} yet... ##
        $new_template = substr_replace( self::$markup['template'], $new_markup, $position, 0 );

        // test ##
        // helper::log( $new_template );

        // push back into main stored markup ##
        self::$markup['template'] = $new_template;

        // kick back ##
        return true;

	}
	


	 /**
     * Check if single tag exists 
     * @todo - work on passed params 
     *  
     */
    public static function contains( string $variable = null, $field = null ) {
		
		// if $markup template passed, check there, else check self::$markup ##
		$markup = is_null( $field ) ? self::$markup['template'] : self::$markup[$field] ;

        if ( ! substr_count( $markup, $variable ) ) {

            return false;

        }

        // good ##
        return true;

	}


}
