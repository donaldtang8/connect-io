<?php
if (isset($_POST['update_details'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];

    $email_check = mysqli_query($con, "SELECT * FROM users WHERE email='$email'");
    $row = mysqli_fetch_array($email_check);
    $matched_user = $row['username'];
    if ($matched_user == "" || $matched_user == $userLoggedIn) {
        $message = "Account updated!";

        $query = mysqli_query($con, "UPDATE users SET first_name='$first_name', last_name='$last_name', email='$email' WHERE username='$userLoggedIn'");
    } else {
        $message  = "That email is already in use.";
    }
} else {
    $message = "";
}

if (isset($_POST['update_password'])) {
    $current_password = strip_tags($_POST['current_password']);
    $new_password_1 = strip_tags($_POST['new_password_1']);
    $new_password_2 = strip_tags($_POST['new_password_2']);

    $password_query = mysqli_query($con, "SELECT * FROM users WHERE username='$userLoggedIn'");
    $row = mysqli_fetch_array($password_query);
    $db_password = $row['password'];

    if (md5($current_password) == $db_password) {
        if ($new_password_1 == $new_password_2) {
            if (strlen($new_password_1) < 5) {
                $password_message = "New password must be at least 5 characters";
            } else {
                $new_password_md5 = md5($new_password_1);
                $password_update_query = mysqli_query($con, "UPDATE users SET password='$new_password_md5' WHERE username='$userLoggedIn'");
                $password_message = "Passwords has been updated";
            }
        } else {
            $password_message = "Passwords must match";
        }
    } else {
        $password_message = "Password is incorrect";
    }
}

if (isset($_POST['close_account'])) {
    header("Location: close_account.php");
}
