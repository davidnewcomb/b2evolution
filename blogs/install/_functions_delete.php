<?php
/**
 * This file implements deletion of DB tables
 *
 * b2evolution - {@link http://b2evolution.net/}
 *
 * Released under GNU GPL License - http://b2evolution.net/about/license.html
 *
 * @copyright (c)2003-2004 by Francois PLANQUE - {@link http://fplanque.net/}
 *
 * @package install
 */
if( substr( basename($_SERVER['SCRIPT_FILENAME']), 0, 1 ) == '_' )
	die(' Please, do not access this page directly.' );

function db_delete()
{
	global $tableposts, $tableusers, $tablesettings, $tablecategories, $tablecomments, $tableblogs,
				$tablepostcats, $tablehitlog, $tableantispam, $tablegroups, $tableblogusers;

	echo "Dropping Antispam table...<br />\n";
	$query = "DROP TABLE IF EXISTS $tableantispam";
	$q = mysql_query($query) or mysql_oops( $query );

	echo "Dropping Hit-Logs...<br />\n";
	$query = "DROP TABLE IF EXISTS $tablehitlog";
	$q = mysql_query($query) or mysql_oops( $query );

	echo "Dropping Comments...<br />\n";
	$query = "DROP TABLE IF EXISTS $tablecomments";
	$q = mysql_query($query) or mysql_oops( $query );

	echo "Dropping Categories-to-Posts relationships...<br />\n";
	$query = "DROP TABLE IF EXISTS $tablepostcats";
	$q = mysql_query($query) or mysql_oops( $query );

	echo "Dropping Categories...<br />\n";
	$query = "DROP TABLE IF EXISTS $tablecategories";
	$q = mysql_query($query) or mysql_oops( $query );

	echo "Dropping Posts...<br />\n";
	$query = "DROP TABLE IF EXISTS $tableposts";
	$q = mysql_query($query) or mysql_oops( $query );

	echo "Dropping Users...<br />\n";
	$query = "DROP TABLE IF EXISTS $tableusers";
	$q = mysql_query($query) or mysql_oops( $query );

	echo "Dropping Groups...<br />\n";
	$query = "DROP TABLE IF EXISTS $tablegroups";
	$q = mysql_query($query) or mysql_oops( $query );

	echo "Dropping Blogs...<br />\n";
	$query = "DROP TABLE IF EXISTS $tableblogs";
	$q = mysql_query($query) or mysql_oops( $query );

	echo "Dropping Blogusers...<br />\n";
	$query = "DROP TABLE IF EXISTS $tableblogusers";
	$q = mysql_query($query) or mysql_oops( $query );

	echo "Dropping Settings...<br />\n";
	$query = "DROP TABLE IF EXISTS $tablesettings";
	$q = mysql_query($query) or mysql_oops( $query );

	echo "Dropping Locales...<br />\n";
	$query = "DROP TABLE IF EXISTS $tablelocales";
	$q = mysql_query($query) or mysql_oops( $query );
}

?>