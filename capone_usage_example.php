<?php

require_once 'capone.inc.php';

$capone = new capone(file_get_contents($argv[1]), "var");
$capone->setopt("outmode", "ret_buffer");
$capone->parse();
$ret = $capone->output();

echo "$ret";

?>
