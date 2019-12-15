<?php
include("includes/header.php");

$message_obj = new Message($con, $userLoggedIn);
// url parameter will tell us who we are messaging
// if there is no parameter, get mostrecentuser, if none then users_to will be new
if (isset($_GET['u']))
    $user_to = $_GET['u'];
else {
    $user_to = $message_obj->getMostRecentUser();
    if ($user_to == false)
        $user_to = 'new';
}

if ($user_to != "new")
    $user_to_obj = new User($con, $user_to);

if (isset($_POST['post_message'])) {
    if (isset($_POST['message_body'])) {
        $body = mysqli_real_escape_string($con, $_POST['message_body']);
        $date = date("Y-m-d H:i:s");
        $message_obj->sendMessage($user_to, $body, $date);
    }
}
?>

<div class="messages">
    <div class="messages_sidebar">
        <div class="messages_sidebar--user">
            <div class="user_pic">
                <a href="<?php echo $userLoggedIn ?>"> <img src="<?php echo $user["profile_pic"]; ?>" alt="<?php echo $user["first_name"] . " " . $user["last_name"]; ?>"></a>
            </div>
            <div class="user_details">
                <ul class="user_details--list">
                    <li class="user_details--item"><a href="<?php echo $userLoggedIn ?>"><?php echo $user["first_name"] . " " . $user["last_name"]; ?></a></li>
                    <li class="user_details--item">Posts: <?php echo $user["num_posts"]; ?></li>
                </ul>
            </div>
        </div>
        <div class="messages_sidebar--convos">
            <div class="heading-1">Conversations</div>
            <div class="loaded_conversations">
                <?php echo $message_obj->getConvos(); ?>
            </div>
            <hr>
            <a href="messages.php?u=new">New Message</a>
        </div>
    </div>
    <div class="messages_main">
        <?php
        if ($user_to != "new") {
            echo "<div class='heading-1'>You and <a href='$user_to'>" . $user_to_obj->getFirstAndLastName() . "</a><hr></div>";
            echo "<div class='loaded_messages scroll_messages' id='scroll_messages'>";
            echo $message_obj->getMessages($user_to);
            echo "</div>";
        } else {
            echo "<div class='heading-1'>New Message</div>";
        }
        ?>

        <div class="message_post">
            <hr>
            <form class="message_post-form" action="" method="POST">
                <?php
                if ($user_to == "new") {
                    ?>
                    <input class="message_post-search" type='text' onkeyup='getUsers(this.value, "<?php echo $userLoggedIn; ?>")' name='q' placeholder='Search Friend' autocomplete='off' id='search_text_input' />
                <?php
                    echo "<div class='results'></div>";
                } else {
                    echo "<div class='message_compose'><textarea name='message_body' class='message_textarea' placeholder='Write your message'></textarea><input type='submit' name='post_message' class='btn message_submit' value='Send' /></div>";
                }
                ?>
            </form>
        </div>
        <script>
            var div = document.getElementById("scroll_messages");
            div.scrollTop = div.scrollHeight;
        </script>
    </div>

</div>