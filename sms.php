<?php
require_once("includes/config.php");
header("Content-Type: text/xml; charset=utf-8");


// $client = new http\Client;
// $request = new http\Client\Request;
// $request->setRequestUrl('https://smsvas.vlserv.com/KannelSending/service.asmx');
// $request->setRequestMethod('POST');
// $body = new http\Message\Body;
// $body->append(new http\QueryString(array(
//   'Username' => 'Adhoc',
//   'Password' => '4LumD99yiP',
//   'SMSText' => 'text',
//   'SMSLang' => 'e',
//   'SMSSender' => 'Markt',
//   'SMSReceiver' => '01111758873')));$request->setBody($body);
// $request->setOptions(array());
// $request->setHeaders(array(
//   'Content-Type' => 'application/x-www-form-urlencoded'
// ));
// $client->enqueue($request)->send();
// $response = $client->getResponse();
// echo $response->getBody();




// $curl = curl_init();

// curl_setopt_array($curl, array(
//   CURLOPT_URL => "https://smsvas.vlserv.com/KannelSending/service.asmx",
//   CURLOPT_RETURNTRANSFER => true,
//   CURLOPT_ENCODING => "",
//   CURLOPT_MAXREDIRS => 10,
//   CURLOPT_TIMEOUT => 0,
//   CURLOPT_FOLLOWLOCATION => true,
//   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//   CURLOPT_CUSTOMREQUEST => "POST",
//   CURLOPT_POSTFIELDS => "Username=Adhoc&Password=4LumD99yiP&SMSText=text&SMSLang=e&SMSSender=Markt&SMSReceiver=01111758873",
//   CURLOPT_HTTPHEADER => array(
//     "Content-Type: application/x-www-form-urlencoded"
//   ),
// ));

// $response = curl_exec($curl);

// curl_close($curl);
// echo $response;

function send_sms($phone)
{
  try {

     $sms = urlencode(SMS);    
     $phone = urlencode($phone);


    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL =>
    "http://smsvas.vlserv.com/kannelsending/service.asmx/SendSMS?Username=Adhoc&Password=4LumD99yiP&SMSText=".$sms."&SMSLang=e&SMSSender=mrkt&SMSReceiver=${phone}",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "utf-8",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    echo $response;
  } catch (Exception $e) {
    echo $e->getMessage();
  }
}

