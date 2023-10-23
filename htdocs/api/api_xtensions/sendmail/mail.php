<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "../../SendGrid-Conf/vendor/autoload.php";

${basename(__FILE__, ".php")} = function () {
  try {
    if ($this->get_request_method() == "POST") {
      $request_method = $this->_request['request_method'];
      if (isset($request_method)) {
        if ($request_method == "form_data") {
          try {
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
                  "Given Token" => $token,
                  "Status Code" => 417,
                ];
                $this->response($this->json($data), 417);
              }

              $userName = $this->_request['username'];
              $userEmail = $this->_request['useremail'];
              $subject = $this->_request['subject'];
              $message = $this->_request['message'];
              $torecieve = $this->_request['torecieve'];
              $username = preg_replace('/[^a-zA-Z0-9 ]/', '', $userName);
              $userEmail = preg_replace('/[^a-zA-Z0-9@.]/', '', str_replace(' ', '', $userEmail));
              $subject = preg_replace('/[^a-zA-Z0-9 ]/', '', $subject);
              $message = preg_replace('/[^a-zA-Z0-9 ]/', '', $message);

              $sendgrid_api_key = $secure_data['sendgrid_api_key'];
              $sendgrid_email = $secure_data['mail_acc'];

              $email = new \SendGrid\Mail\Mail();
              $email->setFrom($sendgrid_email, $username);
              $email->setSubject($subject);
              $email->addTo($torecieve, $username);
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
                      <p><strong>From Email:</strong> $userEmail</p>
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
                  "Status" => "Mail Sent Successfully statuscode=" . $statusCode,
                  "Status Code" => $statusCode,
                ];
                $this->response($this->json($data), 200);
              } else {
                $data = [
                  "Status" => "Mail Not Sent statuscode=" . $statusCode,
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
          } catch (Exception $e) {
            $data = [
              "Status" => "Internal Server Error",
              "Status Code" => 500,
              "Error" => $e->getMessage(),
              "Given datas" => $this->_request['subject']
            ];
            $this->response($this->json($data), 500);
          }
        } elseif ($request_method == "verification") {
          try {
            // $redis = new Redis();
            // $redis->connect('127.0.0.1', 6379);

            // $ip = $_SERVER['REMOTE_ADDR'];
            // $ip = preg_replace('/[^a-zA-Z0-9 ]/', '', $ip);
            // $ip = str_replace(' ', '', $ip);
            // $redisKey = $ip . "_sendmail";
            // $requestCount = $redis->get($redisKey);

            // if ($requestCount === false || $requestCount < 5) {
            //   $redis->incr($redisKey);
            //   $redis->expire($redisKey, 1800);

              $secure_data = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "../../SendGrid-Conf/env.json");
              $secure_data = json_decode($secure_data, true);
              $token = getallheaders()['Authorization'];
              if ($token != "Bearer " . $secure_data['token']) {
                $data = [
                  "Status" => "Invalid Token",
                  "Given Token" => $token,
                  "Status Code" => 417,
                ];
                $this->response($this->json($data), 417);
              }

              $username = $this->_request['username'];
              $subject = $this->_request['subject'];
              $torecieve = $this->_request['torecieve'];
              $link = $this->_request['link'];

              $sendgrid_api_key = $secure_data['sendgrid_api_key'];
              $sendgrid_email = $secure_data['mail_acc'];

              $email = new \SendGrid\Mail\Mail();
              $email->setFrom($sendgrid_email, $username);
              $email->setSubject($subject);
              $email->addTo($torecieve, $username);

              $email->addContent("text/html", "
                      <body>
                              <div class='card'>
                                  <h1>Hii, $username</h1>
                                  <p>Please verify your email by clicking the link below:</p>
                                  <a href='$link'>Verify Email</a>
                              </div>
                          </body>
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
            // }
          } catch (Exception $e) {
            $data = [
              "Status" => "Internal Server Error",
              "Status Code" => 500,
              "Error" => $e->getMessage(),
            ];
            $this->response($this->json($data), 500);
          }
        } elseif ($request_method == "otp") {
          $data = [
            "Status" => "OTP",
            "Status Code" => 200,
            "OTP" => "123456"
          ];
          $this->response($this->json($data), 200);
        } else {
          $data = [
            "Status" => "Invalid Request Method, request_method=" . $request_method,
            "Request method" => "Can be either form_data or verification or otp",
            "Status Code" => 417,
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
      "Error" => $e->getMessage(),
      "Given datas" => $this->_request['data']
    ];
    $this->response($this->json($data), 500);
  }
};
