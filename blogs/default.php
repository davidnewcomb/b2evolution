<?php
/**
 * This is the main public interface file!
 *
 * This file is NOT mandatory. You can delete it if you want.
 * You can also replace the contents of this file with contents similar to the contents
 * of a_stub.php, a_noskin.php, multiblogs.php, etc.
 *
 * b2evolution - {@link http://b2evolution.net/}
 * Released under GNU GPL License - {@link http://b2evolution.net/about/license.html}
 * @copyright (c)2003-2007 by Francois PLANQUE - {@link http://fplanque.net/}
 *
 * @package evoskins
 * @subpackage noskin
 */

/**
 * First thing: Do the minimal initializations required for b2evo:
 */
require_once dirname(__FILE__).'/conf/_config.php';

/**
 * Check this: we are requiring _main.inc.php INSTEAD of _blog_main.inc.php because we are not
 * trying to initialize any particular blog
 */
require_once $inc_path.'_main.inc.php';

header( 'Content-type: text/html; charset='.$io_charset );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php locale_lang() ?>" lang="<?php locale_lang() ?>"><!-- InstanceBegin template="/Templates/evo_distrib_2.dwt" codeOutsideHTMLIsLocked="false" -->
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<!-- InstanceBeginEditable name="doctitle" -->
	<title>b2evolution - Default Page</title>
	<!-- InstanceEndEditable -->
	<link href="rsc/css/evo_distrib_2.css" rel="stylesheet" type="text/css" />
	<!-- InstanceBeginEditable name="head" -->
	<base href="<?php echo $baseurl ?>" />
	<!-- InstanceEndEditable -->
	<!-- InstanceParam name="lang" type="text" value="&lt;?php locale_lang() ?&gt;" --> 
</head>

<body>
	<!-- InstanceBeginEditable name="BodyHead" -->
	<?php
	// ---------------------------- TOOLBAR INCLUDED HERE ----------------------------
	require $skins_path.'_toolbar.inc.php';
	// ------------------------------- END OF TOOLBAR --------------------------------
	?>
	<!-- InstanceEndEditable -->

	<div class="wrapper1">
	<div class="wrapper2">
		<span class="version_top"><!-- InstanceBeginEditable name="Version" --><?php echo T_('Default page') ?><!-- InstanceEndEditable --></span>	
	
		<a href="http://b2evolution.net/" target="_blank"><img src="rsc/img/distrib/b2evolution-logo.gif" alt="b2evolution" width="237" height="92" /></a>
		
		<div class="menu_top"><!-- InstanceBeginEditable name="MenuTop" --> 
			<span class="floatright"><a href="<?php echo $baseurl ?>">Home</a> &bull; <a href="<?php echo $admin_url ?>">Admin</a> </span> 
			&nbsp;
		<!-- InstanceEndEditable --></div>
		
		<!-- InstanceBeginEditable name="Main" -->
		<div class="block1">
		<div class="block2">
		<div class="block3">

			<h1><?php echo T_('It Works !') ?></h1>
			
			<?php
				$Messages->disp( '<div class="action_messages">', '</div>' );
				
				echo '<p><strong>'.T_('You have successfully installed b2evolution.').'</strong></p>';
				
				echo '<p>'.T_('You haven\'t set a default blog yet. Thus, you see this default page.').'</p>';
				
				?>
				<p><strong><a href="<?php echo $admin_url ?>?ctrl=settings&amp;tab=general"><?php echo T_( 'Set a default blog' ) ?> &raquo;</a></strong></p>

		</div>
		</div>
		</div>

		<div class="block1">
		<div class="block2">
		<div class="block3">

	<h2><?php echo T_('Blogs on this system') ?></h2>
	<?php
	/**
	 * @var BlogCache
	 */
	$BlogCache = & get_Cache('BlogCache');
	$BlogCache->load_all();
	if( count( $BlogCache->cache ) == 0 )
	{	// There is no blog on this system!
		echo '<p><strong>'.T_('b2evolution is installed and ready but you haven\'t created any blog on this system yet.').'</strong></p>';
	}
	else
	{
	?>

	<ul>
	<?php // --------------------------- BLOG LIST -----------------------------
		for( $l_Blog = & $BlogCache->get_first();
					! is_null( $l_Blog );
					 $l_Blog = & $BlogCache->get_next() )
		{ # by uncommenting the following lines you can hide some blogs
			// if( $curr_blog_ID == 2 ) continue; // Hide blog 2...
			echo '<li><strong>';
			printf( T_('Blog #%d'), $l_Blog->ID );
			echo ': <a href="'.$l_Blog->gen_blogurl().'" title="'.$l_Blog->dget( 'shortdesc', 'htmlattr' ).'">';
			$l_Blog->disp( 'name' );
			echo '</a></strong>';
			echo '</li>';
		}
		// ---------------------------------- END OF BLOG LIST ---------------------------------
		?>
	</ul>

	<?php		echo '<p><a href="'.$admin_url.'?ctrl=collections&amp;action=new">'.T_( 'Add a new blog' ).' &raquo;</a></p>';
		?>
		</div>
		</div>
		</div>

		<div class="block1">
		<div class="block2">
		<div class="block3">
 
 	<h2><?php echo T_('Bonus templates &amp; features') ?></h2>
	<p class="note"><?php echo T_('These templates demonstrate more advanced uses of b2evolution. These do not make use of skins. The only way to change their look and feel is to edit their PHP template.') ?></p>
	<ul>
		<?php
			$first_Blog = & $BlogCache->get_by_ID( 1, false );
			if( !empty( $first_Blog ) )
			{
			?>
				<li><strong><a href="a_stub.php"><?php echo T_('Blog #1 called through a stub file') ?></a></strong> &nbsp; <span class="note">(a_stub.php)</span></li>
				<li><strong><a href="a_noskin.php"><?php echo T_('Blog #1 called through a custom template (not a skin)') ?></a></strong> &nbsp; <span class="note">(a_noskin.php)</span></li>
				<li><strong><a href="multiblogs.php"><?php echo T_('Multiple blogs displayed on the same page') ?></a></strong> &nbsp; <span class="note">(multiblogs.php)</span></li>
				<li><strong><a href="sitemap_a.php"><?php echo T_('Blog #1 XML sitemap (called through a stub)') ?></a></strong> &nbsp; <span class="note">(sitemap_a.php)</span></li>
				<li><strong><a href="sitemap_blogs.php"><?php echo T_('Blog #1 aggregated XML sitemap (called through a stub)') ?></a></strong> &nbsp; <span class="note">(sitemap_blogs.php)</span></li>
			<?php
			}
		?>
		<li><strong><a href="summary.php"><?php echo T_('Summary of latest posts in all blogs') ?></a></strong> &nbsp; <span class="note">(summary.php)</span></li>
		<li><strong><a href="default.php"><?php echo T_('The page you\'re looking at') ?></a></strong> &nbsp; <span class="note">(default.php)</span></li>
		<li><strong><a href="contact.php"><?php echo T_('A standalone admin-contact page for your site') ?></a></strong> &nbsp; <span class="note">(contact.php)</span></li>
	</ul>

	<?php
	}
?>

</div>
</div>
</div>
<!-- InstanceEndEditable -->
	</div>
		
	<div class="body_fade_out">
		
	<div class="menu_bottom"><!-- InstanceBeginEditable name="MenuBottom" -->Powered by <a href="http://b2evolution.net/" target="_blank">b2evolution</a> &bull; <a href="http://manual.b2evolution.net/" target="_blank">Manual</a> &bull; <a href="http://forums.b2evolution.net/" target="_blank">Forums</a>
		<!-- InstanceEndEditable --></div>
	
	<div class="copyright"><!-- InstanceBeginEditable name="CopyrightTail" -->
		<a href="contact.php"><?php echo T_('Contact the admin') ?></a>
		<?php display_list( $credit_links, ' &middot; ', '', ' &middot; ', ' ', ' ' ); ?>
		<!-- InstanceEndEditable --></div>
		
	</div>
	</div>

	<!-- InstanceBeginEditable name="BodyFoot" -->
	<?php debug_info(); // output debug info if requested ?>
	<!-- InstanceEndEditable -->
</body>
<!-- InstanceEnd --></html>