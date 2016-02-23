<?php

// Get shared functions (mostly for the number lookup)
require_once('functions.php');


// Add IP lockdown?
// XXXX TODO


// Log to file for debug
debug_log('"' . (!!$_POST['ext']?$_POST['ext']:'NO_EXT') . '", "' . (!!$_POST['number']?$_POST['number']:'NO_NUMBER') . '"');


// We need both 'ext' and 'number' sent in order to work, die if they're not both there
if (!(!!$_POST['ext'] && !!$_POST['number'])) {
  die('{"success" : false, "message" : "Missing POST variables (ext and/or number)."}'."\n");
}




// Set relay URL
$url = 'http://phones.bloodwise.org.uk/sca/relay';


// Populate CID field
$cid = lookupNumber($_POST['number']);

// Notify each extension in comma-separated list
foreach (explode(',', $_POST['ext']) as $ext) {
  if ($ext != '') {
    $reg_ids = get_reg_ids($ext);

    // Send notification(s) via relay
    foreach ($reg_ids as $reg_id) {
      send_message($cid, $_POST['number'], $reg_id, $url);
    }
  }
}





// -----------------------------------------------------------------
// -----------------------------------------------------------------
// -----------------------------------------------------------------


function get_reg_ids ($ext) {

      $lookup_table = array();

      $csvData = file_get_contents('registrations.csv');
      $lines = explode(PHP_EOL, $csvData);
      foreach ($lines as $line) {
	  $lookup_table[] = str_getcsv($line);
      }

      $reg_ids = array();
      foreach ($lookup_table as $registration) {
	if ($registration[0] === $ext) {
	  $reg_ids[] = $registration[1];
	}
      }
      return($reg_ids);
}



function send_message ($cid, $number, $reg_id, $url) {

// Build POST data string
$post_string = 'cid=' . urlencode($cid) . '&number=' . urlencode($number) . '&reg_id=' . urlencode($reg_id);

// Send it
$ch = curl_init();
curl_setopt( $ch, CURLOPT_URL, $url);
curl_setopt( $ch, CURLOPT_POST, true);
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt( $ch, CURLOPT_POSTFIELDS, $post_string );
$result = curl_exec( $ch );


//try {
$result = json_decode($result);
print_r($result);
//} catch (Exception $err) {
//echo($err);
//}



curl_close( $ch );
// Don't really care about whether it succeeded or not -- only worth trying once due to transient nature of calls
// ...Although should handle failures in order to clean out the list of registration IDs when needed.

}
