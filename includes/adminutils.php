<?php

 /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *\
 *  adminutils.php - Copyright 2003 Tamlyn Rhodes <tam@zenology.org>   *
 *                                                                     *
 *  This file is part of singapore v0.9                                *
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
        $_SESSION["user"]->check = md5("That girl understood, man. Phew! She fell in love with me, man. $_SERVER[REMOTE_ADDR]");
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
  if($gallery=="") { echo "<p>Cannot add galleries yet. See readme.</p>"; return; }
  
  $gal = sgGetGalleryInfo($gallery);
  
  echo "<p style=\"float:right\"><a href=\"index.php?gallery=$gallery\">";
  echo "<img src=\"thumb.php?gallery=$gallery&amp;image=$gal->filename&amp;size=".sgGetConfig("main_thumb_size")."\" class=\"sgThumbnail\" alt=\"Sample image from gallery\" />";
  echo "</a></p> ";
  echo "<h1>edit gallery: $gal->name</h1>\n";
  
  echo "<form action=\"$_SERVER[PHP_SELF]\" method=\"post\">\n";
  echo "<input type=\"hidden\" name=\"action\" value=\"savegallery\" />\n";
  echo "<input type=\"hidden\" name=\"gallery\" value=\"$gal->id\" />\n";
  echo "<input type=\"hidden\" name=\"sgFlags\" value=\"$gal->flags\" />\n";
  echo "<table class=\"formTable\">\n";
  echo "<tr>\n  <td>Gallery name:</td>\n  <td><input type=\"text\" name=\"sgGalleryName\" value=\"$gal->name\" size=\"40\" /></td>\n</tr>\n";
  echo "<tr>\n  <td>Artist name:</td>\n  <td><input type=\"text\" name=\"sgArtistName\" value=\"$gal->artist\" size=\"40\" /></td>\n</tr>\n";
  echo "<tr>\n  <td valign=\"top\">Description:</td>\n  <td><textarea name=\"sgGalleryDesc\" cols=\"70\" rows=\"8\">$gal->desc</textarea></td>\n</tr>\n";
  echo "<tr>\n  <td></td>\n  <td><input type=\"submit\" class=\"button\" value=\"Save Changes\" /></td>\n</tr>\n";
  echo "</table>\n";
  echo "</form>";
}

function sgSaveGallery($gallery)
{
  $gal = sgGetGallery($gallery);

  $gal->id = $gallery;
  $gal->flags = $_REQUEST["sgFlags"];
  $gal->artist = str_replace("\"","\"\"",$_REQUEST["sgArtistName"]);
  $gal->name = str_replace("\"","\"\"",$_REQUEST["sgGalleryName"]);
  $gal->desc = nl2br(str_replace("\"","\"\"",$_REQUEST["sgGalleryDesc"]));
  
  sgPutGallery($gal);
  
  echo "<h1>gallery saved</h1>\n<p><a href=\"index.php?gallery=$gallery\">Return to gallery</a></p>\n";
  
}

function sgEditImage($gallery, $image = "")
{ 
  if($image=="") { echo "<p>Cannot add images yet. See readme.</p>"; return; }
  
  $img = sgGetImage($gallery, $image);
  
  echo "<h1 style=\"float:left\">edit image: $img->name</h1>\n";
  
  echo "<p><a href=\"index.php?gallery=$gallery&amp;image=$img->filename\">";
  echo "<img src=\"thumb.php?gallery=$gallery&amp;image=$img->filename&amp;size=".sgGetConfig("main_thumb_size")."\" class=\"sgThumbnail\" alt=\"$img->name by $img->artist\" />";
  echo "</a></p> ";
      
  echo "<form action=\"$_SERVER[PHP_SELF]\" method=\"post\">\n";
  echo "<input type=\"hidden\" name=\"action\" value=\"saveimage\" />\n";
  echo "<input type=\"hidden\" name=\"gallery\" value=\"$gallery\" />\n";
  echo "<input type=\"hidden\" name=\"image\" value=\"$image\" />\n";
  echo "<input type=\"hidden\" name=\"sgFlags\" value=\"$img->flags\" />\n";
  echo "<table class=\"formTable\">\n";
  echo "<tr>\n  <td>Artist name:</td>\n  <td><input type=\"text\" name=\"sgArtistName\" value=\"$img->artist\" size=\"40\" /></td>\n</tr>\n";
  echo "<tr>\n  <td>Image name:</td>\n  <td><input type=\"text\" name=\"sgImageName\" value=\"$img->name\" size=\"40\" /></td>\n</tr>\n";
  echo "<tr>\n  <td>Location:</td>\n  <td><input type=\"text\" name=\"sgLocation\" value=\"$img->location\" size=\"40\" /></td>\n</tr>\n";
  echo "<tr>\n  <td>Date:</td>\n  <td><input type=\"text\" name=\"sgDate\" value=\"$img->date\" size=\"40\" /></td>\n</tr>\n";
  echo "<tr>\n  <td valign=\"top\">Description:</td>\n  <td><textarea name=\"sgImageDesc\" cols=\"70\" rows=\"8\">$img->desc</textarea></td>\n</tr>\n";
  echo "<tr>\n  <td>Camera info:</td>\n  <td><input type=\"text\" name=\"sgCamera\" value=\"$img->camera\" size=\"40\" /></td>\n</tr>\n";
  echo "<tr>\n  <td>Lens info:</td>\n  <td><input type=\"text\" name=\"sgLens\" value=\"$img->lens\" size=\"40\" /></td>\n</tr>\n";
  echo "<tr>\n  <td>Film info:</td>\n  <td><input type=\"text\" name=\"sgFilm\" value=\"$img->film\" size=\"40\" /></td>\n</tr>\n";
  echo "<tr>\n  <td></td>\n  <td><input type=\"submit\" class=\"button\" value=\"Save Changes\" /></td>\n</tr>\n";
  echo "</table>\n";
  echo "</form>";
}

function sgSaveImage($gallery, $image)
{
  $img->filename = $image;
  $img->flags = $_REQUEST["sgFlags"];
  $img->artist = str_replace("\"","\"\"",$_REQUEST["sgArtistName"]);
  $img->name = str_replace("\"","\"\"",$_REQUEST["sgImageName"]);
  $img->location = str_replace("\"","\"\"",$_REQUEST["sgLocation"]);
  $img->date = str_replace("\"","\"\"",$_REQUEST["sgDate"]);
  $img->desc = nl2br(str_replace("\"","\"\"",$_REQUEST["sgImageDesc"]));
  $img->camera = str_replace("\"","\"\"",$_REQUEST["sgCamera"]);
  $img->lens = str_replace("\"","\"\"",$_REQUEST["sgLens"]);
  $img->film = str_replace("\"","\"\"",$_REQUEST["sgFilm"]);
  
  sgPutImage($gallery, $img);
  
  echo "<h1>image saved</h1>\n<p><a href=\"index.php?gallery=$gallery&amp;image=$image\">Return to image</a></p>\n";
  
}

function sgShowAdminOptions()
{
  echo "<h1>admin options</h1>\n";
  echo "<p>Welcome to the admin section of singapore.</p>\n";
  echo "<ul>\n";
  echo "  <li><a href=\"index.php\">Browse galleries</a></li>\n";
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

?>
