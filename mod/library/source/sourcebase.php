<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Base class for library source plugins.
 *
 * @package   mod_library
 * @copyright 2019 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/repository/lib.php');

/**
 * Base class for library source plugins.
 *
 * Doesn't do anything on it's own -- it needs to be extended.
 * This class displays library sources.  Because it is called from
 * within /mod/library/source.php you can assume that the page header
 * and footer are taken care of.
 *
 * This file can refer to itself as source.php to pass variables
 * to itself - all these will also be globally available.  You must
 * pass "id=$cm->id" or q=$library->id", and "mode=sourcename".
 *
 * @copyright 2019 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class library_source_base {
    /** @var string the source name. */
    protected $source;
    /** @var string the repository name. */
    protected $reponame;
    /** @var string the path name. */
    protected $pathname;
    /** @var string item search pattern. */
    protected $searchpattern;
    /** @var int the display mode. */
    protected $displaymode;
    /** @var int the display mode. */
    protected $display;
    /** @var array the display options. */
    protected $displayoptions;
    /** @var bool use visible instances only. */
    protected $onlyvisible = true;
    
    /** @var class the repository object. */
    public $repository = null;
    

    /**
     * Create an instance of this source for a particular library.
     * @param stdClass $library record from the database.
     * @param array $parameters pattern substitution array
     */
    public function __construct($library, $parameters = null) {
        $this->source = $library->source;
        $this->reponame = $library->reponame;
        $this->pathname = $library->pathname;
        
        $this->searchpattern = $library->searchpattern;
        if($parameters) {
            $this->get_processed_searchpattern($parameters);
        }
        
        $this->displaymode = $library->displaymode;    
        
        $this->display = $library->display;
        $this->displayoptions = empty($library->displayoptions) ? array() : unserialize($library->displayoptions);
        
        if($this->use_repository()) {
            $this->set_repository();
        }
    }

    /**
     * Set the use of repository plugin, override to change.
     */
    public function use_repository() {
        return true;
    }
    
    /**
     * Check if repository allow file upload from moodle to remote. Override to change.
     */
    public function allow_uploads() {
        return false;
    }

    /**
     * Check if repository allow remote file deletion from moodle. Override to change.
     */
    public function allow_deletes() {
        return false;
    }
    
   
    /**
     * Gets the source repository, either file or directory.
     */
    public function set_repository() {
        global $DB;
        $this->repository = null;

        $this->onlyvisible = get_config('librarysource_'.$this->source, 'allowhidden') ? false : true; 
        
        $instances = repository::get_instances(array('onlyvisible'=>$this->onlyvisible, 'type'=>$this->source));
        foreach($instances as $repository) {
            if($repository->get_name() == $this->reponame) {
                $this->repository = $repository;
                return true;
            }
        }

        return false;
    }    
    
    
    /**
     * Localize searchpattern with module instance data.
     * @param $parameters associative array with param names and values
     */
    public function get_processed_searchpattern($parameters) {
        if($this->searchpattern &&  $parameters) {
            $this->searchpattern = str_replace(array_keys($parameters),array_values($parameters), $this->searchpattern);
        }
        if($this->pathname &&  $parameters) {
            $this->pathname = str_replace(array_keys($parameters),array_values($parameters), $this->pathname);
        }
        
        return $this->searchpattern;
    }
    
    
    /**
     * Get a file by searching repository for matching pattern .
     * @param $search record from the database.
     */
    public function search_files($search) {
        if(!$this->repository) {
            return array();
        }
        $files = $this->repository->search($search)['list'];
        return $files;
    }

    /**
     * Get a list of files within a folder .
     * @param $pathname folder to list.
     */
    public function list_files($pathname) {
        if(!$this->repository) {
            return array();
        }
        return $this->repository->get_listing($pathname)['list'];
    }
    
    
    /**
     * Get a file by searching repository for matching pattern .
     */
    public function get_source_files() {
    
        $search = $this->pathname ? $this->pathname.'/'.$this->searchpattern : $this->searchpattern;
    
        if($this->displaymode == LIBRARY_DISPLAYMODE_FILE) {
            if($files = $this->search_files($search)) {
                $files = array_slice($files, 0, 1);
            }
        } else {
            if($this->searchpattern) { 
                $files = $this->search_files($search);
            } else { 
                $files = $this->list_files($this->pathname);
            }
        }
    
        return $files;
    }
    
    
    /**
     * Get an existing moodle file by reference .
     * @param $source record from the database.
     * @param $array file_record 
     */
    public function get_moodle_file($reference, $file_record = null) {
        $packedref = $reference;
        if(!base64_decode($reference, true)) {
            $packedref = file_storage::pack_reference($file_record); 
        } 

        return $this->repository->get_moodle_file($packedref);
    }
    
    /**
     * Override this function to get the source.
     */
    public function moodle_file_from_source($externalfile, $file_record, $fs) {
    
        if(!isset($externalfile['source'])) {
            //this is a directory or something else, skip
            return false;
        }
    
        $source = $externalfile['source'];
        $filename = basename($source);
        $file_record['filename'] = $filename;
    
        $reference = $this->repository->get_file_reference($source);
        
        if(!$file = $this->get_moodle_file($reference, $file_record)) {
            $file = $fs->create_file_from_reference($file_record, $this->repository->id, $reference);
        } else {
            $this->repository->sync_reference($file);
        }
    
        return $file;
    }
    
    
    /**local_
     * Override this function to get the source.
     */
    public function get_source_content($contextid, $itemid) {
    
        $target = null;

        if(!$files = $this->get_source_files()) {
            return $target;
        }
        
        $file_record = array(
            'contextid' => $contextid, 'component' => 'mod_library', 'filearea' => 'content',
            'sortorder' => 0, 'itemid' => $itemid, 'filepath' => '/',
        );

        // get file storage
        $fs = get_file_storage();
        $current = $fs->get_area_files($contextid, 'mod_library', 'content', $itemid, 'filename', false); 
        $new = array();
        
        foreach($files as $externalfile) {
            if($file = $this->moodle_file_from_source($externalfile, $file_record, $fs)) {
                $new[$file->get_pathnamehash()] = $file; 
            }
        }
        
        if($new) {
            $old = array_diff_key($current, $new);
            foreach($old as $dfile) {
                //$dfile->delete();
            }
            $target = reset($new);
        }

        return $target;
    }

}


trait filesystem_manage_files {

    /**
     * Check if repository allow file upload from moodle to remote. Override to change.
     */
    public function allow_uploads() {
        return true;
    }

    /**
     * Check if repository allow remote file deletion from moodle. Override to change.
     */
    public function allow_deletes() {
        return true;
    }

    /**
     * Get a list of all subdirectories, recursively, within the given path
     * @param string $dir path to scan
     */
    public function get_subdirs($dir) {
        $subDir = array();
        $directories = array_filter(glob($dir, GLOB_MARK|GLOB_ONLYDIR), 'is_dir');
        $subDir = array_merge($subDir, $directories);
        foreach ($directories as $directory) $subDir = array_merge($subDir, $this->get_subdirs($directory.'*'));
        return $subDir;
    }

    
    /**
     * Get a list of all subdirectories, recursively, within the given path
     * @param string $dir path to scan
     */
    public function get_numbered_filename($path, $fname, $extension) {
        $i = 1;
        while(file_exists($path.$fname."($i).".$extension)) {
            $i++;
        }
    
        return $path.$fname."($i).".$extension;
    }
    
    
    /**
     * Get a list of files within a folder .
     * @param array $draftfiles array of files as returned from get_area_files
     * @param string $insertpath root path where to insert files in local filesystem
     * @param string $updatepode how to behave if file already exists
     */
    public function save_uploaded_files($draftfiles, $insertpath, $updatemode) {
    
        $rootpath = rtrim($insertpath, '/');
        
        $count = 0;
        
        foreach($draftfiles as $file) {
            if($file->is_directory()) {
                $file->delete();
                continue;
            }
            $filepath = $file->get_filepath();
            check_dir_exists($rootpath.$filepath);
            $filename = $file->get_filename();
            $fname = pathinfo($filename, PATHINFO_FILENAME);
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $path = $rootpath.$filepath.$filename;
            $copyfile = true;
            if(file_exists($path)) {
                if($updatemode == LIBRARY_FILEUPDATE_REOLD) {
                    //rename old file to new filename and add new
                    $newname = $this->get_numbered_filename($rootpath.$filepath, $fname, $extension);
                    rename($path, $newname);

                } elseif($updatemode == LIBRARY_FILEUPDATE_RENEW) {
                    //rename new file to new filename, keep existing
                    $path = $this->get_numbered_filename($rootpath.$filepath, $fname, $extension);

                } elseif ($updatemode == LIBRARY_FILEUPDATE_NO) {
                    $copyfile = false;
                }
            }
            
            if($copyfile) {
                $file->copy_content_to($path);
                $count++;
            }
            // remove draftfile
            $file->delete();
        }
        return $count;
    }

    /**
     * Deletes files by reference
     * @param array $draftfiles array of files as returned from get_area_files
     */
    public function delete_selected_files($draftfiles) {
        $count = 0;
            foreach($draftfiles as $file) {
                if($file->is_directory()) {
                    $file->delete();
                    continue;
                }
                $path = $this->repository->get_rootpath().ltrim($file->get_reference(), '/');
                if(file_exists($path)) {
                    unlink($path);
                    $count++;
                }
                $file->delete();
            }
        return $count;
    }
    
    /**
     * Gets the absolute path of a library folder
     */
    public function get_absolute_path() {
        $path = rtrim($this->repository->get_rootpath().$this->pathname, '/'); 
        return $path.'/';
    }
    
    /**
     * Returns a list of all subdirectories in the repository
     */
    public function get_folders() {
        $root = rtrim($this->repository->get_rootpath(), '/');
        $folders = array();
        foreach($this->get_subdirs($this->repository->get_rootpath()) as $dir) {
            $folders[$dir] = str_replace($root, '' , $dir);
        }
        ksort($folders);
        return $folders;
    }
    
    public function rglob($pattern='*', $path='', $flags = 0) {
        $paths=glob($path.'*', GLOB_MARK|GLOB_ONLYDIR|GLOB_NOSORT);
        $files=glob($path.$pattern, $flags);
        foreach ($paths as $path) {
        $files=array_merge($files,rglob($pattern, $path, $flags));
        }
        return $files;
    }    
    
}

