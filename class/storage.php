<?php


define( "SNAPSHOT_POSTFIX", "-lxc-backup-snapshot" );
define( "MOUNT_BASENAME", "/tmp/lxc-backup-" );


// Base class for storage engines
class Storage
{
	public $server;
	public $storage;

	public function __construct( $server )
	{
		$this->server = $server;
		$this->storage = Config::get( $server, "storage" );
	}


	static public function get( $server )
	{
		$storage = Config::get($server, "storage");
		if( !is_array($storage) || !$storage['type'] )
			Cmd::quit( "Storage engine is not set for server $server" );

		// Check class exists
		if( !class_exists($class="storage".$storage['type']) )
			Cmd::quit( "Can't find class for storage type ".$storage['type'] );

		return new $class( $server );
	}

	// Generate mount dir
	static protected function mount_dir()
	{
		return MOUNT_BASENAME. substr(str_shuffle( "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10 );
	}

	// Create directory/partition for VM root
	public function create() { Cmd::quit("Create storage for class ". __CLASS__ ."is not implemented"); }
	// return string for lxc.rootfs
	public function rootfs_string() {}
	// Mount VM root and return mount point
	public function mount( $master=false ) { return $this->rootfs_string(); }
	// Umount VM root
	public function umount( $master=false ) {}
}