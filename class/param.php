<?php


class Param
{
	// Check params
	static public function check( $argv, $argc )
	{
		if( $argc < 3 )
			static::usage();

		// Store vm and server's name
		define( "SERVER1", $argv[$argc-3] );
		define( "SERVER2", $argv[$argc-2] );
		define( "VM", $argv[$argc-1] );

		// Check servers's hosts
		foreach( array(SERVER1, SERVER2) as $serv )
		{
			if( !Config::get($serv, "host") )
				Cmd::quit( "Can't find server's host in config ($serv)\n" );
		}

		define( "DEBUG", in_array("--debug", $argv) ); // Debug
		define( "AUTO_COPY_VM", in_array("--copy", $argv) ); // Auto copy
		define( "DATA_ONLY", in_array("--data-only", $argv) ); // Not sync VM config
		define( "STOP_VM", in_array("--stop", $argv) ); // Not sync VM config
	}


	// Show usage
	static protected function usage()
	{
		global $argv;

		Cmd::quit( "Usage: php {$argv[0]} [options] servername1 servername2 VM\n" );
	}
}