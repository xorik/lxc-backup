<?php


class StorageLVM extends Storage
{
	private $mount_point;

	
	public function __construct( $server )
	{
		parent::__construct( $server );
		if( !$this->storage['vgname'] || !$this->storage['template'] )
			Cmd::quit( "Check vgname and template for LVM storage for server $server" );
	}

	public function rootfs_string()
	{
		return "/dev/". $this->storage['vgname'] ."/". Template::get($this->storage['template']);
	}
	
	// Mount LVM partition
	public function mount( $master=false )
	{
		// Make LVM snapshot, if this is master server and snapshot size is set
		if( $master && $this->storage["snapshot-size"] )
		{
			$name = Template::get($this->storage['template']) . SNAPSHOT_POSTFIX;
			Cmd::ssh( $this->server, "sudo lvcreate -L". $this->storage["snapshot-size"] ." -s -n $name -p r ". $this->rootfs_string() );
			$mount_device = $this->rootfs_string() . SNAPSHOT_POSTFIX;
		}
		else
			$mount_device = $this->rootfs_string();

		$dir = $this->mount_point = $this->mount_dir();
		Cmd::ssh( $this->server, "sudo mkdir '$dir'" );
		Cmd::ssh( $this->server, "sudo mount $mount_device '$dir'" );
		
		return $dir;
	}
	
	public function umount( $master=false )
	{
		$dir = $this->mount_point;
		Cmd::ssh( $this->server, "sudo umount '$dir'" );
		Cmd::ssh( $this->server, "sudo rmdir '$dir'" );

		// Remove LVM snapshot
		if( $master && $this->storage["snapshot-size"] )
		{
			Cmd::ssh( $this->server, "sudo lvremove -f " . $this->rootfs_string() . SNAPSHOT_POSTFIX );
		}
	}
}