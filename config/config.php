<?
/** READ ME **/
#	Config file hosts all the variables used by the frame work.
#	please add all custom variables to vars.php file

/** PATH VARIABLES **/
define( 'BASE_PATH', '/' );								# website base path, all files located past this point
define( 'FILE_DIR', "files/" );							# uploaded files
define( 'FILE_TEMP_DIR', FILE_DIR . "temp/" );			# temporary uplaoded file directory
define( 'TEMP_DIR', "tmp/" );							# directory for all temporary files
define( 'CACHE_DIR', TEMP_DIR . "cache/" );				# directory for all cache files
define( 'LOGS_DIR', TEMP_DIR . "logs/" );				# directory for all log files
define( 'SESSION_DIR', TEMP_DIR . "sessions/" );		# directory for all session files
define( 'TRACKER_DIR', TEMP_DIR . "trackers/" );		# directory for all tracker files

/** ENVIRONMENT  **/
#	Supported environments are:
#	LOCAL	- testing on local machine, good for use with MAMP
#	DEV		- on a test server, not your machine, but not the final server either
#	TEST	- on live server, but not visible to public. Good for testing near final build
#	LIVE	- live server, visible to public

switch( $_SERVER["SERVER_NAME"] )			# what environment are we in
{
	default:
		define( 'ENVIRONMENT', 'LOCAL' );		# define environment
		
		define( 'DB_TYPE',		'PDO' );		# database connection type. Valid values are "PDO", "MYSQL"
		define( 'DB_NAME', 		'' );		# database name
		define( 'DB_USER', 		'' );		# database user
		define( 'DB_PASSWORD', 	'' );		# database password
		define( 'DB_HOST', 		'' );		# database host (typically localhost)
		
		define( 'DOMAIN', 'http://' . BASE_PATH );			# nonsecure domain
		define( 'DOMAIN_SECURE', 'http://' . BASE_PATH );	# secure domain url
}

/** DEBUG VARIABLES **/
define( 'DEVELOPMENT_ENVIRONMENT', false );
define( 'PRINT_GLOBALS', false );
define( 'DEVELOPMENT_SHOW_CONTROLLER', false );

/** LOG VARIABLES **/
define( 'LOG_FILE_NAME', 'error.log' );
define( 'LOG_CUST_ERR_FILE_NAME', 'cust.error.log' );

/** CACHING VARIABLES **/
define( 'CACHE_ISON', false );					# is caching on
define( 'CACHE_DEFAULT_LIFESPAN', 5 * 60 );		# how long before cache is refreshed

/** TRACKING VARIABLES **/
define( 'TRACKER_ISON', false );				# is tracking on	

/** HONEY POT VARIABLES **/
define( 'HONEYPOT_URL', "trix/cereal" );
define( 'HONEYPOT_TRAPPED_URL', "trix/sillyrabbit" );
define( 'HONEYPOT_FILENAME', "honeypot.xml" );
define( 'HONEYPOT_SESSION_VAR', "framework_honeypot" );

// set default timezone for date() method
date_default_timezone_set( 'America/Vancouver' );

/** Site Variables **/
define( 'SITE_TITLE', '' );
define( 'META_TITLE', "");
define( 'META_DESCRIPTION', "" );
define( 'META_SEARCH_CATEGORIES', "" );
define( 'META_IMAGE', "" );

/** ADMIN VARIABLES **/
define( 'ADMIN_SESSION_VAR', "admin" );			# admin session variable
define( 'ADMIN_ALIAS', 'backdoor' );			# url where admin section can be found. RECOMMENDED YOU CHANGE