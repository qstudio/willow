<?php

namespace willow\core;

use willow\core;

class file {
    
	public static function put_array( $path, $array ){

		if ( is_array( $array ) ){

			$contents = arrays::var_export_short( $array, true );
			// $contents = var_export( $array, true );

			// stripslashes ## .. hmmm ##
			$contents = str_replace( '\\', '', $contents );

			// w__log( 'd:>Array data good, saving to file' );

			// save in php as an array, ready to return ##
			file_put_contents( $path, "<?php\n return {$contents};\n") ;
			
			// done ##
			return true;

		}

		w__log( 'e:>Error with data format, config file NOT saved' );
		
		// failed ##
		return false;

	}

}
