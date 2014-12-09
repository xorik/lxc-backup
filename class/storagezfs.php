<?php


class StorageZFS extends Storage
{
	public function __construct( $server )
	{
		parent::__construct( $server );
		if( !$this->storage['zfsmount'] || !$this->storage['zfspool'] || !$this->storage['vmdir'] || !$this->storage['template'] )
			Cmd::quit( "Check basedir and template for ZFS storage for server $server" );
		
		if( $this->storage["snapshots-num"] && !$this->storage["snapshot-template"] )
			Cmd::quit( "Snapshots is set, but snapshot-template isn't set for server $server" );
	}

	public function create()
	{
		Cmd::ssh( $this->server, "sudo zfs create ". $this->storage['zfspool'] . "/". $this->storage['vmdir'] ."/". Template::get($this->storage['template']) );
	}

	public function rootfs_string()
	{
		return $this->storage['zfsmount'] . "/". $this->storage['vmdir'] ."/". Template::get($this->storage['template']);
	}


	public function mount( $master=false )
	{
		return parent::mount($master);
	}

	public function umount( $master=false )
	{
		if( $master || !$this->storage["snapshots-num"] )
			return;
		
		// Make ZFS snapshot
		Cmd::ssh( $this->server, "sudo zfs snapshot ". $this->storage["zfspool"] . "/". $this->storage["vmdir"] ."/". Template::get($this->storage["template"]) ."@". Template::get($this->storage["snapshot-template"]) );
	}
}