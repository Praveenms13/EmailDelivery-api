<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "../../SendGrid-Conf/vendor/autoload.php";

${basename(__FILE__, ".php")} = function () {
    if ($this->get_request_method() == "POST") {
        $request_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        if ($request_url == "/api/mail/formdata") {
            if (isset($this->_request['username']) and isset($this->_request['useremail']) and isset($this->_request['subject']) and isset($this->_request['message']) and isset($this->_request['torecieve'])) {
                $token = getallheaders()['Authorization'];
                $secure_data = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "../../SendGrid-Conf/env.json");
                $secure_data = json_decode($secure_data, true);
                if ($token != "Bearer " . $secure_data['token']) {
                    $data = [
                        "Status" => "Unauthorized",
                        "Status Code" => 401,
                        "Request URL" => $request_url,
                        "Message" => "Access to this API is not allowed",
                    ];
                    $this->response($this->json($data), 401);
                }
                $mailClass = new mail($this->_request);
                $mailClass->CheckReqCount();
                $mailClass->HandleFormRequest();
            } else {
                $data = [
                    "Status" => "Bad Request",
                    "Status Code" => 400,
                    "Error" => "Form Datas required are username, useremail, subject, message, torecieve"
                ];
                $this->response($this->json($data), 400);
            }
        } else {
            $data = [
                "Status" => "OK",
                "Status Code" => 200,
                "Request URL" => $request_url,
                "Message" => "Sus Access to this API is not allowed",
            ];
        }
    } else {
        $data = [
            "Status" => "Invalid Request Method",
            "Status Code" => 405,
        ];
        $this->response($this->json($data), 405);
    }
};
