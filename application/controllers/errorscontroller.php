<?
class ErrorsController extends Controller 
{
	function beforeAction ()
	{
		
	}

	function index()
	{
		Tracker::add( "Error", $this->_url );
	
		// set page title
		$this->set( 'page_title', "Page Not Found" );
	}

	function afterAction()
	{

	}
}