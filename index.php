<?php 

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

	$lat = $_GET['lat'];
	$lon = $_GET['lon'];
	
	$gmapResult = googleMaps($lat,$lon);
	$streetResult = streetEasy($lat,$lon);
	//unWrap($gmapResult,'gmaps');
	//unWrap($streetResult,'streetEasy');
	$response = '';
	
	foreach($gmapResult as $i=>$neighb){
		if($i==0){
			$response.='You are in '.$neighb;
		}else{
			if($i==1){
				$response.=', but you are near '.$neighb;
			} else{
				$response.=' and '.$neighb;
			}				
		}
	}
	
	foreach($streetResult as $neighb){
		echo $neighb;
	}
	
	if ($response==''){
		echo 'You are not in a known neighborhood. Congratulations and don\'t get kidnapped!';
	} else{
		echo $response;
	}

/*
function unWrap($onion,$flavor,$needle,$depth){
	switch ($flavor){
		case 'gmaps':
			if(preg_match('/object|array/',gettype($onion))){
				if(gettype($onion)=='object'){
					if(property_exists($onion,$needle)){
						foreach($onion as $types){
							if($types==$needle){
								var_dump($types);
							}else{
								unWrap($onion->$types,'gmaps','
							}
						}
					}
				}
			}else{
				var_dump($onion);
			}
			break;
		case 'streetEasy':
			echo 'streetEasy';
			break;
	}
}	
*/

function googleMaps($lat,$lon){
	$gmap_curl = curl_init();
	$gmap_opts = array(
		CURLOPT_URL => 'https://maps.googleapis.com/maps/api/geocode/json?latlng='.$lat.','.$lon.'&result_type=neighborhood&key=AIzaSyAxjUSRCMSrJN2K0zJxT1nnsGjWIEwkXiI',
		CURLOPT_RETURNTRANSFER => true
	);
	curl_setopt_array($gmap_curl,$gmap_opts); 
	$gmap_output = json_decode(curl_exec($gmap_curl));	
	curl_close($gmap_curl);
	
	$gmap_result = array();
	
	foreach($gmap_output->results as $k1=>$v1){
		if(preg_match('/object|array/',gettype($v1))){
			foreach($v1 as $v2){
				if(preg_match('/object|array/',gettype($v2))){
					foreach($v2 as $k3=>$v3){
						if(preg_match('/object|array/',gettype($v3))){
							foreach($v3 as $k4=>$v4){
								if($k4=='types'){
									foreach($v4 as $types){
										if($types=='neighborhood'){
											array_push($gmap_result,$v3->short_name);
										}
									}
								}																		
							}
						}					
					}
				}
			}
		}
	}
	
	return array_unique($gmap_result);
}


function streetEasy($lat,$lon){
	$street_curl = curl_init();
	$street_opts = array(
		CURLOPT_URL => 'http://streeteasy.com/nyc/api/areas/for_location?lon='.$lon.'&lat='.$lat.'&key=61ffaa09e340ee6c3db9f26d23554e6764e0fb11&format=json',
		CURLOPT_RETURNTRANSFER => true
	);
	
	curl_setopt_array($street_curl,$street_opts); 
	$street_output = json_decode(curl_exec($street_curl));	
	curl_close($street_curl);
	
	$street_result = array();
	
	if(count($street_output)!==0){
		array_push($street_result,$street_output->name);
	}else{
		array_push($street_result,'Not Found');
	}
	
	return $street_result;

}
?>