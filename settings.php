<?php
include("includes/header.php");
include("includes/form_handlers/settings_handler.php");
?>

<div class="heading-1">Account Settings</div>
<div class="settings_details">
    <div class="heading-2">Change Account Details</div>
    <form action="settings.php" method="POST">
        <div class="settings_item">First Name: <input type="text" name="first_name" value="<?php echo $user['first_name']; ?>" /></div>
        <div class="settings_item">Last Name: <input type="text" name="last_name" value="<?php echo $user['last_name']; ?>" /></div>
        <div class="settings_item">Email: <input type="text" name="email" value="<?php echo $user['email']; ?>" /></div>

        <div class="settings_error"><?php echo $message; ?></div>

        <input class="update_details" type="submit" name="update_details" id="save_details" value="Update Account" />
    </form>
    <div class="heading-2">Change Password</div>
    <form action="settings.php" method="POST">
        <div class="settings_item">Current Password: <input type="password" name="current_password" value="" /></div>
        <div class="settings_item">New Password: <input type="password" name="new_password_1" value="" /></div>
        <div class="settings_item">Confirm Password: <input type="password" name="new_password_2" value="" /></div>

        <div class="settings_error"><?php echo $password_message; ?></div>

        <input class="update_details" type="submit" name="update_password" id="save_details" value="Update Password" />
    </form>
    <div class="heading-2">Close Account</div>
    <form action="settings.php" method="POST">
        <input class="update_details" type="submit" name="close_account" id="close_account" value="Close Account" />
    </form>
</div>
</div>