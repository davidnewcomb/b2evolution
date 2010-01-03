<?php
/**
 * This file implements the controller for item types management.
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
 * The Evo Factory grants Francois PLANQUE the right to license
 * The Evo Factory's contributions to this file and the b2evolution project
 * under any OSI approved OSS license (http://www.opensource.org/licenses/).
 * }}
 *
 * @package admin
 *
 * {@internal Below is a list of authors who have contributed to design/coding of this file: }}
 * @author efy-sergey: Evo Factory / Sergey.
 * @author fplanque: Francois PLANQUE.
 *
 * @version $Id$
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

// Load Itemtype class:
load_class( 'items/model/_itemtype.class.php', 'ItemType' );

/**
 * @var AdminUI
 */
global $AdminUI;

/**
 * @var User
 */
global $current_User;

global $dispatcher;

// get reserved ids
global $reserved_ids;
$reserved_ids = ItemType::get_reserved_ids();

// Check minimum permission:
$current_User->check_perm( 'options', 'view', true );

$tab = param( 'tab', 'string', 'settings', true );

$tab3 = param( 'tab3', 'string', 'types', true );

/**
 * We need make this call to build menu for all modules
 */
$AdminUI->set_path( 'items' );

/*
 * Add sub menu entries:
 * We do this here instead of _header because we need to include all filter params into regenerate_url()
 */
attach_browse_tabs();

$AdminUI->set_path( 'items', $tab, $tab3 );

// Get action parameter from request:
param_action();

if( param( 'ptyp_ID', 'integer', '', true) )
{// Load itemtype from cache:
	$ItemtypeCache = & get_ItemTypeCache();
	if( ($edited_Itemtype = & $ItemtypeCache->get_by_ID( $ptyp_ID, false )) === false )
	{	// We could not find the item type to edit:
		unset( $edited_Itemtype );
		forget_param( 'ptyp_ID' );
		$Messages->add( sprintf( T_('Requested &laquo;%s&raquo; object does not exist any longer.'), T_('Itemtype') ), 'error' );
		$action = 'nil';
	}
}

switch( $action )
{

	case 'new':
		// Check permission:
		$current_User->check_perm( 'options', 'edit', true );

		if( ! isset($edited_Itemtype) )
		{	// We don't have a model to use, start with blank object:
			$edited_Itemtype = new ItemType();
		}
		else
		{	// Duplicate object in order no to mess with the cache:
			$edited_Itemtype = duplicate( $edited_Itemtype ); // PHP4/5 abstraction
			$edited_Itemtype->ID = 0;
		}
		break;

	case 'edit':
		// Check permission:
		$current_User->check_perm( 'options', 'edit', true );

		// Make sure we got an ptyp_ID:
		param( 'ptyp_ID', 'integer', true );
 		break;

 	case 'create': // Record new Itemtype
	case 'create_new': // Record Itemtype and create new
	case 'create_copy': // Record Itemtype and create similar
		// Insert new item type...:
		$edited_Itemtype = & new ItemType();

		// Check permission:
		$current_User->check_perm( 'options', 'edit', true );

		// load data from request
		if( $edited_Itemtype->load_from_Request() )
		{	// We could load data from form without errors:

			if( ($edited_Itemtype->ID > $reserved_ids[0]) && ($edited_Itemtype->ID < $reserved_ids[1]) )
			{ // is reserved item type
				param_error( 'ptyp_ID',
					sprintf( T_('Item types with ID from '.$reserved_ids[0].' to '.$reserved_ids[1].' are reserved. Please use another ID' ) ) );
			}
			else
			{ // ID is good

				// While inserting into DB, ID property of Userfield object will be set to autogenerated ID
				// So far as we set ID manualy, we need to preserve this value
				// When assignment of wrong value will be fixed, we can skip this
				$entered_itemtype_id = $edited_Itemtype->ID;

				// Insert in DB:
				$DB->begin();
				// because of manual assigning ID,
				// member function ItemType::dbexists() is overloaded for proper functionality
				$q = $edited_Itemtype->dbexists();
				if($q)
				{	// We have a duplicate entry:

					param_error( 'ptyp_ID',
						sprintf( T_('This item type already exists. Do you want to <a %s>edit the existing item type</a>?'),
							'href="?ctrl=itemtypes&amp;tab='.$tab.'&amp;tab3='.$tab3.'&amp;action=edit&amp;ptyp_ID='.$q.'"' ) );
				}
				else
				{
					$edited_Itemtype->dbinsert();
					$Messages->add( T_('New item type created.'), 'success' );
				}
				$DB->commit();

				if( empty($q) )
				{	// What next?
					switch( $action )
					{
						case 'create_copy':
							// Redirect so that a reload doesn't write to the DB twice:
							header_redirect( '?ctrl=itemtypes&tab='.$tab.'&tab3='.$tab3.'&action=new&ptyp_ID='.$entered_itemtype_id, 303 ); // Will EXIT
							// We have EXITed already at this point!!
							break;
						case 'create_new':
							// Redirect so that a reload doesn't write to the DB twice:
							header_redirect( '?ctrl=itemtypes&tab='.$tab.'&tab3='.$tab3.'&action=new', 303 ); // Will EXIT
							// We have EXITed already at this point!!
							break;
						case 'create':
							// Redirect so that a reload doesn't write to the DB twice:
							header_redirect( '?ctrl=itemtypes&tab='.$tab.'&tab3='.$tab3.'', 303 ); // Will EXIT
							// We have EXITed already at this point!!
							break;
					}
				}
			}
		}
		break;

	case 'update':
		// Edit item type form...:

		// Check permission:
		$current_User->check_perm( 'options', 'edit', true );

		// Make sure we got an ptyp_ID:
		param( 'ptyp_ID', 'integer', true );

		// load data from request
		if( $edited_Itemtype->load_from_Request() )
		{	// We could load data from form without errors:

			if( ($edited_Itemtype->ID > $reserved_ids[0]) && ($edited_Itemtype->ID < $reserved_ids[1]) )
			{ // is reserved item type
				param_error( 'ptyp_ID',
					sprintf( T_('Item types with ID from '.$reserved_ids[0].' to '.$reserved_ids[1].' are reserved. You can not edit this item type' ) ) );
			}
			else
			{ // ID is good
				// Update in DB:
				$DB->begin();

				$edited_Itemtype->dbupdate();
				$Messages->add( T_('Item type updated.'), 'success' );

				$DB->commit();

				header_redirect( '?ctrl=itemtypes&amp;tab='.$tab.'&amp;tab3='.$tab3.'', 303 ); // Will EXIT
				// We have EXITed already at this point!!
			}
		}
		break;

	case 'delete':
		// Delete item type:

		// Check permission:
		$current_User->check_perm( 'options', 'edit', true );

		// Make sure we got an ptyp_ID:
		param( 'ptyp_ID', 'integer', true );

		if( ($edited_Itemtype->ID > $reserved_ids[0]) && ($edited_Itemtype->ID < $reserved_ids[1]) )
		{ // is reserved item type
			param_error( 'ptyp_ID',
				sprintf( T_('Item types with ID from '.$reserved_ids[0].' to '.$reserved_ids[1].' are reserved. You can not delete this item type' ) ) );
		}
		else
		{ // ID is good
			if( param( 'confirm', 'integer', 0 ) )
			{ // confirmed, Delete from DB:
				$msg = sprintf( T_('Item type &laquo;%s&raquo; deleted.'), $edited_Itemtype->dget('name') );
				$edited_Itemtype->dbdelete( true );
				unset( $edited_Itemtype );
				forget_param( 'ptyp_ID' );
				$Messages->add( $msg, 'success' );
				// Redirect so that a reload doesn't write to the DB twice:
				header_redirect( '?ctrl=itemtypes&amp;tab='.$tab.'&amp;tab3='.$tab3.'', 303 ); // Will EXIT
				// We have EXITed already at this point!!
			}
			else
			{	// not confirmed, Check for restrictions:
				if( ! $edited_Itemtype->check_delete( sprintf( T_('Cannot delete item type &laquo;%s&raquo;'), $edited_Itemtype->dget('name') ) ) )
				{	// There are restrictions:
					$action = 'view';
				}
			}
		}
		break;

}

$AdminUI->breadcrumbpath_init();
$AdminUI->breadcrumbpath_add( T_('Contents'), '?ctrl=items&amp;blog=$blog$&amp;tab=full&amp;filter=restore' );
$AdminUI->breadcrumbpath_add( T_('Settings'), '?ctrl=itemtypes&amp;blog=$blog$&amp;tab=settings' );
$AdminUI->breadcrumbpath_add( T_('Post types'), '?ctrl=itemtypes&amp;blog=$blog$&amp;tab=settings&amp;tab3=types' );

// Display <html><head>...</head> section! (Note: should be done early if actions do not redirect)
$AdminUI->disp_html_head();

// Display title, menu, messages, etc. (Note: messages MUST be displayed AFTER the actions)
$AdminUI->disp_body_top();

$AdminUI->disp_payload_begin();

/**
 * Display payload:
 */
switch( $action )
{
	case 'nil':
		// Do nothing
		break;


	case 'delete':
		// We need to ask for confirmation:
		$edited_Itemtype->confirm_delete(
				sprintf( T_('Delete item type &laquo;%s&raquo;?'),  $edited_Itemtype->dget('name') ),
				'itemtype', $action, get_memorized( 'action' ) );
		/* no break */
	case 'new':
	case 'create':
	case 'create_new':
	case 'create_copy':
	case 'edit':
	case 'update':	// we return in this state after a validation error
		$AdminUI->disp_view( 'items/views/_itemtype.form.php' );
		break;


	default:
		// No specific request, list all item types:
		// Cleanup context:
		forget_param( 'ptyp_ID' );
		// Display item types list:
		$AdminUI->disp_view( 'items/views/_itemtypes.view.php' );
		break;

}

$AdminUI->disp_payload_end();

// Display body bottom, debug info and close </html>:
$AdminUI->disp_global_footer();

/*
 * $Log$
 * Revision 1.12  2010/01/03 12:03:18  fplanque
 * More crumbs...
 *
 * Revision 1.11  2009/12/12 01:13:08  fplanque
 * A little progress on breadcrumbs on menu structures alltogether...
 *
 * Revision 1.10  2009/12/06 22:55:16  fplanque
 * Started breadcrumbs feature in admin.
 * Work in progress. Help welcome ;)
 * Also move file settings to Files tab and made FM always enabled
 *
 * Revision 1.9  2009/09/29 18:44:00  fplanque
 * doc
 *
 * Revision 1.8  2009/09/26 12:00:42  tblue246
 * Minor/coding style
 *
 * Revision 1.7  2009/09/25 11:36:44  efy-sergey
 * Replaced "simple list" manager for Post types. Also allow to edit ID for Item types
 *
 * Revision 1.6  2009/09/24 13:50:32  efy-sergey
 * Moved the Global Settings>Post types & Post statuses tabs to "Posts / Comments > Settings > Post types & Post statuses"
 *
 * Revision 1.5  2009/03/08 23:57:43  fplanque
 * 2009
 *
 * Revision 1.4  2009/01/23 22:08:12  tblue246
 * - Filter reserved post types from dropdown box on the post form (expert tab).
 * - Indent/doc fixes
 * - Do not check whether a post title is required when only e. g. switching tabs.
 *
 * Revision 1.3  2009/01/21 18:23:26  fplanque
 * Featured posts and Intro posts
 *
 * Revision 1.2  2008/01/21 09:35:31  fplanque
 * (c) 2008
 *
 * Revision 1.1  2007/06/25 11:00:23  fplanque
 * MODULES (refactored MVC)
 *
 * Revision 1.11  2007/05/14 02:43:04  fplanque
 * Started renaming tables. There probably won't be a better time than 2.0.
 *
 * Revision 1.10  2007/05/13 20:44:52  fplanque
 * more pages support
 *
 * Revision 1.9  2007/04/26 00:11:12  fplanque
 * (c) 2007
 *
 * Revision 1.8  2007/03/26 12:59:18  fplanque
 * basic pages support
 *
 * Revision 1.7  2007/03/26 09:34:16  fplanque
 * removed deprecated list editor
 *
 * Revision 1.6  2006/11/26 01:42:09  fplanque
 * doc
 */
?>