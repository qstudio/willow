# Willow #
**Contributors:** qlstudio  
**Tags:** Simple, logic-less, procedural semantic markup language  
**Requires at least:** 5.0  
**Tested up to:** 5.5  
**Stable tag:** 1.2.4    
**License:** GPL2  

Willow ~ A logic-less template engine built for ACF and WordPress.

Willow has been designed to meet the needs of both front-end and back-end developers by providing a small, yet powerful set of tags and tools to speed up template development and prototype iteration.

## Hello Willow

All Willow tags include a matching opening and closing pair, starting and ending with a curly bracket and one other internal character, as follows:

```
{~ ui~hello {+ Willow says {{ hello }} +} ~}
```

This tag call tries to find the **ui** ( context ) **hello** ( task ) method ( ui::hello() ) and wrap the return data in simple html: 

```php
class ui {

	public static function hello( $args = null ) {

		// define key + value to render ##
		return [
			'hello' => 'Hello'
		];

	}

}
```

### Return:

```
<div>Willow says Hello</div>
```

Visit the Wiki for further details -> https://github.com/qstudio/q-willow/wiki 
