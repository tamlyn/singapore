<?php 

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


function convertDirectory ($path)
{
  if (is_dir($path)) {
    $gallery = getGallery($path);
    echo "Checking $path<br />\n";
    if($gallery) {
      if($gallery->summary != "")
        echo "Did NOT overwrite non-empty summary in $path<br />\n";
      else {
        $gallery->summary = $gallery->desc;
        if($_REQUEST["convertType"]=='move')
          $gallery->desc = "";
        if(putGallery($gallery,$path))
          echo "Successfully converted $path<br />\n";
        else
          echo "Problem saving data file for $path<br />\n";
      }
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
    
  convertDirectory(realpath($config->base_path.$config->pathto_galleries));
  
  echo "<p>All operations complete.</p>\n";

} else { ?>
<p>This will convert all your metadata files to make use of v0.9.10's new 
gallery summary field. You can choose to either copy or move the old description 
field:</p>

<form method="get" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
<p><input type="radio" class="radio" name="convertType" value="copy" /> Copy<br />
<input type="radio" class="radio" name="convertType" value="move" /> Move<br />
<input type="submit" class="button" value="Go" /></p>
</form>

<p>Please note that while the script will create backups of your metadata files 
it is highly recommended that you create your own backups for added security.</p>

<?php } ?>

<p><a href="index.html">Return</a> to tools.</p>

</body>
</html>

