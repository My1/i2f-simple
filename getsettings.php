<?php
function i2fconfig($did) { //disctint ID for user
  $pub="pk_something";
  $sec="sk_something";
  $b64=base64_encode("$pub:$sec");
  $apiurl = 'https://api.instant2fa.com/user-access-tokens/';
  $curl=curl_init($apiurl);
  curl_setopt_array($curl,array(
  CURLOPT_HTTPHEADER=> array('Content-Type: application/vnd.api+json',"Authorization: Basic $b64"),
  CURLOPT_POSTFIELDS => '{"data":{"attributes":{"distinct_id":"'.$did.'"}}}',
  CURLOPT_RETURNTRANSFER => true
  ));
  $response=curl_exec($curl);
  $cstat=curl_errno($curl);
  $status=curl_getinfo($curl,CURLINFO_HTTP_CODE);
  if($cstat==0) { //no problem, return the I2F URL
    if($status>=200&&$status<=299) {
      $data=json_decode($response,true);
      $url=$data["data"]["attributes"]["hosted_page_url"];
      return $data["data"]["attributes"]["hosted_page_url"];
    }
    else { //something is wrong with i2f. log the error and return false
      $log=fopen("i2f-log.txt","ab");
      fwrite ($log,"Pubkey: $pub, Secret Key: $sec Distinct-ID: $did, Response: $response\n");
      fclose($log);
      return false;
    }
  }
  else{ //curl screwed up. log the error and return false
    $log=fopen("i2f-log.txt","ab");
    fwrite ($log,"Pubkey: $pub, Secret Key: $sec Distinct-ID: $did, Invoked URL: $apiurl cURL Error: $cstat / ".curl_error($curl)."\n");
    fclose($log);
    return false;
  }
}
?>
