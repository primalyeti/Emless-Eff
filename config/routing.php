<?
$routing = array(
	/** ADMIN **/
		'/^admin(istrator)?(\/)?/i'						=> 'index/index',
	
	/** HOME **/
		'/^home/i'										=> 'pages/index',	# home
		
	/** HONEY POT **/
		'/^trix\/cereal/i'								=> 'systems/bottrap',	# bot trap
		'/^trix\/sillyrabbit/i'							=> 'systems/bottrapped',	# bot trap

);

$default['controller'] 			= 'pages';
$default['action'] 				= 'index';
$default['admin']['controller'] = 'accounts';
$default['admin']['action'] 	= 'index';