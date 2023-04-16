<?php
require_once('../../config/MomoConfig.php');
function render($result = false, $data = null, $message = null, $shopUrl = CLIENTRETURNURL)
{
    if ($result == false) {
        if ($message == null) {
            $message = "Payment error, please pay again!";
        }
        $retryBtn = '<a href="' . SERVERURL . '/payment/momo/' . $data['orderID'] . '" class="button repay">Retry Payment</a>';
    } else {
        $retryBtn = "";
        if ($message == null) {
            $message = "Thank you for your payment! Your transaction has been completed successfully.";
        }
    }
    $content = '<!DOCTYPE html>
    <html>
    
    <head>
        <meta charset="UTF-8" />
        <title>Payment Result</title>
        <style>
            body {
                font-family: "Open Sans", sans-serif;
                background-color: #f5f5f5;
            }

            .container {
                max-width: 600px;
                margin: 0 auto;
                text-align: center;
                background-color: #ffffff;
                padding: 30px;
                border-radius: 5px;
                box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.1);
            }

            h1 {
                color: #0099ff;
                text-transform: uppercase;
            }

            p {
                color: #555555;
            }

            .button {
                background-color: #0099ff;
                color: #ffffff;
                padding: 10px 20px;
                border-radius: 5px;
                text-decoration: none;
                display: inline-block;
                margin: 10px;
                font-weight: bold;
                transition: background-color 0.2s ease-in-out;
            }

            .shop {
                background-color: #0099ff;
                color: #ffffff;
                padding: 10px 20px;
                border-radius: 5px;
                text-decoration: none;
                display: inline-block;
                margin: 10px;
                font-weight: bold;
                transition: background-color 0.2s ease-in-out;
            }
            
            .shop:hover {
                background-color: #0077cc;
            }
            
            .repay {
                background-color: #ff0000;
                color: #ffffff;
                padding: 10px 20px;
                border-radius: 5px;
                text-decoration: none;
                display: inline-block;
                margin: 10px;
                font-weight: bold;
                transition: background-color 0.2s ease-in-out;
            }
            
            .repay:hover {
                background-color: #cc0000;
            }
        </style>
        </style>
    </head>
    
    <body>
        <div class="container">
            <h1>Payment Result</h1>
            <p>
                ' . $message . '
            </p>
            <div>
                <a href="' . $shopUrl . '" class="button shop">Continue shopping</a>' . $retryBtn . ' 
            </div>
        </div>
    </body>
    
    </html>';
    echo $content;
}
