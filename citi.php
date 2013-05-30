<?php
include 'apikey.php';

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


    $CARTODB_URL = "http://$CARTODB_URL_PREFIX.cartodb.com/api/v2/sql";

    foreach ($parsed_json->stationBeanList as $station) {
        $sql = sprintf("INSERT INTO citibike_times  " .
        "(the_geom, fetch_time, station_id, station_name, docks_total, docks_avail, bikes_avail, address1, address2, lat, lon, station_status, station_status_key, city, postal_code) values ".
        "(ST_GeomFromText('POINT(%s %s)',4326), '%s', %d, '%s', %d, %d, %d, '%s', '%s', %f, %f, '%s', %d, '%s', '%s')",
        $station->longitude,
        $station->latitude,
        $parsed_json->executionTime,
        $station->id,
        $station->stationName,
        $station->totalDocks,
        $station->availableDocks,
        $station->availableBikes,
        $station->stAddress1,
        $station->stAddress2,
        $station->latitude,
        $station->longitude,
        $station->statusValue,
        $station->statusKey,
        $station->city,
        $station->postalCode
);

        #$sql_url = $CARTODB_URL . "?api_key=$CARTODB_API_KEY&q=" . urlencode($sql);
        $sql_url = $CARTODB_URL . "?api_key=$CARTODB_API_KEY&q=".urlencode($sql);
        $result = file_get_contents($sql_url);
        print "$result\n";
//        print "$sql\n";

    }


?>
