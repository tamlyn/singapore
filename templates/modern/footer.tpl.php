</div>
	<div id="sgFooter">
		<p>
  		<?php echo $sg->allRightsReserved(); ?>
  		<?php echo $sg->licenseText(); ?>
  			<br />
  		<?php echo $sg->poweredByText(); ?> 
  		<?php echo $sg->scriptExecTimeText(); ?> |
  		<?php echo $sg->adminLink(); ?>
		</p>
	</div>
<?php
if (!defined('EXTERNAL')) {
	echo '</body>
</html>';
}