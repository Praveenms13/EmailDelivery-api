<?php
echo "<h1>Verifying Email....</h1>";
$FunCall = new REST();
$token = $FunCall->cleanInputs($_GET['token']);
try {
    if (signup::verifyEmail($token)) {
        ?>
<h2>Checking AuthToken....</h2>
<h1 style="color: green">Email verified successfully..</h1><?php
    } else {
        ?>
<h2>Checking AuthToken....</h2>
<h1 style="color: red">Email verification failed, Try again...</h1><?php
    }
} catch (Exception $e) {
    ?>
<h2>Checking AuthToken....</h2>
<h1 style="color: orange">Email already verified........</h1><?php
}
?>