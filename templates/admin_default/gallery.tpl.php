<h2 class="sgTitle"><?php echo $sg->galleryName(); ?></h2>
<h4 class="sgSubTitle"><?php echo $sg->galleryByArtist(); ?></h4>


<div class="sgContainer">
  <div class="sgTab"><?php echo $sg->galleryTab()?></div>
  <div class="sgContent">
    <?php /* <form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post">
    <input type="hidden" name="action" value="multigallery" />
    <input type="hidden" name="gallery" value="<?php echo $sg->galleryIdEncoded(); ?>" />
    <div class="sgGallery">
      <input type="submit" class="button" name="subaction" value="<?php echo $sg->i18n->_g("Delete selected"); ?>" />
      <input type="submit" class="button" name="subaction" value="<?php echo $sg->i18n->_g("Move selected"); ?>" />
      <select name="sgMoveTo">
        <option value=""><?php echo $sg->i18n->_g("Select gallery..."); ?></option>
      </select>
      <input type="submit" class="button" name="subaction" value="<?php echo $sg->i18n->_g("Re-index selected"); ?>" />
    </div> */ ?>
    <?php for($index = $sg->startat; $index < $sg->gallerySelectedGalleriesCount()+$sg->startat; $index++): ?> 
    <div class="sgGallery"><table>
    <tr valign="top">
      <?php /* <td><input type="checkbox" class="checkbox" name="sgGalleries[<?php echo $sg->galleryIdEncoded($index); ?>]" /></td> */ ?>
      <td class="sgGalleryThumbnail"><?php echo $sg->galleryThumbnailImage($index) ?></td>
      <td>
        <?php echo $sg->galleryName($index) ?> - <?php echo $sg->galleryContents($index) ?><br />
        <?php 
          echo '<a href="'.$sg->formatAdminURL("view",$sg->galleryIdEncoded($index)).'">'.$sg->i18n->_g("admin bar|View gallery")."</a> |\n";
          echo '<a href="'.$sg->formatAdminURL("editgallery",$sg->galleryIdEncoded($index)).'">'.$sg->i18n->_g("admin bar|Edit gallery")."</a> |\n";
          echo '<a href="'.$sg->formatAdminURL("editpermissions",$sg->galleryIdEncoded($index)).'">'.$sg->i18n->_g("admin bar|Edit permissions")."</a> |\n";
          echo '<a href="'.$sg->formatAdminURL("deletegallery",$sg->galleryIdEncoded($index)).'">'.$sg->i18n->_g("admin bar|Delete gallery")."</a> |\n";
          echo '<a href="'.$sg->formatAdminURL("newgallery",$sg->galleryIdEncoded($index)).'">'.$sg->i18n->_g("admin bar|New subgallery")."</a> |\n";
          echo '<a href="'.$sg->formatAdminURL("reindex",$sg->galleryIdEncoded($index)).'">'.$sg->i18n->_g("admin bar|Re-index gallery")."</a> |\n";
          //echo '<a href="'.$sg->formatAdminURL("newimage",$sg->galleryIdEncoded($index)).'">'.$sg->i18n->_g("admin bar|New image")."</a> |\n";
          echo '<a href="'.$sg->formatAdminURL("changethumbnail",$sg->galleryIdEncoded($index)).'">'.$sg->i18n->_g("admin bar|Change thumbnail")."</a>\n";
        ?>
      </td>
    </tr>
    </table></div>
    <?php endfor; ?> 
    <?php /* </form> */ ?>
  </div>
</div>
  
  
<p>
<?php foreach($sg->galleryDetailsArray() as $key => $value): ?>
<strong><?php echo $key ?>:</strong> <?php echo $value ?><br />
<?php endforeach; ?>
</p>
