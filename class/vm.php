<?php


// TODO: handle other states
define( "VM_STATE_NOT_EXISTS", 0 );
define( "VM_STATE_RUNNING", 1 );
define( "VM_STATE_STOPPED", 2 );

define( "LXC_ROOTFS", "lxc.rootfs" );
define( "RSYNC_VM_OPTIONS", "--devices --delete -aH" );


class VM
{
	public $server;
	public $state;
	public $lxc_dir;
	public $storage;

	public function __construct( $server )
	{
		$cmd = "sudo lxc-ls -1 -f -F state '^".VM."$' 2>/dev/null | tail -n1";
		$state = Cmd::ssh( $server, $cmd );

		$this->state = $this->parseState( $state );
		$this->server = $server;
		$this->lxc_dir = Config::get($server, "lxc-dir") ? Config::get($server, "lxc-dir") : "/var/lib/lxc";

		$this->storage = Storage::get( $server );
	}
	
	// Check state
	private function parseState( $state )
	{
		if( !isset($state[0]) || $state[0]=="-----" )
			return VM_STATE_NOT_EXISTS;
		elseif( $state[0] == "RUNNING" )
			return VM_STATE_RUNNING;
		elseif( $state[0] == "STOPPED" )
			return VM_STATE_STOPPED;
		else
			Cmd::quit( "Unknown server state: {$state[0]}\n" );
	}

	static public function rsync( VM $server1, VM $server2 )
	{
		// Copy configs
		if( !DATA_ONLY )
		{
			Cmd::rsync( $server1->server, $server2->server, $server1->lxc_dir."/".VM, $server2->lxc_dir."/".VM );
			$server2->replace_rootfs();
		}
		
		// TODO: stop/freeze VM if needed
		
		// Rsync data
		$src = $server1->storage->mount( true );
		$dest = $server2->storage->mount();
		Cmd::rsync( $server1->server, $server2->server, $src, $dest, RSYNC_VM_OPTIONS );
		$server1->storage->umount( true );
		$server2->storage->umount();
	}

	public function replace_rootfs()
	{
		$file = $this->lxc_dir ."/". VM ."/config";

		$str = Cmd::ssh( $this->server, "cat $file | grep ^". LXC_ROOTFS )[0];
		if( !$str )
			Cmd::quit( "Can't find lxc.rootfs in VM config" );

		$str2 = "lxc.rootfs = ". $this->storage->rootfs_string();
		// Same rootfs path, nothing to do
		if( $str == $str2 )
			return;
		
		$sed = str_replace( " ", "\\ ", "s#$str#$str2#g" );
		
		Cmd::ssh( $this->server, "sudo sed -i '$sed' $file" );
	}
}