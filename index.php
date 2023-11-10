<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple WhatsApp API implementation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 8px;
        }

        input, textarea {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            width: 100%;
        }

        textarea {
            resize: vertical;
            height: 100px;
        }

        input[type="submit"] {
            background-color: #4caf50;
            color: #fff;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        footer {
            margin-top: 20px;
            text-align: center;
            color: #777;
        }
        
         .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
            font-size: 16px;
        }

        .alert-success {
            color: #3c763d;
            background-color: #dff0d8;
            border-color: #d6e9c6;
        }

        .alert-error {
            color: #a94442;
            background-color: #f2dede;
            border-color: #ebccd1;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Simple WhatsApp API implementation</h2>
        <?php
        
            function sanitize($dirty) {
                  global $db;
                  $dirty = trim($dirty);
                  return htmlentities($dirty, ENT_QUOTES, "UTF-8");
            }
                
            function validatePhoneNumber($phoneNumber) {
                //format 0712345678
                $pattern = '/^07\d{8}$/';
            
                if (preg_match($pattern, $phoneNumber)) {
                    return true;
                } else {
                    return false;
                }
            }
            
            //store this in a db or somewhere secure
            $storedBcryptHash = password_hash("1234", PASSWORD_BCRYPT);
            
            function validatePin($enteredPin, $hashedPinFromDatabase) {
                return password_verify($enteredPin, $hashedPinFromDatabase);
            }
                        
            //1. Verify form submition
            if(isset($_POST['submit'])){
                
                $error = "";
                $success = "";
                $phone = ((isset($_POST['phone'])) ? sanitize($_POST['phone']) : '');
                $pin = ((isset($_POST['pin'])) ? sanitize($_POST['pin']) : '');
                
                
                if(!validatePhoneNumber($phone)) {
                    $error = "Invalid phone number. Please input a phone number in this format 071234568";
                } elseif (!validatePin($pin, $storedBcryptHash)) {
                    $error = "Wrong PIN";
                } else {
                    //lets now make the request to whatsapp servers
                    
                    // URL to which the request is made
                    $url = '//YOUR_API_LINK';
                    
                    // Headers for the request
                    $headers = array(
                        'Authorization: Bearer //YOUR_AUTHORIZATION_TOKEN',
                        'Content-Type: application/json'
                    );
                    
                
                    $data = array(
                                "messaging_product" => "whatsapp",
                                "to" => "254".(int)$phone,
                                "type" => "template",
                                "template" => array(
                                    "name" => "//MESSAGE_TEMPLATE_NAME",
                                    "language" => array(
                                        "code" => "en_US"
                                    )
                                )
                            );
                  

                    $ch = curl_init();
                    
                    // Set cURL options
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    
                    $response = curl_exec($ch);
                    
                    // get the status code
                    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    // curl_error($ch) // You might want to use it
                    
                    
                    if ($status === 200) {
                        //incase of an error response from saf
                        if (strpos($response, "error") !== false) {
                          $error = "An error occured";
                        } else {
                            $success = "Message sent successfully";
                            // echo $response;
                        }

                        
                    } else {
                        // error
                         $error = "An error occured";
                    }
                    
                    curl_close($ch);
                    
                    
                    
                    
                    
                }
                
            }
            
            
        ?>
        
        <?php if(!empty($success)) { ?>
         <!-- Success Alert -->
        <div class="alert alert-success">
            <strong>Success!</strong> <?php echo $success; ?>.
        </div>
        <?php } ?>
    
        <?php if(!empty($error)) { ?>
        <!-- Error Alert -->
        <div class="alert alert-error">
            <strong>Error!</strong> <?php echo $error; ?>.
        </div>
        <?php } ?>
    
        <form method="post" action="">
            <label for="input1">To: (0712345678)</label>
            <input type="text" id="input1" name="phone" value="<?php echo @$phone ?>" placeholder="Enter the recipient phone number">

            <label for="input2">Message:</label>
            <textarea id="input2" name="input2" readonly>Welcome and congratulations!! This message demonstrates your ability to send a WhatsApp message notification from the Cloud API, hosted by Meta. Thank you for taking the time to test with us.</textarea>

            <label for="input3">PIN:</label>
            <input type="password" id="input3" name="pin" pattern="[0-9]{4}" maxlength="4" placeholder="Enter PIN">

            <input type="submit" name="submit" value="Submit">
        </form>

        <footer>Created with ❤️ by Emmanuel Haggai</footer>
    </div>
</body>
</html>

