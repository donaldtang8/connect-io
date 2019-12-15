<?php
require "config/config.php";
require "includes/form_handlers/register_handler.php";
require "includes/form_handlers/login_handler.php";
?>

<html>
    <head>
        <title>Social Media</title>
        <link rel="stylesheet" type="text/css" href="assets/css/index.css">
        <link rel="stylesheet" type="text/css" href="assets/css/register.css">
        <script
            src="https://code.jquery.com/jquery-3.4.1.min.js"
            integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
            crossorigin="anonymous"></script>
        <script src="./assets/js/index.js"></script>
    </head>
    <body>

    <?php  

	if(isset($_POST['register_button'])) {
		echo '
		<script>
		$(document).ready(function() {
			$(".register").css("display", "flex");
            $(".register").addClass("show");
            $(".login").css("display", "none");
            $(".login").removeClass("show");
		});
		</script>

		';
	}
	?>
        <div class="container">
            <div class="block">
            <div class="heading">Social Media</div>
            <div class="forms">
                <div class="login show">
                    <div class="error">
                        <?php if(in_array("Email or password was incorrect", $error_array)) echo "Email or password was incorrect"; ?>
                    </div>
                    <form action="register.php" method="POST">
                        <input type="email" name="log_email" placeholder="Email" value="<?php if(isset($_SESSION['log_email'])) { echo $_SESSION['log_email']; } ?>" required />
                        <input type="password" name="log_password" placeholder="Password">
                        <input type="submit" name="login_button" value="LOGIN" />
                    </form>
                    <div class="desc">
                        Don't have an account? <span class="redirect redirect-reg">Create one here</span>.
                    </div>
                </div>

                <div class="register">
                    <div class="success">
                        <?php if(in_array("<span>You're all set!</span>", $error_array)) echo "<span>You're all set!</span>"; ?>
                    </div>
                    <form action="register.php" method="POST">
                        <input type="text" name="reg_fname" placeholder="First Name" value="<?php if(isset($_SESSION['reg_fname'])) { echo $_SESSION['reg_fname']; } ?>" required />
                        <?php if(in_array("Your first name must be at least 2 characters and at most 25 characters", $error_array)) echo "Your first name must be at least 2 characters and at most 25 characters"; ?>
                        <input type="text" name="reg_lname" placeholder="Last Name" value="<?php if(isset($_SESSION['reg_lname'])) { echo $_SESSION['reg_lname']; } ?>" required />
                        <?php if(in_array("Your last name must be at least 2 characters and at most 25 characters", $error_array)) echo "Your last name must be at least 2 characters and at most 25 characters"; ?>
                        <input type="text" name="reg_username" placeholder="Username" value="<?php if(isset($_SESSION['reg_username'])) { echo $_SESSION['reg_username']; } ?>" required />
                        <?php if(in_array("Your username must be at least 3 characters and at most 100 characters", $error_array)) echo "Your username must be at least 3 characters and at most 100 characters";
                        else if(in_array("Username already taken", $error_array)) echo "Username already taken"; ?>
                        <input type="email" name="reg_email" placeholder="Email" value="<?php if(isset($_SESSION['reg_email'])) { echo $_SESSION['reg_email']; } ?>" required />
                        <input type="email" name="reg_email2" placeholder="Confirm Email" value="<?php if(isset($_SESSION['reg_email2'])) { echo $_SESSION['reg_email2']; } ?>" required />
                        <?php if(in_array("Email already in use", $error_array)) echo "Email already in use";
                        else if(in_array("Invalid format", $error_array)) echo "Invalid format";
                        else if(in_array("Emails dont match", $error_array)) echo "Emails dont match"; ?>
                        <input type="password" name="reg_password" placeholder="Password" required />
                        <input type="password" name="reg_password2" placeholder="Confirm Password" required />
                        <?php if(in_array("Your passwords do not match", $error_array)) echo "Your passwords do not match";
                        else if(in_array("Your password can only contain characters or numbers", $error_array)) echo "Your password can only contain characters or numbers";
                        else if(in_array("Your password must be between 5 and 30 characters", $error_array)) echo "Your password must be between 5 and 30 characters"; ?>
                        <input type="submit" name="register_button" value="REGISTER" />
                    </form>
                    <div class="desc">
                        Already have an account? <span class="redirect redirect-log">Log in here</span>.
                    </div>
                </div>
            </div>
            </div>
        </div>
        
    </body>
</html>