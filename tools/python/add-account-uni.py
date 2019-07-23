import os
import requests
import json

API_ENDPOINT = "127.0.0.1:8000/mail"

with open('uniMap.json') as json_file:
    json_data = json.load(json_file)

result_list = []

for json_dict in json_data:
    result_list.append(json_dict)

print(result_list)

headers = {'content-type': 'application/json', 'Accept-Charset': 'UTF-8'}

for uni in result_list:
    print(uni)
    data = {
        "uni" : ""
    }
    r = requests.post(url = API_ENDPOINT, data = data , headers = headers)
print("Sended all mail!")