<h1><?php echo $sg->galleryName()?></h1>

<div class="sgContainer">
  <div class="sgTab"><?php echo $sg->galleryHitsTab()?></div>
  <div class="sgContent">
    <table class="sgList">
      <tr><th><?php echo $sg->i18n->_g("Image name") ?></th><th><?php echo $sg->i18n->_g("hits table|Hits") ?></th><th><?php echo $sg->i18n->_g("hits table|Last hit") ?></th><th><?php echo $sg->i18n->_g("hits table|Graph") ?></th></tr>
      <?php foreach($sg->galleryImagesArray() as $index => $img): ?>
      <tr>
        <tr class="sgRow<?php echo $index%2 ?>"><td><a href="<?php echo $sg->formatAdminURL("view", $sg->gallery->idEncoded, rawurlencode($img->filename)) ?>"><?php echo $img->name ?></a></td>
        <td align="right"><?php echo empty($img->hits->hits) ? "0" : $img->hits->hits ?></td>
        <td align="right" title="<?php echo empty($img->hits->lasthit) ? "n/a" : date("Y-m-d H:i:s",$img->hits->lasthit) ?>"><?php empty($img->hits->lasthit) ? "n/a" : date("D j H:i",$img->hits->lasthit) ?></td>
        <td><img src="<?php echo $sg->config->pathto_admin_template ?>images/graph.gif" height="8" width="<?php echo $sg->gallery->maxhits==0 ? "0" : floor(($img->hits->hits/$sg->gallery->maxhits)*300) ?>" /></td></tr>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>    
</div>
