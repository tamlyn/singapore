<?php 

/**
 * IO class.
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 * @version $Id: io_fallback.class.php,v 1.1 2004/02/29 04:37:47 tamlyn Exp $
 */

/**
 * Static (but instantiable) class used to read and write data to and from CSV files.
 * @see sgIO_iifn
 * @package singapore
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 * @version 0.9.6
 */
class sgIO_fallback
{
  
    function getGallery (&$gal, $galleryPath, $getImages = true)
    {
        // gallery directory exists
        if (file_exists($galleryPath) && is_readable($galleryPath) && is_dir($galleryPath)) {
            $bits = explode('/', $gal->id);
            $temp = strtr($bits[count($bits)-1], "_", " ");
            if (strpos($temp, " - "))
                list($gal->artist,$gal->name) = explode(" - ", $temp);
            elseif($temp == ".")
                $gal->name = $this->config->gallery_name;
            else
                $gal->name = $temp;

            // set gallery thumbnail to first image in gallery (if any)
            $dir = Singapore::getListing($galleryPath.'/', 'images');
            if (isset($dir->files[0])) $gal->filename = $dir->files[0];
      
            // also get all images
            if ($getImages) {
                for ($i=0; $i<count($dir->files); $i++) {
                    $gal->images[$i] = new sgImage();
                    $gal->images[$i]->filename = $dir->files[$i];
                    // trim off file extension and replace underscores with spaces
                    $temp = strtr(substr($gal->images[$i]->filename, 0, strrpos($gal->images[$i]->filename,".")-strlen($gal->images[$i]->filename)), "_", " ");
                    // split string in two on " - " delimiter
                    if (strpos($temp, " - ")) list($gal->images[$i]->artist,$gal->images[$i]->name) = explode(" - ", $temp);
                    else $gal->images[$i]->name = $temp;
      
                    // get image size and type
                    list(
                        $gal->images[$i]->width, 
                        $gal->images[$i]->height, 
                        $gal->images[$i]->type
                    ) = GetImageSize($galleryPath.'/'.$gal->images[$i]->filename);
                }
            }
        } else {
            // selected gallery does not exist
            $gal->name = sprintf("Gallery not found '%s'", $gal->id);
            return false;
        }
        return true;
    }

    function getSubGalleries ($parent, $galleryPath)
    {
        $subGalleries = array();
        $dir = Singapore::getListing($galleryPath.$parent.'/', 'dirs');
        foreach ($dir->dirs as $gallery) {
            $subGalleries[] = $parent.'/'.$gallery;
        }
        return $subGalleries;
    }

    function &getUsers ()
    {
        // Init
        $users = array();
        // Set default admin
        $tmp->username     = 'admin';
        $tmp->userpass     = md5('password');
        $tmp->permissions  = 777;
        $tmp->fullname     = "Administrator";
        $tmp->description  = "Default administrator account";
        $tmp->stats        = '';
        $users[] = &$tmp;
        // Return
        return $users;
    }

}

?>
