<?php
include_once('simple_html_dom.php');
include_once('Controll.php');
$url = 'http://www.stern.de/reise/deutschland/bahnfahrender-kater-amuesiert-mitreisende-und-facebook-user-7298098.html';

echo get_real_location($url);
$html = new simple_html_dom ();
$html->load ( $store );
$ret_auto_liefrate = $html->find('p');

foreach($ret_auto_liefrate as $item){
	
	//echo  $item->plaintext."/n";
}
	function get_real_location($url) {
		$options['http']['method'] = 'HEAD';
		stream_context_set_default($options); # don't fetch the full page
		$headers = get_headers($url,1);
		$this->lang=$headers['Content-Language'];
		if ( isset($headers[0]) ) {
			if (strpos($headers[0],'301')!==false && isset($headers['Location'])) {
				$location_text = $headers['Location'];
				
				$url = parse_url($url);	
				$location_array = parse_url($location_text);
			
				if ($url['host'] != $location_array['host'])
					return $location_text;
			}
		}
	
		return $url;
	}
	
	//echo'now'.(int)_is_short_url('http://bit.ly/2je0joI');
	

?> 
           