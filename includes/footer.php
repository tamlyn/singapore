<?php 

 /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *\ 
 *  footer.php - Copyright 2003 Tamlyn Rhodes <tam@zenology.org>       *
 *                                                                     *
 *  This file is part of singapore v0.9.2                              *
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

?>
<!-- end of generated content -->

<div id="footer"><p>
  All rights reserved. Images may not be reproduced in any form
  without the express written permission of the copyright holder.<br />
  Powered by <a href="http://singapore.sourceforge.net/">singapore v0.9.2</a> | 
  <?php
    if(sgGetConfig("show_execution_time")) {
      list($usec, $sec) = explode(" ",$scriptStartTime); 
      $scriptStartTime = (float)$usec + (float)$sec; 
      list($usec, $sec) = explode(" ",microtime()); 
      $scriptEndTime = (float)$usec + (float)$sec; 
    
      $scriptExecTime = floor(($scriptEndTime - $scriptStartTime)*1000);
      echo "Execution time {$scriptExecTime}ms | ";
    } 
  ?>
  <a href="admin.php">admin</a>.
</p></div>

</body>
</html>
