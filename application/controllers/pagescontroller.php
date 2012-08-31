<?
class PagesController extends Controller
{
	function beforeAction()
	{
	
	}
	
	function index()
	{
		#$this->disable_wrappers();
		
		$this->load()->library("form_manager");
			
		$this->form_manager->set_rule( "username", "required" );
		$this->form_manager->set_rule( "password", "required" );
		$this->form_manager->set_rule( "type", "required" );
		$this->form_manager->set_rule( "features", "required" );
		
		if( $this->form_manager->validate( $this->post ) === false )
		{
			print_r( $this->form_manager );
		}
		
		$this->view( "index" );
	}
	
	function maintenance()
	{
		$this->disable_wrappers();
	
		$this->view( "maintenance" );
	}
	
	function afterAction()
	{
	
	}
}