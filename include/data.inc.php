<?php
	class Data {
		function giveValue($key) {
			if(isset($this->data[$key])) return $this->data[$key];
			else return null;
		}

		function formatNumber($number, $force_float = false, $decimals = 2) {
			$number = str_replace(',', '.', $number);
			$number = number_format($number, $decimals, "," , ".");
			if(empty($force_float)) {
				$number = preg_replace("/[0]*$/", '', $number);
				$number = preg_replace("/[\,]*$/", '', $number);
			}
			return $number;
		} /* End of function DataList::formatNumber() */

		function formatDate($date, $format = NULL) {
			if(empty($format)) {
				$format = $GLOBALS['_PJ_format_date'];
			}
			if($date == '') {
				return NULL;
			}
			list($year, $month, $day) = explode("-", $date);
			/* workaround for Windows because Windows does not support dates before 1.1.1970 */
			if(strstr(PHP_OS, "WIN")) {
				$date = str_replace("d", $day, $format);
				$date = str_replace("m", $month, $date);
				$date = str_replace("Y", $year, $date);
				return $date;
			}
			$timestamp = mktime(0, 0, 0, $month, $day, $year);
			$error_reporting = error_reporting(0);
			$date = date($format, $timestamp);
			error_reporting($error_reporting);
			return $date;
		} /* End of function DataList::formatDate() */

		function formatTime($time, $format = "H:i:s") {
			if($time == '') {
				return NULL;
			}
			// Fix: Handle both date (YYYY-MM-DD) and time (HH:MM:SS) formats
			if(strpos($time, '-') !== false) {
				// Date format (YYYY-MM-DD or YYYY-MM-DD HH:MM:SS)
				$timestamp = strtotime($time);
				if($timestamp === false) {
					return NULL;
				}
				return date($format, $timestamp);
			} else if(strpos($time, ':') !== false) {
				// Time format (HH:MM:SS)
				$time_parts = explode(":", $time);
				$hour = isset($time_parts[0]) ? (int)$time_parts[0] : 0;
				$minute = isset($time_parts[1]) ? (int)$time_parts[1] : 0;
				$second = isset($time_parts[2]) ? (int)$time_parts[2] : 0;
				/* workaround for Windows because Windows does not support dates before 1.1.1970 */
				if(strstr(PHP_OS, "WIN")) {
					$date = str_replace("H", sprintf("%02d", $hour), $format);
					$date = str_replace("i", sprintf("%02d", $minute), $date);
					$date = str_replace("s", sprintf("%02d", $second), $date);
					return $date;
				}
				$timestamp = mktime($hour, $minute, $second, 1, 1, 2002);
				$error_reporting = error_reporting(0);
				$date = date($format, $timestamp);
				error_reporting($error_reporting);
				return $date;
			} else {
				// Invalid format
				return NULL;
			}
		} /* End of function DataList::formatTime() */

		function checkUserAccess($mode = 'read') {
			return $this->user_access[$mode];
		}

		function getUserAccess() {
			if(!isset($this->user) || !is_object($this->user)) {
				return array('read' => false, 'write' => false, 'new' => false);
			}
			if(is_callable(array($this->user, 'checkPermission')) && $this->user->checkPermission('admin')) {
				return array('read' => true, 'write' => true, 'new' => true);
			}
			$u_gids			= explode(',', is_callable(array($this->user, 'giveValue')) ? $this->user->giveValue('gids') : (property_exists($this->user, 'gids') ? $this->user->gids : ''));
			// Fix: Add null checks for substr() to prevent PHP 8.4 deprecation warnings
			$access_value = $this->giveValue('access');
			// FATAL: access field is null - this should never happen!
			if ($access_value === null) {
				$error_msg = "FATAL ERROR: access field is null - class: " . get_class($this) . ", user_id: " . ($this->user ? $this->user->giveValue('id') : 'no_user') . ", object_id: " . ($this->giveValue('id') ?? 'no_id');
				error_log($error_msg);
				die($error_msg . " - This indicates a programming error that must be fixed!");
			}
			
			$access_owner	= substr($access_value, 0, 3);
			$access_group	= substr($access_value, 3, 3);
			$access_world	= substr($access_value, 6, 3);
			if(substr($access_world, 0, 1) == 'r' ||
			   (in_array($this->giveValue('gid'), $u_gids) && substr($access_group, 0, 1) == 'r') ||
			   ($this->giveValue('user') == $this->user->giveValue('id') && substr($access_owner, 0, 1) == 'r')
			   ) {
				$user_access['read']		= true;
			} else {
				$user_access['read']		= false;
			}
			if((!$this->giveValue('billed') && $this->giveValue('billed') != '0000-00-00') && (
			    substr($access_world, 1, 1) == 'w' ||
			   (in_array($this->giveValue('gid'), $u_gids) && substr($access_group, 1, 1) == 'w') ||
			   ($this->giveValue('user') == $this->user->giveValue('id') && substr($access_owner, 1, 1) == 'w')
			   )) {
				$user_access['write']		= true;
			} else {
				$user_access['write']		= false;
			}
			if(substr($access_world, 2, 1) == 'x' ||
			   (in_array($this->giveValue('gid'), $u_gids) && substr($access_group, 2, 1) == 'x') ||
			   ($this->giveValue('user') == $this->user->giveValue('id') && substr($access_owner, 2, 1) == 'x')
			   ) {
				$user_access['new']		= true;
			} else {
				$user_access['new']		= false;
			}
			return $user_access;
		} /* End of function Data::getUserAccess() */

	} /* End of class Data*/	
?>
