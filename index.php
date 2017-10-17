<!DOCTYPE html>
<html>
	<head>
		<title>Flugsuche</title>
		<meta name="description" content="Beispiel Google QPX API">
		<meta name="keywords" content="HTML,CSS,XML,JavaScript">
		<meta name="author" content="Laura Wölbeling">
		<meta charset="UTF-8">
	</head>
	
	<body>
		<h1>Suche nach Flügen</h1>
		<?php if (empty($_POST)) {?>
			
			<p> <form method="post">
				Abflughafen:<br>
				 <select name="origin">
					<option value="BER">Berlin</option>
					<option value="MUC">München</option>
					<option value="FRA">Frankfurt</option>
					<option value="WDH">Windhoek</option>
					<option value="CDG">Paris</option>
					<option value="AMS">Amsterdam</option>
				</select><br>
				Zielflughafen:<br>
				<select name="destination">
					<option value="BER">Berlin</option>
					<option value="MUC">München</option>
					<option value="FRA">Frankfurt</option>
					<option value="WDH" selected>Windhoek</option>
					<option value="CDG">Paris</option>
					<option value="AMS">Amsterdam</option>
				</select>
				<br>
				Datum Hinflug:<br>
					<input type="date" name="hdate" value="2016-06-25"><br> <!-- funktioniert nur in Firefox -->
				Datum Rückflug:<br>
					<input type="date" name="rdate" value="2016-07-01"><br> <!-- funktioniert nur in Firefox -->
				<br><br>
				<input type="submit" value="Suche">
			</form></p>
			
		<?php } else {
			echo "<p>Ihre Suche: ";
			//print_r($_POST);
			echo "Flug (1 Erwachsener) von ".$_POST['origin'];
			echo " nach ".$_POST['destination'];
			echo " und zurück, ";
			echo "Hinflug am ".date('d.m.Y', strtotime($_POST['hdate']));
			echo ", Rückflug am ".date('d.m.Y', strtotime($_POST['rdate']));
			echo "</p>";
			?>
			
			<p><a href="index.php">Neue Suche</a></p>
			<h2>Suchergebnisse</h2>
			<?php

			// Eingaben an API senden und Ergebnis in Array speichern
			$slices = array(array('origin' => $_POST['origin'], 'destination' => $_POST['destination'], 'date' => $_POST['hdate'])
						  , array('origin' => $_POST['destination'], 'destination' => $_POST['origin'], 'date' => $_POST['rdate']));

			$resultAsArray = getInformation($slices);

			// Namen der Flughäfen den IATA-Codes zuordnen (alles in Ergebnis-Array enthalten)
			foreach ($resultAsArray['trips']['data']['airport'] as $city) {
				$ap2[$city['code']] = $city['name'];
			};

			// nur Trip-Alternativen aus Ergebnis-Array filtern	
			$trips = array_filter($resultAsArray['trips']['tripOption'], function($kind) {
				if (!isset($kind['kind'])) {
					return false;
				}
				if ($kind['kind'] == "qpxexpress#tripOption") {
					return true;
				}
				return false;
			});


				// Ausgabe der Suchergebnisse
			foreach ($trips as $no => $trip) {
				echo "<p><b>------- ALTERNATIVE " . ($no +1) . " ---------</b><br>\n";
				echo "Preis: " . $trip['saleTotal'] . "<br>\n";
				foreach ($trip['slice'] as $index => $slice) {
					if ($index == 0) echo "<b>Hinflug</b><br>\n";
					else echo "<b>Rückflug</b><br>\n";
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
** Funktion zum Aufruf der QPX-API, gibt PHP-Array zurück
**hier nach key= bitte euren Google Developer Key eintragen
**/
function getInformation($slices) {
    $url = "https://www.googleapis.com/qpxExpress/v1/trips/search?key=";

    $postData = '{
                "request": {
                    "passengers": {
                        "adultCount": 1
                        },
                    "slice": ' . json_encode($slices) . ',
					"solutions": 10
                }
            }';

    $curlConnection = curl_init();
    curl_setopt($curlConnection, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
    curl_setopt($curlConnection, CURLOPT_URL, $url);
    curl_setopt($curlConnection, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($curlConnection, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($curlConnection, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($curlConnection, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curlConnection, CURLOPT_SSL_VERIFYPEER, FALSE);
    $results = json_decode(curl_exec($curlConnection), true);
    if (isset($results['error'])) {
        var_dump($results);
        exit();
    }
    return $results;
}
?>