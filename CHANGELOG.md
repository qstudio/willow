### 2.0.1 ###

* Update - removed DI model inside plugin

### 2.0.0 ###

* Breaking changes - fundamental changes to architecture and OOP model, will require updates to themes / plugins to use - see documentation
* New: willow() global function to retrieve current instance and access public methods - get(), set(), config(), filter() extend()

### 1.6.3 ###

* New: post.this Willow, which returns all standard WP Post, media and meta info for current post object
* Update: Partials can now include one or multiple Willows inside them, making for more useful, re-uable snippets.

### 1.6.2 ###

* New: Additional variable filters added - w__substr_first & w__substr_first

### 1.6.1 ###

* New: [ debug ] flag added to Willows to allow for quickly debugging of single Willow

### 1.6.0 ###

* New: New loops functionality more stable, more flexible and potentially faster.

### 1.5.3 ###

* Updates: Fixes to edge-cases usage of repeat loops + Willows

### 1.5.2 ###

* New: Repeat loops from same data / scope within single Willow

### 1.5.1 ###

* Update: Speed improvements by simplification of loops process

### 1.5.0 ###

* New: filters to extend wp_post and wp_term objects
* Update: standardized return objects from navigation contexts

### 1.4.9 ###

* New: i18n parser for translation strings in templates

### 1.4.8 ###

* New: html_entity_decode filter

### 1.4.7 ###

* New: Example cache clear hook - from Fastest Cache plugin

### 1.4.6 ###

* Update: Reduced lookup filter runs for loaded config, performance improvements
* Update: cast true integer values to strings, when passed as return values before render

### 1.4.5 ###

* Update: Debugging Log merged into main WP log file
* Update: Willow loading time improvements due to reduction and standerdization of lookup locations
* Fix: Removed additional Q references and dependencies

### 1.4.1 ###

* FIX: Bad namespace in plugin/acf

### 1.4.0 ###

* New: Moved all getter methods in to Willow, acf, wp, etc
* New: Strings classes, for string data manipulation
* New: Willow is more stand-alone ready, less dependent on Q plugin

### 1.3.6 ###

* Update: Removed Q namespace, plugin now called "Willow" with main class "willow"

### 1.3.5 ###

* Update: Removed Extension context, merged into module on Q

### 1.3.4 ###

* New: Added htaccess rules to protect .willow files on plugin activation

### 1.3.3 ###

* Update: Standardization of filters across Willows, variables, php_functions and php_variables
* Update: Standerdization of flags across comments, arguments, php_functions

### 1.3.2 ###

* Added `<code>tag</code>` exception to regex cleanup of Willow tags, to allow them to be skipped - thanks Wiktor Stribiżew :) https://stackoverflow.com/users/3832970/wiktor-stribi%c5%bcew

### 1.3.1 ###

* WP_Post object meta properties added to return - available via field_name.meta.meta_field

### 1.3.0 ###

* Formatting flags replaced with extensible filters - https://github.com/qstudio/q-willow/wiki/Tags-Flag

### 1.2.4 ###

* New: {{ [u] Uppercase }} and {{ [l] Lowercase }} filters for variables and strings

### 1.2.3 ###

* Fix: Minor stability release
* Fix: Missing config method to write file 

### 1.2.1 ###

* New: Added base context/global.php file

### 1.2.0 ###

* Update: Transitional debugging to Willow, 
* Removed: Last direct dependency on Q

### 1.1.0 ###

* Update: Removed most dependencies on Q plugin - WP still required

### 1.0.2 ###

* Fix: for duplicate default value assignment

### 1.0.1 ###

* Fix: removed duplicate default value assignment for loops

### 1.0.0 ###

* First stable Beta

### 0.8.0 ###

* More stable and logical context set-up
* Moved all major getting / setting functionality back to Q

### 0.5.5 ###

* Added render/template for partial rendering with filters and config lookup

### 0.5.0 ###

* Updated parse_str to respect content in double quotes
* Improved debugging, less clutter in error log
* Fixed loops issue when passing arguments in array from template

### 0.4.0 ###

* Added action and filter contexts
* Buffer Map hashes more stable
* Moved UI header and Footers into main buffer map process

### 0.3.0 ###

* Added basic PHP variable lookups - for now just $_GET values

### 0.2.5 ###

* Buffer Map added to track all templates tags

### 0.2.0 ###

* Default templates working from willows
* Field, Markup & Value filters available via template flags

### 0.1.0 ###

* Core engine working, but loaded with bugs!

### 0.0.1 ###

* Initial working version
