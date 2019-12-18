<?php
include("includes/header.php");
include("includes/form_handlers/settings_handler.php");
?>

<div class="container">
    <div class="heading-1">Account Settings</div>
    <?php
    echo "<img src='" . $user['profile_pic'] . "' id='small_profile_pics' />";

    ?>
    <a href="upload.php">Upload new profile picture</a>
    <div class="settings_details">
        <div class="heading-2">Change Account Details</div>
        <form action="settings.php" method="POST">
            First Name: <input type="text" name="first_name" value="<?php echo $user['first_name']; ?>" />
            Last Name: <input type="text" name="last_name" value="<?php echo $user['last_name']; ?>" />
            Email: <input type="text" name="email" value="<?php echo $user['email']; ?>" />

            <?php echo $message; ?>

            <input type="submit" name="update_details" id="save_details" value="Update Account" />
        </form>
        <div class="heading-2">Change Password</div>
        <form action="settings.php" method="POST">
            Current Password: <input type="password" name="current_password" value="" />
            New Password: <input type="password" name="new_password_1" value="" />
            Confirm Password: <input type="password" name="new_password_2" value="" />

            <?php echo $password_message; ?>

            <input type="submit" name="update_password" id="save_details" value="Update Password" />
        </form>
        <div class="heading-2">Close Account</div>
        <form action="settings.php" method="POST">
            <input type="submit" name="close_account" id="close_account" value="Close Account" />
        </form>
    </div>
</div>