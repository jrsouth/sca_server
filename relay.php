<?php

require('functions.php');

debug_log('"' . urldecode(http_build_query($_POST)) . '"');

// Check for reg_id
// --> Validation?
if (!$_POST['reg_id']) {
  die('{"success" : false, "message" : "No GCM Registration ID found."}');
}

// Set up variables
$api_key = 'AIzaSyBcz45bu5uFJmTP1JoM2JaWC5fCvvrZuLo';
$project_id = 286602291763;

$cid = !!$_POST['cid']?$_POST['cid']:'Unknown caller';
$number = !!$_POST['number']?$_POST['number']:'Unknown number';
$reg_id = !!$_POST['reg_id']?$_POST['reg_id']:null;

$url = 'https://android.googleapis.com/gcm/send';


// Build request components

$data = array(
  "cid" => $cid,
  "number" => $number,
  );

$post = array(
  'registration_ids'  => array($reg_id),
  'data'              => $data,
  );

$headers = array( 
  'Authorization: key=' . $api_key,
  'Content-Type: application/json'
  );




// Send it

$ch = curl_init();

curl_setopt( $ch, CURLOPT_URL, $url );
curl_setopt( $ch, CURLOPT_POST, true );
curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $post ) );

$result = curl_exec( $ch );
curl_close( $ch );

// Don't really care about whether it succeeded or not -- only worth trying once due to transient nature of calls
