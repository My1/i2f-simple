<?php
function i2flogin($did) { //you need some kind of distinct ID to identify the user, dont use sensitive data as it will be transferred to instant2fa.
  $pub="pk_something";
  $sec="sk_something";
  $b64=base64_encode("$pub:$sec");
  $apiurl = 'https://api.instant2fa.com/verification-requests/';
  $curl=curl_init($apiurl);
  curl_setopt_array($curl,array(
  CURLOPT_HTTPHEADER=> array('Content-Type: application/vnd.api+json',"Authorization: Basic $b64"),
  CURLOPT_POSTFIELDS => '{"data":{"type":"user-access-tokens","id":"","attributes":{"distinct_id":"'.$did.'","hosted_page_url":null}}}',
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
    elseif($status==422) { //we dont have 2FA on this user, return empty string
      return "";
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
