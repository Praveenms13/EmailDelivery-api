# Email Delivery API

Welcome to the Email Delivery API. This API allows you to send emails from your apps or web projects by making a simple post API request. Please refer to the documentation below for details on how to interact with the API.

- The API is rate limited to 5 requests every 30 minutes.
- All user-submitted data (Usernames, email, subject, message, to emails) are erased on a regular basis.
- This API is just for education purposes; please don't rely on it for production.

## Authentication

- **API Key Authentication** is required for the endpoint to authenticate. Ask Praveen personally for the API Keys.
- Basically a **Bearer Token Authentication**.

## API Base URL

**https://sendemailapi.praveenms.site/**

## POST /api/sendmail/mail

To send the new mail, submit the following data in POST method.

**Request Body:**

```json
{
  "username": "The username of the user.",
  "email": "Your email to display it in the mail sent to the recipient.",
  "toemail": "The recipient's email.",
  "subject": "Subject of the email.",
  "message": "Body of the email."
}
````

**Example Response 1 :**

```json
{
  "Status": "Mail Sent Successfully",
  "Status Code": 202
}
```

**Example Response 2 :**

```json
{
    "Status": "Rate Limit Exceeded",
    "Status Code": 429,
    "Message": "You have exceeded the rate limit of 5 requests in 30 minutes."
}
```