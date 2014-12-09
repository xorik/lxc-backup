<?php

// TODO: do something with lvm.autostart

// Autoloader
spl_autoload_register( function($class)
{
	require( "class/". strtolower($class) .".php" );
});


Config::init( __DIR__."/config.json" );
Param::check( $argv, $argc );

$vm = array( new VM(SERVER1),new VM(SERVER2) );

// Check if any of VM exists
if( $vm[0]->state==VM_STATE_NOT_EXISTS && $vm[1]->state==VM_STATE_NOT_EXISTS )
	Cmd::quit( "VM is not exists on both hosts!" );

// If not exists on one host - need to copy
elseif( $vm[0]->state==VM_STATE_NOT_EXISTS || $vm[1]->state==VM_STATE_NOT_EXISTS )
{
	if( !AUTO_COPY_VM )
		Cmd::quit( "VM is not exist on one of servers and --copy param is not set" );
	
	// Copy from server1 to server2
	if( $vm[1]->state==VM_STATE_NOT_EXISTS )
	{
		$vm[1]->storage->create();
		VM::rsync( $vm[0], $vm[1] );
	}
	// Copy from server2 to server1
	else
	{
		$vm[0]->storage->create();
		VM::rsync( $vm[1], $vm[0] );
	}
}
// VM exists on both hosts
// Running on server1
elseif( $vm[0]->state==VM_STATE_RUNNING && $vm[1]->state==VM_STATE_STOPPED )
{
	VM::rsync( $vm[0], $vm[1] );
}
// Running on server2
elseif( $vm[1]->state==VM_STATE_RUNNING && $vm[0]->state==VM_STATE_STOPPED )
{
	VM::rsync( $vm[1], $vm[0] );
}
else
{
	Cmd::quit( "VM is stopped or running on both servers, don't know what to do..." );
}