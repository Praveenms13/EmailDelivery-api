<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "../../SendGrid-Conf/vendor/autoload.php";

${basename(__FILE__, ".php")} = function () {
    try {
        if ($this->get_request_method() == "POST") {
            if (isset($this->_request['username']) and isset($this->_request['email']) && isset($this->_request['toemail']) && isset($this->_request['subject']) && isset($this->_request['message'])) {
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

                $sendgrid_api_key = $secure_data['sendgrid_api_key'];
                $fromemail = $secure_data['mail_acc'];

                $username = preg_replace('/[^a-zA-Z0-9 ]/', '', $this->_request['username']);
                $recipientEmail = preg_replace('/[^a-zA-Z0-9@.]/', '', str_replace(' ', '', $this->_request['toemail']));
                $subject = preg_replace('/[^a-zA-Z0-9 ]/', '', $this->_request['subject']);
                $message = preg_replace('/[^a-zA-Z0-9 ]/', '', $this->_request['message']);

                $email = new \SendGrid\Mail\Mail();
                $email->setFrom($fromemail, $username);
                $email->setSubject($subject);
                $email->addTo($recipientEmail, $username);
                $email->addContent("text/plain", $message);
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
                $data = [
                    "Status" => "Invalid Inputs: isset failed"
                ];
                $this->response($this->json($data), 417);
            } else {
                $data = [
                    "Status" => "Invalid Inputs: isset failed"
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
