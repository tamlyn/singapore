<p class="sgNavBar sgTopNavBar">
<?php if($sg->gallery->hasPrev()) echo $sg->gallery->prevLink()." | "; ?> 
<?php if(!$sg->gallery->isRoot()) echo $sg->gallery->parentLink(); ?> 
<?php if($sg->gallery->hasNext()) echo " | ".$sg->gallery->nextLink(); ?>
</p>

<h2 class="sgTitle"><?php echo $sg->gallery->name(); ?></h2>

<div class="sgContainer">
  <div class="sgTab"><?php echo $sg->galleryTab()?></div>
  <div class="sgContent">
    <form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post">
    <input type="hidden" name="action" value="multigallery" />
    <input type="hidden" name="gallery" value="<?php echo $sg->gallery->idEncoded(); ?>" />
    <div class="sgGallery">
      <input type="submit" class="button" name="subaction" value="<?php echo $sg->translator->_g("Delete selected"); ?>" />
      <input type="submit" class="button" name="subaction" value="<?php echo $sg->translator->_g("Move selected"); ?>" />
      <select name="sgMoveTo">
        <option value=""><?php echo $sg->translator->_g("Select gallery..."); ?></option>
      </select>
      <input type="submit" class="button" name="subaction" value="<?php echo $sg->translator->_g("Re-index selected"); ?>" />
    </div>
    <?php for($index = $sg->gallery->startat; $index < $sg->gallery->galleryCountSelected()+$sg->gallery->startat; $index++): ?> 
    <div class="sgGallery"><table>
    <tr valign="top">
      <td><input type="checkbox" class="checkbox" name="sgGalleries[<?php echo $sg->gallery->galleries[$index]->idEncoded(); ?>]" /></td>
      <td class="sgGalleryThumbnail"><?php echo $sg->gallery->galleries[$index]->thumbnailHTML() ?></td>
      <td>
        <?php echo $sg->gallery->galleries[$index]->name() ?> - <?php echo $sg->gallery->galleries[$index]->itemCountText() ?><br />
        <?php 
          echo '<a href="'.$sg->formatAdminURL("view",$sg->gallery->galleries[$index]->idEncoded()).'">'.$sg->translator->_g("admin bar|View gallery")."</a> |\n";
          echo '<a href="'.$sg->formatAdminURL("editgallery",$sg->gallery->galleries[$index]->idEncoded()).'">'.$sg->translator->_g("admin bar|Edit gallery")."</a> |\n";
          echo '<a href="'.$sg->formatAdminURL("editpermissions",$sg->gallery->galleries[$index]->idEncoded()).'">'.$sg->translator->_g("admin bar|Access control")."</a> |\n";
          echo '<a href="'.$sg->formatAdminURL("deletegallery",$sg->gallery->galleries[$index]->idEncoded()).'">'.$sg->translator->_g("admin bar|Delete gallery")."</a> |\n";
          echo '<a href="'.$sg->formatAdminURL("newgallery",$sg->gallery->galleries[$index]->idEncoded()).'">'.$sg->translator->_g("admin bar|New subgallery")."</a> |\n";
          echo '<a href="'.$sg->formatAdminURL("reindex",$sg->gallery->galleries[$index]->idEncoded()).'">'.$sg->translator->_g("admin bar|Re-index gallery")."</a> |\n";
          //echo '<a href="'.$sg->formatAdminURL("newimage",$sg->gallery->galleries[$index]->idEncoded()).'">'.$sg->translator->_g("admin bar|New image")."</a> |\n";
          echo '<a href="'.$sg->formatAdminURL("changethumbnail",$sg->gallery->galleries[$index]->idEncoded()).'">'.$sg->translator->_g("admin bar|Change thumbnail")."</a>\n";
        ?>
      </td>
    </tr>
    </table></div>
    <?php endfor; ?> 
    </form>
  </div>
</div>
  
  
<p>
<?php foreach($sg->gallery->detailsArray() as $key => $value): ?>
<strong><?php echo $key ?>:</strong> <?php echo $value ?><br />
<?php endforeach; ?>
</p>
