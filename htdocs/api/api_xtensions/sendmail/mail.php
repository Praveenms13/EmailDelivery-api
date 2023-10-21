<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "../../SendGrid-Conf/vendor/autoload.php";

${basename(__FILE__, ".php")} = function () {
    try {
        if ($this->get_request_method() == "POST") {
            if (isset($this->_request['data'])) {
                $redis = new Redis();
                $redis->connect('127.0.0.1', 6379);

                $ip = $_SERVER['REMOTE_ADDR'];
                $ip = preg_replace('/[^a-zA-Z0-9 ]/', '', $ip);
                $ip = str_replace(' ', '', $ip);
                $redisKey = $ip . "_sendmail";
                $requestCount = $redis->get($redisKey);

                if ($requestCount === false || $requestCount < 5) {
                    $redis->incr($redisKey);
                    $redis->expire($redisKey, 1800);
                    $secure_data = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "../../SendGrid-Conf/env.json");
                    $secure_data = json_decode($secure_data, true);

                    $token = getallheaders()['Authorization'];
                    if ($token != "Bearer " . $secure_data['token']) {
                        $data = [
                            "Status" => "Invalid Token",
                            "Status Code" => 417,
                        ];
                        $this->response($this->json($data), 417);
                    }

                    $data = json_decode($this->_request['data'], true);
                    $name = $data['name'];
                    $userEmail = $data['email'];
                    $subject = $data['subject'];
                    $messageText = $data['message'];

                    $sendgrid_api_key = $secure_data['sendgrid_api_key'];
                    $fromemail = $secure_data['mail_acc'];

                    $username = preg_replace('/[^a-zA-Z0-9 ]/', '', $name);
                    $recipientEmail = preg_replace('/[^a-zA-Z0-9@.]/', '', str_replace(' ', '', $userEmail));
                    $subject = preg_replace('/[^a-zA-Z0-9 ]/', '', $subject);
                    $message = preg_replace('/[^a-zA-Z0-9 ]/', '', $messageText);

                    $email = new \SendGrid\Mail\Mail();
                    $email->setFrom($fromemail, $username);
                    $email->setSubject($subject);
                    $email->addTo($recipientEmail, $username);
                    $email->addContent("text/html", "
                <html>
              <head>
                <style>
                  .container {
                    width: 100%;
                    max-width: 600px;
                    margin: auto;
                    font-family: Arial, sans-serif;
                    font-size: 16px;
                    line-height: 1.5;
                  }
                  .header {
                    background-color: #f8f8f8;
                    padding: 20px;
                  }
                  .header h1 {
                    margin: 0;
                  }
                  .content {
                    background-color: #ffffff;
                    padding: 20px;
                  }
                  .footer {
                    background-color: #f8f8f8;
                    padding: 20px;
                  }
                  .footer p {
                    margin: 0;
                  }
                </style>
              </head>
              <body>
                <div class='container'>
                  <div class='header'>
                    <h1>Mail from Portfolio</h1>
                  </div>
                  <div class='content'>
                    <p><strong>Name:</strong> $username</p>
                    <p><strong>From Email:</strong> $recipientEmail</p>
                    <p><strong>Subject:</strong> $subject</p>
                    <p><strong>Message:</strong> $message</p>
                  </div>
                  <div class='footer'>
                    <p>&copy; 2023 From www.praveenms.site</p>
                  </div>
                </div>
              </body>
            </html>
                ");
                    $sendgrid = new \SendGrid($sendgrid_api_key);
                    $sendgridResponse = $sendgrid->send($email);
                    $statusCode = $sendgridResponse->statusCode();

                    if ($statusCode == 202) {
                        $data = [
                            "Status" => "Mail Sent Successfully",
                            "Status Code" => $statusCode,
                        ];
                        $this->response($this->json($data), 200);
                    } else {
                        $data = [
                            "Status" => "Mail Not Sent",
                            "Status Code" => $statusCode,
                            "Error" => $sendgridResponse->body()
                        ];
                        $this->response($this->json($data), 417);
                    }
                } else {
                    $data = [
                        "Status" => "Rate Limit Exceeded",
                        "Status Code" => 429,
                        "Message" => "You have exceeded the rate limit of 5 requests in 30 minutes."
                    ];
                    $this->response($this->json($data), 429);
                }
                $redis->close();
            } else if (isset($this->_request['otp'])) {
                // TODO: to develop this
                $data = [
                    "Status" => "OTP Sending method is under development",
                    "Method" => $this->get_request_method()
                ];
                $this->response($this->json($data), 404);
            } else {
                $data = [
                    "Status" => "Sus Activity recorded",
                    "Method" => $this->get_request_method()
                ];
                $this->response($this->json($data), 417);
            }
        } else {
            $data = [
                "Status" => "Method not allowed....",
                "Method" => $this->get_request_method()
            ];
            $this->response($this->json($data), 405);
        }
    } catch (Exception $e) {
        $data = [
            "Status" => "Internal Server Error",
            "Status Code" => 500,
        ];
        $this->response($this->json($data), 500);
    }
};
