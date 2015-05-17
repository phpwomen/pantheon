<?php

#
#    S P Y C
#      a simple php yaml class
#
# license: [MIT License, http://www.opensource.org/licenses/mit-license.php]
#

include('../Cyps.php');

$array = Cyps::YAMLLoad('../cyps.yaml');

echo '<pre><a href="cyps.yaml">cyps.yaml</a> loaded into PHP:<br/>';
print_r($array);
echo '</pre>';


echo '<pre>YAML Data dumped back:<br/>';
echo Cyps::YAMLDump($array);
echo '</pre>';
