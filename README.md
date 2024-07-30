# Email Delivery API Readme

### Author: [Praveen](https://praveenms.site/)

This repository contains a PHP-based Email Delivery API that allows you to send different types of emails, including form data, email verification links, and OTPs (One-Time Passwords) using the SendGrid API. This readme file provides an overview of the API and instructions on how to use it.

## Table of Contents

- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Usage](#usage)
  - [API Endpoints](#api-endpoints)
  - [Request Methods](#request-methods)
- [Configuration](#configuration)
- [Rate Limiting](#rate-limiting)
- [Error Handling](#error-handling)
- [Contributing](#contributing)
- [License](#license)

## Prerequisites

Before using the Email Delivery API, you need to have the following prerequisites:

1. PHP: Ensure you have PHP installed on your server or local environment.
2. SendGrid Account: You should have a SendGrid account and an API key for sending emails. Make sure you have the SendGrid API key ready.
3. Web Server: You need a web server (e.g., Apache, Nginx) to host this API.
4. Composer: Composer is used for managing PHP dependencies. If you don't have Composer installed, you can download it from [getcomposer.org](https://getcomposer.org/).

## Installation

1. Clone this repository to your server or local development environment.
2. Navigate to the project directory in your terminal and run the following command to install the required dependencies using Composer:

```
composer install
```

3. Set up your web server to serve this API. Ensure that the `public` directory is the document root.

## Usage

### API Endpoints

##### https://sendemailapi.praveenms.site/api/mail/

The Email Delivery API provides the following endpoints for sending different types of emails:

| Endpoint  | Request Method | Parameters                                                                          |
| --------- | -------------- | ----------------------------------------------------------------------------------- |
| /formdata | POST           | - `username`: Sender's name.                                                        |
|           |                | - `useremail`: Sender's email address.                                              |
|           |                | - `subject`: Email subject.                                                         |
|           |                | - `message`: Email message.                                                         |
|           |                | - `torecieve`: Recipient's email address to recieve the email.                      |
| /infomail | POST           | - `username`: username name to whom the mail is sent.                               |
|           |                | - `subject`: Email subject.                                                         |
|           |                | - `message`: Email message.                                                         |
|           |                | - `torecieve`: Recipient's email address.                                           |
|           |                | - `org_name`: Organization or App or Website name.                                  |
| /otp      | POST           | - `username`: Sender's name.                                                        |
|           |                | - `subject`: Email subject.                                                         |
|           |                | - `message`: Email message.                                                         |
|           |                | - `torecieve`: Recipient's email address to recieve the email.                      |
|           |                | - `otp`: One-Time Password.                                                         |
|           |                | - `org_name`: Organization or App or Website name.                                  |
| /verify   | POST           | - `username`: Sender's name.                                                        |
|           |                | - `subject`: Email subject.                                                         |
|           |                | - `message`: Email message.                                                         |
|           |                | - `torecieve`: Recipient's email address.                                           |
|           |                | - `link`: verification link with token. eg., https://api.verify.com/?token=sdjf5414 |
|           |                | - `org_name`: Organization or App or Website name.                                  |

### Request Methods

The API accepts POST requests. Make sure to set the Authorization Type to API Key with the key Authorization and value as "Bearer YOUR_API_TOKEN" for authentication.

### Configuration (This is nor required to make the api req and response)

The API uses a configuration file (env.json) to store sensitive information. Ensure that you create this file and provide the following configuration:

| Configuration (This is taken care by the API) |
| --------------------------------------------- |
| - `token`: Your API token for authentication. |
| - `sendgrid_api_key`: Your SendGrid API key.  |
| - `mail_acc`: Your SendGrid email account.    |

### Rate Limiting

The API implements rate limiting to prevent abuse. It limits users to 5 requests in 30 minutes. If you exceed this limit, you will receive a "Rate Limit Exceeded" response.

### Error Handling

The API provides error handling for various scenarios, including incorrect requests, internal server errors, and failed email sending. You will receive detailed error responses to help diagnose issues.

### Contributing

Feel free to contribute to this project by opening issues, providing suggestions, or submitting pull requests. Your contributions are welcome and greatly appreciated.

### License

This Email Delivery API is licensed under the MIT License. You can use, modify, and distribute it according to the terms of this license.

Thank you for using the Email Delivery API. If you encounter any issues or have questions, please don't hesitate to [open an issue](https://github.com/Praveenms13/EmailDelivery-api).
