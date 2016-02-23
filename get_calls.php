<?php

require_once('functions.php');


$ext = ''; // Dummy value so SQL statement is valid even if $_POST['ext'] is missing.
$pass = '';
$json = ''; // Empty JSON string to build upon


if (isset($_POST['ext']) && isset($_POST['pass'])) {
  $ext = $_POST['ext'];
  $pass = $_POST['pass'];
} else {
  $json .= '{ "errors" : [ { "e_num" : 1, "e_text" : "Missing parameter -- extension or password" ], "calls_curr" : [ ], "calls_hist" : [ ] }';
  echo($json);
  die();
}


// Set up empty lists;
$calls_curr_json = '';
$calls_hist_json = '';
$errors_json = '';

$errors = Array();


if ($pass === $ext) { // XXX Actual authentication to go here instead of crappy "if" statement XXX

// Generate current calls list
//$calls_curr_json = ' { "cid": "Jim South", "number": "02075042267", "start": "1436456013", "end": "" }';

/*
 *
 *
 *	CODE TO COME -- Where is live data stored?
 *
 *
 */

 
// Generate historic calls list from call database (cdb)

// Values set in settings.php

try {
//  $cdb = new PDO('mysql:host='.$cdbhost.';port='.$cdbport.';dbname='.$cdbschema.';charset=utf8', $cdbuser, $cdbpass);
  $cdb = new PDO('mysql:host='.$settings['cdbhost'].';port='.$settings['cdbport'].';dbname='.$settings['cdbschema'].';charset=utf8', $settings['cdbuser'], $settings['cdbpass']);
  $cdb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $cdb->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $Exception) {
  $errors[] = 'Problem connecting to Asterisk server';
}

$sql = '
SELECT
    "(Unknown)" `cid`,
    UNIX_TIMESTAMP(MAX(`calldate`)) `start`,
    UNIX_TIMESTAMP(MAX(`calldate`)) + `duration` `end`,
    LEAST(`src`, `dst`) `number`
FROM
    cdr
WHERE
    (`src` = ' . $ext . ' OR `dst` = ' . $ext . ')
        AND
    (`src` LIKE "0%" OR `dst` LIKE "0%" OR `src` LIKE "+%" OR `dst` LIKE "+%")
GROUP BY `number`
ORDER BY `start` DESC
LIMIT 6;
';

$results = $cdb->query($sql);
$calls = $results->fetchAll(PDO::FETCH_ASSOC);

// Generate lookup table

$count = 0;
foreach ($calls as $call) {
    $calls_hist_json .= ($count++>0?',':'').'{ "cid": "' .lookupNumber($call['number']). '", "number": "' . $call['number'] . '", "start": "' .$call['start']. '", "end": "' .$call['end']. '" }';
}

} else {
  $errors[] = 'Authentication error';
}





// Output JSON
if (count($errors) > 0) {
    foreach ($errors as $key => $error) {
	$errors_json .= '{ "e_num" : ' . $key . ', "e_text" : "' . $error . '" }';
    }
}


$json .= '{ "errors" : [ ' . $errors_json . ' ], "calls_curr" : [ ' . $calls_curr_json . ' ], "calls_hist" : [ ' . $calls_hist_json . ' ] }';
echo($json);
