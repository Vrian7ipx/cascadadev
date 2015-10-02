<?php 

class ProofController extends \BaseController {

	public function index(){		        
    	extract($_POST);
        $username='firstuser';
		$password='first_password';
		$URL='localhost/cascada_ventas/public/api/v1/url';
		$fields= array(
			'url'=>urlencode('https://faccturavirtual.com'),
			'description'=> urlencode('HELLO'),
			);
		$fields_string="";
		foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
		rtrim($fields_string, '&');

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$URL);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30); //timeout after 30 seconds
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_POST, count($fields));
		curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
		$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);   //get status code
		if(!curl_exec($ch)){
    		die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
		}
		//$result=curl_exec ($ch);


		curl_close ($ch);
	}	
}

?>