<?php
// Get settings
require_once('settings.php');

// Initialise global values
$ciddb = null;




// Function for quick writing to debug log
function debug_log($msg = 'NO MESSAGE') {
  file_put_contents('debug.log', date('Y-m-d H:i:s').' ['.$_SERVER['REMOTE_ADDR'].'] '.($_SERVER['REQUEST_URI']).' --> '.$msg."\n", FILE_APPEND);
}




function lookupNumber ($number) { // Inefficient! Should probably do a single DB lookup "IN (xxx,xxx,xxx)" then match within PHP.
global $settings, $ciddb;

$error = 0;

try {

if ($ciddb == null) {

    $ciddb = new PDO('mysql:host='.$settings['ciddbhost'].';port='.$settings['ciddbport'].';dbname='.$settings['ciddbschema'].';charset=utf8', $settings['ciddbuser'], $settings['ciddbpass']);
    $ciddb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $ciddb->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

}

  $sql = 'SELECT civicrm_contact.display_name cid, civicrm_contact.id civi_id, civicrm_phone.phone number FROM civicrm_phone LEFT JOIN civicrm_contact ON civicrm_phone.contact_id = civicrm_contact.id WHERE civicrm_contact.is_deleted = 0 AND civicrm_phone.phone_numeric = "'.$number.'" GROUP BY `civicrm_contact`.`id`';

  $results = $ciddb->query($sql);

} catch (PDOException $Exception) {
  $error += 1;
}

if ($error > 0) {
return('(Lookup Failed)');
} else {

  switch ($results->rowCount()) {
  case 0  : return('(Unmatched)');
  case 1  : $cids = $results->fetchAll(PDO::FETCH_ASSOC);
            return($cids[0]['cid']);
  default : return('(Multiple matches)');
}

}



}
