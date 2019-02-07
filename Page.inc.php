ISA<?php

if( !defined( 'ROOT' ) ) {
	die( 'ROOT undefined' );
	exit;
}

session_start(); // Creates a 'Full Path Disclosure' vuln.

if (!file_exists(ROOT . 'config/config.inc.php')) {
	die ("config file not found. Copy config/config.inc.php.dist to config/config.inc.php and configure to your environment.");
}

// Include configs
require_once ROOT . 'config/config.inc.php';
require_once( 'PhpIds.inc.php' );

// Declare the $html variable
if( !isset( $html ) ) {
	$html = "";
}

// Valid security levels
$security_levels = array('low', 'medium', 'high', 'impossible');
if( !isset( $_COOKIE[ 'security' ] ) || !in_array( $_COOKIE[ 'security' ], $security_levels ) ) {
	// Set security cookie to impossible if no cookie exists
	if( in_array( $_ISA[ 'default_security_level' ], $security_levels) ) {
		SecurityLevelSet( $_ISA[ 'default_security_level' ] );
	}
	else {
		SecurityLevelSet( 'impossible' );
	}

	if( $_ISA[ 'default_phpids_level' ] == 'enabled' )
		PhpIdsEnabledSet( true );
	else
		PhpIdsEnabledSet( false );
}




// Start session functions --

function &SessionGrab() {
	if( !isset( $_SESSION[ 'isa' ] ) ) {
		$_SESSION[ 'isa' ] = array();
	}
	return $_SESSION[ 'isa' ];
}


function PageStartup( $pActions ) {
	if( in_array( 'authenticated', $pActions ) ) {
		if( !IsLoggedIn()) {
			Redirect( ROOT . 'login.php' );
		}
	}

	if( in_array( 'phpids', $pActions ) ) {
		if( PhpIdsIsEnabled() ) {
			PhpIdsTrap();
		}
	}
}


function PhpIdsEnabledSet( $pEnabled ) {
	$Session =& SessionGrab();
	if( $pEnabled ) {
		$Session[ 'php_ids' ] = 'enabled';
	}
	else {
		unset( $Session[ 'php_ids' ] );
	}
}


function PhpIdsIsEnabled() {
	$Session =& SessionGrab();
	return isset( $Session[ 'php_ids' ] );
}


function Login( $pUsername ) {
	$Session =& SessionGrab();
	$Session[ 'username' ] = $pUsername;
}


function IsLoggedIn() {
	$Session =& SessionGrab();
	return isset( $Session[ 'username' ] );
}


function Logout() {
	$Session =& SessionGrab();
	unset( $Session[ 'username' ] );
}


function PageReload() {
	Redirect( $_SERVER[ 'PHP_SELF' ] );
}

function CurrentUser() {
	$Session =& SessionGrab();
	return ( isset( $Session[ 'username' ]) ? $Session[ 'username' ] : '') ;
}

// -- END (Session functions)

function &PageNewGrab() {
	$returnArray = array(
		'title'           => ‘ISA',
		'title_separator' => ' :: ',
		'body'            => '',
		'page_id'         => '',
		'help_button'     => '',
		'source_button'   => '',
	);
	return $returnArray;
}


function SecurityLevelGet() {
	return isset( $_COOKIE[ 'security' ] ) ? $_COOKIE[ 'security' ] : 'impossible';
}


function SecurityLevelSet( $pSecurityLevel ) {
	if( $pSecurityLevel == 'impossible' ) {
		$httponly = true;
	}
	else {
		$httponly = false;
	}
	setcookie( session_name(), session_id(), null, '/', null, null, $httponly );
	setcookie( 'security', $pSecurityLevel, NULL, NULL, NULL, NULL, $httponly );
}


// Start message functions --

function MessagePush( $pMessage ) {
	$Session =& SessionGrab();
	if( !isset( $Session[ 'messages' ] ) ) {
		$Session[ 'messages' ] = array();
	}
	$Session[ 'messages' ][] = $pMessage;
}


function MessagePop() {
	$Session =& SessionGrab();
	if( !isset( $Session[ 'messages' ] ) || count( $Session[ 'messages' ] ) == 0 ) {
		return false;
	}
	return array_shift( $Session[ 'messages' ] );
}


function messagesPopAllToHtml() {
	$messagesHtml = '';
	while( $message = MessagePop() ) {   // TODO- sharpen!
		$messagesHtml .= "<div class=\"message\">{$message}</div>";
	}

	return $messagesHtml;
}

// --END (message functions)

function HtmlEcho( $pPage ) {
	$menuBlocks = array();

	$menuBlocks[ 'home' ] = array();
	if( IsLoggedIn() ) {
		$menuBlocks[ 'home' ][] = array( 'id' => 'home', 'name' => 'Home', 'url' => '.' );
		$menuBlocks[ 'home' ][] = array( 'id' => 'instructions', 'name' => 'Instructions', 'url' => 'instructions.php' );
		$menuBlocks[ 'home' ][] = array( 'id' => 'setup', 'name' => 'Setup / Reset DB', 'url' => 'setup.php' );
	}
	else {
		$menuBlocks[ 'home' ][] = array( 'id' => 'setup', 'name' => 'Setup', 'url' => 'setup.php' );
		$menuBlocks[ 'home' ][] = array( 'id' => 'instructions', 'name' => 'Instructions', 'url' => 'instructions.php' );
	}

	if( IsLoggedIn() ) {
		$menuBlocks[ 'vulnerabilities' ] = array();
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'brute', 'name' => 'Brute Force', 'url' => 'vulnerabilities/brute/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'exec', 'name' => 'Command Injection', 'url' => 'vulnerabilities/exec/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'csrf', 'name' => 'CSRF', 'url' => 'vulnerabilities/csrf/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'fi', 'name' => 'File Inclusion', 'url' => 'vulnerabilities/fi/.?page=include.php' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'upload', 'name' => 'File Upload', 'url' => 'vulnerabilities/upload/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'captcha', 'name' => 'Insecure CAPTCHA', 'url' => 'vulnerabilities/captcha/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sqli', 'name' => 'SQL Injection', 'url' => 'vulnerabilities/sqli/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sqli_blind', 'name' => 'SQL Injection (Blind)', 'url' => 'vulnerabilities/sqli_blind/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'weak_id', 'name' => 'Weak Session IDs', 'url' => 'vulnerabilities/weak_id/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'xss_d', 'name' => 'XSS (DOM)', 'url' => 'vulnerabilities/xss_d/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'xss_r', 'name' => 'XSS (Reflected)', 'url' => 'vulnerabilities/xss_r/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'xss_s', 'name' => 'XSS (Stored)', 'url' => 'vulnerabilities/xss_s/' );
	}

	$menuBlocks[ 'meta' ] = array();
	if( IsLoggedIn() ) {
		$menuBlocks[ 'meta' ][] = array( 'id' => 'security', 'name' => ' Security', 'url' => 'security.php' );
		$menuBlocks[ 'meta' ][] = array( 'id' => 'phpinfo', 'name' => 'PHP Info', 'url' => 'phpinfo.php' );
	}
	$menuBlocks[ 'meta' ][] = array( 'id' => 'about', 'name' => 'About', 'url' => 'about.php' );

	if( IsLoggedIn() ) {
		$menuBlocks[ 'logout' ] = array();
		$menuBlocks[ 'logout' ][] = array( 'id' => 'logout', 'name' => 'Logout', 'url' => 'logout.php' );
	}

	$menuHtml = '';

	foreach( $menuBlocks as $menuBlock ) {
		$menuBlockHtml = '';
		foreach( $menuBlock as $menuItem ) {
			$selectedClass = ( $menuItem[ 'id' ] == $pPage[ 'page_id' ] ) ? 'selected' : '';
			$fixedUrl = ROOT.$menuItem[ 'url' ];
			$menuBlockHtml .= "<li onclick=\"window.location='{$fixedUrl}'\" class=\"{$selectedClass}\"><a href=\"{$fixedUrl}\">{$menuItem[ 'name' ]}</a></li>\n";
		}
		$menuHtml .= "<ul class=\"menuBlocks\">{$menuBlockHtml}</ul>";
	}

	// Get security cookie --
	$securityLevelHtml = '';
	switch( SecurityLevelGet() ) {
		case 'low':
			$securityLevelHtml = 'low';
			break;
		case 'medium':
			$securityLevelHtml = 'medium';
			break;
		case 'high':
			$securityLevelHtml = 'high';
			break;
		default:
			$securityLevelHtml = 'impossible';
			break;
	}
	// -- END (security cookie)

	$phpIdsHtml   = '<em>PHPIDS:</em> ' . ( PhpIdsIsEnabled() ? 'enabled' : 'disabled' );
	$userInfoHtml = '<em>Username:</em> ' . ( CurrentUser() );

	$messagesHtml = messagesPopAllToHtml();
	if( $messagesHtml ) {
		$messagesHtml = "<div class=\"body_padded\">{$messagesHtml}</div>";
	}

	$systemInfoHtml = "";
	if( IsLoggedIn() )
		$systemInfoHtml = "<div align=\"left\">{$userInfoHtml}<br /><em>Security Level:</em> {$securityLevelHtml}<br />{$phpIdsHtml}</div>";
	if( $pPage[ 'source_button' ] ) {
		$systemInfoHtml = ButtonSourceHtmlGet( $pPage[ 'source_button' ] ) . " $systemInfoHtml";
	}
	if( $pPage[ 'help_button' ] ) {
		$systemInfoHtml = ButtonHelpHtmlGet( $pPage[ 'help_button' ] ) . " $systemInfoHtml";
	}

	// Send Headers + main HTML code
	Header( 'Cache-Control: no-cache, must-revalidate');   // HTTP/1.1
	Header( 'Content-Type: text/html;charset=utf-8' );     // TODO- proper XHTML headers...
	Header( 'Expires: Tue, 23 Jun 2009 12:00:00 GMT' );    // Date in the past

	echo "
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">

<html xmlns=\"http://www.w3.org/1999/xhtml\">

	<head>
		<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />

		<title>{$pPage[ 'title' ]}</title>

		<link rel=\"stylesheet\" type=\"text/css\" href=\"" . ROOT . "isa/css/main.css\" />

		<link rel=\"icon\" type=\"\image/ico\" href=\"" . ROOT . "favicon.ico\" />

		<script type=\"text/javascript\" src=\"" . ROOT . "isa/js/Page.js\"></script>

	</head>

	<body class=\"home\">
		<div id=\"container\">

			<div id=\"header\">

				<img src=\"" . ROOT . "isa/images/logo.png\" alt=\”ISA\” />

			</div>

			<div id=\"main_menu\">

				<div id=\"main_menu_padded\">
				{$menuHtml}
				</div>

			</div>

			<div id=\"main_body\">

				{$pPage[ 'body' ]}
				<br /><br />
				{$messagesHtml}

			</div>

			<div class=\"clear\">
			</div>

			<div id=\"system_info\">
				{$systemInfoHtml}
			</div>

			<div id=\"footer\">

				<p> ISA</p>

			</div>

		</div>

	</body>

</html>";
}


function HelpHtmlEcho( $pPage ) {
	// Send Headers
	Header( 'Cache-Control: no-cache, must-revalidate');   // HTTP/1.1
	Header( 'Content-Type: text/html;charset=utf-8' );     // TODO- proper XHTML headers...
	Header( 'Expires: Tue, 23 Jun 2009 12:00:00 GMT' );    // Date in the past

	echo "
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">

<html xmlns=\"http://www.w3.org/1999/xhtml\">

	<head>

		<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />

		<title>{$pPage[ 'title' ]}</title>

		<link rel=\"stylesheet\" type=\"text/css\" href=\"" . ROOT . "isa/css/help.css\" />

		<link rel=\"icon\" type=\"\image/ico\" href=\"" . ROOT . "favicon.ico\" />

	</head>

	<body>

	<div id=\"container\">

			{$pPage[ 'body' ]}

		</div>

	</body>

</html>";
}


function SourceHtmlEcho( $pPage ) {
	// Send Headers
	Header( 'Cache-Control: no-cache, must-revalidate');   // HTTP/1.1
	Header( 'Content-Type: text/html;charset=utf-8' );     // TODO- proper XHTML headers...
	Header( 'Expires: Tue, 23 Jun 2009 12:00:00 GMT' );    // Date in the past

	echo "
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">

<html xmlns=\"http://www.w3.org/1999/xhtml\">

	<head>

		<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />

		<title>{$pPage[ 'title' ]}</title>

		<link rel=\"stylesheet\" type=\"text/css\" href=\"" . ROOT . "isa/css/source.css\" />

		<link rel=\"icon\" type=\"\image/ico\" href=\"" . ROOT . "favicon.ico\" />

	</head>

	<body>

		<div id=\"container\">

			{$pPage[ 'body' ]}

		</div>

	</body>

</html>";
}

// To be used on all external links --
function ExternalLinkUrlGet( $pLink,$text=null ) {
	if(is_null( $text )) {
		return '<a href="' . $pLink . '" target="_blank">' . $pLink . '</a>';
	}
	else {
		return '<a href="' . $pLink . '" target="_blank">' . $text . '</a>';
	}
}
// -- END ( external links)

function ButtonHelpHtmlGet( $pId ) {
	$security = SecurityLevelGet();
	return "<input type=\"button\" value=\"View Help\" class=\"popup_button\" onclick=\"javascript:popUp( '" . ROOT . "vulnerabilities/view_help.php?id={$pId}&security={$security}' )\">";
}


function ButtonSourceHtmlGet( $pId ) {
	$security = SecurityLevelGet();
	return "<input type=\"button\" value=\"View Source\" class=\"popup_button\" onclick=\"javascript:popUp( '" . ROOT . "vulnerabilities/view_source.php?id={$pId}&security={$security}' )\">";
}


// Database Management --

if( $DBMS == 'MySQL' ) {
	$DBMS = htmlspecialchars(strip_tags( $DBMS ));
	$DBMS_errorFunc = 'mysqli_error()';
}
elseif( $DBMS == 'PGSQL' ) {
	$DBMS = htmlspecialchars(strip_tags( $DBMS ));
	$DBMS_errorFunc = 'pg_last_error()';
}
else {
	$DBMS = "No DBMS selected.";
	$DBMS_errorFunc = '';
}

//$DBMS_connError = '
//	<div align="center">
//		<img src="' . ROOT . 'isa/images/logo.png" />
//		<pre>Unable to connect to the database.<br />' . $DBMS_errorFunc . '<br /><br /></pre>
//		Click <a href="' . ROOT . 'setup.php">here</a> to setup the database.
//	</div>';

function DatabaseConnect() {
	global $_ISA;
	global $DBMS;
	//global $DBMS_connError;
	global $db;

	if( $DBMS == 'MySQL' ) {
		if( !@($GLOBALS["___mysqli_ston"] = mysqli_connect( $_ISA[ 'db_server' ],  $_ISA[ 'db_user' ],  $_ISA[ 'db_password' ] ))
		|| !@((bool)mysqli_query($GLOBALS["___mysqli_ston"], "USE " . $_ISA[ 'db_database' ])) ) {
			//die( $DBMS_connError );
			Logout();
			MessagePush( 'Unable to connect to the database.<br />' . $DBMS_errorFunc );
			Redirect( ROOT . 'setup.php' );
		}
		// MySQL PDO Prepared Statements (for impossible levels)
		$db = new PDO('mysql:host=' . $_ISA[ 'db_server' ].';dbname=' . $_ISA[ 'db_database' ].';charset=utf8', $_ISA[ 'db_user' ], $_ISA[ 'db_password' ]);
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	}
	elseif( $DBMS == 'PGSQL' ) {
		//$dbconn = pg_connect("host={$_ISA[ 'db_server' ]} dbname={$_ISA[ 'db_database' ]} user={$_ISA[ 'db_user' ]} password={$_ISA[ 'db_password' ])}"
		//or die( $DBMS_connError );
		MessagePush( 'PostgreSQL is not yet fully supported.' );
		PageReload();
	}
	else {
		die ( "Unknown {$DBMS} selected." );
	}
}

// -- END (Database Management)


function Redirect( $pLocation ) {
	session_commit();
	header( "Location: {$pLocation}" );
	exit;
}

// XSS Stored guestbook function --
function Guestbook() {
	$query  = "SELECT name, comment FROM guestbook";
	$result = mysqli_query($GLOBALS["___mysqli_ston"],  $query );

	$guestbook = '';

	while( $row = mysqli_fetch_row( $result ) ) {
		if( SecurityLevelGet() == 'impossible' ) {
			$name    = htmlspecialchars( $row[0] );
			$comment = htmlspecialchars( $row[1] );
		}
		else {
			$name    = $row[0];
			$comment = $row[1];
		}

		$guestbook .= "<div id=\"guestbook_comments\">Name: {$name}<br />" . "Message: {$comment}<br /></div>\n";
	}
	return $guestbook;
}
// -- END (XSS Stored guestbook)


// Token functions --
function checkToken( $user_token, $session_token, $returnURL ) {  # Validate the given (CSRF) token
	if( $user_token !== $session_token || !isset( $session_token ) ) {
		MessagePush( 'CSRF token is incorrect' );
		Redirect( $returnURL );
	}
}

function generateSessionToken() {  # Generate a brand new (CSRF) token
	if( isset( $_SESSION[ 'session_token' ] ) ) {
		destroySessionToken();
	}
	$_SESSION[ 'session_token' ] = md5( uniqid() );
}

function destroySessionToken() {  # Destroy any session with the name 'session_token'
	unset( $_SESSION[ 'session_token' ] );
}

function tokenField() {  # Return a field for the (CSRF) token
	return "<input type='hidden' name='user_token' value='{$_SESSION[ 'session_token' ]}' />";
}
// -- END (Token functions)


// Setup Functions --
$PHPUploadPath    = realpath( getcwd() . DIRECTORY_SEPARATOR . ROOT . "hackable" . DIRECTORY_SEPARATOR . "uploads" ) . DIRECTORY_SEPARATOR;
$PHPIDSPath       = realpath( getcwd() . DIRECTORY_SEPARATOR . ROOT . "external" . DIRECTORY_SEPARATOR . "phpids" . DIRECTORY_SEPARATOR . PhpIdsVersionGet() . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . "IDS" . DIRECTORY_SEPARATOR . "tmp" . DIRECTORY_SEPARATOR . "phpids_log.txt" );
$PHPCONFIGPath       = realpath( getcwd() . DIRECTORY_SEPARATOR . ROOT . "config");


$phpDisplayErrors = 'PHP function display_errors: <em>' . ( ini_get( 'display_errors' ) ? 'Enabled</em> <i>(Easy Mode!)</i>' : 'Disabled</em>' );                                                  // Verbose error messages (e.g. full path disclosure)
$phpSafeMode      = 'PHP function safe_mode: <span class="' . ( ini_get( 'safe_mode' ) ? 'failure">Enabled' : 'success">Disabled' ) . '</span>';                                                   // DEPRECATED as of PHP 5.3.0 and REMOVED as of PHP 5.4.0
$phpMagicQuotes   = 'PHP function magic_quotes_gpc: <span class="' . ( ini_get( 'magic_quotes_gpc' ) ? 'failure">Enabled' : 'success">Disabled' ) . '</span>';                                     // DEPRECATED as of PHP 5.3.0 and REMOVED as of PHP 5.4.0
$phpURLInclude    = 'PHP function allow_url_include: <span class="' . ( ini_get( 'allow_url_include' ) ? 'success">Enabled' : 'failure">Disabled' ) . '</span>';                                   // RFI
$phpURLFopen      = 'PHP function allow_url_fopen: <span class="' . ( ini_get( 'allow_url_fopen' ) ? 'success">Enabled' : 'failure">Disabled' ) . '</span>';                                       // RFI
$phpGD            = 'PHP module gd: <span class="' . ( ( extension_loaded( 'gd' ) && function_exists( 'gd_info' ) ) ? 'success">Installed' : 'failure">Missing' ) . '</span>';                    // File Upload
$phpMySQL         = 'PHP module mysql: <span class="' . ( ( extension_loaded( 'mysqli' ) && function_exists( 'mysqli_query' ) ) ? 'success">Installed' : 'failure">Missing' ) . '</span>';                // Core 
$phpPDO           = 'PHP module pdo_mysql: <span class="' . ( extension_loaded( 'pdo_mysql' ) ? 'success">Installed' : 'failure">Missing' ) . '</span>';                // SQLi
$Recaptcha    = 'reCAPTCHA key: <span class="' . ( ( isset( $_ISA[ 'recaptcha_public_key' ] ) && $_ISA[ 'recaptcha_public_key' ] != '' ) ? 'success">' . $_ISA[ 'recaptcha_public_key' ] : 'failure">Missing' ) . '</span>';

$UploadsWrite = '[User: ' . get_current_user() . '] Writable folder ' . $PHPUploadPath . ': <span class="' . ( is_writable( $PHPUploadPath ) ? 'success">Yes' : 'failure">No' ) . '</span>';                                     // File Upload
$bakWritable = '[User: ' . get_current_user() . '] Writable folder ' . $PHPCONFIGPath . ': <span class="' . ( is_writable( $PHPCONFIGPath ) ? 'success">Yes' : 'failure">No' ) . '</span>';   // config.php.bak check                                  // File Upload
$PHPWrite     = '[User: ' . get_current_user() . '] Writable file ' . $PHPIDSPath . ': <span class="' . ( is_writable( $PHPIDSPath ) ? 'success">Yes' : 'failure">No' ) . '</span>';                                              // PHPIDS

$OS           = 'Operating system: <em>' . ( strtoupper( substr (PHP_OS, 0, 3)) === 'WIN' ? 'Windows' : '*nix' ) . '</em>';
$SERVER_NAME      = 'Web Server SERVER_NAME: <em>' . $_SERVER[ 'SERVER_NAME' ] . '</em>';                                                                                                          // CSRF

$MYSQL_USER       = 'MySQL username: <em>' . $_ISA[ 'db_user' ] . '</em>';
$MYSQL_PASS       = 'MySQL password: <em>' . ( ($_ISA[ 'db_password' ] != "" ) ? '******' : '*blank*' ) . '</em>';
$MYSQL_DB         = 'MySQL database: <em>' . $_ISA[ 'db_database' ] . '</em>';
$MYSQL_SERVER     = 'MySQL host: <em>' . $_ISA[ 'db_server' ] . '</em>';
// -- END (Setup Functions)

?>
