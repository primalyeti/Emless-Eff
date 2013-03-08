<?
/**
 *
 * Base configuration file for Emless-F framework.
 *
 * This config file allows you to configure the following, MySQL settings,
 * directory names, environment and development settings, site variables 
 * and miscellaneous functions\
 *
 */

// ** ENVIRONMENT and MySQL SETTINGS - Set up local, dev, test and live environments, base paths and their MySQL settings ** //
/**
 *
 * Switches between all domain names, ex: www.example.com, text.example.com, dev.example.com and local.example.com
 * Lets you set up different MySQL and environment setting depending on what server/domain we're on
 * 
 * Supported environments are:
 * LOCAL	- testing on local machine, good for use with MAMP
 * DEV		- on a test server, not your machine, but not the final server either
 * TEST		- on live server, but not visible to public. Good for testing near final build
 * LIVE		- live server, visible to public
 *
 */

/** Site Title */
define( 'SITE_TITLE', '' );

/** Default Timezone */
define( 'TIMEZONE', 'America/Vancouver' );

/** Switch between domains */
switch( $_SERVER["SERVER_NAME"] )
{
	//case "www.example.com":
	//case "test.example.com":
	//case "dev.example.com":
	//case "local.example.com":
	
	default:
		/** Environment Mode, see supported environment values */
		define( 'ENVIRONMENT', 'LOCAL' );
		
		/** Change if framework is installed in a subdirectory */
		define( 'BASE_PATH', '/' );	
		
		/** Database Connection Type, supports PDO and MySQL */
		define( 'DB_TYPE',		'PDO' );		# database connection type. Valid values are "PDO", "MYSQL"
		
		/** Database Name */
		define( 'DB_NAME', 		'' );
		
		/** Database Login Username */
		define( 'DB_USER', 		'' );
		
		/** Database Login Password */
		define( 'DB_PASSWORD', 	'' );
		
		/** Database Host */
		define( 'DB_HOST', 		'' );
}

/** Standard Domain */
define( 'DOMAIN', 'http://' . $_SERVER["SERVER_NAME"] . BASE_PATH );

/** Secure Domain, uses HTTPS */
define( 'DOMAIN_SECURE', 'http://' . $_SERVER["SERVER_NAME"] . BASE_PATH );

/** Authentication key */
define( 'AUTH_KEY', '' );

/** Version Number **/
define( 'VERSION', '' );

/** ##### */

// ** DEVELOPMENT VARIABLES - Enable debug mode ** //
/**
 *
 */

/** Eanble Development Environment */
define( 'DEVELOPMENT_ENVIRONMENT', true );

/** Eanble Printing Globals on Page Load */
define( 'DEVELOPMENT_PRINT_GLOBALS', false );

/** Enable Showing the Currently Loaded Controller */
define( 'DEVELOPMENT_SHOW_CONTROLLER', false );


/** Error Log Name */
define( 'LOG_FILE_NAME', 'error.log' );

/** Additional Error Log Name */
define( 'LOG_CUST_ERR_FILE_NAME', 'cust.error.log' );

/** ##### */

// ** PATH VARIABLES - Where are folders located on your server, relative to the root index.php file ** //
/**
 *
 */
 
/** User uploaded files */
define( 'FILE_DIR', "files/" );

/** Temporary user uploaded files */
define( 'FILE_TEMP_DIR', FILE_DIR . "temp/" );

/** Temporary framework files */
define( 'TEMP_DIR', "tmp/" );

/** Cache files */
define( 'CACHE_DIR', TEMP_DIR . "cache/" );

/** Log files */
define( 'LOGS_DIR', TEMP_DIR . "logs/" );

/** Session files (unused at this time) */
define( 'SESSION_DIR', TEMP_DIR . "sessions/" );

/** Tracker files (experimental) */
define( 'TRACKER_DIR', TEMP_DIR . "trackers/" );

/** ##### */

// ** MISCELLANEOUS VARIABLES - Misc functions ettings ** //
/**
 *
 */

/** Is caching enabled */
define( 'CACHE_ISON', false );

/** Default cache lifetime */
define( 'CACHE_DEFAULT_LIFESPAN', 5 * 60 );

/** Is caching enabled */
define( 'PROFILER_ISON', false );

/** Is Page Tracking enabled */
define( 'TRACKER_ISON', true );

/** What should the tracker track
* Supported tracking types are:
* CONTROLLER	- track anything that requires a controller
* TEMPLATE		- track anythign that requires a template
*/
define( 'TRACKER_TYPE', 'CONTROLLER' );

/** Tracker Session Variable Name */
define( 'TRACKER_SESSION_VAR', 'tracker' );

/** Is honey pot enabled, to catch bots (in development) */
define( 'HONEYPOT_ACTIVE', false );


/** Admin Session Variable Name */
define( 'ADMIN_SESSION_VAR', "admin" );

/** Admin URL Alias, update routing also */
define( 'ADMIN_ALIAS', 'backdoor' );

/** Scripts URL Alias */
define( 'SCRIPTS_ALIAS', 'scripts' );

/** AJAX Alias */
define( 'AJAX_ALIAS', 'ajax' );