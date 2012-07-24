<?
class PagesController extends Controller
{
	function beforeAction()
	{
	
	}
	
	function index()
	{
		$this->load->library("form_manager");
			
		$this->form_manager->set_rule( "username", "required" );
		$this->form_manager->set_rule( "password", "required" );
		$this->form_manager->set_rule( "type", "required" );
		$this->form_manager->set_rule( "features", "required" );
		
		#print_r( $this->post );
		#print_r( $this->form_manager );
		
		if( $this->form_manager->validate( $this->post ) === false )
		{
			print_r( $this->form_manager );
		}
	}
	
	function maintenance()
	{
		
	}
	
	function afterAction()
	{
	
	}
}