
<?php
/**
 * Switch TOR to a new identity.
 **/
function tor_new_identity($tor_ip='127.0.0.1', $control_port='9051', $auth_code=""){
    $fp = fsockopen($tor_ip, $control_port, $errno, $errstr, 30);
    if (!$fp) return false; //can't connect to the control port
   
    fputs($fp, "AUTHENTICATE $auth_code\r\n");
    $response = fread($fp, 1024);
	
    list($code, $text) = explode(' ', $response, 2);
    if ($code != '250') return false; //authentication failed
 
    //send the request to for new identity
    fputs($fp, "signal NEWNYM\r\n");
    $response = fread($fp, 1024);
    list($code, $text) = explode(' ', $response, 2);
    if ($code != '250') return false; //signal failed
 
    fclose($fp);
    return true;
}
 
/**
 * Load the TOR's "magic cookie" from a file and encode it in hexadecimal.
 **/
function tor_get_cookie($filename){
    $cookie = file_get_contents($filename);
    //convert the cookie to hexadecimal
    $hex = '';
    for ($i=0;$i<strlen($cookie);$i++){
        $h = dechex(ord($cookie[$i]));
        $hex .= str_pad($h, 2, '0', STR_PAD_LEFT);
    }
    return strtoupper($hex);
}
function get_punkt_num($num,$platz){
		$num_array=explode(" ",$num);
		return comma_2_punkt($num_array[$platz]);

}
function comma_2_punkt($num){
		$comma_array=explode(",",$num);
		if(count($comma_array) > 1){$num_punkt=$comma_array[0].'.'.$comma_array[1];}
		else {$num_punkt=$comma_array[0];}
		return $num_punkt;
}
function punkt_2_comma($num){
		$comma_array=explode(".",$num);
		if(count($comma_array) > 1){$num_punkt=$comma_array[0].','.$comma_array[1];}
		else {$num_punkt=$comma_array[0];}
		return $num_punkt;
}
function percent($num) {
		$count1 =($num*25)/100;
		$num01=$num+$count1;
		$count2 = ($num01*19)/100;
		$Percent_num=$num01+$count2;
                $Percent_num=round($Percent_num,2);
		//$count = number_format($count2, 0);
		return $Percent_num;
}
Function curl_get_html($url){
		$ch = curl_init();
		$headers = array(
		'User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en; rv:1.9.2.3) Gecko/20100401 MRA 5.6 (build 03278) Firefox/3.6.3 (.NET CLR 3.5.30729)',
		'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
		'Accept-Language: en-us;q=0.7,en;q=0.3',	'Accept-Charset: utf-8;q=0.7,*;q=0.7'
		);
		curl_setopt($ch, CURLOPT_URL,$url);// SET URL FOR THE POST FORM LOGIN
		curl_setopt ($ch, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
		curl_setopt ($ch, CURLOPT_POST,0);// ENABLE HTTP POST
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_ENCODING, "");
		curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
		//curl_setopt($ch, CURLOPT_PROXY, '127.0.0.1:9050');
        //curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
		curl_setopt ($ch, CURLOPT_AUTOREFERER, true);
		
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 120);
		curl_setopt ($ch, CURLOPT_MAXREDIRS, 10);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 10);
		
		$store=curl_exec ($ch);// EXECUTE 1st REQUEST (FORM LOGIN)
               		
		
                if($store === false)
                    {
                      return 'Curl error: ' . curl_error($ch);
                    }
                    else
                    {
                        return $store;
                    }
                    curl_close ($ch);
		unset($ch);
		return $store;

}
function get_Num($str){
    
$numbersOnly = preg_replace ( '#[^\d+]#i' , '' , $str ) ;

 return $numbersOnly;
}
function get_j_num($num){
		$slash_array=explode("-",$num);
		
		$num01=get_Num($slash_array[0]);
		if($slash_array[1]){
                $num02=get_Num($slash_array[1]);
                $num_slash=$num01.'-'.$num02;
				}
				else{
				$num_slash=$num01;
				}
				
		return $num_slash;
}
function get_aritikel_Nummer($Artike_num){
    $artikle_array=explode(",",$Artike_num);
	$artikel=str_replace("'","",$artikle_array[1]);
	return str_replace(" ","",$artikel) ;
}

function build_csv($Herrsteller_Ordner,$Portal_Name,$list_name,$header_array,$data_array){
    $output=fopen($Herrsteller_Ordner.$list_name."_".$Portal_Name."_liste.csv", 'w+');
//print_r($korriegern_liste_contetnt);
    if($header_array!='Null'){
        foreach($header_array as $header){
           $first_str.= "'".$header."';";
        }
        $str.=$first_str."\n";
        unset($first_str);
    }
                foreach($data_array as $row){
                    foreach($row as $key=> $_artikel){
                       $data_array[$key]=str_replace("'","",$_artikel);
                       if($data_array[$key]==''){
                         $first_str.=';';  
                       }
                       else{
                     $first_str.="'".$data_array[$key]."';";
                       }
                    
                    }
                //fputcsv($korrektor_output, $korriegert_artikel,";","'");
                 $str.=$first_str."\n";
                 unset($first_str);
                }
                fwrite($output,$str);
                fclose($output);
                 unset($str);
}
?>