<?php 

//v0.9(a) load function
function sgGetGallery($file) {
  $fp = fopen($file,"r");
  
  while($temp[] = fgetcsv($fp,4096));
  fclose($fp);
  
  list(
    $gal->filename,
    $gal->flags,
    $gal->artist,
    $gal->name,
    ,
    $gal->desc
  ) = $temp[1];
  
  for($i=0;$i<count($temp)-3;$i++) {
    list(
      $gal->img[$i]->filename,
      $gal->img[$i]->flags,
      $gal->img[$i]->artist,
      $gal->img[$i]->name,
      $gal->img[$i]->location,
      $gal->img[$i]->desc,
      $gal->img[$i]->date,
      $gal->img[$i]->camera,
      $gal->img[$i]->lens,
      $gal->img[$i]->film
    ) = $temp[$i+2];
  }
  
  return $gal;
}


//v0.9.1 save function
function sgPutGallery($gal) {
   
  echo "filename,thumbnail,owner,group(s),permissions,catergories,image name,artist name,artist email,copyright,image description,image location,date taken,camera info,lens info,film info,darkroom manipulation,digital manipulation";
  echo "\n".
  $gal->filename.",,".
  $gal->owner.",".
  $gal->groups.",".
  $gal->permissions.",".
  $gal->categories.",\"".
  $gal->name."\",\"".
  $gal->artist."\",\"".
  $gal->email."\",\"".
  $gal->copyright."\",\"".
  $gal->desc."\"";
  
  for($i=0;$i<count($gal->img);$i++)
    echo "\n".
    $gal->img[$i]->filename.",".
    $gal->img[$i]->thumbnail.",".
    $gal->img[$i]->owner.",".
    $gal->img[$i]->groups.",".
    $gal->img[$i]->permissions.",".
    $gal->img[$i]->categories.",\"".
    $gal->img[$i]->name."\",\"".
    $gal->img[$i]->artist."\",\"".
    $gal->img[$i]->email."\",\"".
    $gal->img[$i]->copyright."\",\"".
    $gal->img[$i]->desc."\",\"".
    $gal->img[$i]->location."\",\"".
    $gal->img[$i]->date."\",\"".
    $gal->img[$i]->camera."\",\"".
    $gal->img[$i]->lens."\",\"".
    $gal->img[$i]->film."\",\"".
    $gal->img[$i]->darkroom."\",\"".
    $gal->img[$i]->digital."\"";
}




if(isset($_REQUEST["tempfile"])) {

  $gal = sgGetGallery(stripslashes($_REQUEST["tempfile"]));
  
  header("Content-Type: text/plain");
  //header("Content-Disposition: attachment; filename='metadata.csv'");
  
  sgPutGallery($gal);

} else {
?>
<html>
<body>
<form action="<?php echo $_SERVER["PHP_SELF"] ?>" method="post">
<p>
From version:<br />
<input type="radio" name="fromver" value="0.9" checked="true" />0.9 or 0.9a<br />
To version:<br />
<input type="radio" name="tover" value="0.9.1" checked="true" />0.9.1 and up<br />
</p>
<p>Metadata file: 
<input type="file" name="tempfile" />
<input type="submit" value="Convert" /></p>

<p>Please backup your csv files prior to conversion.... just in case.</p>

<p>When you click on 'convert' the script will return the converted file 
which you can then copy and paste into your old metadata.csv file 
(opened in a text editor such as Notepad).</p>

<p>If the </p>
</form>
</body>
</html>


<?php
}
?>