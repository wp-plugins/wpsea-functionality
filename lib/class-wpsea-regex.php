<?php

/**
* description of package
*
* @package YourPackage
* @subpackage Subpackage name
* @author firstname lastname <user@host.com>
*/
class Wpsea_Regex
{
	public function __construct() { }

	public function get_name_regex() {
		return '/^[a-zA-Z0-9]+[\s[a-zA-Z0-9\.\-,]+]{0,4}$/';
	}

	public function get_subject_regex() {
		return '/^[a-zA-Z0-9]+[\s[a-zA-Z0-9\.\-\@\$:\?\,]+]{0,}$/';
	}

	public function get_message_regex() {
		return '/^[a-zA-Z0-9]+[\s[a-zA-Z0-9\.\-\@\$:\?\,\+\_\=]+]{0,}$/';
	}

	public function get_empty_regex() {
		return '/^\s*$/';
	}

	public function get_analytics_regex() {
		return '/UA\-\d{4,10}\-\d{1,2}/';
	}

}

