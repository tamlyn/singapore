<h1><?php echo $sg->galleryName()?></h1>

<div class="sgContainer">
  <div class="sgTab"><?php echo $sg->galleryHitsTab()?></div>
  <div class="sgContent">
    <table class="sgList">
      <tr><th><?php echo $sg->i18n->_g("Gallery name") ?></th><th><?php echo $sg->i18n->_g("hits table|Hits") ?></th><th><?php echo $sg->i18n->_g("hits table|Last hit") ?></th><th><?php echo $sg->i18n->_g("hits table|Graph") ?></th></tr>
      <?php foreach($sg->galleryGalleriesArray() as $index => $gal): ?>
      <tr class="sgRow<?php echo $index%2 ?>">
        <td><a href="<?php echo $sg->formatAdminURL($sg->isAlbum($index) ? "showimagehits": "showgalleryhits", $sg->encodeId($gal->id)) ?>"><?php echo $gal->name ?></a></td>
        <td align="right"><?php echo $gal->hits->hits==0 ? "0" : $gal->hits->hits ?></td>
        <td align="right" title="<?php echo $gal->hits->lasthit==0 ? "n/a" : date("Y-m-d H:i:s",$gal->hits->lasthit) ?>"><?php echo $gal->hits->lasthit==0 ? "n/a" : date("D j H:i",$gal->hits->lasthit) ?></td>
        <td><img src="<?php echo $sg->config->pathto_admin_template ?>images/graph.gif" height="8" width="<?php echo $sg->gallery->maxhits==0 ? "0" : floor(($gal->hits->hits/$sg->gallery->maxhits)*300) ?>" alt="" /></td>
      </tr>
      <?php endforeach; ?>
    </table>
    
  </div>
</div>
