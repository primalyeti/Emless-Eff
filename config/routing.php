<?
$routing = array(
	/** ADMIN **/
		'/^admin(istrator)?(\/)?/i'						=> 'index/index',
	
	/** HOME **/
		'/^home/i'										=>	'pages/index',					# home
);

$default['controller'] = 'pages';
$default['action'] = 'index';