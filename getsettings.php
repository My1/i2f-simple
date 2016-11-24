<?php
function geti2fsettings($did) { //disctint ID for user
  $pub="pk_something";
  $sec="sk_something";
  $b64=base64_encode("$pub:$sec");

  $opts = array('http' =>
    array(
    'method'  => 'POST',
    'header' => "Content-Type: application/vnd.api+json"."\n".
                "Authorization: Basic $b64",
    'content' => '{"data":{"type":"user-access-tokens","id":"","attributes":{"distinct_id":"'.$did.'","hosted_page_url":null}}}'
    )
    );

  $url = 'https://api.instant2fa.com/user-access-tokens/';

  $context  = stream_context_create($opts);
  $response = file_get_contents($url, false, $context);

  $data=json_decode($response,true);
  echo $data["data"]["attributes"]["hosted_page_url"]
}
?>
