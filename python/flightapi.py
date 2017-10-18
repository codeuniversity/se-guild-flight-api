import json
from flask import Flask, render_template, request
import dateutil.parser
import requests
from key import key


app = Flask(__name__)


@app.template_filter()
def pretty_time(date_str):
    return dateutil.parser.parse(date_str).strftime('%d.%m.%Y %H:%M')


@app.route('/', methods=['GET'])
def show_form():
    return render_template('search.html')


@app.route('/', methods=['POST'])
def do_request():
    # build the request object
    api_request = {
        'request': {
            'passengers': {
                'adultCount': 1
            },
            'slice': [
                {
                    'origin': request.form['origin'],
                    'destination': request.form['destination'],
                    'date': request.form['hdate']
                },
                {
                    'origin': request.form['destination'],
                    'destination': request.form['origin'],
                    'date': request.form['rdate']
                }
            ],
            'solutions': 10
        }
    }

    # request data from api
    url = "https://www.googleapis.com/qpxExpress/v1/trips/search?key=" + key
    api_response = requests.post(url, json=api_request).json()

    # map airport codes to their names
    airport_names = {
        airport['code'] : airport['name']
        for airport in api_response['trips']['data']['airport']
    }

    return render_template('results.html',
                           req=request.form,
                           api_response=api_response,
                           airport_names=airport_names)
