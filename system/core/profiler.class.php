<?
class Profiler 
{
	protected $_aTimes = array();		# stores the data
	protected $_toProfile = false;	# whether to profile or not
	protected $_validPoints = array( "page", "mysql" );
	
	public function __construct()
	{
		if( PROFILER_ISON === false )
		{
			return;
		}
		
		// figure out if we're profiling
		$this->_toProfile = ( mt_rand( 0, 100 ) > 99 );
	}
	
	/**
	*
	* Checks if the profiler is set to profile or not
	*
	**/
	public function is_profiling()
	{
		return $this->_toProfile;
	}
	
	/**
	*
	* Sets to Profile variable
	*
	**/
	public function set_profiler( $switch )
	{
		$this->_toProfile = $switch;
	}
	
	/**
	*
	* checks if the point provided is supported
	*
	**/
	protected function is_valid_point( $point )
	{
		return in_array( $point, $this->_validPoints );
	}
	
	/**
	*
	* start profiling a point
	*
	**/
	public function start_time( $point )
	{
		// check if profiling and valid point
		if( !$this->is_profiling() || !$this->is_valid_point( $point ) )
		{
			return false;
		}
	
		// get cpu readouts
		$dat = getrusage();
		
		// init an empty array (prevents notices from being thrown)
		$key = array(
			"start" => 0,			# start time
			"start_utime" => 0,		# user cpu time used
			"start_stime" => 0,		# system cpu time used
			"end" => 0,				# end time
			"end_utime" => 0,		# end user cpu time used
			"end_stime" => 0,		# end system cpu time used
			"comment" => "",		# pount comments
			"count" => 0,			# item count (for mysql)
			"sum" => 0,				# sum of sum utime and sum stime 
			"sum_utime" => 0,		# sum of utime
			"sum_stime" => 0,		# sum of stime
		);
		
		// check if the point's already been initialized
		if( isset( $this->_aTimes[$point] ) )
		{
			$key = &$this->_aTimes[$point];
		}
		
		$key["start"] = microtime( true );
		$key["start_utime"] = $dat["ru_utime.tv_sec"] * 1e6 + $dat["ru_utime.tv_usec"];
		$key["start_stime"] = $dat["ru_stime.tv_sec"] * 1e6 + $dat["ru_stime.tv_usec"];
		
		$this->_aTimes[$point] = $key;
	}
	
	/**
	*
	* stop profiling a point
	*
	**/
	public function stop_time( $point, $comment = "" )
	{
		// check if profiling and valid point
		if( !$this->is_profiling() || !$this->is_valid_point( $point ) )
		{
			return false;
		}
	
		// make sure point has been started
		if( !isset( $this->_aTimes[$point] ) )
		{
			return false;
		}
		
		// get cpu readouts
		$dat = getrusage();
		
		$key = &$this->_aTimes[$point];
		
		$key["end"] = microtime( true );
		$key["end_utime"] = $dat["ru_utime.tv_sec"] * 1e6 + $dat["ru_utime.tv_usec"];
		$key["end_stime"] = $dat["ru_stime.tv_sec"] * 1e6 + $dat["ru_stime.tv_usec"];
		
		$key["sum_utime"] = ( $key["end_utime"] - $key["start_utime"] ) / 1e6;
		$key["sum_stime"] = ( $key["end_stime"] - $key["start_stime"] ) / 1e6;
		
		$key["comment"] .= $comment . "\n" . 
			"End utime: " . $key["sum_utime"] . "\n" . 
			"End stime: " . $key["sum_stime"] . "\n" . 
			"--------------------------------------------------\n\n";
		
		$key["sum"] += $key["end"] - $key["start"];
		$key["count"] += 1;		# how many times this point has been used
	}
	
	/**
	*
	* prepare the data for logging
	*
	**/
	public function log_data()
	{
		// check if profiling and valid points are set
		if( !$this->is_profiling() || empty( $this->_aTimes ) )
		{
			return false;
		}
		// get supported points
		foreach( $this->_validPoints as $vp )
		{
			${$vp} = $this->_aTimes[$vp];
		}
		
		// initialize data array
		$data = array(
			"utime" => 0,
			"stime"	=> 0,
			"wtime" => 0,
			"mysql_time" => 0,
			"ttime" => 0,
			"mysql_count_queries" => 0,
			"mysql_queries" => "",
		);
		
		// page data
		if( isset( $page ) )
		{
			$data["utime"] = $page["sum_utime"];	# user cpu time
			$data["stime"] = $page["sum_stime"];	# system cpu time
			$data["wtime"] = $page["sum"];			# wall clock time
		}
		
		// mysql data
		if( isset( $mysql ) )
		{
			$data["mysql_time"] 			= $mysql["sum"];
			$data["mysql_count_queries"] 	= $mysql["count"];
			$data["mysql_queries"] 			= $mysql["comment"];
		}
		
		// total time
		$data["ttime"] = $data["wtime"] + $data["mysql_time"];
		
		// log it!
		$this->log_profiling_data( $data );
	}
	
	/**
	*
	* log the data to the DB
	*
	**/
	protected function log_profiling_data( $data )
	{	
		$table_suffix = "_" . @date("y_m");		# appent year and month to table name
		$table_name = "profiler_log";# . $table_suffix;
		
		$sql = "INSERT DELAYED INTO " . $table_name . " ( 
				ip, 
				page, 
				utime, 
				wtime, 
				stime, 
				ttime,
				mysql_time,
				mysql_count_queries, 
				mysql_queries, 
				user_agent, 
				referer 
			) VALUES (
				" . Registry::get("dbh")->clean( $_SERVER['REMOTE_ADDR'], "str" ) . ", 
				" . Registry::get("dbh")->clean( $_SERVER["REQUEST_URI"], "str" ) . ", 
				" . Registry::get("dbh")->clean( $data["utime"], "int" ) . ", 
				" . Registry::get("dbh")->clean( $data["wtime"], "int" ) . ", 
				" . Registry::get("dbh")->clean( $data["stime"], "int" ) . ", 
				" . Registry::get("dbh")->clean( $data["ttime"], "int" ) . ", 
				" . Registry::get("dbh")->clean( $data["mysql_time"], "int" ) . ", 
				" . Registry::get("dbh")->clean( $data["mysql_count_queries"], "int" ) . ", 
				" . Registry::get("dbh")->clean( $data["mysql_queries"], "str" ) . ", 
				" . Registry::get("dbh")->clean( $_SERVER['HTTP_USER_AGENT'], "str" ) . ", 
				" . Registry::get("dbh")->clean( $_SERVER['HTTP_REFERER'], "str" ) . "
			)";
		
		// insert data
		Registry::get("dbh")->query( $sql );
		
		// table not found
		if( Registry::get("dbh")->error() && Registry::get("dbh")->errno() == "42S02" ) 
		{
			// create the table
			Registry::get("dbh")->query( $this->create_table( $table_name ) );
			
			// if that failed, bail
			if( Registry::get("dbh")->error() )
			{
				return false;
			}
			
			// insert log
			Registry::get("dbh")->query( $sql );
		}
	}
	
	/**
	*
	* log the data to the DB
	*
	**/
	protected function create_table( $table_name )
	{
		$sql = "
			CREATE TABLE " . $table_name . " (
				`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
				`page` VARCHAR(255) NOT NULL,
				`utime` FLOAT NOT NULL,
				`stime` FLOAT NOT NULL,
				`wtime` FLOAT NOT NULL,
				`mysql_time` FLOAT NOT NULL,
				`ttime` FLOAT NOT NULL,
				`mysql_count_queries` INT UNSIGNED NOT NULL,
				`mysql_queries` TEXT NOT NULL,
				`logged` TIMESTAMP NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
				`user_agent` VARCHAR(255) NOT NULL,
				`ip` VARCHAR(15) NOT NULL,
				`referer` VARCHAR(255) NOT NULL,
				PRIMARY KEY(`id`)
			) ENGINE=ARCHIVE;
		";
		
		return $sql;
	}
}














