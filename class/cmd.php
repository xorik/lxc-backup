<?php


class Cmd
{
	// Run command on remote host
	static public function ssh( $server, $command, &$code=null )
	{
		$host = Config::get( $server, "host" );
		if( $user=Config::get($server, "ssh-user") )
			$host = "$user@$host";

		$port = Config::get( $server, "ssh-port" );
		if( $port && $port!=22 )
			$host = "-p $port $host";

		if( DEBUG ) echo "\033[1mssh $host: $command\033[0m\n";
		exec( "ssh $host $command", $out, $code );

		if( $code!==0 || DEBUG )
			echo implode("\n", $out)."\n\n";
		if( $code !== 0 )
			Cmd::quit( "ssh $host: $command\nerror code: $code\n" );

		return $out;
	}
	
	// Rsync files
	static public function rsync( $src, $dest, $src_dir, $dest_dir, $rsync_opt="-a" )
	{
		$path = "$src_dir/ ". Config::get($dest, "host") .":$dest_dir/";
		$cmd = "sudo rsync $rsync_opt $path";
		static::ssh( $src, $cmd );
	}
	
	// Exit with message to stderr
	static public function quit( $message )
	{
		$f = fopen( "php://stderr", "a" );
		fwrite( $f,$message."\n" );
		fclose( $f );
		exit(1);
	}
}