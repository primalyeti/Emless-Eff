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
		$this->view( "maintenance" );
	}

	function afterAction()
	{

	}
}