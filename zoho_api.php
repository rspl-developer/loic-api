<?php
// echo '<pre>';
// print_r($_GET);
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

$refresh_token = file_get_contents('zoho_refresh_token.txt');
$access_token = getZohoAccessToken($refresh_token);


updateInventoryItem($access_token);

function updateInventoryItem($access_token){
	
$curl = curl_init();
$sku = $_GET['sku'];
$quantityItem = $_GET['quantityItem'];
$keyItem = $_GET['count'];
$inventoryArray = array();
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

	$itemId = $itemDataArr['items'][0]['item_id'];

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
	echo'=========';
	echo $keyItem;
	// echo '<pre>';
	// print_r($itemDataArrN);
	//die();
	$finalQuantity = $quantityItem - $itemDataArrN['initial_stock'];
	$date = date('Y-m-d', time());
	$inventoryArray['date'] = $date;
	$inventoryArray['reason'] = "Inventory Revaluation";
	$inventoryArray['reference_number'] = "REF-IA-00001";
	$inventoryArray['adjustment_type'] = "quantity";
	$inventoryArray['line_items'][$keyItem]['item_id'] = $itemDataArrN['item']['item_id'];
	$inventoryArray['line_items'][$keyItem]['quantity_adjusted'] = $finalQuantity;
	echo '<pre>';
	print_r($inventoryArray);

	$finalEncodeArr = json_encode($inventoryArray, true);

	echo '<pre>';
	print_r($finalEncodeArr);
die();
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
	    'Authorization: Zoho-oauthtoken 1000.39f4f2ae658db963a91e019b42e2f491.24ba9fa70e5c191d82d03658a1b263ab',
	    'content-type: application/json',
	    'Cookie: BuildCookie_845585240=1; JSESSIONID=5A4B1AB02F4D03819335EF47031A1D8F; _zcsr_tmp=cd69580e-dbdf-49c1-aee5-0b52beb7fced; zalb_f73898f234=98d9ddc8dd356dbaefca0e9e3d7441bb; zomcscook=cd69580e-dbdf-49c1-aee5-0b52beb7fced'
	  ),
	));

	$response = curl_exec($curl);

	curl_close($curl);
	echo $response;
}

