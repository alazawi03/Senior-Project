import requests
import random

 

# Define the URL you want to send the POST request to
url = "http://localhost/www/sites/eh/example1_rc/signup.php"

# Define the data you want to send in the POST body
data = {"email": "a12345666111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111","name": "bbbb","cpassword": "bbbb", "password": "bbbb","submit":"submit"}

# Send the POST request and get the response
response = requests.post(url, data=data)

# Check the response status code
if response.status_code == 200:
    # The request was successful, access the response content
    print(response.text)
else:
    # The request failed, handle the error
    print(f"Error: {response.status_code}")
