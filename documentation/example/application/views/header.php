<?=$this->html->doctype()?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head profile="http://www.w3.org/2005/10/profile">
	<?=$this->html->title()?>
	<?=$this->html->charset()?>
	
	<?=$this->html->meta( "description", ( isset( $page_description ) ? meta_set_description( $page_description ) : "" ) )?>
	<?=$this->html->meta( "search categories", META_SEARCH_CATEGORIES )?>
	<?=$this->html->meta( "viewport", "width=device-width" )?>
	
	<?=$this->html->icon( "img/favicon.png", "image/png" )?>
	<?=$this->html->author()?>
	
</head>

<body>
	<header>Header</header>

<?=bot_honey_trap_link()?>