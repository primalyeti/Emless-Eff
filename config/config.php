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
		define( 'ENVIRONMENT', 'LOCAL' );	# define environment
		
		define( 'DB_NAME', 		'' );		# database name
		define( 'DB_USER', 		'' );		# database user
		define( 'DB_PASSWORD', 	'' );		# database password
		define( 'DB_HOST', 		'' );		# database host (typically localhost)
		
		define( 'DOMAIN', 'http://local.audiobase.com' . BASE_PATH );			# nonsecure domain
		define( 'DOMAIN_SECURE', 'http://local.audiobase.com' . BASE_PATH );	# secure domain url
}

/** LOG VARIABLES **/
define( 'LOG_FILE_NAME', 'error.log' );
define( 'LOG_CUST_ERR_FILE_NAME', 'cust.error.log' );

/** CACHING VARIABLES **/
define( 'CACHE_ISON', false );					# is caching on
define( 'CACHE_DEFAULT_LIFESPAN', 5 * 60 );		# how long before cache is refreshed

/** TRACKING VARIABLES **/
define( 'TRACKER_ISON', false );		# is tracking on	

// set default timezone for date() method
date_default_timezone_set( 'America/Vancouver' );

/** Site Variables **/
define( 'SITE_TITLE', 'audioBase.com' );
define( 'META_TITLE', "Download Royalty-Free Audio Sample Loops");
define( 'META_DESCRIPTION', "Buy, Sell, &amp; Download AppleLoops, WAV Loops, Audio Samples, Song Construction Kits, Sound EFX, and more ..." );
define( 'META_SEARCH_CATEGORIES', "appleloops,wave loops,aiff loops,sample loops,audio samples,hip hop samples,house samples,techno samples" );
define( 'META_KEYWORDS', "Hip Hop Samples,Techno Samples,House Samples,Free Loops,Royalty Free Sounds,Hip Hop Loops,download,sound fx" );
define( 'META_IMAGE', "https://www.audiobase.com/img/logo.jpg" );

/** ADMIN VARIABLES **/
define( 'ADMIN_SESSION_VAR', "admin" );		# admin session variable
define( 'ADMIN_ALIAS', '/admin' );			# url where admin section can be found. RECOMMENDED YOU CHANGE