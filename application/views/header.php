<!DOCTYPE>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head profile="http://www.w3.org/2005/10/profile">
	<title><?=( isset( $page_title ) ? meta_set_title( $page_title ) : "" )?> <?=SITE_TITLE?></title>
	
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="description" content="<?=( isset( $page_description ) ? meta_set_description( $page_description ) : "" )?>" />
	<meta name="search categories" content="<?=META_SEARCH_CATEGORIES?>" />
	<meta name="viewport" content="width=device-width" />
	
	<link rel="icon" type="image/png" href="<?=BASE_PATH?>img/favicon.png">
	<link rel="author" type="text/plain" href="humans.txt" />
		
	<!-- FACEBOOK META DATA -->
	<meta property="og:title" content="<?=( isset( $page_title ) ? meta_set_title( $page_title ) : "" )?> <?=SITE_TITLE?>" />
	<meta property="og:type" content="website" />
	<meta property="og:url" content="<?=( isset( $page_url ) ? meta_set_url( $page_url ) : "" )?>" />
	<meta property="og:image" content="<?=( isset( $page_image ) ? meta_set_image( $page_image ) : "" )?>" />
	<meta property="og:site_name" content="<?=SITE_TITLE?>" />
	<meta proterty="og:description" content="<?=( isset( $page_description ) ? meta_set_description( $page_description ) : "" )?>" />
	
</head>

<body>
	<header>Header</header>

<?=bot_honey_trap_link()?>