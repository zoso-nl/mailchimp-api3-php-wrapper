<?php
	public function mailchimp($apikey, $listid = null, $url = null, $method, $data = null){
		$method = strtolower($method);
		$url = strtolower($url);
		$result['reference'] = 'http://developer.mailchimp.com/documentation/mailchimp/reference/overview/'; 
		
		// CONTROLE OP JUISTE DATA
		if(empty($apikey)){
			$result['status'] = 'error'; 
			$result['notice'] = 'Er is geen Apikey opgegeven.'; 
			return $result;
		}
		if(empty($method) || ($method != 'post' && $method != 'get' && $method != 'patch' &&  $method != 'delete' &&  $method != 'put')){
			$result['status'] = 'error'; 
			$result['notice'] = 'Er is geen of geen juiste methode opgegeven. Dit kan zijn: post, get, patch, put of delete.'; 
			return $result;
		}
		if(empty($listid) && ($url != "/lists" && !empty($url))){
			$result['status'] = 'error'; 
			$result['notice'] = 'Er is geen listid opgegeven.'; 
			return $result;
		}
		if(!empty($data) && !is_array($data)){
			$result['status'] = 'error'; 
			$result['notice'] = 'De data die meegestuurd wordt, moet als array worden aangeleverd.'; 
			return $result;
		}
		
		// MAILCHIMP DATACENTER EN URL VOORBEREIDEN
		$dataCenter = substr($apikey,strpos($apikey,'-')+1);
		$url = 'https://'.$dataCenter.'.api.mailchimp.com/3.0/'.$url;

		// DATA OMZETTEN
		$data = json_encode($data);

		//Naar Mailchimp pushen
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_USERPWD, 'user:' . $apikey);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
		if($method == 'post' || $method == 'put'){
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}

		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		
		switch($httpCode) {
			case 200:
			case 201:
			case 202:
				$result['status'] = 'success';
				$result['mailchimp'] = (array) json_decode($response);		
			break;
			case 300:
			case 301:
			case 302:
			case 307:
			case 308:
				$result['status'] = 'error';
				$result['notice'] = 'De url is niet correct. Je wordt omgeleid. (Status: '.$httpCode.')';
				$result['url'] = $url;
				$result['mailchimp'] = (array) json_decode($response);		
				return $result;
			break;
			case 0:
			case 400:
			case 401:
			case 403:
			case 404:
			case 405:
				$result['status'] = 'error';
				$result['notice'] = 'De url is niet correct. Pagina niet gevonden. (Status: '.$httpCode.')';
				$result['url'] = $url;
				$result['mailchimp'] = (array) json_decode($response);		
				return $result;
			break;
		}

		$result = (array) $result;
		return $result;
	}
?>
