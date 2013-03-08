<?
/**
 *
 * Irregular Word List
 *
 * This list helps map irregular words to their plural form, for controller mapping
 *
 * 'singular' => 'plural' 
 *
 */

$irregularWords = array(
	"admin"		=>	"admin",
	"cart"		=>	"cart",
	"contact"	=>	"contact",
	"index" 	=>	"index",
);

/**
 *
 * Routing List
 *
 * Used to route words to specific controllers. Perfect for hiding real controller names.
 *
 * Ex:
 * '/^homes/i' => 'pages/index' 
 *
 */
 
$routing = array(
	/** ADMIN **/
		'/^admin(istrator)?(\/)?/i'						=> 'index/index',
	
	/** HOME **/
		'/^home/i'										=> 'pages/index',	# home
		
);

/**
 *
 * Profiler Ignore List
 *
 * Page URLS for profiler to ignore
 *
 * Ex:
 * "/ignore/this/page",
 *
 */
 
$profilerIgnoreList = array(		
);

/**
 *
 * Default Pages and Views
 *
 * Default controller and action
 * 
 */
 
$defaultPage = array(
	"controller" 		=> "pages",
	"action" 			=> "index",
	"admin"	=> array(
		"controller" 	=> "accounts",
		"action" 		=> "index",
	),
);

$defaultViews = array(
	"beforeView" => array(
		"/header",
	),
	"afterView" => array(
		"/footer",
	),
);
