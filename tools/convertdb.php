<?php 

//permissions bit flags
define("SG_GRP_READ",   1);
define("SG_GRP_EDIT",   2);
define("SG_GRP_ADD",    4);
define("SG_GRP_DELETE", 8);
define("SG_WLD_READ",   16);
define("SG_WLD_EDIT",   32);
define("SG_WLD_ADD",    64);
define("SG_WLD_DELETE", 128);


function getGallery($path) 
{
  $gal = new sgGallery('.');

  $fp = @fopen($path."/metadata.csv","r");
  if($fp) {

    while($temp[] = fgetcsv($fp,2048));
    fclose($fp);
    
    @list(
      $gal->filename,
      ,
      $gal->owner,
      $gal->groups,
      $gal->permissions,
      $gal->categories,
      $gal->name,
      $gal->artist,
      $gal->email,
      $gal->copyright,
      $gal->desc,
      $gal->summary,
      $gal->date
    ) = $temp[1];
    
    
    for($i=0;$i<count($temp)-3;$i++) {
      $gal->images[$i] = new sgImage();
      list(
        $gal->images[$i]->filename,
        $gal->images[$i]->thumbnail,
        $gal->images[$i]->owner,
        $gal->images[$i]->groups,
        $gal->images[$i]->permissions,
        $gal->images[$i]->categories,
        $gal->images[$i]->name,
        $gal->images[$i]->artist,
        $gal->images[$i]->email,
        $gal->images[$i]->copyright,
        $gal->images[$i]->desc,
        $gal->images[$i]->location,
        $gal->images[$i]->date,
        $gal->images[$i]->camera,
        $gal->images[$i]->lens,
        $gal->images[$i]->film,
        $gal->images[$i]->darkroom,
        $gal->images[$i]->digital
      ) = $temp[$i+2];
    
      //don't get image size and type
      
    }
      
  } else {
    //selected gallery does not exist or no metadata
    return null;
  }
  
  return $gal;
}

function putGallery($gallery, $path) {
    
  //backup data file
  copy($path."/metadata.csv", $path."/metadata.bak");
  $fp = fopen($path."/metadata.csv","w");
  
  if(!$fp) return false;
  
  $success = true;

  $success &= (bool) fwrite($fp,"filename,thumbnail,owner,group(s),permissions,catergories,image name,artist name,artist email,copyright,image description,image location,date taken,camera info,lens info,film info,darkroom manipulation,digital manipulation");
  $success &= (bool) fwrite($fp,"\n\"".
    $gallery->filename."\",,".
    $gallery->owner.",".
    $gallery->groups.",".
    $gallery->permissions.",".
    $gallery->categories.',"'.
    str_replace('"','""',$gallery->name).'","'.
    str_replace('"','""',$gallery->artist).'","'.
    str_replace('"','""',$gallery->email).'","'.
    str_replace('"','""',$gallery->copyright).'","'.
    str_replace('"','""',$gallery->desc).'","'.
    str_replace('"','""',$gallery->summary).'","'.
    str_replace('"','""',$gallery->date).'"'
  );
  
  for($i=0;$i<count($gallery->images);$i++)
    $success &= (bool) fwrite($fp,"\n\"".
      $gallery->images[$i]->filename."\",".
      $gallery->images[$i]->thumbnail.",".
      $gallery->images[$i]->owner.",".
      $gallery->images[$i]->groups.",".
      $gallery->images[$i]->permissions.",".
      $gallery->images[$i]->categories.',"'.
      str_replace('"','""',$gallery->images[$i]->name).'","'.
      str_replace('"','""',$gallery->images[$i]->artist).'","'.
      str_replace('"','""',$gallery->images[$i]->email).'","'.
      str_replace('"','""',$gallery->images[$i]->copyright).'","'.
      str_replace('"','""',$gallery->images[$i]->desc).'","'.
      str_replace('"','""',$gallery->images[$i]->location).'","'.
      str_replace('"','""',$gallery->images[$i]->date).'","'.
      str_replace('"','""',$gallery->images[$i]->camera).'","'.
      str_replace('"','""',$gallery->images[$i]->lens).'","'.
      str_replace('"','""',$gallery->images[$i]->film).'","'.
      str_replace('"','""',$gallery->images[$i]->darkroom).'","'.
      str_replace('"','""',$gallery->images[$i]->digital).'"'
    );
  $success &= (bool) fclose($fp);

  return $success;
}

function setPerms($obj) {
  $obj->permissions = 0;
  if(!empty($_POST["sgGrpRead"]))   $obj->permissions |= SG_GRP_READ;
  if(!empty($_POST["sgGrpEdit"]))   $obj->permissions |= SG_GRP_EDIT;
  if(!empty($_POST["sgGrpAdd"]))    $obj->permissions |= SG_GRP_ADD;
  if(!empty($_POST["sgGrpDelete"])) $obj->permissions |= SG_GRP_DELETE;
  if(!empty($_POST["sgWldRead"]))   $obj->permissions |= SG_WLD_READ;
  if(!empty($_POST["sgWldEdit"]))   $obj->permissions |= SG_WLD_EDIT;
  if(!empty($_POST["sgWldAdd"]))    $obj->permissions |= SG_WLD_ADD;
  if(!empty($_POST["sgWldDelete"])) $obj->permissions |= SG_WLD_DELETE;
  
  $obj->groups = $_REQUEST["sgGroups"];
  $obj->owner = $_REQUEST["sgOwner"];
  
  return $obj;
}


function convertDirectory ($path)
{
  if (is_dir($path)) {
    $gallery = getGallery($path);
    echo "<ul><li>Checking $path<br />\n";
    if($gallery) {
      if($gallery->summary != "" && empty($_REQUEST["convertOverwrite"]))
        echo "Did NOT overwrite non-empty summary in $path<br />\n";
      else {
        if($_REQUEST["convertType"]!='none')
          $gallery->summary = $gallery->desc;
        if($_REQUEST["convertType"]=='move')
          $gallery->desc = "";
      }

      $gallery = setPerms($gallery);
      
      for($i=0; $i<count($gallery->images); $i++)
        $gallery->images[$i] = setPerms($gallery->images[$i]);
      
      if(putGallery($gallery,$path))
        echo "Successfully converted $path<br />\n";
      else
        echo "Problem saving data file for $path<br />\n";
    } else
      echo "Skipping $path<br />\n";
    $d = dir($path);
    while (($file = $d->read()) !== false) {
      if ($file == '.' || $file == '..') continue;
      $path = $d->path."/".$file;
      if (is_dir($path)) {
        convertDirectory($path);
      }
    }
    echo "</li></ul>\n";
  }
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>database converter</title>
<link rel="stylesheet" type="text/css" href="tools.css" />
</head>

<body>

<h1>database converter</h1>

<?php 
if(isset($_REQUEST["convertType"])) { 

  include "../includes/config.class.php";
  include "../includes/gallery.class.php";
  include "../includes/image.class.php";
  $config = new sgConfig("../singapore.ini");
  
  $config->base_path = "../";
  
  //echo "<ul>\n";
  convertDirectory($config->base_path.$config->pathto_galleries);
  //echo "</ul>\n";
  
  echo "<p>All operations complete.</p>\n";

} else { ?>
<p>This will convert all your metadata files from singapore 0.9.6, 0.9.7, 0.9.8 or 0.9.9 to 0.9.10.</p>

<form method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
<h3>summary field</h3>
<p>There is a new gallery summary field that is displayed instead of the 
description in the parent gallery. You can choose to either copy or move the 
old description field to the summary field or leave both untouched:</p>

<p><input type="radio" class="radio" name="convertType" value="copy" checked="true" /> Copy<br />
<input type="radio" class="radio" name="convertType" value="move" /> Move<br />
<input type="radio" class="radio" name="convertType" value="none" /> Neither<br />

<p>By default only empty summary fields will be written to. Check this option to
allow the summary field to be overwritten <input type="checkbox" class="checkbox" name="convertOverwrite" /></p>

<h3>permissions</h3>

<p>This version introduces multiple authorised users and image &amp; gallery 
permissions. Please choose the default permissions that you would like all 
objects to be set to. The default permissions selected below are recommended as 
they will make all images &amp; galleries readable by everyone but only 
modifiable by administrators. See the readme for more information on the 
permissions model used by singapore.</p>
<table>
<tr>
  <td>Owner</td>
  <td><input type="text" name="sgOwner" value="__nobody__" /></td>
</tr>
<tr>
  <td>Groups</td>
  <td><input type="text" name="sgGroups" value="" /></td>
</tr>
<tr>
  <td>Group permissions</td>
  <td><div class="inputbox">
    <input type="checkbox" class="checkbox" name="sgGrpRead" checked="true"/> Read
    <input type="checkbox" class="checkbox" name="sgGrpEdit" /> Edit
    <input type="checkbox" class="checkbox" name="sgGrpAdd" /> Add
    <input type="checkbox" class="checkbox" name="sgGrpDelete" /> Delete
  </div></td>
</tr>
<tr>
  <td>World permissions</td>
  <td><div class="inputbox">
    <input type="checkbox" class="checkbox" name="sgWldRead" checked="true"/> Read
    <input type="checkbox" class="checkbox" name="sgWldEdit" /> Edit
    <input type="checkbox" class="checkbox" name="sgWldAdd" /> Add
    <input type="checkbox" class="checkbox" name="sgWldDelete" /> Delete
  </div></td>
</tr>
</table>


<p>Please note that while the script will create backups of your metadata files 
it is highly recommended that you create your own backups for added security.</p>

<input type="submit" class="button" value="Go" /></p>
</form>
<?php } ?>

<p><a href="index.html">Return</a> to tools.</p>

</body>
</html>