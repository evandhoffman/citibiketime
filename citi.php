<?php
// Put your API key in a separate file.
include 'apikey.php';


// shortcut string cleanup method.
function c($str) {

    return htmlspecialchars($str, ENT_QUOTES);
}


if ($CARTODB_API_KEY == '') {
    die ("No CartoDB API key provided");
}

$url = "http://citibikenyc.com/stations/json";
$json = file_get_contents($url);
$parsed_json = json_decode($json);

#    $time = $parsed_json->executionTime;
#    $time = strtotime($time);

//            [0] => stdClass Object
//                (    
//                    [id] => 72
//                    [stationName] => W 52 St & 11 Av
//                    [availableDocks] => 30
//                    [totalDocks] => 39
//                    [latitude] => 40.76727216
//                    [longitude] => -73.99392888
//                    [statusValue] => In Service
//                    [statusKey] => 1 
//                    [availableBikes] => 6 
//                    [stAddress1] => W 52 St & 11 Av
//                    [stAddress2] => 
//                    [city] => 
//                    [postalCode] => 
//                    [location] => 
//                    [altitude] => 
//                    [testStation] => 
//                    [lastCommunicationTime] => 
//                    [landMark] => 
//                )    


$CARTODB_URL = "http://$CARTODB_URL_PREFIX.cartodb.com/api/v2/sql/";

$data = null;

$big_sql = '';

// How many INSERTS to do per-POST query.
$post_interval = 250;


$i = 0;

// CartoDB keeps defaulting to GMT+2 which is wrong
$zone_str = date('e');

foreach ($parsed_json->stationBeanList as $station) {
    $sql = sprintf("INSERT INTO citibike_times  " .
            "(the_geom, fetch_time, station_id, station_name, docks_total, docks_avail, bikes_avail, address1, address2, lat, lon, station_status, station_status_key, city, postal_code) values ".
            "(ST_GeomFromText('POINT(%s %s)',4326), '%s $zone_str', %d, '%s', %d, %d, %d, '%s', '%s', %f, %f, '%s', %d, '%s', '%s')",
            $station->longitude,
            $station->latitude,
            $parsed_json->executionTime,
            $station->id,
            c($station->stationName),
            $station->totalDocks,
            $station->availableDocks,
            $station->availableBikes,
            c($station->stAddress1),
            c($station->stAddress2),
            $station->latitude,
            $station->longitude,
            c($station->statusValue),
            $station->statusKey,
            c($station->city),
            c($station->postalCode)
            );


    $big_sql .= urlencode($sql) .';';

    if (($i++ % $post_interval) == 0) {
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $CARTODB_URL);
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_POSTFIELDS, "api_key=$CARTODB_API_KEY&q=$big_sql");

        $result = curl_exec($ch);
        if (preg_match('/error/',$result)) {
            print "POST status: $result\n";
            print "Error contents: \n";
            print urldecode($big_sql) ."\n";
        }
        curl_close($ch);

        $big_sql = '';
    } 

}



?>
