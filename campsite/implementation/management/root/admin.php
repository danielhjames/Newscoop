<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/configuration.php');

$no_menu_scripts = array('/login.php', '/do_login.php', '/pub/issues/preview.php',
	'/pub/issues/empty.php', '/ad_popup.php', '/pub/issues/sections/articles/preview.php',
	'/pub/issues/sections/articles/empty.php');

$request_uri = $_SERVER['REQUEST_URI'];
$call_script = substr($request_uri, strlen("/$ADMIN"));

// Remove any GET parameters
if (($question_mark = strpos($call_script, '?')) !== false) {
	$call_script = substr($call_script, 0, $question_mark);
}

// Remove all attempts to get at other parts of the file system
$call_script = str_replace('/../', '/', $call_script);

$is_image = (strstr($call_script, '/img/') !== false);
$extension = '';
if (($extension_start = strrpos($call_script, '.')) !== false) {
	$extension = strtolower(substr($call_script, $extension_start));
}
	
// Is it an image?
if ($is_image) {
	$extension = substr(strrchr($call_script, '.'), 1);
	// Expire one day from now.
	$secondsTillExpired = 86400;
	$currentTime = time();
	$expireTime = $currentTime + $secondsTillExpired;
	header("Content-type: image/$extension");
    header('Expires: ' . gmdate("D, d M Y H:i:s", $expireTime) . ' GMT');
    header('Last-Modified: ' . gmdate("D, d M Y H:i:s", $currentTime) . ' GMT');
    header('Cache-Control: private, max-age=' . $secondsTillExpired . ', must-revalidate, pre-check=' . $secondsTillExpired);
	readfile($Campsite['HTML_DIR'] . "/$ADMIN_DIR/$call_script");
} 
elseif (($extension == '.php') || ($extension == '')) {
	// Requested file is not an image
	header("Content-type: text/html; charset=UTF-8");

	// If they arent trying to login in...
	if (($call_script != '/login.php') && ($call_script != '/do_login.php')) {
		// Check if the user is logged in already
		require_once($_SERVER['DOCUMENT_ROOT'].'/classes/common.php');
		//load_common_include_files($ADMIN_DIR);
		list($access, $User) = check_basic_access($_REQUEST);
		if (!$access) {
			// If not logged in, show the login screen.
			header("Location: /$ADMIN/login.php");
			return;
		}
	}
		
	// If its not a PHP file, assume its a directory.
	if ($extension != '.php') {
		// If its a directory
		if (($call_script != '') && ($call_script[strlen($call_script)-1] != '/') ) {
			$call_script .= '/';
		}
		$call_script .= 'index.php';
	}
	$needs_menu = ! in_array($call_script, $no_menu_scripts);

	$_top_menu = '';
	if ($needs_menu) {
		ob_start();
		echo "<html><table width=\"100%\">\n<tr><td>\n";
		require_once($Campsite['HTML_DIR'] . "/$ADMIN_DIR/menu.php");
		echo "</td></tr>\n<tr><td>\n";
		$_top_menu = ob_get_clean();
	}
	
	// Clean up the global namespace before we call the script
	unset($is_image);
	unset($extension);
	unset($extension_start);
	unset($question_mark);
	unset($no_menu_scripts);
	unset($request_uri);
	
	// Call the script
	ob_start();
	require_once($Campsite['HTML_DIR'] . "/$ADMIN_DIR/$call_script");
	$content = ob_get_clean();
	echo $_top_menu . $content;
	
	if ($needs_menu) {
		echo "</td></tr>\n</table>\n</html>\n";
	}
}
else {
	readfile($Campsite['HTML_DIR'] . "/$ADMIN_DIR/$call_script");	
}
?>