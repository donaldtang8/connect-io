<?php
include("includes/header.php");

$message_obj = new Message($con, $userLoggedIn);

// from rewrite rule of .htaccess
if (isset($_GET['profile_username'])) {
    $username = $_GET['profile_username'];
    $user_details_query = mysqli_query($con, "SELECT * FROM users WHERE username='$username'");
    $user_array = mysqli_fetch_array($user_details_query);

    // profile info
    $num_friends = (substr_count($user_array['friend_array'], ",")) - 1;
}

if (isset($_POST['remove_friend'])) {
    $user = new User($con, $userLoggedIn);
    $user->removeFriend($username);
}

if (isset($_POST['add_friend'])) {
    $user = new User($con, $userLoggedIn);
    $user->sendRequest($username);
}

if (isset($_POST['respond_request_friend'])) {
    header("Location: requests.php");
}

if (isset($_POST['post_message'])) {
    if (isset($_POST['message_body'])) {
        $body = mysqli_real_escape_string($con, $_POST['message_body']);
        $date = date("Y-m-d H:i:s");
        $message_obj->sendMessage($username, $body, $date);
    }

    $link = '#profile_tabs a[href="#messages"]';
    echo "<script>
        $(function() {
            $('" . $link . "').tab('show');
        });
    </script>";
}
?>
<div class="profile">
    <div class="profile_sidebar">
        <div class="profile_pic">
            <img class="profile_pic-img" src="<?php echo $user_array['profile_pic']; ?>" alt="">
            <a class="profile_pic-edit" href="upload.php">
                Edit
            </a>
        </div>
        <div class="profile_info">
            <div class="profile_info-item"><?php echo $user_array['first_name'] . " " . $user_array['last_name']; ?></div>
            <div class="profile_info-item"><?php echo "Friends: " . $num_friends ?></div>
            <div class="profile_info-item"><?php echo "Posts: " . $user_array['num_posts']; ?></div>
            <!-- <div class="profile_info-item"><?php echo "Likes: " . $user_array['num_likes']; ?></div> -->
        </div>
        <div class="profile_friend">
            <?php
                                                $logged_in_user_obj = new User($con, $userLoggedIn);
                                                if ($userLoggedIn != $username) {
                                                    echo '<div class="profile_info_bottom">';
                                                    echo $logged_in_user_obj->getMutualFriends($username) . " mutual friends";
                                                    echo '</div>';
                                                }
            ?>
            <form action="<?php echo $username; ?>" method="POST">
                <?php $profile_user_obj = new User($con, $username);
                                                if ($profile_user_obj->isClosed()) {
                                                    header("Location: user_closed.php");
                                                }


                                                // check if user that is logged in is already friends with the user on profile
                                                if ($userLoggedIn != $username) {
                                                    if ($logged_in_user_obj->isFriend($username)) {
                                                        echo '<input class="btn btn-friend" type="submit" name="remove_friend" value="Remove Friend" />';
                                                    } else if ($logged_in_user_obj->didReceiveRequest($username)) {
                                                        echo '<input class="btn btn-friend" type="submit" name="respond_request_friend" value="Respond to request" />';
                                                    } else if ($logged_in_user_obj->didSendRequest($username)) {
                                                        echo '<input class="btn btn-friend" type="submit" name="" value="Request pending" />';
                                                    } else {
                                                        echo '<input class="btn btn-friend" type="submit" name="add_friend" value="Add Friend" />';
                                                    }
                                                }
                ?>
            </form>
        </div>
    </div>
    <div class="profile_main">

        <ul class="nav nav-tabs" role="tablist" id="profile_tabs">
            <li role="presentation" class="nav-item active">
                <a class="nav-link active" href="#newsfeed" data-toggle="tab">Newsfeed</a>
            </li>
            <li role="presentation" class="nav-item">
                <a class="nav-link" href="#messages" data-toggle="tab">Messages</a>
            </li>

        </ul>

        <div class="tab-content">
            <div class="tab-pane active" id="newsfeed">
                <div class="profile_button">
                    <input class="btn btn-primary" type="submit" data-toggle="modal" data-target="#post_form" value="Create a post" />
                </div>
                <div class="posts">
                </div>
                <div class="loading">Loading...</div>
            </div>
            <div class="tab-pane" id="messages">
                <?php
                                                echo "<div class='heading-1'>You and <a href='" . $username . "'>" . $profile_user_obj->getFirstAndLastName() . "</a><hr></div>";
                                                echo "<div class='loaded_messages scroll_messages' id='scroll_messages'>";
                                                echo $message_obj->getMessages($username);
                                                echo "</div>";
                ?>

                <div class="message_post">
                    <hr>
                    <form class="message_post-form" action="" method="POST">
                        <div class='message_compose'><textarea name='message_body' class='message_textarea' placeholder='Write your message'></textarea><input type='submit' name='post_message' class='btn message_submit' value='Send' /></div>
                    </form>
                </div>
                <script>
                    var div = document.getElementById("scroll_messages");
                    div.scrollTop = div.scrollHeight;
                </script>
            </div>
        </div>
    </div>


    <!-- Modal -->
    <div class="modal fade" id="post_form" tabindex="-1" role="dialog" aria-labelledby="postModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form class="profile_post" action="" method="POST">
                        <a class="profile_post-pic" href="<?php echo $userLoggedIn ?>"> <img src="<?php echo $user["profile_pic"]; ?>" alt="<?php echo $user["first_name"] . " " . $user["last_name"]; ?>"></a>
                        <textarea class="profile_post-body" name="profile_post-body" id="profile_post-body" placeholder="Write something here..."></textarea>
                        <input type="hidden" name="user_from" value="<?php echo $userLoggedIn ?>" />
                        <input type="hidden" name="user_to" value="<?php echo $username ?>" />
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" name="profile_post-btn" id="submit_profile_post">Post</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var userLoggedIn = '<?php echo $userLoggedIn; ?>';
    var profileUsername = '<?php echo $username; ?>';

    $(document).ready(function() {
        $(".loading").show(); // show spinner

        // ajax request for loading first posts
        $.ajax({
            url: "includes/handlers/ajax_load_profile_posts.php",
            type: "POST",
            data: "page=1&userLoggedIn=" + userLoggedIn + "&profileUsername=" + profileUsername,
            cache: false,

            success: function(data) {
                $(".loading").hide();
                $(".posts").html(data)
            }
        });

        $(window).scroll(function() {
            var height = $(".posts").height();
            var scroll_top = $(this).scrollTop(); // dynamically tracks the top of the page on scrollbar
            var page = $(".posts").find(".nextPage").val(); // this will retrieve the value of the hidden input field to get page count
            var noMorePosts = $(".posts").find(".noMorePosts").val(); // this will retrieve the value of hidden input field to get boolean if there are any posts to find

            // if we scroll to the bottom and "noMorePosts" is false, we make another ajax request to fetch posts
            if ((document.body.scrollHeight == document.body.scrollTop + window.innerHeight) && noMorePosts == "false") {
                $(".loading").show();

                var ajaxReq = $.ajax({
                    url: "includes/handlers/ajax_load_profile_posts.php",
                    type: "POST",
                    data: "page=" + page + "&userLoggedIn=" + userLoggedIn + "&profileUsername=" + profileUsername,
                    cache: false,

                    success: function(response) {
                        $(".nextPage").remove(); // removes current .nextpage
                        $(".noMorePosts").remove(); // removes current .noMorePosts
                        $(".loading").hide();
                        $(".posts").append(response);
                    }
                });
            }
            return false;
        });
    });
</script>

</body>

</html>