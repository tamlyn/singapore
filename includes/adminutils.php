<?php

 /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *\
 *  adminutils.php - Copyright 2003 Tamlyn Rhodes <tam@zenology.org>   *
 *                                                                     *
 *  This file is part of singapore v0.9.2                              *
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
  
//admin functions

function sgLogin() {
  if(isset($_POST["user"]) && isset($_POST["pass"])) {
	  $users = sgGetUsers();
		for($i=1;$i < count($users);$i++) {
      if($_POST["user"] == $users[$i]->username && md5($_POST["pass"]) == $users[$i]->userpass){
        $_SESSION["user"] = $users[$i];
        $_SESSION["user"]->check = md5(sgGetConfig("secret_string").$_SERVER["REMOTE_ADDR"]);
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
  $_SESSION["user"] = NULL;
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
  echo "</form>";
} 

function sgEditGallery($gallery = "")
{ 
  if(empty($gallery)) { echo "<p>The web interface cannot add galleries yet. See <a href=\"Readme.html#managing\">Readme</a>.</p>"; return; }
  
  $gal = sgGetGalleryInfo($gallery);
  
  echo "<p style=\"float:right\"><a href=\"index.php?gallery=$gallery\">";
  echo "<img src=\"thumb.php?gallery=$gallery&amp;image=$gal->filename&amp;size=".sgGetConfig("main_thumb_size")."\" class=\"sgThumbnail\" alt=\"Sample image from gallery\" />";
  echo "</a></p> ";
  echo "<h1>edit gallery: $gal->name</h1>\n";
  
  echo "<form action=\"$_SERVER[PHP_SELF]\" method=\"post\">\n";
  echo "<input type=\"hidden\" name=\"action\" value=\"savegallery\" />\n";
  echo "<input type=\"hidden\" name=\"gallery\" value=\"$gal->id\" />\n";
  echo "<input type=\"hidden\" name=\"sgOwner\" value=\"$img->owner\" />\n";
  echo "<input type=\"hidden\" name=\"sgGroups\" value=\"$img->groups\" />\n";
  echo "<input type=\"hidden\" name=\"sgPermissions\" value=\"$img->permissions\" />\n";
  echo "<input type=\"hidden\" name=\"sgCategories\" value=\"$img->categories\" />\n";
  echo "<table class=\"formTable\">\n";
  echo "<tr>\n  <td>Gallery name:</td>\n  <td><input type=\"text\" name=\"sgGalleryName\" value=\"".htmlentities($gal->name)."\" size=\"40\" /></td>\n</tr>\n";
  echo "<tr>\n  <td>Artist name:</td>\n  <td><input type=\"text\" name=\"sgArtistName\" value=\"".htmlentities($gal->artist)."\" size=\"40\" />*</td>\n</tr>\n";
  echo "<tr>\n  <td>Artist email:</td>\n  <td><input type=\"text\" name=\"sgArtistEmail\" value=\"".htmlentities($gal->email)."\" size=\"40\" />*</td>\n</tr>\n";
  echo "<tr>\n  <td>Copyright holder:</td>\n  <td><input type=\"text\" name=\"sgCopyright\" value=\"".htmlentities($gal->copyright)."\" size=\"40\" />*</td>\n</tr>\n";
  echo "<tr>\n  <td valign=\"top\">Description:</td>\n  <td><textarea name=\"sgGalleryDesc\" cols=\"70\" rows=\"8\">".str_replace("<br />","\n",htmlentities($gal->desc))."</textarea></td>\n</tr>\n";
  echo "<tr>\n  <td></td>\n  <td><input type=\"submit\" class=\"button\" value=\"Save Changes\" /></td>\n</tr>\n";
  echo "</table>\n";
  echo "<p>Note: fields marked * are stored in the database but are not currently used.</p>";
  echo "</form>";
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
  
  sgPutGallery($gal);
  
  if(sgPutGallery($gal)) echo "<h1>gallery saved</h1>\n";
  else echo "<h1>gallery not saved</h1>\n";
  echo 
  "<ul>\n".
  "  <li><a href=\"index.php?gallery=$gallery\">View gallery</a></li>\n".
  "  <li><a href=\"admin.php?action=editgallery\">New gallery</a></li>\n".
  //"  <li><a href=\"admin.php?action=editimage&amp;gallery=$gallery&amp;image=$_REQUEST[sgNextImage]\">Edit next image</a></li>\n".
  //"  <li><a href=\"admin.php?action=editimage&amp;gallery=$gallery&amp;image=$_REQUEST[sgPrevImage]\">Edit previous image</a></li>\n".
  "</ul>\n";
}

function sgEditImage($gallery, $image = "")
{ 
  if(empty($image)) { echo "<p>The web interface cannot add images yet. See <a href=\"Readme.html#managing\">Readme</a>.</p>"; return; }
  
  $img = sgGetImage($gallery, $image);
  
  echo "<p style=\"float:right\"><a href=\"index.php?gallery=$gallery&amp;image=$img->filename\">";
  echo "<img src=\"thumb.php?gallery=$gallery&amp;image=$img->filename&amp;size=".sgGetConfig("main_thumb_size")."\" class=\"sgThumbnail\" alt=\"$img->name by $img->artist\" />";
  echo "</a></p> ";
      
  echo "<h1>edit image: $img->name</h1>\n";
  
  echo "<form action=\"$_SERVER[PHP_SELF]\" method=\"post\">\n";
  echo "<input type=\"hidden\" name=\"action\" value=\"saveimage\" />\n";
  echo "<input type=\"hidden\" name=\"gallery\" value=\"$gallery\" />\n";
  echo "<input type=\"hidden\" name=\"image\" value=\"$image\" />\n";
  echo "<input type=\"hidden\" name=\"sgThumbnail\" value=\"$img->thumbnail\" />\n";
  echo "<input type=\"hidden\" name=\"sgOwner\" value=\"$img->owner\" />\n";
  echo "<input type=\"hidden\" name=\"sgGroups\" value=\"$img->groups\" />\n";
  echo "<input type=\"hidden\" name=\"sgPermissions\" value=\"$img->permissions\" />\n";
  echo "<input type=\"hidden\" name=\"sgCategories\" value=\"$img->categories\" />\n";
  echo "<input type=\"hidden\" name=\"sgNextImage\" value=\"{$img->next[0]->filename}\" />\n";
  echo "<input type=\"hidden\" name=\"sgPrevImage\" value=\"{$img->prev[0]->filename}\" />\n";
  echo "<table class=\"formTable\">\n";
  echo "<tr>\n  <td>Image name:</td>\n  <td><input type=\"text\" name=\"sgImageName\" value=\"".htmlentities($img->name)."\" size=\"40\" /></td>\n</tr>\n";
  echo "<tr>\n  <td>Artist name:</td>\n  <td><input type=\"text\" name=\"sgArtistName\" value=\"".htmlentities($img->artist)."\" size=\"40\" /></td>\n</tr>\n";
  echo "<tr>\n  <td>Artist email:</td>\n  <td><input type=\"text\" name=\"sgArtistEmail\" value=\"".htmlentities($img->email)."\" size=\"40\" /></td>\n</tr>\n";
  echo "<tr>\n  <td>Location:</td>\n  <td><input type=\"text\" name=\"sgLocation\" value=\"".htmlentities($img->location)."\" size=\"40\" /></td>\n</tr>\n";
  echo "<tr>\n  <td>Date:</td>\n  <td><input type=\"text\" name=\"sgDate\" value=\"".htmlentities($img->date)."\" size=\"40\" /></td>\n</tr>\n";
  echo "<tr>\n  <td>Copyright holder:</td>\n  <td><input type=\"text\" name=\"sgCopyright\" value=\"".htmlentities($img->copyright)."\" size=\"40\" /></td>\n</tr>\n";
  echo "<tr>\n  <td valign=\"top\">Description:</td>\n  <td><textarea name=\"sgImageDesc\" cols=\"70\" rows=\"8\">".str_replace("<br />","\n",htmlentities($img->desc))."</textarea></td>\n</tr>\n";
  echo "<tr>\n  <td>Camera info:</td>\n  <td><input type=\"text\" name=\"sgCamera\" value=\"".htmlentities($img->camera)."\" size=\"40\" /></td>\n</tr>\n";
  echo "<tr>\n  <td>Lens info:</td>\n  <td><input type=\"text\" name=\"sgLens\" value=\"".htmlentities($img->lens)."\" size=\"40\" /></td>\n</tr>\n";
  echo "<tr>\n  <td>Film info:</td>\n  <td><input type=\"text\" name=\"sgFilm\" value=\"".htmlentities($img->film)."\" size=\"40\" /></td>\n</tr>\n";
  echo "<tr>\n  <td>Darkroom manipulation:</td>\n  <td><input type=\"text\" name=\"sgDarkroom\" value=\"".htmlentities($img->darkroom)."\" size=\"40\" /></td>\n</tr>\n";
  echo "<tr>\n  <td>Digital manipulation:</td>\n  <td><input type=\"text\" name=\"sgDigital\" value=\"".htmlentities($img->digital)."\" size=\"40\" /></td>\n</tr>\n";
  echo "<tr>\n  <td></td>\n  <td><input type=\"submit\" class=\"button\" value=\"Save Changes\" /></td>\n</tr>\n";
  echo "</table>\n";
  echo "</form>";
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
  "  <li><a href=\"admin.php?action=editimage&amp;gallery=$gallery\">New image</a></li>\n".
  "  <li><a href=\"admin.php?action=editimage&amp;gallery=$gallery&amp;image=$_REQUEST[sgNextImage]\">Edit next image</a></li>\n".
  "  <li><a href=\"admin.php?action=editimage&amp;gallery=$gallery&amp;image=$_REQUEST[sgPrevImage]\">Edit previous image</a></li>\n".
  "  <li><a href=\"index.php?gallery=$gallery\">View gallery</a></li>\n".
  "  <li><a href=\"admin.php?action=editgallery&amp;gallery=$gallery\">Edit gallery</a></li>\n".
  "</ul>\n";
  
}

function sgShowAdminOptions()
{
  echo "<h1>admin options</h1>\n";
  echo "<p>Welcome to the admin section of singapore.</p>\n";
  echo "<ul>\n";
  echo "  <li><a href=\"index.php\">Browse galleries</a></li>\n";
  echo "  <li><a href=\"admin.php?action=showgalleryhits\">Show viewing statistics</a></li>\n";
  echo "  <li><a href=\"admin.php?action=confirmpurge\">Purge cached thumbnails</a></li>\n";
  echo "  <li><a href=\"admin.php?action=editpass\">Change password</a></li>\n";
  echo "  <li><a href=\"admin.php?action=logout\">Logout</a></li>\n";
  echo "</ul>";
}

function sgEditPass()
{
  echo "<form action=\"$_SERVER[PHP_SELF]\" method=\"post\">\n";
  echo "<input type=\"hidden\" name=\"action\" value=\"savepass\" />\n";
  echo "<input type=\"hidden\" name=\"user\" value=\"{$_SESSION[user]->username}\" />\n";
  echo "<table>\n";
  echo "<tr>\n  <td>Current password:</td>\n  <td><input type=\"password\" name=\"sgOldPass\" size=\"23\" /></td>\n</tr>\n";
  echo "<tr>\n  <td>New password:</td>\n  <td><input type=\"password\" name=\"sgNewPass1\" size=\"23\" /></td>\n</tr>\n";
  echo "<tr>\n  <td>Confirm password:</td>\n  <td><input type=\"password\" name=\"sgNewPass2\" size=\"23\" /></td>\n</tr>\n";
  echo "<tr>\n  <td></td>\n  <td><input type=\"submit\" class=\"button\" value=\"Save Changes\" /></td>\n</tr>\n";
  echo "</table>\n";
  echo "</form>";
}

function sgSavePass()
{
  $users = sgGetUsers();
  
  for($i=1;$i < count($users);$i++)
    if($_POST["user"] == $users[$i]->username)
      if(md5($_POST["sgOldPass"]) == $users[$i]->userpass)
        if($_POST["sgNewPass1"]==$_POST["sgNewPass2"])
          if(strlen($_POST["sgNewPass1"]) >= 6 && strlen($_POST["sgNewPass1"]) <= 16) { 
            $users[$i]->userpass = md5($_POST["sgNewPass1"]);
            sgPutUsers($users);
            echo "<p>Password changed successfully.</p>";
          } else { echo "<p>New password must be between 6 and 16 characters long.</p>"; sgEditPass(); }
        else { echo "<p>The new passwords you entered do not match.</p>"; sgEditPass(); }
      else { echo "<p>The current password you entered does not match the one in the database.</p>"; sgEditPass(); }
		else { echo "<p>The username specified was not found in the database.</p>"; sgEditPass(); }
}

function sgShowImageHits($gallery, $startat = 0)
{
  $gal = sgGetGallery($gallery);
  if(!$gal) {
    echo("<h1>gallery '$gallery' not found</h1>\n");
    sgShowIndex(sgGetConfig("pathto_galleries"));
    return;
  }
  $hits = sgGetHits($gal->id);
  
  for($i=$max=0;$i<count($hits->img);$i++) {
    if($hits->img[$i]->hits > $max) $max = $hits->img[$i]->hits;
    for($j=0;$j<count($gal->img);$j++) 
      if($hits->img[$i]->filename == $gal->img[$j]->filename) $gal->img[$j]->hits = $hits->img[$i];
  }
  
  //get contaner xhtml
  $code = sgGetContainerCode();
  
  //if logged in to admin show admin panel
  sgShowAdminBar("galleryhits", $gallery);
  
  echo "<h1>stats for $gal->name</h1>\n";
  
  //container frame top (tab)
  echo $code->tab1;
  
  echo "Showing ".($startat+1)."-".($startat+sgGetConfig("main_thumb_number")>count($gal->img)?count($gal->img):$startat+sgGetConfig("main_thumb_number"))." of ".count($gal->img)." | ";
  
  if($startat>0) echo "<a href=\"admin.php?action=showimagehits&amp;gallery=$gal->id&amp;startat=".($startat-sgGetConfig("main_thumb_number"))."\">Previous</a> | ";
  echo "<a href=\"admin.php?action=showgalleryhits\" title=\"Back to galleries list\">Up</a>";
  if(count($gal->img)>$startat+sgGetConfig("main_thumb_number")) echo " | <a href=\"admin.php?action=showimagehits&amp;gallery=$gal->id&amp;startat=".($startat+sgGetConfig("main_thumb_number"))."\">Next</a>";
  
  //container frame middle (tab)
  echo $code->tab2;
  
  $pathto_current_theme = sgGetConfig("pathto_themes").sgGetConfig("theme_name");
  echo "<table class=\"sgList\">\n<tr><th>Image</th><th>Hits</th><th>Last hit</th><th>Graph</th></tr>\n";
  
  for($i=$startat;$i<$startat+sgGetConfig("main_thumb_number") && $i<count($gal->img);$i++) {
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
  $dir = sgGetListing(sgGetConfig("pathto_galleries").$gallery);
  
  //get contaner xhtml
  $code = sgGetContainerCode();
  
  //if logged in to admin show admin panel
  sgShowAdminBar("indexhits");
  
  echo "<h1>viewing statistics</h1>\n";
  
  //container frame top (tab)
  echo $code->tab1;
  
  echo "Showing ".($startat+1)."-".($startat+sgGetConfig("gallery_thumb_number")>count($dir->dirs)?count($dir->dirs):$startat+sgGetConfig("gallery_thumb_number"))." of ".count($dir->dirs);
  
  if($startat>0) echo " | <a href=\"?startat=".($startat-sgGetConfig("main_thumb_number"))."\">Previous</a>";
  if(count($dir->dirs)>$startat+sgGetConfig("gallery_thumb_number")) echo " | <a href=\"?startat=".($startat+sgGetConfig("gallery_thumb_number"))."\">Next</a>";
  
  //container frame middle (tab)
  echo $code->tab2;
  
  for($i=$max=0;$i<count($dir->dirs);$i++) {
    $galhits[$i] = sgGetGalleryInfo($dir->dirs[$i]); 
    $galhits[$i]->hits = sgGetHits($dir->dirs[$i]);
    if($galhits[$i]->hits->hits > $max) $max = $galhits[$i]->hits->hits;
  }
  
  $pathto_current_theme = sgGetConfig("pathto_themes").sgGetConfig("theme_name");
  echo "<table class=\"sgList\">\n<tr><th>Gallery</th><th>Hits</th><th>Last hit</th><th></th></tr>\n";
  
  for($i=$startat;$i<$startat+sgGetConfig("gallery_thumb_number") && $i<count($dir->dirs);$i++) {
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
  $dir = sgGetListing(sgGetConfig("pathto_cache"),"all");

  echo "<h1>purge thumbnails</h1>\n";
  
  echo "<form action=\"$_SERVER[PHP_SELF]\" method=\"post\">\n";
  echo "<input type=\"hidden\" name=\"action\" value=\"dopurge\" />\n";
  echo "<p>Are you sure you want to delete ".count($dir->files)." cached thumbnail images?</p>";
  echo "<p><input type=\"submit\" class=\"button\" name=\"confirm\" value=\"OK\">\n";
  echo "<input type=\"submit\" class=\"button\" name=\"confirm\" value=\"Cancel\"></p>";
  echo "</form>\n";
 
}

function sgPurgeCache()
{
  $dir = sgGetListing(sgGetConfig("pathto_cache"),"all");
  
  $success = true;
  
  for($i=0;$i<count($dir->files);$i++) {
    $success &= unlink($dir->path.$dir->files[$i]);
  }
  
  if($success) echo "<h1>thumbnails purged</h1>\n";

  echo "<p><a href=\"admin.php\">Admin options</a></p>";
}


?>