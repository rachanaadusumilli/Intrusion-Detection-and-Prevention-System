<?php

define( 'ROOT', '../' );
require_once ROOT . 'isa/includes/Page.inc.php';

PageStartup( array( 'authenticated', 'phpids' ) );

$page = PageNewGrab();
$page[ 'title' ] = 'Help' . $page[ 'title_separator' ].$page[ 'title' ];

$id       = $_GET[ 'id' ];
$security = $_GET[ 'security' ];

ob_start();
eval( '?>' . file_get_contents( ROOT . "vulnerabilities/{$id}/help/help.php" ) . '<?php ' );
$help = ob_get_contents();
ob_end_clean();

$page[ 'body' ] .= "
<div class=\"body_padded\">
	{$help}
</div>\n";

HelpHtmlEcho( $page );

?>
