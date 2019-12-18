<?php
include("includes/header.php");

if (isset($_POST['close_account'])) {
    $closed_query = mysqli_query($con, "UPDATE users SET user_closed='yes' WHERE username='$userLoggedIn'");
    session_destroy();
    header("Location: register.php");
}

if (isset($_POST['cancel'])) {
    header("Location: settings.php");
}
?>

<div class="container">
    <div class="heading-1">Close Your Account</div>
    <div class="heading-2">Are you sure you want to close your account? You can re-open your account by logging in</div>

    <form action="close_account.php" method="POST">
        <input class="update_details" type="submit" name="close_account" id="closed_account" value="Yes, close account" />
        <input class="update_details" type="submit" name="cancel" id="update_details" value="Cancel" />
    </form>
</div>