<?php
/**
 * @copyright Bashpole Inc 2013.
 * @author Samuel Lawson
 */

// Removed Include statements for security 

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'batchbook-functions.php';

function get_contacts_from_batchbook_paged($orgname, $apikey, $page ) {
	try {
		$json = up_get_contacts_from_batchbook_paged($orgname, $apikey, $page ) ;
		send_http_response($json, '200', 'application/json');
	} catch (Exception $ex) {
		send_http_response($ex -> getMessage(), $ex -> getCode(), 'text/plain');
	}
}

function authenticate_batchbook_account($orgname, $apikey ) {
	try {
		$blnResult = up_authenticate_batchbook_account($orgname, $apikey);
		send_http_response($blnResult, '200', 'application/json');
	} catch (Exception $ex) {
		send_http_response($ex -> getMessage(), $ex -> getCode(), 'text/plain');
	}
}

function save_cfrecord_to_s3bucket($cid, $postdata, $clid, $uid) {
	try {
		$bln = up_save_cfrecord_to_s3bucket($cid, $postdata, $clid, $uid);
		send_http_response($bln, '200', 'application/json');
	} catch (Exception $ex) {
		send_http_response($ex -> getMessage(), $ex -> getCode(), 'text/plain');
	}
	
}