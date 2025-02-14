import requests
import random

 

# Define the URL you want to send the POST request to
url = "http://localhost/www/sites/eh/example1_rc/login.php"

# Define the data you want to send in the POST body
data = {"email": "a12345","name": "bbbb","cpassword": "bbbb", "password": "bbbb","submit":"submit"}



while True:
    # Send the POST request and get the response
    response = requests.post(url, data=data)

    # Check the response status code
    if response.status_code == 200:
        # The request was successful, access the response content
        if(len(response.text) != 1947 ):
            print(response.text)
            break
