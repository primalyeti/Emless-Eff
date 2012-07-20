<?
class PagesController extends Controller
{
	function beforeAction()
	{
	
	}
	
	function index()
	{
		$this->load->library("form_validation");
		
		$this->form_validation->set_rule( "username", "unique|required" );
		$this->form_validation->set_rule( "password", "unique" );
		
		print_r( $this->form_validation );
		
		if( $this->form_validation->run( $this->post ) == false )
		{
			
		}
	}
	
	function maintenance()
	{
		
	}
	
	function afterAction()
	{
	
	}
}