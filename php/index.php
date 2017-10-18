<!DOCTYPE html>
<html>
    <head>
        <title>Flight Search</title>
        <meta name="description" content="Example flight search using Google QPX API">
        <meta name="keywords" content="HTML,CSS,XML,JavaScript">
        <meta name="author" content="Laura Wölbeling">
        <meta charset="UTF-8">
    </head>

    <body>
        <h1>Search for flights</h1>
        <?php if (empty($_POST)) {?>

            <p> <form method="post">
                Origin airport:<br>
                 <select name="origin">
                    <option value="BER">Berlin</option>
                    <option value="MUC">München</option>
                    <option value="FRA">Frankfurt</option>
                    <option value="WDH">Windhoek</option>
                    <option value="CDG">Paris</option>
                    <option value="AMS">Amsterdam</option>
                </select><br>
                Destination airport:<br>
                <select name="destination">
                    <option value="BER">Berlin</option>
                    <option value="MUC">München</option>
                    <option value="FRA">Frankfurt</option>
                    <option value="WDH">Windhoek</option>
                    <option value="CDG" selected>Paris</option>
                    <option value="AMS">Amsterdam</option>
                </select>
                <br>
                Depart:<br>
                    <input type="date" name="hdate" value="2017-10-25"><br>
                Return:<br>
                    <input type="date" name="rdate" value="2017-11-01"><br>
                <br><br>
                <input type="submit" value="search">
            </form></p>

        <?php } else {
            echo "<p>Your search: ";
            //print_r($_POST);
            echo "flight (1 adult) from ".$_POST['origin'];
            echo " to ".$_POST['destination'];
            echo " and back, ";
            echo "departs ".date('d.m.Y', strtotime($_POST['hdate']));
            echo ", returns ".date('d.m.Y', strtotime($_POST['rdate']));
            echo "</p>";
            ?>

            <p><a href="index.php">New search</a></p>
            <h2>Results</h2>
            <?php

            // Send input to API and retrieve result in an array
            $slices = array(
                array(
                    'origin' => $_POST['origin'], 
                    'destination' => $_POST['destination'], 
                    'date' => $_POST['hdate']),
                array(
                    'origin' => $_POST['destination'], 
                    'destination' => $_POST['origin'], 
                    'date' => $_POST['rdate'])
            );

            $resultAsArray = getInformation($slices);

            // associate airport names with their IATA-Codes (data contained in the result array)
            foreach ($resultAsArray['trips']['data']['airport'] as $city) {
                $ap2[$city['code']] = $city['name'];
            };

            // filter trip-alternatives from the result
            $trips = array_filter($resultAsArray['trips']['tripOption'], function($kind) {
                if (!isset($kind['kind'])) {
                    return false;
                }
                if ($kind['kind'] == "qpxexpress#tripOption") {
                    return true;
                }
                return false;
            });


            // print results
            foreach ($trips as $no => $trip) {
                echo "<p><b>------- ALTERNATIVE " . ($no +1) . " ---------</b><br>\n";
                echo "Cost: " . $trip['saleTotal'] . "<br>\n";
                foreach ($trip['slice'] as $index => $slice) {
                    if ($index == 0) echo "<b>Depart</b><br>\n";
                    else echo "<b>Return</b><br>\n";
                    //print "Flug ". ($index + 1) .": " . $slices[$index]['origin'] . " nach " . $slices[$index]['destination'] . "\n<br>";
                    foreach ($slice['segment'] as $segment) {
                        echo $segment['flight']['carrier'].$segment['flight']['number'].": ";
                        foreach ($segment['leg'] as $leg) {
                            echo date('d.m.Y H:i', strtotime($leg['departureTime'])) . " <i>" . $ap2[$leg['origin']]. "</i> (". $leg['origin'] . ")";
                            echo "    ---    ";
                            echo date('d.m.Y H:i', strtotime($leg['arrivalTime'])) . " <i>" . $ap2[$leg['destination']]. "</i> (". $leg['destination'] . ")";
                            echo "\n<br>";
                        }
                    }
                }
                echo "</p>";
            }
        }
        ?>
    </body>
</html>

<?php
/**
 * Calls the QPX-API using curl
 **/
function getInformation($slices) {
    include 'key.php';

    if (!isset($key)) exit("Could not find an API key in key.php");

    $url = "https://www.googleapis.com/qpxExpress/v1/trips/search?key=" . $key;

    $postData = '{
        "request": {
            "passengers": {
                "adultCount": 1
                },
            "slice": ' . json_encode($slices) . ',
            "solutions": 10
        }
    }';
    // echo "Post-Data:<br><pre>" . $postData . "</pre>";

    $curlConnection = curl_init();
    curl_setopt($curlConnection, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
    curl_setopt($curlConnection, CURLOPT_URL, $url);
    curl_setopt($curlConnection, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($curlConnection, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($curlConnection, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($curlConnection, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curlConnection, CURLOPT_SSL_VERIFYPEER, FALSE);
    $json = curl_exec($curlConnection);
    // echo "Response-Data:<br><pre>" . $json . "</pre>";
    $results = json_decode($json, true);
    if (isset($results['error'])) {
        var_dump($results);
        exit();
    }
    return $results;
}
?>