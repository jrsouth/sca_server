<?php

// Check for ext and reg_id
if (!(!!$_POST['ext'] && !!$_POST['reg_id'])) {
  die('{"success" : false, "message" : "Missing POST variables (ext and/or reg_id)."}'."\n");
}

$ext = $_POST['ext'];
$reg_id = $_POST['reg_id'];

// Check for existing reg_id
/* Not implemented yet
 *
 *
 *
 *
 *
 *
 */

 
// Add to list of registrations

file_put_contents('registrations.csv', "\n".$ext.','.$reg_id, FILE_APPEND);

// Error handling needed
// die('{"success" : false, "message" : "Error adding registration. Check file permissions."}');

echo('{"success" : true, "message" : "Added registration for extension '.$ext.'."}'."\n");