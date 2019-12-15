<?php
// declaring variables to prevent errors
$fname = "";    // first name
$lname = "";    // last name
$email = "";    //email
$email2 = "";   // email2
$password = ""; // password
$password2 = "";    // password2
$date = "";         // sign up date
$profile_pic = "";  // profile picture
$error_array = array();  // holds error messages

// if register_button was clicked, handle form
if (isset($_POST["register_button"])) {
    // register form values

    $fname = strip_tags($_POST["reg_fname"]);   // strip_tags means any html tags put into input form will be removed and take what was sent from the POST form and use that value
    $fname = str_replace(" ", "", $fname);      // remove all spaces
    $fname = ucfirst(strtolower($fname));       // convert all characters to lowercase and only capitalize first letter
    $_SESSION['reg_fname'] = $fname;            // stores first name into session variable

    $lname = strip_tags($_POST["reg_lname"]);
    $lname = str_replace(" ", "", $lname);
    $lname = ucfirst(strtolower($lname));
    $_SESSION['reg_lname'] = $lname;            // stores last name into session variable

    $username = strip_tags($_POST["reg_username"]);
    $username = str_replace(" ", "", $username);
    $username = strtolower($username);
    $_SESSION['reg_username'] = $username; 

    $email = strip_tags($_POST["reg_email"]);
    $email = str_replace(" ", "", $email);
    $email = ucfirst(strtolower($email));
    $_SESSION['reg_email'] = $email; 

    $email2 = strip_tags($_POST["reg_email2"]);
    $email2 = str_replace(" ", "", $email2);
    $email2 = ucfirst(strtolower($email2));
    $_SESSION['reg_email2'] = $email2; 

    $password = strip_tags($_POST["reg_password"]);
    $password2 = strip_tags($_POST["reg_password2"]);

    $date = date("Y-m-d"); // get current date

    if ($email == $email2) {
        // check if email is in valid format
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = filter_var($email, FILTER_VALIDATE_EMAIL);
            // check if email already exists - if exists, returns something, else nothing returns
            $em_check = mysqli_query($con, "SELECT email FROM users WHERE email='$email'");
            // count number of rows returned
            $num_rows = mysqli_num_rows($em_check);
            
            if($num_rows > 0) {
                array_push($error_array, "Email already in use");
            }
        } else {
            array_push($error_array, "Invalid format");
        }
    } else {
        array_push($error_array, "Emails dont match");
    }

    if (strlen($username) > 100 || strlen($username) < 2) {
        array_push($error_array, "Your username must be at least 3 characters and at most 100 characters");
    } else {
        if (mysqli_num_rows(mysqli_query($con, "SELECT username FROM users WHERE username='$username'")) > 0) {
            array_push($error_array, "Username already taken");
        } 
    }
    
    if (strlen($fname) > 25 || strlen($fname) < 2) {
        array_push($error_array, "Your first name must be at least 2 characters and at most 25 characters");
    }

    if (strlen($lname) > 25 || strlen($lname) < 2) {
        array_push($error_array, "Your last name must be at least 2 characters and at most 25 characters");
    }

    if ($password != $password2) {
        array_push($error_array, "Your passwords do not match");
    } else {
        if (preg_match('/[^A-Za-z0-9]/', $password)) {
            array_push($error_array, "Your password can only contain characters or numbers");
        }
    }

    if (strlen($password) > 30 || strlen($password) < 5) {
        array_push($error_array, "Your password must be between 5 and 30 characters");
    }

    // if there are no errors
    if (empty($error_array)) {
        // encryption
        $password = md5($password); // encrpyt password before sending to database

        // profile picture assignment
        $rand = rand(1, 2);         // random number between 1 and 2
        if ($rand == 1) 
            $profile_pic = "assets/images/profile_pics/defaults/head_deep_blue.png";
        else if ($rand == 2)
            $profile_pic = "assets/images/profile_pics/defaults/head_emerald.png";

        $query = mysqli_query($con, "INSERT INTO users VALUES('', '$fname', '$lname', '$username', '$email', '$password', '$date', '$profile_pic', '0', '0', 'no', ',')");
        array_push($error_array, "<span>You're all set!</span>");

        // clear session variables
        $_SESSION['reg_fname'] = "";
        $_SESSION['reg_lname'] = "";
        $_SESSION['reg_email'] = "";
        $_SESSION['reg_email2'] = "";
    }
}
?>