<?php

include "../REST.api.php";
class mail
{
  private $conn;
  private $_request = array();
  private $restObj;
  public function __construct($request_datas)
  {

    $this->conn = Database::getConnection();
    if ($this->conn->connect_error) {
      $data = [
        "Status" => "Internal Server Error",
        "Status Code" => 500,
        "Error" => $this->conn->connect_error
      ];
      $this->restObj->response($this->json($data), 500);
    }
    $this->restObj = new REST();
    $this->_request = $request_datas;
    $this->CheckToken();
    $this->CheckReqCount();
    if ($this->_request['request_method'] == "form_data") {
      if (isset($this->_request['username']) && isset($this->_request['useremail']) && isset($this->_request['subject']) && isset($this->_request['message']) && isset($this->_request['torecieve'])) {
        $this->HandleFormRequest();
      } else {
        $data = [
          "Status" => "Invalid Request",
          "Status Code" => 417,
          "Message" => "username, useremail, subject, message, torecieve are required"
        ];
        $this->restObj->response($this->json($data), 417);
      }
      $this->HandleFormRequest();
    } else if ($this->_request['request_method'] == "verification") {
      if (isset($this->_request['username']) && isset($this->_request['subject']) && isset($this->_request['torecieve']) && isset($this->_request['link'])) {
        $this->HandleEmailVerification();
      } else {
        $data = [
          "Status" => "Invalid Request",
          "Status Code" => 417,
          "Message" => "username, subject, torecieve, link are required"
        ];
        $this->restObj->response($this->json($data), 417);
      }
      $this->HandleEmailVerification();
    } else if ($this->_request['request_method'] == "otp") {
      if (isset($this->_request['username']) && isset($this->_request['subject']) && isset($this->_request['torecieve']) && isset($this->_request['otp'])) {
        $this->HandleOTPRequest();
      } else {
        $data = [
          "Status" => "Invalid Request",
          "Status Code" => 417,
          "Message" => "username, subject, torecieve are required"
        ];
        $this->restObj->response($this->json($data), 417);
      }
      $this->HandleOTPRequest();
    } else {
      $data = [
        "Status" => "Invalid Request Method, request_method=" . $this->_request['request_method'],
        "Request method" => "Can be either form_data or verification or otp",
        "Status Code" => 417,
      ];
      $this->restObj->response($this->json($data), 417);
    }
  }
  private function returnDatas()
  {
    $secure_data = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "../../SendGrid-Conf/env.json");
    return json_decode($secure_data, true);
  }
  private function json($data)
  {
    if (is_array($data)) {
      return json_encode($data, JSON_PRETTY_PRINT);
    } else {
      return "{}";
    }
  }
  private function CheckReqCount()
  {
    $ip = $_SERVER['REMOTE_ADDR'];
    $sql = "SELECT * FROM `sendmail` WHERE `ip` = '$ip'";
    $result = $this->conn->query($sql);
    if ($result->num_rows > 0) {
      $row = $result->fetch_assoc();
      $req_count = $row['req_count'];
      $last_req_time = $row['timestamp'];
      $last_req_time = strtotime($last_req_time);
      $current_time = strtotime(date("Y-m-d H:i:s"));
      $diff = $current_time - $last_req_time;
      if ($diff < 30) {
        if ($req_count < 3) {
          $req_count = $req_count + 1;
          $sql = "UPDATE `sendmail` SET `req_count` = '$req_count' WHERE `ip` = '$ip'";
          $this->conn->query($sql);
        } else {
          $data = [
            "Status" => "Rate Limit Exceeded",
            "Status Code" => 429,
            "Message" => "You have exceeded the rate limit of 5 requests in 30 minutes."
          ];
          $this->restObj->response($this->json($data), 429);
        }
      } else {
        $req_count = 1;
        $sql = "UPDATE `sendmail` SET `req_count` = '$req_count' WHERE `ip` = '$ip'";
        $this->conn->query($sql);
      }
    } else {
      $req_count = 1;
      $token = password_hash($ip . time(), PASSWORD_DEFAULT);
      $sql = "INSERT INTO `sendmail` (`ip`, `req_count`, `token`) VALUES ('$ip', '$req_count', '$token')";
      $this->conn->query($sql);
    }
  }
  private function CheckToken()
  {
    $secure_data = $this->returnDatas();
    $token = getallheaders()['Authorization'];
    if ($token != "Bearer " . $secure_data['token']) {
      $data = [
        "Status" => "Invalid Token",
        "Given Token" => $token,
        "Status Code" => 417,
      ];
      $this->restObj->response($this->json($data), 417);
    }
  }
  private function HandleFormRequest()
  {
    $userName = $this->_request['username'];
    $userEmail = $this->_request['useremail'];
    $subject = $this->_request['subject'];
    $message = $this->_request['message'];
    $torecieve = $this->_request['torecieve'];
    $username = preg_replace('/[^a-zA-Z0-9 ]/', '', $userName);
    $userEmail = preg_replace('/[^a-zA-Z0-9@.]/', '', str_replace(' ', '', $userEmail));
    $subject = preg_replace('/[^a-zA-Z0-9 ]/', '', $subject);
    $message = preg_replace('/[^a-zA-Z0-9 ]/', '', $message);

    $sendgrid_api_key = $this->returnDatas()['sendgrid_api_key'];
    $sendgrid_email = $this->returnDatas()['mail_acc'];

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
      $this->restObj->response($this->json($data), 200);
    } else {
      $data = [
        "Status" => "Mail Not Sent statuscode=" . $statusCode,
        "Status Code" => $statusCode,
        "Error" => $sendgridResponse->body()
      ];
      $this->restObj->response($this->json($data), 417);
    }
  }
  private function HandleEmailVerification()
  {
    $username = $this->_request['username'];
    $subject = $this->_request['subject'];
    $torecieve = $this->_request['torecieve'];
    $link = $this->_request['link'];

    $sendgrid_api_key = $this->returnDatas()['sendgrid_api_key'];
    $sendgrid_email = $this->returnDatas()['mail_acc'];

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
      $this->restObj->response($this->json($data), 200);
    } else {
      $data = [
        "Status" => "Mail Not Sent",
        "Status Code" => $statusCode,
        "Error" => $sendgridResponse->body()
      ];
      $this->restObj->response($this->json($data), 417);
    }
  }
  private function HandleOTPRequest()
  {
    $username = $this->_request['username'];
    $subject = $this->_request['subject'];
    $torecieve = $this->_request['torecieve'];
    $otp = $this->_request['otp'];

    $sendgrid_api_key = $this->returnDatas()['sendgrid_api_key'];
    $sendgrid_email = $this->returnDatas()['mail_acc'];

    $email = new \SendGrid\Mail\Mail();
    $email->setFrom($sendgrid_email, $username);
    $email->setSubject($subject);
    $email->addTo($torecieve, $username);

    $email->addContent("text/html", "
                      <body>
                              <div class='card'>
                                  <h1>Hii, $username</h1>
                                  <p>Your OTP is $otp</p>
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
      $this->restObj->response($this->json($data), 200);
    } else {
      $data = [
        "Status" => "Mail Not Sent",
        "Status Code" => $statusCode,
        "Error" => $sendgridResponse->body()
      ];
      $this->restObj->response($this->json($data), 417);
    }
  }
}
