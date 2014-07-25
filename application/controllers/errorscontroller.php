<?
class ErrorsController extends Controller
{
	function beforeAction ()
	{

	}

	function index()
	{
		$this->view( "index" );
	}

	function maintenance()
	{
		if( $token === MAINTENANCE_MODE_ACCESS_TOKEN )
		{
			$_SESSION[MAINTENANCE_MODE_ACCESS_SESSION_VAR] = true;
			$this->set( "access_granted", true );
		}

		$this->view( "maintenance" );
	}

	function afterAction()
	{

	}
}