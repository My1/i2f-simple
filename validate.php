<?php
function i2fverify($did,$token) { //you need some kind of distinct ID to identify the user, dont use sensitive data as it will be transferred to instant2fa.
  $tol=120; //time tolerance in secs
  $pub="pk_9bb135ef94aa44a7a0f274b2cee9e32d";
  $sec="sk_645fd1759b9c4d06b8409696685a7814";
  $b64=base64_encode("$pub:$sec");
  $apiurl = 'https://api.instant2fa.com/verification-response-tokens/'.$token.'/';
  $curl=curl_init($apiurl);
  curl_setopt_array($curl,array(
  CURLOPT_HTTPHEADER=> array("Authorization: Basic $b64"),
  CURLOPT_RETURNTRANSFER => true
  ));
  $response=curl_exec($curl);
  $cstat=curl_errno($curl);
  $status=curl_getinfo($curl,CURLINFO_HTTP_CODE);
  if($cstat==0) { //no problem, return the I2F URL
    if($status>=200&&$status<=299) {  //I2F answered properly
      $data=json_decode($response,true)["data"];
      if($data["attributes"]["distinct_id"]==$did) { //check the distinct-ID
        if(strtotime($data["attributes"]["created_at"].' UTC')>(time()-$tol)) { // make sure the request isnt too old.
          if($data["attributes"]["status"]=="succeeded") { //check success status
            return true;
          }
          else { //something wrong
            $log=fopen("i2f-log.txt","ab");
            fwrite ($log,"Invoked URL: $apiurl Something went wrong: '$did' was requesting login at ".date("Y-m-d H:i:s")." but it didn't succeed. Status is: ".$data["attributes"]["status"]."\n");
            fclose($log);
            return false;
          }
        }
        else { //too old
          $log=fopen("i2f-log.txt","ab");
          fwrite ($log,"Invoked URL: $apiurl Too late: '$did' was requesting login at ".date("Y-m-d H:i:s")." UTC, but the Validation from I2F was created at ".$data["attributes"]["created_at"].".\n");
          fclose($log);
          return false;
        }
      }
      else { //That's not Your Request!
        $log=fopen("i2f-log.txt","ab");
        fwrite ($log,"Invoked URL: $apiurl Wrong Distinct-ID: '$did' was requesting login but  '".$data["attributes"]["distinct_id"]."' was given by I2F.\n");
        fclose($log);
        return false;
      }
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
