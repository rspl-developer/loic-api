<?php
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

require_once "Classes/PHPExcel.php";

function checkZohoAccessToken($access_token){
  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://www.zohoapis.com/inventory/v1/organizations',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
      'Authorization: Bearer '.$access_token,
      'Cookie: JSESSIONID=C39B1E708E7C12AFDA98DD6D020A8367; _zcsr_tmp=feed0e01-d97d-4698-946e-54d1eaeebbb9; zalb_f73898f234=40c0d0b11ac9e6227fa9cc54a5a3755e; zomcscook=feed0e01-d97d-4698-946e-54d1eaeebbb9'
    ),
  ));

  $response = curl_exec($curl);

  curl_close($curl);
  $data = json_decode($response, true);

  return $data['message'];

}

function getZohoAccessToken($refreshToken = null){

  $access_token = file_get_contents('zoho_access_token.txt');
  $isSuccess = checkZohoAccessToken($access_token);

  $returndata = '';
  if($isSuccess != 'success'){
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://accounts.zoho.com/oauth/v2/token?refresh_token='.$refreshToken.'&client_id=1000.NMZHILY0O7URG8MUTB9SWIYGWAR6TU&client_secret=5fa92cd80da557c5685c81a2f06d6767c7ac0c54f6&redirect_uri=https%3A%2F%2Fdemo.redsymbolhost.com%2Floic-api%2Fzoho_api.php&grant_type=refresh_token',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_HTTPHEADER => array(
        'Cookie: _zcsr_tmp=7d068eee-fad4-4d88-bed4-26d03e05984f; iamcsr=7d068eee-fad4-4d88-bed4-26d03e05984f; zalb_b266a5bf57=9f371135a524e2d1f51eb9f41fa26c60'
      ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);

    $data = json_decode($response, true);

    //echo '<pre>';
    //print_r($data);

    if($data['error']){
      $returndata = $data['error'];
    }else{
      $returndata = $data['access_token'];
      file_put_contents('zoho_access_token.txt', $data['access_token']);
    }
  }else{
    $returndata = $access_token;
  }

  return $returndata;

}

$inventoryUpdatesTicketsData = array();

function getTicketData($cursor = null){

  //echo 'cursor: '.$cursor.'<br/>';

  if($cursor){
    //$apiUrl = 'https://foromshop.gorgias.com/api/views/444416/items?limit=100&cursor='.$cursor;
  }else{
    $apiUrl = 'https://foromshop.gorgias.com/api/views/444416/items?limit=100';
  }
  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => $apiUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
      'Authorization: Basic cmFjaGFuYS5zYXJhc3dhdDExQGdtYWlsLmNvbTozYmY1NWQ1ZDIxMTUyMjM0ZWIzODVlOWJlMjIyYWZmYTgwMWQyM2NjOWM3MGEwZjAwYmQ2MGExNzVjMmFmODQy'
    ),
  ));

  $response = curl_exec($curl);
  curl_close($curl);

  $gorgiasData = json_decode($response, true);

// echo '<pre>';
// print_r($gorgiasData);
  //die();
  $i=0;
  foreach($gorgiasData['data'] as $gorD){
  
    if($gorD['is_unread']){
      //file_put_contents("is_unread.text", $gorD['id'], FILE_APPEND);
      $inventoryUpdatesTicketsData[] = $gorD['id'];
    }
    
    /*if($i == (count($gorgiasData['data'])-1)){
      if($gorgiasData['meta']['next_cursor']){
        //getTicketData($gorgiasData['meta']['next_cursor']);
      }
    }*/
    
    $i++;

  }

  return $inventoryUpdatesTicketsData;
}
$refresh_token = file_get_contents('zoho_refresh_token.txt');
$access_token = getZohoAccessToken($refresh_token);
//die();
/*$i=0;
$file = fopen('Forom_US_Inventory.csv', 'r');
while (($line = fgetcsv($file)) !== FALSE) {
  //$line is an array of the csv elements
  if($i != 0){
    print_r($line);
    die();
  }
  $i++;
}
fclose($file);

die();*/

$inventoryUpdatesItemsArr = getTicketData();
//file_put_contents("inventory_update_item.php", print_r($inventoryUpdatesItemsArr, true));
foreach($inventoryUpdatesItemsArr as $ticket){

  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://foromshop.gorgias.com/api/tickets/'.$ticket,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
      'Authorization: Basic cmFjaGFuYS5zYXJhc3dhdDExQGdtYWlsLmNvbTozYmY1NWQ1ZDIxMTUyMjM0ZWIzODVlOWJlMjIyYWZmYTgwMWQyM2NjOWM3MGEwZjAwYmQ2MGExNzVjMmFmODQy'
    ),
  ));

  $response = curl_exec($curl);
  curl_close($curl);

  $ticketData = json_decode($response, true);
//   echo '<pre>';
//   print_r($ticketData);
// die();
  if(!empty($ticketData['messages'])){
    foreach($ticketData['messages'] as $message){
      if(!empty($message['attachments'])){
        foreach($message['attachments'] as $attachment){

          $aUrl = $attachment['url'];
          $aUrlArr = explode('uploads.gorgias.io', $aUrl);

          if($aUrlArr[1]){

            $getfile = downloadAttachement($aUrlArr[1], $attachment['name']);

            if($getfile){

              $inventoryArray = array();
              $x = 0;
              $extension = explode('.', $getfile);
              
              if($extension[1] == 'csv' || $extension[1] == 'CSV'){
                //echo'Csv';
                $file = fopen($getfile, 'r');
                //$file = fopen('Forom_US_Inventory.csv', 'r');
                $headerLine = true;
                $date = date('Y-m-d', time());
                $inventoryArray['date'] = $date;
                $inventoryArray['reason'] = "Inventory Revaluation";
                $inventoryArray['reference_number'] = "REF-IA-00001";
                $inventoryArray['adjustment_type'] = "quantity";

                while (($line = fgetcsv($file, 1000, ",")) !== FALSE) {
                  // if ($x == 1) {
                  //   break;
                  // }
                  $sku = '';
                  $itemQuantity = '';
                  $vendorEmail = $ticketData['customer']['email'];
                  if($headerLine) { $headerLine = false; }
                  else {
                    //$line is an array of the csv elements
                    // echo'<pre>';
                    // print_r($line);
                    if($vendorEmail == 'donotreply@globalviews.com'){
                      $sku = $line[0];
                      $itemQuantity = $line[6];
                    }elseif ($vendorEmail == 'noreplyemail@momeni.com') {
                      $sku = $line[0];
                      $itemQuantity = $line[7];
                    }elseif ($vendorEmail == 'productdatamanagement@moeshome.com') {
                      $sku = $line[0];
                      $itemQuantity = $line[10];
                    }elseif ($vendorEmail == 'michelle@ameico.com') {
                      $sku = $line[0];
                      $itemQuantity = $line[2];
                    }elseif ($vendorEmail == 'jasmine@normode.com') {
                      $sku = $line[0];
                      $itemQuantity = $line[2];
                    }elseif ($vendorEmail == 'anders.hansen@fermliving.com') {
                      $sku = $line[3];
                      $itemQuantity = $line[5];
                    }elseif ($vendorEmail == 'orders@jamieyoung.com') {
                      $sku = $line[1];
                      $itemQuantity = $line[2];
                    }elseif ($vendorEmail == 'sophie@massimo.dk') {
                      $sku = $line[0];
                      $itemQuantity = $line[2];
                    }elseif ($vendorEmail == 'noreplyemail@momeni.com') {
                      $sku = $line[0];
                      $itemQuantity = $line[7];
                    }elseif ($vendorEmail == 'ecom@noirfurniturela.com') {
                      $sku = $line[0];
                      $itemQuantity = $line[2];
                    }elseif ($vendorEmail == 'ecomm2@nuevo-ca.com') {
                      $sku = $line[2];
                      $itemQuantity = $line[5];
                    }elseif ($vendorEmail == 'customercare@sunatsix.com') {
                      $sku = $line[0];
                      $itemQuantity = $line[4];
                    }elseif ($vendorEmail == '3dvr@unionhomefurniture.com') {
                      $sku = $line[0];
                      $itemQuantity = $line[3];
                    }else{
                      $sku = $line[0];
                      $itemQuantity = $line[10];
                    }

                    $curl = curl_init();
                      curl_setopt_array($curl, array(
                        CURLOPT_URL => 'https://www.zohoapis.com/inventory/v1/items?sku='.$sku.'&organization_id=845585240&per_page=1',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'GET',
                        CURLOPT_HTTPHEADER => array(
                          'Authorization: Bearer '.$access_token,
                          'Cookie: BuildCookie_845585240=1; JSESSIONID=C39B1E708E7C12AFDA98DD6D020A8367; _zcsr_tmp=feed0e01-d97d-4698-946e-54d1eaeebbb9; zalb_f73898f234=40c0d0b11ac9e6227fa9cc54a5a3755e; zomcscook=feed0e01-d97d-4698-946e-54d1eaeebbb9'
                        ),
                      ));

                      $response = curl_exec($curl);
                      curl_close($curl);
                      
                      $itemDataArr = json_decode($response, true);
                      echo '<pre>';
                      print_r($itemDataArr);
                      $itemId = $itemDataArr['items'][0]['item_id'];
                      if($itemId){
                      $curl = curl_init();

                      curl_setopt_array($curl, array(
                        CURLOPT_URL => 'https://www.zohoapis.com/inventory/v1/items/'.$itemId.'?organization_id=845585240',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'GET',
                        CURLOPT_HTTPHEADER => array(
                          'Authorization: Zoho-oauthtoken '.$access_token,
                          'content-type: application/json',
                          'Cookie: BuildCookie_845585240=1; JSESSIONID=F266591F12DCB981A84325D3E2C72D02; _zcsr_tmp=b8357d69-40c5-44d7-8826-a2ad6aecbfe7; zalb_f73898f234=40c0d0b11ac9e6227fa9cc54a5a3755e; zomcscook=b8357d69-40c5-44d7-8826-a2ad6aecbfe7'
                        ),
                      ));

                      $response = curl_exec($curl);

                      curl_close($curl);

                      $itemDataArrN = json_decode($response, true);
                        // echo'=========';
                        // echo '<pre>';
                        // print_r($itemDataArrN);
                        // die();
                        $itemIdd = $itemDataArrN['item']['item_id'];

                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                          CURLOPT_URL => 'https://www.zohoapis.com/inventory/v1/items/'.$itemIdd.'?organization_id=845585240',
                          CURLOPT_RETURNTRANSFER => true,
                          CURLOPT_ENCODING => '',
                          CURLOPT_MAXREDIRS => 10,
                          CURLOPT_TIMEOUT => 0,
                          CURLOPT_FOLLOWLOCATION => true,
                          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                          CURLOPT_CUSTOMREQUEST => 'PUT',
                          CURLOPT_POSTFIELDS =>'{
                                "status": "active"
                        }',
                          CURLOPT_HTTPHEADER => array(
                            'content-type: application/json',
                            'Authorization: Zoho-oauthtoken '.$access_token,
                            'Cookie: BuildCookie_845585240=1; JSESSIONID=7CF35EAC419FABCF9C2B8A32A036A5AA; _zcsr_tmp=c7177af9-264e-4d5d-bb51-373f9590f079; zalb_f73898f234=5a419c9ed30867d7b36a07c3110fd083; zomcscook=c7177af9-264e-4d5d-bb51-373f9590f079'
                          ),
                        ));

                        $response = curl_exec($curl);

                        curl_close($curl);
                        echo $response;


                        $finalQuantity = $itemQuantity - $itemDataArrN['initial_stock'];
                        if($finalQuantity > 0){
                          $inventoryArray['line_items'][] = array('item_id'=>$itemIdd, 'quantity_adjusted'=>$finalQuantity);
                        }
                    }
                      //$x++;
                  }
                }
                // echo '<pre>';
                // print_r($inventoryArray);
                $finalEncodeArr = json_encode($inventoryArray);
                $curl = curl_init();

                curl_setopt_array($curl, array(
                  CURLOPT_URL => 'https://www.zohoapis.com/inventory/v1/inventoryadjustments?organization_id=845585240',
                  CURLOPT_RETURNTRANSFER => true,
                  CURLOPT_ENCODING => '',
                  CURLOPT_MAXREDIRS => 10,
                  CURLOPT_TIMEOUT => 0,
                  CURLOPT_FOLLOWLOCATION => true,
                  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                  CURLOPT_CUSTOMREQUEST => 'POST',
                  CURLOPT_POSTFIELDS => $finalEncodeArr,
                  CURLOPT_HTTPHEADER => array(
                    'Authorization: Zoho-oauthtoken '.$access_token,
                    'content-type: application/json',
                    'Cookie: BuildCookie_845585240=1; JSESSIONID=5A4B1AB02F4D03819335EF47031A1D8F; _zcsr_tmp=cd69580e-dbdf-49c1-aee5-0b52beb7fced; zalb_f73898f234=98d9ddc8dd356dbaefca0e9e3d7441bb; zomcscook=cd69580e-dbdf-49c1-aee5-0b52beb7fced'
                  ),
                ));

                $response = curl_exec($curl);

                curl_close($curl);
                echo'===== Adjust Inventory =====';
                echo $response;

                fclose($file);
              }elseif($extension[1] == 'xlsx'){
                echo'Xlsx';
                $date = date('Y-m-d', time());
                $inventoryArray['date'] = $date;
                $inventoryArray['reason'] = "Inventory Revaluation";
                $inventoryArray['reference_number'] = "REF-IA-00001";
                $inventoryArray['adjustment_type'] = "quantity";

                $vendorEmail = $ticketData['customer']['email'];
                //echo $getfile;
                $path = $getfile;
                $reader = PHPExcel_IOFactory::createReaderForFile($path);
                $excel_Obj = $reader->load($path);

                $worksheet = $excel_Obj->getSheet('0');

                $lastRow = $worksheet->getHighestRow();
                $columnCount = $worksheet->getHighestDataColumn();
                $columncount_number = PHPExcel_cell::columnIndexFromString($columnCount);

                $itemSkuQty = array();
                $itemQTY = '';
                $i = 0;

                for($row=3;$row<=$lastRow;$row++){

                  if($i == 10){
                    break;
                  }
                  for($col=0;$col<=$columncount_number;$col++){
                    //echo $worksheet->getCell(PHPExcel_cell::stringFromColumnIndex($col).$row)->getValue();
                    if($col == 0){
                      $itemSkuQty[$i]['sku'] = $worksheet->getCell(PHPExcel_cell::stringFromColumnIndex($col).$row)->getValue();
                    }
                    if($col == 2){
                      $itemSkuQty[$i]['qty'] = $worksheet->getCell(PHPExcel_cell::stringFromColumnIndex($col).$row)->getValue();
                    }
                  }
                  $i++;
                }
                // echo'<pre>';
                // print_r($itemSkuQty);
                foreach ($itemSkuQty as $key => $value) {
                  // code...
                  $sku = $value['sku'];
                  $qty = $value['qty'];

                  $curl = curl_init();
                      curl_setopt_array($curl, array(
                        CURLOPT_URL => 'https://www.zohoapis.com/inventory/v1/items?sku='.$sku.'&organization_id=845585240&per_page=10',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'GET',
                        CURLOPT_HTTPHEADER => array(
                          'Authorization: Bearer '.$access_token,
                          'Cookie: BuildCookie_845585240=1; JSESSIONID=C39B1E708E7C12AFDA98DD6D020A8367; _zcsr_tmp=feed0e01-d97d-4698-946e-54d1eaeebbb9; zalb_f73898f234=40c0d0b11ac9e6227fa9cc54a5a3755e; zomcscook=feed0e01-d97d-4698-946e-54d1eaeebbb9'
                        ),
                      ));

                      $response = curl_exec($curl);
                      curl_close($curl);
                      
                      $itemDataArr = json_decode($response, true);
                      // echo '<pre>';
                      // print_r($itemDataArr);
                      $itemId = $itemDataArr['items'][0]['item_id'];
                      
                      //if($itemId){
                      $curl = curl_init();

                      curl_setopt_array($curl, array(
                        CURLOPT_URL => 'https://www.zohoapis.com/inventory/v1/items/'.$itemId.'?organization_id=845585240',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'GET',
                        CURLOPT_HTTPHEADER => array(
                          'Authorization: Zoho-oauthtoken '.$access_token,
                          'content-type: application/json',
                          'Cookie: BuildCookie_845585240=1; JSESSIONID=F266591F12DCB981A84325D3E2C72D02; _zcsr_tmp=b8357d69-40c5-44d7-8826-a2ad6aecbfe7; zalb_f73898f234=40c0d0b11ac9e6227fa9cc54a5a3755e; zomcscook=b8357d69-40c5-44d7-8826-a2ad6aecbfe7'
                        ),
                      ));

                      $response = curl_exec($curl);

                      curl_close($curl);

                      $itemDataArrN = json_decode($response, true);
                        // echo'=========';
                        // echo '<pre>';
                        // print_r($itemDataArrN);
                        // die();
                        echo $itemIdd = $itemDataArrN['item']['item_id'];

                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                          CURLOPT_URL => 'https://www.zohoapis.com/inventory/v1/items/'.$itemIdd.'?organization_id=845585240',
                          CURLOPT_RETURNTRANSFER => true,
                          CURLOPT_ENCODING => '',
                          CURLOPT_MAXREDIRS => 10,
                          CURLOPT_TIMEOUT => 0,
                          CURLOPT_FOLLOWLOCATION => true,
                          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                          CURLOPT_CUSTOMREQUEST => 'PUT',
                          CURLOPT_POSTFIELDS =>'{
                                "status": "active"
                        }',
                          CURLOPT_HTTPHEADER => array(
                            'content-type: application/json',
                            'Authorization: Zoho-oauthtoken '.$access_token,
                            'Cookie: BuildCookie_845585240=1; JSESSIONID=7CF35EAC419FABCF9C2B8A32A036A5AA; _zcsr_tmp=c7177af9-264e-4d5d-bb51-373f9590f079; zalb_f73898f234=5a419c9ed30867d7b36a07c3110fd083; zomcscook=c7177af9-264e-4d5d-bb51-373f9590f079'
                          ),
                        ));

                        $response = curl_exec($curl);

                        curl_close($curl);
                        //echo $response;

                        
                        $finalQuantity = $qty - $itemDataArrN['initial_stock'];
                        if($finalQuantity > 0){
                          $inventoryArray['line_items'][] = array('item_id'=>$itemIdd, 'quantity_adjusted'=>$finalQuantity);
                        }
                    //}

                }
                // echo '<pre>';
                // print_r($inventoryArray);
                $finalEncodeArr = json_encode($inventoryArray);
                $curl = curl_init();

                curl_setopt_array($curl, array(
                  CURLOPT_URL => 'https://www.zohoapis.com/inventory/v1/inventoryadjustments?organization_id=845585240',
                  CURLOPT_RETURNTRANSFER => true,
                  CURLOPT_ENCODING => '',
                  CURLOPT_MAXREDIRS => 10,
                  CURLOPT_TIMEOUT => 0,
                  CURLOPT_FOLLOWLOCATION => true,
                  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                  CURLOPT_CUSTOMREQUEST => 'POST',
                  CURLOPT_POSTFIELDS => $finalEncodeArr,
                  CURLOPT_HTTPHEADER => array(
                    'Authorization: Zoho-oauthtoken '.$access_token,
                    'content-type: application/json',
                    'Cookie: BuildCookie_845585240=1; JSESSIONID=5A4B1AB02F4D03819335EF47031A1D8F; _zcsr_tmp=cd69580e-dbdf-49c1-aee5-0b52beb7fced; zalb_f73898f234=98d9ddc8dd356dbaefca0e9e3d7441bb; zomcscook=cd69580e-dbdf-49c1-aee5-0b52beb7fced'
                  ),
                ));

                $response = curl_exec($curl);

                curl_close($curl);
                echo'===== Adjust Inventory =====';
                echo $response;
              }
            }
          }
        }
      }
    }
  }

  //die();
}


function downloadAttachement($attchementUrl, $name){

  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://foromshop.gorgias.com/api/attachment/download'.$attchementUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
      'Authorization: Basic cmFjaGFuYS5zYXJhc3dhdDExQGdtYWlsLmNvbTozYmY1NWQ1ZDIxMTUyMjM0ZWIzODVlOWJlMjIyYWZmYTgwMWQyM2NjOWM3MGEwZjAwYmQ2MGExNzVjMmFmODQy'
    ),
  ));
  $response = curl_exec($curl);
  curl_close($curl);
  file_put_contents($name, $response);

  return $name;
}

