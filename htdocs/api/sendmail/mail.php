<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "../../SendGrid-Conf/vendor/autoload.php";

${basename(__FILE__, ".php")} = function () {
  try {
    if ($this->get_request_method() == "POST") {
      // ************************************************************************************************************
      $request_method = $this->_request['request_method'];
      if (!isset($request_method)) {
        $data = [
          "Status" => "Request Method Not Given",
          "Request method" => "Can be either form_data or verification or otp",
          "Status Code" => 417,
        ];
        $this->response($this->json($data), 417);
      }

      if ($request_method != "form_data" && $request_method != "verification" && $request_method != "otp") {
        $data = [
          "Status" => "Invalid Request Method, request_method=" . $request_method,
          "Request method" => "Can be either form_data or verification or otp",
          "Status Code" => 417,
        ];
        $this->response($this->json($data), 417);
      }
      // ************************************************************************************************************
      $ClassMail = new mail($this->_request);
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
