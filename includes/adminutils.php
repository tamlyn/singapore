<?php

 /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *\
 *  adminutils.php - Copyright 2003 Tamlyn Rhodes <tam@zenology.org>   *
 *                                                                     *
 *  This file is part of singapore v0.9.4                              *
 *                                                                     *
 *  singapore is free software; you can redistribute it and/or modify  *
 *  it under the terms of the GNU General Public License as published  *
 *  by the Free Software Foundation; either version 2 of the License,  *
 *  or (at your option) any later version.                             *
 *                                                                     *
 *  singapore is distributed in the hope that it will be useful,       *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty        *
 *  of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.            *
 *  See the GNU General Public License for more details.               *
 *                                                                     *
 *  You should have received a copy of the GNU General Public License  *
 *  along with this; if not, write to the Free Software Foundation,    *
 *  Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA      *
 \* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
  
//
//authentication functions
//

function sgLogin() {
  if(isset($_POST["user"]) && isset($_POST["pass"])) {
	  $users = sgGetUsers();
		for($i=1;$i < count($users);$i++) {
      if($_POST["user"] == $users[$i]->username && md5($_POST["pass"]) == $users[$i]->userpass){
        $_SESSION["user"] = $users[$i];
        $_SESSION["user"]->check = md5($GLOBALS["sgConfig"]->secret_string.$_SERVER["REMOTE_ADDR"]);
			  $_SESSION["user"]->ip = $_SERVER["REMOTE_ADDR"];
        $_SESSION["user"]->loginTime = time();
        return true;
			}
		}
		sgLogout();
	}
	return false;
}

function sgLogout() {
  //unset($GLOBALS['_SESSION']['user']);
  $_SESSION["user"] = null;
	return true;
}

function sgLoginForm() {
  echo "<p>Only authorised users are allowed into the admin section. Please enter your \n";
  echo "admin username and password below.</p>\n";
  echo "<form action=\"$_SERVER[PHP_SELF]\" method=\"post\">\n";
  echo "<input type=\"hidden\" name=\"action\" value=\"login\" />\n";
  echo "<p>\n";
  echo "  Username: <input type=\"text\" name=\"user\" />\n";
  echo "  Password: <input type=\"password\" name=\"pass\" />\n";
  echo "  <input type=\"submit\" class=\"button\" value=\" Go \" />\n";
  echo "</p>\n";
  echo "</form>\n";
} 

function sgEditPass()
{
  echo "<form action=\"$_SERVER[PHP_SELF]\" method=\"post\">\n";
  echo "<input type=\"hidden\" name=\"action\" value=\"savepass\" />\n";
  echo "<input type=\"hidden\" name=\"user\" value=\"{$_SESSION['user']->username}\" />\n";
  echo "<table>\n";
  echo "<tr>\n  <td>Current password:</td>\n  <td><input type=\"password\" name=\"sgOldPass\" size=\"23\" /></td>\n</tr>\n";
  echo "<tr>\n  <td>New password:</td>\n  <td><input type=\"password\" name=\"sgNewPass1\" size=\"23\" /></td>\n</tr>\n";
  echo "<tr>\n  <td>Confirm password:</td>\n  <td><input type=\"password\" name=\"sgNewPass2\" size=\"23\" /></td>\n</tr>\n";
  echo "<tr>\n  <td></td>\n  <td><input type=\"submit\" class=\"button\" value=\"Save Changes\" /></td>\n</tr>\n";
  echo "</table>\n";
  echo "</form>\n";
}

function sgSavePass()
{
  $users = sgGetUsers();
  
  $found = false;
  for($i=1;$i < count($users);$i++)
    if($_POST["user"] == $users[$i]->username) {
      $found = true;
      if(md5($_POST["sgOldPass"]) == $users[$i]->userpass)
        if($_POST["sgNewPass1"]==$_POST["sgNewPass2"])
          if(strlen($_POST["sgNewPass1"]) >= 6 && strlen($_POST["sgNewPass1"]) <= 16) { 
            $users[$i]->userpass = md5($_POST["sgNewPass1"]);
            if(sgPutUsers($users)) echo "<h1>password changed</h1>\n<p><a href=\"admin.php\">Admin options</a>.</p>";
            else echo "<h1>password error</h1>\n<p>There was an error saving the new password. Password not changed. <a href=\"admin.php\">Admin options</a>.</p>";
          } else { echo "<h1>password error</h1>\n<p>New password must be between 6 and 16 characters long.</p>"; sgEditPass(); }
        else { echo "<h1>password error</h1>\n<p>The new passwords you entered do not match.</p>"; sgEditPass(); }
      else { echo "<h1>password error</h1>\n<p>The current password you entered does not match the one in the database.</p>"; sgEditPass(); }
		}
    
  if(!$found) { echo "<h1>password error</h1>\n<p>The username specified was not found in the database.</p>"; sgEditPass(); }
}


//
//gallery handling functions
//

function sgNewGallery() {
  echo "<h1>new gallery</h1>\n";
  
  echo "<form action=\"$_SERVER[PHP_SELF]\" method=\"post\">\n";
  echo "<input type=\"hidden\" name=\"action\" value=\"addgallery\" />\n";
  
  echo "<table class=\"formTable\">\n";
  echo "<tr>\n  <td>Identifier:</td>\n  <td><input type=\"text\" name=\"gallery\" value=\"".md5(uniqid(rand(),1))."\" size=\"40\" /></td>\n</tr>\n";
  echo "<tr>\n  <td></td>\n  <td><input type=\"submit\" class=\"button\" value=\"Create\" /></td>\n</tr>\n";
  echo "</table>\n";
  
  echo "</form>\n";

}

function sgAddGallery($gallery)
{
  $path = $GLOBALS["sgConfig"]->pathto_galleries.$gallery;
  if(!file_exists($path) && mkdir($path, $GLOBALS["sgConfig"]->directory_mode)) return true;
  return false;
}

function sgEditGallery($gallery)
{ 
  $gal = sgGetGallery($gallery);
  if(!$gal) {
    echo("<h1>gallery '$gallery' not found</h1>\n");
    sgShowIndex("",0);
    return;
  }
  
  echo "<h1>edit gallery</h1>\n\n";
  
  echo "<form action=\"$_SERVER[PHP_SELF]\" method=\"post\">\n";
  echo "<input type=\"hidden\" name=\"action\" value=\"savegallery\" />\n";
  echo "<input type=\"hidden\" name=\"gallery\" value=\"$gal->id\" />\n";
  echo "<input type=\"hidden\" name=\"sgOwner\" value=\"$gal->owner\" />\n";
  echo "<input type=\"hidden\" name=\"sgGroups\" value=\"$gal->groups\" />\n";
  echo "<input type=\"hidden\" name=\"sgPermissions\" value=\"$gal->permissions\" />\n";
  echo "<input type=\"hidden\" name=\"sgCategories\" value=\"$gal->categories\" />\n\n";
  
  echo "<table class=\"formTable\">\n";
  echo "<tr>\n  <td>Gallery thumbnail:</td>\n  <td><div class=\"inputbox sgImageInput\">";
  switch($gal->filename) {
  case "__none__" :
    echo "No<br />thumbnail";
    break;
  case "__random__" :
    echo "Random<br />image";
    break;
  default :
    echo "<img src=\"thumb.php?gallery=$gal->id&amp;image=$gal->filename&amp;size=".$GLOBALS["sgConfig"]->gallery_thumb_size."\" class=\"sgThumbnail\" alt=\"Example image from gallery\" />";
  }
  echo "<br />\n<a href=\"admin.php?action=changethumbnail&amp;gallery=$gal->id\">Change...</a>\n";
  echo "</div></td>\n</tr>\n";
  echo "<tr>\n  <td>Gallery name:</td>\n  <td><input type=\"text\" name=\"sgGalleryName\" value=\"".htmlentities($gal->name)."\" size=\"40\" /></td>\n</tr>\n";
  echo "<tr>\n  <td>Artist name:</td>\n  <td><input type=\"text\" name=\"sgArtistName\" value=\"".htmlentities($gal->artist)."\" size=\"40\" />*</td>\n</tr>\n";
  echo "<tr>\n  <td>Artist email:</td>\n  <td><input type=\"text\" name=\"sgArtistEmail\" value=\"".htmlentities($gal->email)."\" size=\"40\" />*</td>\n</tr>\n";
  echo "<tr>\n  <td>Copyright holder:</td>\n  <td><input type=\"text\" name=\"sgCopyright\" value=\"".htmlentities($gal->copyright)."\" size=\"40\" />*</td>\n</tr>\n";
  echo "<tr>\n  <td>Description:</td>\n  <td><textarea name=\"sgGalleryDesc\" cols=\"70\" rows=\"8\">".htmlentities(str_replace("<br />","\n",$gal->desc))."</textarea></td>\n</tr>\n";
  echo "<tr>\n  <td></td>\n  <td><input type=\"submit\" class=\"button\" value=\"Save Changes\" /></td>\n</tr>\n";
  echo "</table>\n";
  
  echo "<p>Note: fields marked * are stored in the database but are not currently displayed.</p>\n";
  echo "</form>\n";
}

function sgSaveGallery($gallery)
{
  $gal = sgGetGallery($gallery);

  $gal->id = $gallery;
  $img->owner = $_REQUEST["sgOwner"];
  $img->groups = $_REQUEST["sgGroups"];
  $img->permissions = $_REQUEST["sgPermissions"];
  $img->categories = $_REQUEST["sgCategories"];
  $gal->name = stripslashes($_REQUEST["sgGalleryName"]);
  $gal->artist = stripslashes($_REQUEST["sgArtistName"]);
  $gal->email = stripslashes($_REQUEST["sgArtistEmail"]);
  $gal->copyright = stripslashes($_REQUEST["sgCopyright"]);
  $gal->desc = str_replace(array("\n","\r"),array("<br />",""),stripslashes($_REQUEST["sgGalleryDesc"]));
  
  if(sgPutGallery($gal)) echo "<h1>gallery saved</h1>\n";
  else echo "<h1>gallery not saved</h1>\n";
  echo 
  "<ul>\n".
  "  <li><a href=\"index.php?gallery=$gallery\">View gallery</a></li>\n".
  "  <li><a href=\"admin.php?action=newgallery\">New gallery</a></li>\n".
  "</ul>\n";
}

function sgShowGalleryDeleteConfirmation($gallery)
{
  $gal = sgGetGallery($gallery);

  echo "<h1>delete gallery</h1>\n";
  
  echo "<form action=\"$_SERVER[PHP_SELF]\" method=\"post\">\n";
  echo "<input type=\"hidden\" name=\"action\" value=\"deletegallery-confirmed\" />\n";
  echo "<input type=\"hidden\" name=\"gallery\" value=\"$gallery\" />\n";
  echo "<p>Are you sure you want to irretrievably delete gallery <em>$gal->name</em>";
  if(count($gal->img)>0) echo " and all ".count($gal->img)." images contained therein";
  echo "?</p>\n";
  echo "<p><input type=\"submit\" class=\"button\" name=\"confirm\" value=\"OK\">\n";
  echo "<input type=\"submit\" class=\"button\" name=\"confirm\" value=\"Cancel\"></p>";
  echo "</form>\n";
 
}

function sgDeleteGallery($gallery)
{
  //checks that there are no "../" references in the gallery name
  //this ensures that the gallery being deleted really is a 
  //subdirectory of the galleries directory 
  if(strpos($gallery,"../") !== false) return false;
  
  //remove the offending directory and all contained therein
  return rmdir_all($GLOBALS["sgConfig"]->pathto_galleries.$gallery);
}


function sgChangeThumbnail($gallery)
{
  $gal = sgGetGallery($gallery);

  echo "<h1>choose thumbnail</h1>\n";
  
  echo "<form action=\"$_SERVER[PHP_SELF]\" method=\"post\">\n";
  echo "<input type=\"hidden\" name=\"action\" value=\"savethumbnail\" />\n";
  echo "<input type=\"hidden\" name=\"gallery\" value=\"$gallery\" />\n";
  echo "<p>Choose the filename of the image used to represent this gallery.</p>\n";
  
  echo "<p><select name=\"sgThumbName\">\n";
  echo "<option value=\"__none__\">None</option>\n";
  echo "<option value=\"__random__\">Random</option>\n";
  foreach($gal->img as $img) echo "<option value=\"$img->filename\">$img->filename: $img->name by $img->artist</option>\n";
  echo "</select></p>\n";
  echo "<p><input type=\"submit\" class=\"button\" name=\"confirm\" value=\"OK\">\n";
  echo "<input type=\"submit\" class=\"button\" name=\"confirm\" value=\"Cancel\"></p>";
  echo "</form>\n";
 
}

function sgSaveThumbnail($gallery, $thumbnail)
{
  $gal = sgGetGallery($gallery);

  $gal->filename = $thumbnail;
  
  return sgPutGallery($gal);
}


//
//image handling functions
//

function sgNewImage($gallery) {
  echo "<h1>new image</h1>\n\n";
  
  echo "<form action=\"$_SERVER[PHP_SELF]\" enctype=\"multipart/form-data\" method=\"post\">\n";
  echo "<input type=\"hidden\" name=\"gallery\" value=\"$gallery\" />\n";
  echo "<input type=\"hidden\" name=\"action\" value=\"addimage\" />\n\n";
  
  echo "<table class=\"formTable\">\n";
  echo "<tr>\n  <td><input type=\"radio\" class=\"radio\" name=\"sgLocationChoice\" value=\"remote\"> Remote file</td>\n  <td></td>\n<tr>\n";
  echo "<tr>\n  <td>URL of image:</td>\n  <td><input type=\"text\" name=\"sgImageURL\" value=\"http://\" size=\"40\" /></td>\n</tr>\n";
  echo "<tr>\n  <td><input type=\"radio\" class=\"radio\" name=\"sgLocationChoice\" value=\"local\" checked=\"true\"> Local file</td>\n  <td></td>\n<tr>\n";
  echo "<tr>\n  <td>Image file to upload:</td>\n  <td><input type=\"file\" name=\"sgImageFile\" value=\"\" size=\"40\" /></td>\n</tr>\n";
  echo "<tr>\n  <td>Identifier:</td>\n  <td>\n    <input type=\"radio\" class=\"radio\" name=\"sgNameChoice\" value=\"same\" checked=\"true\"> Use filename of uploaded file.<br />\n";
  echo "    <input type=\"radio\" class=\"radio\" name=\"sgNameChoice\" value=\"new\"> Specify different filename:<br />\n";
  echo "    <input type=\"text\" name=\"sgFileName\" value=\"".md5(uniqid(rand(),1)).".jpeg\" size=\"40\" /></td>\n</tr>\n";
  echo "<tr>\n  <td></td>\n  <td><input type=\"submit\" class=\"button\" value=\"Create\" /></td>\n</tr>\n";
  echo "</table>\n";
  
  echo "</form>\n";

}

function sgAddImage($gallery)
{
  $gal = sgGetGallery($gallery);
  if(!$gal) return false;
  
  if($_REQUEST["sgLocationChoice"] == "remote") $image = $_REQUEST["sgImageURL"];
  elseif($_REQUEST["sgLocationChoice"] == "local") {
    //set filename as requested
    if($_REQUEST["sgNameChoice"] == "same") $image = $_FILES["sgImageFile"]["name"];
    else $image = $_REQUEST["sgFileName"];
    
    //make sure file has a .jpeg extension
    if(!preg_match("/(\.jpeg)|(\.jpg)$/i",$image)) $image .= ".jpeg";
    
    $path = $GLOBALS["sgConfig"]->pathto_galleries."$gallery/$image";
    
    if(file_exists($path) || !move_uploaded_file($_FILES["sgImageFile"]["tmp_name"],$path)) 
      return false;
  } else return false;
  
  $i = count($gal->img);
  
  $gal->img[$i]->filename = $image;
  $gal->img[$i]->name = $image;
  
  //set default values
  $gal->img[$i]->thumbnail = "";
  $gal->img[$i]->owner = "__nobody__";
  $gal->img[$i]->groups = "__nogroup__";
  $gal->img[$i]->permissions = 4096;
  $gal->img[$i]->categories = "";
  
  $gal->img[$i]->artist =
  $gal->img[$i]->email = $gal->img[$i]->copyright = $gal->img[$i]->desc =
  $gal->img[$i]->location = $gal->img[$i]->date = $gal->img[$i]->camera =
  $gal->img[$i]->lens = $gal->img[$i]->film = $gal->img[$i]->darkroom =
  $gal->img[$i]->digital = "";

  if(sgPutGallery($gal)) return $image;
  else return false;
}

function sgEditImage($gallery, $image)
{ 
  $img = sgGetImage($gallery, $image);
  if(!$img) {
    echo("<h1>image '$image' not found</h1>\n");
    sgShowThumbnails($gallery,0);
    return;
  }
  
  echo "<h1>edit image</h1>\n\n";
  
  echo "<form action=\"$_SERVER[PHP_SELF]\" method=\"post\">\n";
  echo "<input type=\"hidden\" name=\"action\" value=\"saveimage\" />\n";
  echo "<input type=\"hidden\" name=\"gallery\" value=\"$gallery\" />\n";
  echo "<input type=\"hidden\" name=\"image\" value=\"$image\" />\n";
  echo "<input type=\"hidden\" name=\"sgThumbnail\" value=\"$img->thumbnail\" />\n";
  echo "<input type=\"hidden\" name=\"sgOwner\" value=\"$img->owner\" />\n";
  echo "<input type=\"hidden\" name=\"sgGroups\" value=\"$img->groups\" />\n";
  echo "<input type=\"hidden\" name=\"sgPermissions\" value=\"$img->permissions\" />\n";
  echo "<input type=\"hidden\" name=\"sgCategories\" value=\"$img->categories\" />\n";
  echo "<input type=\"hidden\" name=\"sgPrevImage\" value=\"".(isset($img->prev[0])?$img->prev[0]->filename:"")."\" />\n";
  echo "<input type=\"hidden\" name=\"sgNextImage\" value=\"".(isset($img->next[0])?$img->next[0]->filename:"")."\" />\n";
  echo "<table class=\"formTable\">\n";
  echo "<tr>\n  <td>Image:</td>\n  <td><div class=\"inputbox sgImageInput\"><a href=\"index.php?gallery=$gallery&amp;image=$img->filename\"><img src=\"thumb.php?gallery=$gallery&amp;image=$img->filename&amp;size=".$GLOBALS["sgConfig"]->main_thumb_size."\" class=\"sgThumbnail\" alt=\"Thumbnail of currently selected image\" /></a></div></td>\n</tr>\n";
  echo "<tr>\n  <td>Image name:</td>\n  <td><input type=\"text\" name=\"sgImageName\" value=\"".htmlentities($img->name)."\" size=\"40\" /></td>\n</tr>\n";
  echo "<tr>\n  <td>Artist name:</td>\n  <td><input type=\"text\" name=\"sgArtistName\" value=\"".htmlentities($img->artist)."\" size=\"40\" /></td>\n</tr>\n";
  echo "<tr>\n  <td>Artist email:</td>\n  <td><input type=\"text\" name=\"sgArtistEmail\" value=\"".htmlentities($img->email)."\" size=\"40\" /></td>\n</tr>\n";
  echo "<tr>\n  <td>Location:</td>\n  <td><input type=\"text\" name=\"sgLocation\" value=\"".htmlentities($img->location)."\" size=\"40\" /></td>\n</tr>\n";
  echo "<tr>\n  <td>Date:</td>\n  <td><input type=\"text\" name=\"sgDate\" value=\"".htmlentities($img->date)."\" size=\"40\" /></td>\n</tr>\n";
  echo "<tr>\n  <td>Copyright holder:</td>\n  <td><input type=\"text\" name=\"sgCopyright\" value=\"".htmlentities($img->copyright)."\" size=\"40\" /></td>\n</tr>\n";
  echo "<tr>\n  <td>Description:</td>\n  <td><textarea name=\"sgImageDesc\" cols=\"70\" rows=\"8\">".htmlentities(str_replace("<br />","\n",$img->desc))."</textarea></td>\n</tr>\n";
  echo "<tr>\n  <td>Camera info:</td>\n  <td><input type=\"text\" name=\"sgCamera\" value=\"".htmlentities($img->camera)."\" size=\"40\" /></td>\n</tr>\n";
  echo "<tr>\n  <td>Lens info:</td>\n  <td><input type=\"text\" name=\"sgLens\" value=\"".htmlentities($img->lens)."\" size=\"40\" /></td>\n</tr>\n";
  echo "<tr>\n  <td>Film info:</td>\n  <td><input type=\"text\" name=\"sgFilm\" value=\"".htmlentities($img->film)."\" size=\"40\" /></td>\n</tr>\n";
  echo "<tr>\n  <td>Darkroom manipulation:</td>\n  <td><input type=\"text\" name=\"sgDarkroom\" value=\"".htmlentities($img->darkroom)."\" size=\"40\" /></td>\n</tr>\n";
  echo "<tr>\n  <td>Digital manipulation:</td>\n  <td><input type=\"text\" name=\"sgDigital\" value=\"".htmlentities($img->digital)."\" size=\"40\" /></td>\n</tr>\n";
  echo "<tr>\n  <td></td>\n  <td><input type=\"submit\" class=\"button\" value=\"Save Changes\" /></td>\n</tr>\n";
  echo "</table>\n";
  echo "</form>\n";
}

function sgSaveImage($gallery, $image)
{
  $img->filename = $image;
  $img->thumbnail = $_REQUEST["sgThumbnail"];
  $img->owner = $_REQUEST["sgOwner"];
  $img->groups = $_REQUEST["sgGroups"];
  $img->permissions = $_REQUEST["sgPermissions"];
  $img->categories = $_REQUEST["sgCategories"];
  $img->name = stripslashes($_REQUEST["sgImageName"]);
  $img->artist = stripslashes($_REQUEST["sgArtistName"]);
  $img->email = stripslashes($_REQUEST["sgArtistEmail"]);
  $img->location = stripslashes($_REQUEST["sgLocation"]);
  $img->date = stripslashes($_REQUEST["sgDate"]);
  $img->copyright = stripslashes($_REQUEST["sgCopyright"]);
  $img->desc = str_replace(array("\n","\r"),array("<br />",""),stripslashes($_REQUEST["sgImageDesc"]));
  $img->camera = stripslashes($_REQUEST["sgCamera"]);
  $img->lens = stripslashes($_REQUEST["sgLens"]);
  $img->film = stripslashes($_REQUEST["sgFilm"]);
  $img->darkroom = stripslashes($_REQUEST["sgDarkroom"]);
  $img->digital = stripslashes($_REQUEST["sgDigital"]);
  
  if(sgPutImage($gallery, $img)) echo "<h1>image saved</h1>\n";
  else echo "<h1>image not saved</h1>\n";
  echo 
  "<ul>\n".
  "  <li><a href=\"index.php?gallery=$gallery&amp;image=$image\">View image</a></li>\n".
  "  <li><a href=\"admin.php?action=editimage&amp;gallery=$gallery&amp;image=$_REQUEST[sgNextImage]\">Edit next image</a></li>\n".
  "  <li><a href=\"admin.php?action=editimage&amp;gallery=$gallery&amp;image=$_REQUEST[sgPrevImage]\">Edit previous image</a></li>\n".
  "  <li><a href=\"index.php?gallery=$gallery\">View gallery</a></li>\n".
  "</ul>\n";
  
}

function sgShowImageDeleteConfirmation($gallery, $image)
{
  $img = sgGetImage($gallery, $image);

  echo "<h1>delete image</h1>\n";
  
  echo "<form action=\"$_SERVER[PHP_SELF]\" method=\"post\">\n";
  echo "<input type=\"hidden\" name=\"action\" value=\"deleteimage-confirmed\" />\n";
  echo "<input type=\"hidden\" name=\"gallery\" value=\"$gallery\" />\n";
  echo "<input type=\"hidden\" name=\"image\" value=\"$img->filename\" />\n";
  echo "<input type=\"hidden\" name=\"sgPrevImage\" value=\"".(isset($img->prev[0])?$img->prev[0]->filename:"")."\" />\n";
  echo "<input type=\"hidden\" name=\"sgNextImage\" value=\"".(isset($img->next[0])?$img->next[0]->filename:"")."\" />\n";
  echo "<p>Are you sure you want to irretrievably delete image <em>'$img->name'";
  if(!empty($img->author)) echo " by $img->author";
  echo "</em> from gallery <em>$img->galname</em>?</p>\n";
  echo "<p><input type=\"submit\" class=\"button\" name=\"confirm\" value=\"OK\">\n";
  echo "<input type=\"submit\" class=\"button\" name=\"confirm\" value=\"Cancel\"></p>";
  echo "</form>\n";
 
}

function sgDeleteImage($gallery, $image)
{
  $gal = sgGetGallery($gallery);
  
  for($i=0;$i<count($gal->img);$i++)
    if($gal->img[$i]->filename == $image)
      array_splice($gal->img,$i,1);
  if(file_exists($GLOBALS["sgConfig"]->pathto_galleries."$gallery/$image"))
    return(unlink($GLOBALS["sgConfig"]->pathto_galleries."$gallery/$image") && sgPutGallery($gal));
  else return sgPutGallery($gal);
}


//
//other functions
//

function sgShowImageHits($gallery, $startat = 0)
{
  $gal = sgGetGallery($gallery);
  if(!$gal) {
    echo("<h1>gallery '$gallery' not found</h1>\n");
    sgShowIndex($GLOBALS["sgConfig"]->pathto_galleries);
    return;
  }
  $hits = sgGetHits($gal->id);
  
  $max = 1;
  
  if(isset($hits->img)) 
    for($i=0;$i<count($hits->img);$i++) {
      if($hits->img[$i]->hits > $max) $max = $hits->img[$i]->hits;
      for($j=0;$j<count($gal->img);$j++) 
        if($hits->img[$i]->filename == $gal->img[$j]->filename) $gal->img[$j]->hits = $hits->img[$i];
    }
  
  //get container xhtml
  $code = sgGetContainerCode();
  
  echo "<h1>$gal->name statistics</h1>\n";
  
  //container frame top (tab)
  echo $code->tab1;
  
  echo "Showing ".($startat+1)."-".($startat+$GLOBALS["sgConfig"]->main_thumb_number>count($gal->img)?count($gal->img):$startat+$GLOBALS["sgConfig"]->main_thumb_number)." of ".count($gal->img)." | ";
  
  if($startat>0) echo "<a href=\"admin.php?action=showimagehits&amp;gallery=$gal->id&amp;startat=".($startat-$GLOBALS["sgConfig"]->main_thumb_number)."\">Previous</a> | ";
  echo "<a href=\"admin.php?action=showgalleryhits\" title=\"Back to galleries list\">Up</a>";
  if(count($gal->img)>$startat+$GLOBALS["sgConfig"]->main_thumb_number) echo " | <a href=\"admin.php?action=showimagehits&amp;gallery=$gal->id&amp;startat=".($startat+$GLOBALS["sgConfig"]->main_thumb_number)."\">Next</a>";
  
  //container frame middle (tab)
  echo $code->tab2;
  
  $pathto_current_theme = $GLOBALS["sgConfig"]->pathto_themes.$GLOBALS["sgConfig"]->theme_name;
  echo "<table class=\"sgList\">\n<tr><th>Image</th><th>Hits</th><th>Last hit</th><th>Graph</th></tr>\n";
  
  for($i=$startat;$i<$startat+$GLOBALS["sgConfig"]->main_thumb_number && $i<count($gal->img);$i++) {
    if(!isset($gal->img[$i]->hits)) {
      $gal->img[$i]->hits->hits = 0;
      $gal->img[$i]->hits->lasthit = 0;
    }
    echo "<tr class=\"sgRow".($i%2)."\"><td><a href=\"index.php?gallery=".$gal->id."&amp;image=".$gal->img[$i]->filename."\" title=\"by ".$gal->img[$i]->artist."\">".$gal->img[$i]->name."</a></td>";
    echo "<td align=\"right\">".($gal->img[$i]->hits->hits==0?"0":$gal->img[$i]->hits->hits)."</td>";
    echo "<td align=\"right\" title=\"".($gal->img[$i]->hits->lasthit==0?"n/a":date("Y-m-d H:i:s",$gal->img[$i]->hits->lasthit))."\">".($gal->img[$i]->hits->lasthit==0?"n/a":date("D&\\n\b\s\p;j&\\n\b\s\p;H:i",$gal->img[$i]->hits->lasthit))."</td>";
    echo "<td><img src=\"$pathto_current_theme/images/graph.gif\" height=\"8\" width=\"".($max==0?"0":floor(($gal->img[$i]->hits->hits/$max)*300))."\" /></td></tr>\n";
  }

  echo "</table>\n";

  //contaner frame bottom
  echo $code->bottom;
}

function sgShowGalleryHits($gallery = "", $startat = 0)
{
  $dir = sgGetListing($GLOBALS["sgConfig"]->pathto_galleries.$gallery);
  
  //get contaner xhtml
  $code = sgGetContainerCode();
  
  echo "<h1>gallery statistics</h1>\n";
  
  //container frame top (tab)
  echo $code->tab1;
  
  echo "Showing ".($startat+1)."-".($startat+$GLOBALS["sgConfig"]->gallery_thumb_number>count($dir->dirs)?count($dir->dirs):$startat+$GLOBALS["sgConfig"]->gallery_thumb_number)." of ".count($dir->dirs);
  
  if($startat>0) echo " | <a href=\"?startat=".($startat-$GLOBALS["sgConfig"]->main_thumb_number)."\">Previous</a>";
  if(count($dir->dirs)>$startat+$GLOBALS["sgConfig"]->gallery_thumb_number) echo " | <a href=\"?startat=".($startat+$GLOBALS["sgConfig"]->gallery_thumb_number)."\">Next</a>";
  
  //container frame middle (tab)
  echo $code->tab2;
  
  for($i=$max=0;$i<count($dir->dirs);$i++) {
    $galhits[$i] = sgGetGalleryInfo($dir->dirs[$i]); 
    $galhits[$i]->hits = sgGetHits($dir->dirs[$i]);
    if($galhits[$i]->hits->hits > $max) $max = $galhits[$i]->hits->hits;
  }
  
  $pathto_current_theme = $GLOBALS["sgConfig"]->pathto_themes.$GLOBALS["sgConfig"]->theme_name;
  echo "<table class=\"sgList\">\n<tr><th>Gallery</th><th>Hits</th><th>Last hit</th><th></th></tr>\n";
  
  for($i=$startat;$i<$startat+$GLOBALS["sgConfig"]->gallery_thumb_number && $i<count($dir->dirs);$i++) {
    echo "<tr class=\"sgRow".($i%2)."\"><td><a href=\"admin.php?action=showimagehits&amp;gallery=".$galhits[$i]->id."\">".$galhits[$i]->name."</a></td>";
    echo "<td align=\"right\">".($galhits[$i]->hits->hits==0?"0":$galhits[$i]->hits->hits)."</td>";
    echo "<td align=\"right\" title=\"".($galhits[$i]->hits->lasthit==0?"n/a":date("Y-m-d H:i:s",$galhits[$i]->hits->lasthit))."\">".($galhits[$i]->hits->lasthit==0?"n/a":date("D j H:i",$galhits[$i]->hits->lasthit))."</td>";
    echo "<td><img src=\"$pathto_current_theme/images/graph.gif\" height=\"8\" width=\"".($max==0?"0":floor(($galhits[$i]->hits->hits/$max)*300))."\" /></td></tr>\n";
  }
  echo "</table>\n";
  
  //container frame bottom
  echo $code->bottom;
}

function sgShowPurgeConfirmation()
{
  $dir = sgGetListing($GLOBALS["sgConfig"]->pathto_cache,"jpegs");

  echo "<h1>purge thumbnails</h1>\n";
  
  echo "<form action=\"$_SERVER[PHP_SELF]\" method=\"post\">\n";
  echo "<input type=\"hidden\" name=\"action\" value=\"purgecache-confirmed\" />\n";
  echo "<p>Are you sure you want to delete ".count($dir->files)." cached thumbnail images?</p>";
  echo "<p><input type=\"submit\" class=\"button\" name=\"confirm\" value=\"OK\">\n";
  echo "<input type=\"submit\" class=\"button\" name=\"confirm\" value=\"Cancel\"></p>";
  echo "</form>\n";
 
}

function sgPurgeCache()
{
  $dir = sgGetListing($GLOBALS["sgConfig"]->pathto_cache,"jpegs");
  
  $success = true;
  for($i=0;$i<count($dir->files);$i++) {
    $success &= unlink($dir->path.$dir->files[$i]);
  }
  
  return $success;
}

function sgShowAdminOptions()
{
  echo "<h1>admin options</h1>\n";
  echo "<p>Welcome to the admin section of singapore.</p>\n";
  echo "<ul>\n";
  echo "  <li><a href=\"index.php\">Browse galleries</a></li>\n";
  echo "  <li><a href=\"admin.php?action=showgalleryhits\">Show viewing statistics</a></li>\n";
  echo "  <li><a href=\"admin.php?action=purgecache\">Purge cached thumbnails</a></li>\n";
  echo "  <li><a href=\"admin.php?action=editpass\">Change password</a></li>\n";
  echo "  <li><a href=\"admin.php?action=logout\">Logout</a></li>\n";
  echo "</ul>\n";
}

?>
