<?php

/*

This file contains all of the code to setup the initial MySQL database. (setup.php)

*/

define( 'ROOT', '../../../' );

if( !@($GLOBALS["___mysqli_ston"] = mysqli_connect( $_ISA[ 'db_server' ],  $_ISA[ 'db_user' ],  $_ISA[ 'db_password' ] )) ) {
	MessagePush( "Could not connect to the MySQL service.<br />Please check the config file." );
	if ($_ISA[ 'db_user' ] == "root") {
		MessagePush( 'Your database user is root, if you are using MariaDB, this will not work, please read the README.md file.' );
	}
	PageReload();
}


// Create database
$drop_db = "DROP DATABASE IF EXISTS {$_ISA[ 'db_database' ]};";
if( !@mysqli_query($GLOBALS["___mysqli_ston"],  $drop_db ) ) {
	MessagePush( "Could not drop existing database<br />SQL: " . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) );
	PageReload();
}

$create_db = "CREATE DATABASE {$_ISA[ 'db_database' ]};";
if( !@mysqli_query($GLOBALS["___mysqli_ston"],  $create_db ) ) {
	MessagePush( "Could not create database<br />SQL: " . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) );
	PageReload();
}
MessagePush( "Database has been created." );


// Create table 'users'
if( !@((bool)mysqli_query($GLOBALS["___mysqli_ston"], "USE " . $_ISA[ 'db_database' ])) ) {
	MessagePush( 'Could not connect to database.' );
	PageReload();
}

$create_tb = "CREATE TABLE users (user_id int(6),first_name varchar(15),last_name varchar(15), user varchar(15), password varchar(32),avatar varchar(70), last_login TIMESTAMP, failed_login INT(3), PRIMARY KEY (user_id));";
if( !mysqli_query($GLOBALS["___mysqli_ston"],  $create_tb ) ) {
	MessagePush( "Table could not be created<br />SQL: " . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) );
	PageReload();
}
MessagePush( "'users' table was created." );


// Insert some data into users
// Get the base directory for the avatar media...
$baseUrl  = 'http://' . $_SERVER[ 'SERVER_NAME' ] . $_SERVER[ 'PHP_SELF' ];
$stripPos = strpos( $baseUrl, 'setup.php' );
$baseUrl  = substr( $baseUrl, 0, $stripPos ) . 'hackable/users/';

$insert = "INSERT INTO users VALUES
	('1','admin','admin','admin',MD5('password'),'{$baseUrl}admin.jpg', NOW(), '0'),
	('2','Gordon','Brown','gordonb',MD5('abc123'),'{$baseUrl}gordonb.jpg', NOW(), '0'),
	('3','Hack','Me','1337',MD5('charley'),'{$baseUrl}1337.jpg', NOW(), '0'),
	('4','Pablo','Picasso','pablo',MD5('letmein'),'{$baseUrl}pablo.jpg', NOW(), '0'),
	('5','Bob','Smith','smithy',MD5('password'),'{$baseUrl}smithy.jpg', NOW(), '0');";
if( !mysqli_query($GLOBALS["___mysqli_ston"],  $insert ) ) {
	MessagePush( "Data could not be inserted into 'users' table<br />SQL: " . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) );
	PageReload();
}
MessagePush( "Data inserted into 'users' table." );


// Create guestbook table
$create_tb_guestbook = "CREATE TABLE guestbook (comment_id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT, comment varchar(300), name varchar(100), PRIMARY KEY (comment_id));";
if( !mysqli_query($GLOBALS["___mysqli_ston"],  $create_tb_guestbook ) ) {
	MessagePush( "Table could not be created<br />SQL: " . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) );
	PageReload();
}
MessagePush( "'guestbook' table was created." );


// Insert data into 'guestbook'
$insert = "INSERT INTO guestbook VALUES ('1','This is a test comment.','test');";
if( !mysqli_query($GLOBALS["___mysqli_ston"],  $insert ) ) {
	MessagePush( "Data could not be inserted into 'guestbook' table<br />SQL: " . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) );
	PageReload();
}
MessagePush( "Data inserted into 'guestbook' table." );




// Copy .bak for a fun directory listing vuln
$conf = ROOT . 'config/config.inc.php';
$bakconf = ROOT . 'config/config.inc.php.bak';
if (file_exists($conf)) {
	// Who cares if it fails. Suppress.
	@copy($conf, $bakconf);
}

MessagePush( "Backup file /config/config.inc.php.bak automatically created" );

// Done
MessagePush( "<em>Setup successful</em>!" );

if( !IsLoggedIn())
	MessagePush( "Please <a href='login.php'>login</a>.<script>setTimeout(function(){window.location.href='login.php'},5000);</script>" );
PageReload();

?>
