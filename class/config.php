<?php


class Config
{
	static protected $data;

	// Load config
	static public function init( $file )
	{
		static::$data = json_decode( file_get_contents($file), true );
		if( !static::$data )
			Cmd::quit( "Error parsing config.json" );
	}

	// Get config key
	static public function get( $key, $subkey=false )
	{
		if( $subkey && isset(static::$data[$key][$subkey]) )
			return static::$data[$key][$subkey];
		elseif( !$subkey && isset(static::$data[$key]) )
			return static::$data[$key];
		else
			return false;
	}
}