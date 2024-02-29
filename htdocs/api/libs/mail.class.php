<?php
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
  }
  public function CheckReqCount()
  {
    $ip = $_SERVER['REMOTE_ADDR'];
    $current_time = date("Y-m-d H:i:s"); // Format the current time

    // Use prepared statements to prevent SQL injection
    $sql = "SELECT `req_count`, `timestamp` FROM `sendmail` WHERE `ip` = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $ip);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
      $row = $result->fetch_assoc();
      $req_count = $row['req_count'];
      $last_req_time = $row['timestamp'];
      $diff = strtotime($current_time) - strtotime($last_req_time); // Correctly parse timestamps

      if ($diff < 1800) {
        if ($req_count < 2) {
          // Increment req_count and update timestamp in a single query
          $req_count++;
          $sql = "UPDATE `sendmail` SET `req_count` = ?, `timestamp` = ? WHERE `ip` = ?";
          $stmt = $this->conn->prepare($sql);
          $stmt->bind_param("sss", $req_count, $current_time, $ip);
          $stmt->execute();
        } else {
          $data = [
            "Status" => "Rate Limit Exceeded",
            "Status Code" => 429,
            "Message" => "You have exceeded the rate limit of 2 requests in 30 minutes."
          ];
          $this->restObj->response($this->json($data), 429);
        }
      } else {
        // Reset req_count and update timestamp in a single query
        $req_count = 1;
        $sql = "UPDATE `sendmail` SET `req_count` = ?, `timestamp` = ? WHERE `ip` = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sss", $req_count, $current_time, $ip);
        $stmt->execute();
      }
    } else {
      // Insert a new record
      $req_count = 1;
      $token = password_hash($ip . time(), PASSWORD_DEFAULT);
      $sql = "INSERT INTO `sendmail` (`ip`, `req_count`, `timestamp`, `token`) VALUES (?, ?, ?, ?)";
      $stmt = $this->conn->prepare($sql);
      $stmt->bind_param("ssss", $ip, $req_count, $current_time, $token);
      $stmt->execute();
    }
  }
  private function json($data)
  {
    if (is_array($data)) {
      return json_encode($data, JSON_PRETTY_PRINT);
    } else {
      return "{}";
    }
  }
  private function returnDatas()
  {
    $secure_data = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "../../SendGrid-Conf/env.json");
    $secure_data = json_decode($secure_data, true);
    return $secure_data;
  }
  public function HandleFormRequest()
  {
    $this->formrequest();
  }
  public function HandleEmail()
  {
    $this->_email();
  }
  public function HandleEmailVerification()
  {
    $this->verifyemail();
  }
  public function HandleOTPRequest()
  {
    $this->otprequest();
  }
  private function formrequest()
  {
    $userName = $this->_request['username'];
    $userEmail = $this->_request['useremail'];
    $subject = $this->_request['subject'];
    $message = $this->_request['message'];
    $torecieve = $this->_request['torecieve'];
    $username = preg_replace('/[^a-zA-Z0-9 ]/', '', $userName);
    $userEmail = preg_replace('/[^a-zA-Z0-9@.]/', '', str_replace(' ', '', $userEmail));
    $subject = preg_replace('/[^a-zA-Z0-9 \'"]/', '', $subject);
    $message = preg_replace('/[^a-zA-Z0-9 \'"]/', '', $message);

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
  private function _email()
  {
    $torecieve = $this->_request['torecieve'];
    $subject = $this->_request['subject'];
    $message = $this->_request['message'];
    $username = $this->_request['username'];
    $org_name = $this->_request['org_name'];

    $username = preg_replace('/[^a-zA-Z0-9 ]/', '', $username);
    $torecieve = preg_replace('/[^a-zA-Z0-9@.]/', '', str_replace(' ', '', $torecieve));
    $subject = preg_replace('/[^a-zA-Z0-9 \'"]/', '', $subject);
    $message = preg_replace('/[^a-zA-Z0-9 \'"]/', '', $message);
    $org_name = preg_replace('/[^a-zA-Z0-9 \'"]/', '', $org_name);

    $sendgrid_api_key = $this->returnDatas()['sendgrid_api_key'];
    $sendgrid_email = $this->returnDatas()['mail_acc'];

    $email = new \SendGrid\Mail\Mail();
    $email->setFrom($sendgrid_email, $org_name);
    $email->setSubject($subject);
    $email->addTo($torecieve, $org_name);

    $email->addContent("text/html", "
                      <body>
                              <div class='card'>
                                  <h1>Hii, $username</h1>
                                  <p>$message</p>
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
  private function otprequest()
  {
    $torecieve = $this->_request['torecieve'];
    $subject = $this->_request['subject'];
    $message = $this->_request['message'];
    $username = $this->_request['username'];
    $org_name = $this->_request['org_name'];
    $otp = $this->_request['otp'];

    $username = preg_replace('/[^a-zA-Z0-9 ]/', '', $username);
    $torecieve = preg_replace('/[^a-zA-Z0-9@.]/', '', str_replace(' ', '', $torecieve));
    $subject = preg_replace('/[^a-zA-Z0-9 \'"]/', '', $subject);
    $message = preg_replace('/[^a-zA-Z0-9 \'"]/', '', $message);
    $org_name = preg_replace('/[^a-zA-Z0-9 \'"]/', '', $org_name);
    $otp = preg_replace('/[^0-9]/', '', $otp);

    $sendgrid_api_key = $this->returnDatas()['sendgrid_api_key'];
    $sendgrid_email = $this->returnDatas()['mail_acc'];

    $email = new \SendGrid\Mail\Mail();
    $email->setFrom($sendgrid_email, $org_name);
    $email->setSubject($subject);
    $email->addTo($torecieve, $org_name);

    $email->addContent("text/html", "
                      <body>
                              <div class='card'>
                                  <h1>Hii, $username</h1>
                                  <p>$message</p>
                                  <p>OTP: $otp</p>
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
  private function verifyemail()
  {
    $username = $this->_request['username'];
    $subject = $this->_request['subject'];
    $message = $this->_request['message'];
    $torecieve = $this->_request['torecieve'];
    $link = $this->_request['link'];
    $org_name = $this->_request['org_name'];

    $username = preg_replace('/[^a-zA-Z0-9 ]/', '', $username);
    $torecieve = preg_replace('/[^a-zA-Z0-9@.]/', '', str_replace(' ', '', $torecieve));
    $subject = preg_replace('/[^a-zA-Z0-9 \'"]/', '', $subject);
    $message = preg_replace('/[^a-zA-Z0-9 \'"]/', '', $message);
    $org_name = preg_replace('/[^a-zA-Z0-9 \'"]/', '', $org_name);

    $sendgrid_api_key = $this->returnDatas()['sendgrid_api_key'];
    $sendgrid_email = $this->returnDatas()['mail_acc'];

    $email = new \SendGrid\Mail\Mail();
    $email->setFrom($sendgrid_email, $org_name);
    $email->setSubject($subject);
    $email->addTo($torecieve, $org_name);

    $email->addContent("text/html", "
                      <body>
                              <div class='card'>
                                  <h1>Hii, $username</h1>
                                  <p>$message</p>
                                  <a href='$link'>Click here to verify your email</a>
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
