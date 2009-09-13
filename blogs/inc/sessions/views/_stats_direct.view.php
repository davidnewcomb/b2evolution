<?php
/**
 * This file implements the UI view for the Direct accesses stats.
 *
 * This file is part of the evoCore framework - {@link http://evocore.net/}
 * See also {@link http://sourceforge.net/projects/evocms/}.
 *
 * @copyright (c)2003-2009 by Francois PLANQUE - {@link http://fplanque.net/}
 *
 * {@internal License choice
 * - If you have received this file as part of a package, please find the license.txt file in
 *   the same folder or the closest folder above for complete license terms.
 * - If you have received this file individually (e-g: from http://evocms.cvs.sourceforge.net/)
 *   then you must choose one of the following licenses before using the file:
 *   - GNU General Public License 2 (GPL) - http://www.opensource.org/licenses/gpl-license.php
 *   - Mozilla Public License 1.1 (MPL) - http://www.opensource.org/licenses/mozilla1.1.php
 * }}
 *
 * {@internal Open Source relicensing agreement:
 * }}
 *
 * @package admin
 *
 * @version $Id$
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

/**
 * View funcs
 */
require_once dirname(__FILE__).'/_stats_view.funcs.php';


global $blog, $admin_url, $rsc_url;

?>
<h2><?php echo T_('Direct browser hits') ?></h2>
<p class="notes"><?php echo T_('These are browser hits from people who came to this blog system by direct access (either by typing the URL directly, or using a bookmark. Invalid (too short) referers are also listed here.)') ?></p>
<?php
// Create result set:
$Results = & new Results( "
	SELECT SQL_NO_CACHE hit_ID, hit_datetime, hit_blog_ID, hit_uri, hit_remote_addr, blog_shortname
		FROM T_hitlog INNER JOIN T_useragents ON hit_agnt_ID = agnt_ID
				 LEFT JOIN T_blogs ON hit_blog_ID = blog_ID
	 WHERE hit_referer_type = 'direct'
		 AND agnt_type = 'browser'"
		.( empty($blog) ? '' : "AND hit_blog_ID = $blog "), 'lstref_', 'D' );

$Results->title = T_('Direct browser hits');

// datetime:
$Results->cols[] = array(
		'th' => T_('Date Time'),
		'order' => 'hit_ID', // This field is index, much faster than actually sorting on the datetime!
		'td_class' => 'timestamp',
		'td' => '%mysql2localedatetime_spans( \'$hit_datetime$\' )%',
	);

// Referer:
if( $current_User->check_perm( 'stats', 'edit' ) )
{
	$Results->cols[] = array(
			'th' => /* TRANS: Abbrev. for Delete (stats) */ T_('Del'),
			'td_class' => 'center',
			'td' => ' <a href="%regenerate_url( \'action\', \'action=delete&amp;hit_ID=$hit_ID$\')%" title="'
						 .T_('Delete this hit!').'">'.get_icon('delete').'</a>',
		);
}

// Target Blog:
if( empty($blog) )
{
	$Results->cols[] = array(
			'th' => T_('Target Blog'),
			'order' => 'hit_blog_ID',
			'td' => '$blog_shortname$',
		);
}

// Requested URI (linked to blog's baseurlroot+URI):
$Results->cols[] = array(
		'th' => T_('Requested URI'),
		'order' => 'hit_uri',
		'td' => '%stats_format_req_URI( #hit_blog_ID#, #hit_uri# )%',
	);

// Remote address (IP):
$Results->cols[] = array(
		'th' => T_('Remote IP'),
		'order' => 'hit_remote_addr',
		'td' => '% $GLOBALS[\'Plugins\']->get_trigger_event( \'FilterIpAddress\', $tmp_params = array(\'format\'=>\'htmlbody\', \'data\'=>\'$hit_remote_addr$\') ) %',
	);

// Display results:
$Results->display();

/*
 * $Log$
 * Revision 1.6  2009/09/13 21:26:50  blueyed
 * SQL_NO_CACHE for SELECT queries using T_hitlog
 *
 * Revision 1.5  2009/03/08 23:57:45  fplanque
 * 2009
 *
 * Revision 1.4  2008/02/19 11:11:18  fplanque
 * no message
 *
 * Revision 1.3  2008/02/14 02:19:52  fplanque
 * cleaned up stats
 *
 * Revision 1.2  2008/01/21 09:35:33  fplanque
 * (c) 2008
 *
 * Revision 1.1  2007/06/25 11:01:02  fplanque
 * MODULES (refactored MVC)
 *
 * Revision 1.5  2007/04/26 00:11:12  fplanque
 * (c) 2007
 *
 * Revision 1.4  2007/03/20 09:53:26  fplanque
 * Letting boggers view their own stats.
 * + Letthing admins view the aggregate by default.
 *
 * Revision 1.3  2006/11/24 18:27:26  blueyed
 * Fixed link to b2evo CVS browsing interface in file docblocks
 */
?>