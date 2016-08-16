<?php


#defined('MOODLE_INTERNAL') || die;
echo 'This';
require_once(dirname(__FILE__) . '/../../../config.php');
require_once(dirname(__FILE__) . '/../lib.php');

$elms_id = $_GET['elmsid'];

echo $elms_id;

?>
