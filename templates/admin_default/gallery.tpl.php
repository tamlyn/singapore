<h1 style="margin-bottom: 0px"><?php echo $sg->galleryName()?></h1>
<p style="margin-top: 0;"><?php echo $sg->galleryByArtist()?></p>

<div class="sgShadow"><table class="sgShadow" cellspacing="0">
  <tr>
    <td><img src="<?php echo $sg->config->pathto_admin_template?>images/shadow-tabl.gif" alt="" /></td>
    <td class="tabm"><table class="sgShadowTab" cellspacing="0"><tr><td>
  
    <?php echo $sg->galleryTab()?>
  
    </td><td><img src="<?php echo $sg->config->pathto_admin_template?>images/shadow-tabr.gif" alt="" /></td></tr></table></td>
    <td class="tabr"><img src="<?php echo $sg->config->pathto_admin_template ?>images/blank.gif" alt="" /></td>
  </tr>
  <tr>
    <td class="tl"><img src="<?php echo $sg->config->pathto_admin_template ?>images/blank.gif" width="32" height="16" alt="" /></td>
    <td class="tm"><img src="<?php echo $sg->config->pathto_admin_template ?>images/blank.gif" alt="" /></td>
    <td class="tr"><img src="<?php echo $sg->config->pathto_admin_template ?>images/blank.gif" alt="" /></td>
  </tr>
  <tr>
    <td class="ml"><img src="<?php echo $sg->config->pathto_admin_template ?>images/blank.gif" alt="" /></td>
    <td class="mm">
  
    <?php 
      $selectedImagesArray = $sg->gallerySelectedImagesArray();
      for($i=0;$i<count($selectedImagesArray);$i++): ?>
    <div class="sgThumbnail">
      <div class="sgThumbnailContent">
        <img class="borderTL" src="<?php echo $sg->config->pathto_admin_template ?>images/slide-tl.gif" alt="" />
        <img class="borderTR" src="<?php echo $sg->config->pathto_admin_template ?>images/slide-tr.gif" alt="" />
        
        <table><tr><td>
          <?php $sg->selectImage($selectedImagesArray[$i]->filename) ?>
          <?php echo $sg->imageThumbnailLinked() ?>
        </td></tr></table>
        
        <div class="roundedCornerSpacer">&nbsp;</div>
      </div>
      <div class="bottomCorners">
        <img class="borderBL" src="<?php echo $sg->config->pathto_admin_template ?>images/slide-bl.gif" alt="" />
        <img class="borderBR" src="<?php echo $sg->config->pathto_admin_template ?>images/slide-br.gif" alt="" />
      </div>
    </div>
    <?php endfor; ?>
  
    </td>
    <td class="mr"><img src="<?php echo $sg->config->pathto_admin_template ?>images/blank.gif" alt="" /></td>
  </tr>
  <tr>
    <td class="bl"><img src="<?php echo $sg->config->pathto_admin_template ?>images/blank.gif" alt="" /></td>
    <td class="bm"><img src="<?php echo $sg->config->pathto_admin_template ?>images/blank.gif" alt="" /></td>
    <td class="br"><img src="<?php echo $sg->config->pathto_admin_template ?>images/blank.gif" width="32" height="32" alt="" /></td>
  </tr>
</table></div>
  
  
<p>
<?php foreach($sg->galleryDetailsArray() as $detail): ?>
<strong><?php echo $detail->name ?>:</strong> <?php echo $detail->value ?><br />
<?php endforeach; ?>
</p>
