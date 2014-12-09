<?php


class Template
{
	static public function get( $str )
	{
		return str_replace(
			array("{VM}", "{YEAR}", "{MONTH}", "{DAY}", "{HOUR}", "{MIN}", "{SEC}"),
			array(VM, date("Y"), date("m"), date("d"), date("H"), date("i"), date("s")),
			$str
		);
	}
}