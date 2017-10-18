# How to get an API Key

* Create a new project at https://console.developers.google.com/apis/credentials and get some new credentials
* Enable the QPX Express Airfare API https://console.developers.google.com/apis/library/qpxexpress.googleapis.com/ for your project

# How to run the PHP example
* Install a webserver and php (for example: Apache using [XAMPP](https://www.apachefriends.org/))
* Create a file called `key.php` in the php folder containing your API credentials like this:

```php
<?php
    $key = "QIAERTNE-xyz2387fuie"
?>
```

* Copy the php folder into your htdocs

# Ho to run the python example
* Install [Flask](http://flask.pocoo.org/)
* Create a file called `key.py` in the python folder:

```python
key = "QIAERTNE-xyz2387fuie"
```

* Run flask with `FLASK_DEBUG=1 FLASK_APP=flightapi.py flask run` from the terminal inside the python folder
