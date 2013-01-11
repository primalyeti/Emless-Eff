<?
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

$default['controller'] 			= 'pages';
$default['action'] 				= 'index';
$default['admin']['controller'] = 'accounts';
$default['admin']['action'] 	= 'index';