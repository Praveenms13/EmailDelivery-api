<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Email API</title>
</head>

<body>
    <h1 id="api-based-notes-app-under-development">Send Email API by SendGrid Email Provider</h1>
    <h3 id="api-base-url-https-apis-selfmade-one">API Base Url: <a
            href="https://sendemailapi.praveenms.site/">https://sendemailapi.praveenms.site/</a></h3>
    <h3 id="view-">View :</h3>
    <p>This API is used to send emails by making a simple post request to the api.
        which follows OAuth 2.0, REST API Protocols, AS of no login or signup is required only a API key is required =>
        Get it from <a href="https://www.praveenms.site">Praveen</a></p>
    <hr>
    <h2 id="login-api">Email API</h2>
    <hr>
    <h3 id="request">Request</h3>
    <pre><code>POST <span class="hljs-regexp">/api/sendmail/</span>/mail
</code></pre>
    <hr>
    <h4 id="form-data">Form Data</h4>
    <table>
        <thead>
            <tr>
                <th>Attribute</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Username</td>
                <td>Can be username or email address to be displayed in gmail</td>
            </tr>
            <tr>
                <td>email</td>
                <td>Your Email to be displayed in the recievers side</td>
            </tr>
            <tr>
                <td>toemail</td>
                <td>recipientEmail the mail subject and body to be sent</td>
            </tr>
            <tr>
                <td>subject</td>
                <td>subject of the mail</td>
            </tr>
            <tr>
                <td>messaege</td>
                <td>body of the mail</td>
            </tr>
        </tbody>
    </table>
    <pre><code><span class="hljs-symbol">Authorization:</span> Bearer <span class="hljs-params">&lt;secret_token&gt;</span>
</code></pre>
    <hr>
    <h5 id="example-">Example:</h5>
    <pre><code>Authorization: Bearer secret-key
</code>
</pre>
    <hr>
    <h4 id="response-examples">Request Examples</h4>
    <h5 id="200">202</h5>
    <img width="50%" src="req.png" alt="">
    <img width="50%" src="apikey.png" alt="">

    </pre>
    <hr>
    <h4 id="response-examples">Response Examples</h4>
    <h5 id="200">202</h5>
    <pre><code>{
    <span class="hljs-attr">"Status"</span>: <span class="hljs-string">"Mail Sent Successfully"</span>,
    <span class="hljs-attr">"Status Code"</span>: <span class="hljs-string">"202"</span>
}
</code></pre>
    <h5 id="406">417</h5>
    <pre><code>{
    <span class="hljs-attr">"Status"</span>: <span class="hljs-string">"Invalid Inputs: isset failed"</span>
}
</code></pre>
</body>

</html>