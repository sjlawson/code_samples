<?php
/**
 * @author Samuel Lawson
 * @copyright Bashpole Inc 2013.
 */
 
 // Removed Include statements for security 

/**
 * 
 * @param $orgname
 * @param $apikey
 * @param $page
 * @throws Exception
 * @return json object people:jsonarray
 */
function up_get_contacts_from_batchbook_paged($orgname, $apikey, $page) {
	$url = "https://{$orgname}.batchbook.com/api/v1/people.json?auth_token={$apikey}&page={$page}";
	$request = new HttpRequest($url, HttpRequest::METH_GET);
	$request -> setContentType('application/json');
	
	try {
		$response = $request -> send();
		if ($response -> getResponseCode() == '201' || $response -> getResponseCode() == '200') {
			$result_json = $response->getBody();
			
			return $result_json;
		} else {
			throw new Exception($response -> getBody(), $response -> getResponseCode());
		}
	} catch (HttpException $ex) {
		throw new Exception('Internal Server Error: ' . $ex -> getMessage(), 500);
	}
}

/**
 * Probe batchbook account to test authenticity
 * @param $orgname
 * @param $apikey
 * @throws Exception
 * @return boolean
 */
function up_authenticate_batchbook_account($orgname, $apikey) {
	
	try {
		$result = json_decode(up_get_contacts_from_batchbook_paged($orgname, $apikey, 1));
		if(isset($result->page) && $result->page == 1) {
			return true;
		} else {
			return false;
		}
	} catch (HttpException $ex) {
		throw new Exception('Internal Server Error: ' . $ex -> getMessage(), 500);
	}
	return false;
}

/**
 * 
 * @param string $orgname
 * @param string $apikey
 * @param jsonObject $json_contact
 * @throws Exception
 * @return unknown
 */
function up_post_new_batchbook_contact($orgname, $apikey, $json_contact) {
	$raw_data = json_decode($json_contact, true);
	$url = "https://{$orgname}.batchbook.com/api/v1/people.json?auth_token={$apikey}";
	$request = new HttpRequest($url, HttpRequest::METH_POST);
	$request -> setContentType('application/json');
	$request->setPostFields($raw_data);
	try {
		$response = $request->send();
		if ($response->getResponseCode() == '201' || $response -> getResponseCode() == '200') {
			$result_json = $response->getBody();
				
			return $result_json;
		} else {
			throw new Exception($response->getBody(), $response->getResponseCode());
		}
	} catch (HttpException $ex) {
		throw new Exception('Internal Server Error: ' . $ex->getMessage() , 500);
	}
	
}

/**
 * 
 * @param  $orgname
 * @param  $apikey
 * @param  $json_contact
 * @throws Exception
 * @return unknown
 */
function up_put_edited_batchbook_contact($orgname, $apikey, $json_contact) {
	$raw_data = json_decode($json_contact, true);
	$contact_id = $raw_data['person']['id'];
	$url = "https://{$orgname}.batchbook.com/api/v1/people/{$contact_id}.json?auth_token={$apikey}";
	
	$request = new HttpRequest($url, HttpRequest::METH_PUT);
	$request->setContentType('application/json');
	$request->setPutData(json_encode($raw_data));
	try {
		$response = $request->send();
		if ($response->getResponseCode() == '201' || $response -> getResponseCode() == '200') {
			$result_json = $response->getBody();

			return $result_json;
		} else {
			throw new Exception($response->getBody(), $response->getResponseCode());
		}
	} catch (HttpException $ex) {
		throw new Exception('Internal Server Error: ' . $ex->getMessage() , 500);
	}

}

/**
 * Put a bbContact snapshot in a file on the S3 bucket 
 * @param $cid
 * @param $postdata
 * @throws Exception
 * @return boolean
 * 
 * userid_clid_cid_timestamp.json
 */
function up_save_cfrecord_to_s3bucket($cid, $postdata, $clid, $uid) {
	$rawData = json_decode($postdata);
	$s3 = new S3(AMAZON_ACCESS_KEY, AMAZON_SECRET_ACCESS_KEY, true);
	$uri = $uid.'_'.$clid. '_' . $cid . "_" . strtotime("NOW") . '.json';
	try {
		if($s3->putObjectString($postdata, AMAZON_S3_BATCHBOOK, $uri)) {
			return true;
		} else {
			throw new Exception('Could not save file' ,500);
		}
	} catch (Exception $e) {
		throw new Exception($e->getMessage(), $e->getCode());
	}
}
