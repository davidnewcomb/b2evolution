<?php
/**
 * This file implements the Filelist class.
 *
 * This file is part of the b2evolution/evocms project - {@link http://b2evolution.net/}.
 * See also {@link http://sourceforge.net/projects/evocms/}.
 *
 * @copyright (c)2003-2004 by Francois PLANQUE - {@link http://fplanque.net/}.
 * Parts of this file are copyright (c)2004 by Daniel HAHLER - {@link http://thequod.de/contact}.
 *
 * @license http://b2evolution.net/about/license.html GNU General Public License (GPL)
 * {@internal
 * b2evolution is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * b2evolution is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with b2evolution; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * }}
 *
 * {@internal
 * Daniel HAHLER grants Fran�ois PLANQUE the right to license
 * Daniel HAHLER's contributions to this file and the b2evolution project
 * under any OSI approved OSS license (http://www.opensource.org/licenses/).
 * }}
 *
 * @package evocore
 *
 * {@internal Below is a list of authors who have contributed to design/coding of this file: }}
 * @author blueyed: Daniel HAHLER.
 *
 * @version $Id$
 * @todo: method add() to allow specific file (outside path)
 *
 */
if( !defined('DB_USER') ) die( 'Please, do not access this page directly.' );


/**
 * Includes
 */
require_once dirname(__FILE__).'/_file.class.php';


/**
 *
 */
class Filelist
{
	var $listpath = '';

	var $filterString = NULL;
	var $filterIsRegexp = NULL;


	/* -- PRIVATE -- */

	/**
	 * the list of Files
	 * @var array
	 */
	var $entries = array();

	/**
	 * Number of directories
	 */
	var $count_dirs;

	/**
	 * Number of files
	 */
	var $count_files;

	/**
	 * Number of bytes
	 */
	var $count_bytes;

	/**
	 * the current index of the directory items (looping)
	 * @var integer
	 * @access protected
	 */
	var $current_file_idx = -1;


	/**
	 * default order
	 * @var string
	 * @access protected
	 */
	var $order = NULL;
	/**
	 * are we sorting ascending (or descending). default is asc for 'name', desc for the rest
	 * @var mixed
	 * @access protected
	 */
	var $orderasc = NULL;


	/**
	 * User preference: recursive size of dirs?
	 *
	 * The load() method uses this.
	 *
	 * @var boolean
	 */
	var $recursivedirsize = false;


	/**
	 * to be extended by Filemanager class
	 * @var Log
	 */
	var $Messages;


	/**
	 * Constructor
	 *
	 * @param string the path for the files
	 */
	function Filelist( $path, $filterString = NULL, $filterIsRegexp = NULL, $showhidden = false )
	{
		$this->listpath = trailing_slash( $path );
		$this->showhidden = (bool)$showhidden;
		if( empty($path) )
		{
			$this->Messages->add( 'No valid path provided.', 'fl_error' );
			$this->listpath = false;
		}
	}


	/**
	 * loads the filelist entries
	 *
	 * @param boolean get recursive size for directories?
	 */
	function load()
	{
		if( !$this->listpath )
		{
			return false;
		}

		$this->entries = array();
		$this->count_bytes = $this->count_files = $this->count_dirs = 0;


		if( $this->filterString === NULL || $this->filterIsRegexp )
		{ // use dir() to access the directory
			$dir = @dir( $this->listpath );
		}
		else
		{ // use glob() to access the directory
			$oldcwd = getcwd();
			$dir = @chdir( $this->listpath );
			if( $dir )
			{
				$dir = glob( $this->listpath.$this->filterString, GLOB_BRACE ); // GLOB_BRACE allows {a,b,c} to match a, b or c
			}
			chdir( $oldcwd );
		}

		if( $dir === false )
		{
			$this->Messages->add( sprintf( T_('Cannot open directory [%s]!'), $this->listpath ), 'fl_error' );
			return false;
		}
		else
		{ // read the directory
			while( ( ($this->filterString === NULL || $this->filterIsRegexp) && ($entry = $dir->read()) )
						|| ($this->filterString !== NULL && !$this->filterIsRegexp && ( $entry = each( $dir ) ) && ( $entry = str_replace( $this->listpath, '', $entry[1] ) ) ) )
			{
				if( $entry == '.' || $entry == '..'
						|| ( !$this->showhidden && substr($entry, 0, 1) == '.' )  // hidden files (prefixed with .)
						|| ( $this->filterString !== NULL && $this->filterIsRegexp && !preg_match( '#'.str_replace( '#', '\#', $this->filterString ).'#', $entry ) ) // does not match the regexp filter
					)
				{ // don't use these
					continue;
				}

				$this->addFile( $entry );
			}

			if( $this->filterString === NULL || $this->filterIsRegexp )
			{ // close the handle
				$dir->close();
			}
		}
	}


	/**
	 * Add a file to the list
	 */
	function addFile( $name, $allPaths = false )
	{
		if( basename($name) != $name )
		{ // path attached
			if( !$allPaths && (dirname($name).'/' != $this->listpath) )
			{ // not this list's path
				return false;
			}
			else
			{
				$entry =& new File( basename($name), dirname($name).'/' );
			}
		}
		else
		{
			$entry =& new File( $name, $this->listpath );
		}

		if( $this->recursivedirsize && $entry->isDir( $this->listpath.$name ) )
		{
			$entry->setSize( get_dirsize_recursive( $this->listpath.$name ) );
		}

		if( $entry->isDir() )
		{
			$this->count_dirs++;
		}
		else
		{
			$this->count_files++;
		}
		$this->count_bytes += $entry->getSize();

		$this->entries[] = $entry;

		return $entry;
	}


	/**
	 * Sorts the entries.
	 *
	 * @param string the entries key
	 * @param boolean ascending (true) or descending
	 * @param boolean sort directories at top?
	 */
	function sort( $order = NULL, $orderasc = NULL, $dirsattop = NULL )
	{
		if( !count($this->entries) )
		{
			return false;
		}
		if( $order === NULL )
		{
			$order = $this->order;
		}
		if( $orderasc === NULL )
		{
			$orderasc = $this->orderasc;
		}
		if( $dirsattop === NULL )
		{
			$dirsattop = $this->dirsattop;
		}

		if( $order == 'size' )
		{
			if( $this->recursivedirsize )
			{
				$sortfunction = '$r = ( $a->getSize() - $b->getSize() );';
			}
			else
			{
				$sortfunction = '$r = ($a->isDir() && $b->isDir()) ?
															strcasecmp( $a->getName(), $b->getName() ) :
															( $a->getSize() - $b->getSize() );';
			}
		}
		else
		{
			$sortfunction = '$r = strcasecmp( $a->get'.$order.'(), $b->get'.$order.'() );';
		}

		if( !$orderasc )
		{ // switch order
			$sortfunction .= '$r = -$r;';
		}

		if( $dirsattop )
		{
			$sortfunction .= 'if( $a->isDir() && !$b->isDir() )
													$r = -1;
												elseif( $b->isDir() && !$a->isDir() )
													$r = 1;';
		}
		$sortfunction .= 'return $r;';

		#echo $sortfunction;
		usort( $this->entries, create_function( '$a, $b', $sortfunction ) );

		// Restart the list
		$this->restart();
	}


	/**
	 * Restart the list
	 */
	function restart()
	{
		$this->current_file_idx = -1;
	}


	/**
	 * @return integer 1 for ascending sorting, 0 for descending
	 */
	function isSortingAsc( $type = '' )
	{
		if( empty($type) )
		{
			$type = $this->order;
		}

		if( $this->orderasc == '#' )
		{ // default
			return ( $type == 'name' ) ? 1 : 0;
		}
		else
		{
			return ( $this->orderasc ) ? 1 : 0;
		}
	}


	/**
	 * Is a filter active?
	 * @return boolean
	 */
	function isFiltering()
	{
		return $this->filterString !== NULL;
	}


	/**
	 * Return the current filter
	 *
	 * @param boolean add a note when it's a regexp?
	 * @return string the filter
	 */
	function getFilter( $note = true )
	{
		if( $this->filterString === NULL )
		{
			return T_('No filter');
		}
		else
		{
			$r = $this->filterString;
			if( $note && $this->filterIsRegexp )
			{
				$r .= ' ('.T_('regular expression').')';
			}
			return $r;
		}
	}


	function countDirs()
	{
		return $this->count_dirs;
	}


	function countFiles()
	{
		return $this->count_files;
	}


	function countBytes()
	{
		return $this->count_bytes;
	}


	/**
	 * Get the next entry and increment internal counter.
	 *
	 * @param string can be used to query only 'file's or 'dir's.
	 * @return boolean File object (by reference) on success, false on end of list
	 */
	function &getNextFile( $type = '' )
	{
		$this->current_file_idx++;
		if( !count($this->entries) || $this->current_file_idx >= count( $this->entries ) )
		{
			return false;
		}

		if( $type != '' )
		{
			if( $type == 'dir' && !$this->entries[ $this->current_file_idx ]->isDir() )
			{ // we want a dir
				return $this->get_next( 'dir' );
			}
			elseif( $this->entries[ $this->current_file_idx ]->isDir() )
			{ // we want a file
				return $this->get_next( 'file' );
			}
		}
		else
		{
			return $this->entries[ $this->current_file_idx ];
		}
	}


	/**
	 * loads a specific file as current file and saves current one (can be nested).
	 *
	 * (for restoring see {@link Fileman::restorec()})
	 *
	 * @param string the filename (in cwd)
	 * @return mixed File object (by reference) on success, false on failure.
	 */
	function &getFileByFilename( $filename )
	{
		$this->save_idx[] = $this->current_file_idx;

		if( ($this->current_file_idx = $this->findkey( $filename )) === false )
		{ // file could not be found
			$this->current_file_idx = array_pop( $this->save_idx );
			return false;
		}
		else
		{
			return $this->entries[ $this->current_file_idx ];
		}
	}


	/**
	 * restores the previous current entry (see {@link Fileman::loadc()})
	 * @return boolean true on success, false on failure (if there are no entries to restore on the stack)
	 */
	function restorec()
	{
		if( count($this->save_idx) )
		{
			$this->current_file_idx = array_pop( $this->save_idx );
			if( $this->current_file_idx != -1 )
			{
				$this->current_entry = $this->entries[ $this->current_file_idx ];
			}
			return true;
		}
		else
		{
			return false;
		}
	}


	/**
	 * wrapper to get properties of a specific file.
	 *
	 * @param string the file (in cwd)
	 * @param string what to get
	 * @param mixed optional parameter
	 */
	function cget_file( $file, $what, $param = '', $displayiftrue = '' )
	{
		echo 'obsolete call to cget_file!';
		return false;
		if( $this->loadc( $file ) )
		{
			$r = $this->cget( $what, $param, $displayiftrue );
		}
		else
		{
			return false;
		}

		$this->restorec();
		return $r;
	}


	/**
	 * finds an entry ('name' field) in the entries array
	 *
	 * @access protected
	 * @param string needle
	 * @return integer the key of the entries array
	 */
	function findkey( $find )
	{
		foreach( $this->entries as $key => $File )
		{
			if( $File->getName() == $find )
			{
				return $key;
			}
		}
		return false;
	}


	/**
	 * Unlinks (deletes) a file
	 *
	 * @param File file object
	 * @return boolean true on success, false on failure
	 */
	function unlink( $File )
	{
		foreach( $this->entries as $lkey => $lentry )
		{
			if( $lentry == $File )
			{
				$unlinked = $File->isDir() ?
										@rmdir( $File->getPath(true) ) :
										@unlink( $lentry->getPath(true) );

				if( !$unlinked )
				{
					return false;
				}

				unset( $this->entries[$lkey] );
				return true;
			}
		}
		return false;
	}
}

/*
 * $Log$
 * Revision 1.1  2004/10/13 22:46:32  fplanque
 * renamed [b2]evocore/*
 *
 * Revision 1.12  2004/10/12 10:27:18  fplanque
 * Edited code documentation.
 *
 */
?>