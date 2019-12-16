<?php
require 'config/config.php';
include("includes/classes/User.php");
include("includes/classes/Post.php");
include("includes/classes/Notification.php");

if (isset($_SESSION['username'])) {
    $userLoggedIn = $_SESSION['username'];
    $user_details_query = mysqli_query($con, "SELECT * FROM users WHERE username='$userLoggedIn'");
    $user = mysqli_fetch_array($user_details_query);
} else {
    header("Location: register.php");
}

?>
<html>

<head>
    <title></title>
    <link rel="stylesheet" type="text/css" href="assets/css/layout.css">
</head>

<body class="comment_section-body">

    <script>
        function toggle() {
            var element = document.getElementById("comment_section");

            if (element.style.display == "flex")
                element.style.display = "none";
            else
                element.style.display = "flex";
        }
    </script>

    <?php
    //Get id of post
    if (isset($_GET['post_id'])) {
        $post_id = $_GET['post_id'];
    }

    $user_query = mysqli_query($con, "SELECT added_by, user_to FROM posts WHERE id='$post_id'");
    $row = mysqli_fetch_array($user_query);

    // person who created the post
    $posted_to = $row['added_by'];
    // direction of the post (profile, other person)
    $user_to = $row['user_to'];

    if (isset($_POST['post_body'])) {
        $post_body = $_POST['post_body'];
        $post_body = mysqli_escape_string($con, $post_body);
        $date_time_now = date("Y-m-d H:i:s");
        $insert_post = mysqli_query($con, "INSERT INTO comments VALUES ('', '$post_body', '$userLoggedIn', '$posted_to', '$date_time_now', 'no', '$post_id')");

        // if posting comment to someone else's post, add notification
        if ($posted_to != $userLoggedIn) {
            $notification = new Notification($con, $userLoggedIn);
            $notification->insertNotification($post_id, $posted_to, "comment");
        } 

        // if commenting on a profile post 
        if ($user_to != "none" && $user_to != $userLoggedIn) {
            $notification = new Notification($con, $userLoggedIn);
            $notification->insertNotification($post_id, $user_to, "profile_comment");
        }

        // notify all commenters of a post when a new comment is made
        $get_commenters = mysqli_query($con, "SELECT * FROM comments WHERE post_id='$post_id'");
        $notified_users = array();
        while($row = mysqli_fetch_array($get_commenters)) {
            // if person who posted comment is not the original poster and 
            // the person who posted the comment is not the profile owner and
            // the person who posted the comment is not the user logged in and
            // the person who posted the comment is not already in the array (no reason to put notifications for same user who commented multiple times)
            // reason why we check this is because we already give notifications for post owners and profile owners
            if($row['posted_by'] != $posted_to && $row['posted_by'] != $user_to && $row['posted_by'] != $userLoggedIn && !in_array($row['posted_by'], $notified_users)) {
                $notification = new Notification($con, $userLoggedIn);
                $notification->insertNotification($post_id, $row['posted_by'], "comment_non_owner");
                array_push($notified_users, $row['posted_by']);
            }
        }

        echo "<p>Comment Posted! </p>";
    }
    ?>
    <div class="comment_postComment">
        <div class="comment_profilePic"><img src="<?php echo $user["profile_pic"]; ?>" alt="<?php echo $user["first_name"]; ?>" /></div>
        <form action="comment_frame.php?post_id=<?php echo $post_id; ?>" class="post_item-commentForm" id="comment_form" name="postComment<?php echo $post_id; ?>" method="POST">
            <!-- <textarea class="comment_textArea" name="post_body" placeholder="Write a comment"></textarea> -->
            <input type="text" class="comment_postComment-input" name="post_body" placeholder="Write a comment" />
            <!-- <input type="submit" name="postComment<?php echo $post_id; ?>" value="Post"> -->
        </form>
    </div>


    <!-- Load comments -->
    <?php
    $get_comments = mysqli_query($con, "SELECT * FROM comments WHERE post_id='$post_id' ORDER BY id ASC");
    $count = mysqli_num_rows($get_comments);

    if ($count != 0) {
        while ($comment = mysqli_fetch_array($get_comments)) {
            $comment_body = $comment['post_body'];
            $posted_to = $comment['posted_to'];
            $posted_by = $comment['posted_by'];
            $date_added = $comment['date_added'];
            $removed = $comment['removed'];

            // get date and time
            $date_time_now = date("Y-m-d H:i:s");
            $start_date = new DateTime($date_added);     // time of post
            $end_date = new DateTime($date_time_now);   // current time
            $interval = $start_date->diff($end_date);   // difference between dates
            // if posted 1 or more year ago
            if ($interval->y >= 1) {
                if ($interval == 1)
                    $time_message = $interval->y . " year ago";     // 1 year ago
                else
                    $time_message = $interval->y . " years ago";    // 1+ years ago
            } else if ($interval->m >= 1) {
                if ($interval->d == 0) {
                    $days = " ago";                                 // no additional days
                } else if ($interval->d == 1) {
                    $days = $interval->d . " day ago";              // 1 day ago
                } else {
                    $days = $interval->d . " days ago";             // 1+ days ago
                }

                if ($interval->m == 1) {
                    $time_message = $interval->m . " month" . $days;    // 1 month ago
                } else {
                    $time_message = $interval->m . " months" . $days;   // 1+ months ago
                }
            } else if ($interval->d >= 1) {
                if ($interval->d == 1) {
                    $time_message = "Yesterday";
                } else {
                    $time_message = $interval->d . " days ago";
                }
            } else if ($interval->h >= 1) {
                if ($interval->h == 1) {
                    $time_message = $interval->h . " hour ago";
                } else {
                    $time_message = $interval->h . " hours ago";
                }
            } else if ($interval->i >= 1) {
                if ($interval->i == 1) {
                    $time_message = $interval->i . " minute ago";
                } else {
                    $time_message = $interval->i . " minutes ago";
                }
            } else {
                if ($interval->s < 30) {
                    $time_message = "Just now";    // less than 30 seconds ago - just now
                } else {
                    $time_message = $interval->s . " seconds ago";
                }
            }

            $user_obj = new User($con, $posted_by);
            ?>
            <div class="comment_item">
                <a class="comment_item-pic" href="<?php echo $posted_by ?>" target="_parent">
                    <img src="<?php echo $user_obj->getProfilePic(); ?>" alt="<?php echo $posted_by; ?>" />
                </a>
                <div class="comment_item-postedBy">
                    <a class="comment_item-postedByName" href="<?php echo $posted_by ?>" target="_parent"><?php echo $user_obj->getFirstAndLastName() ?></a>
                    <div class="comment_item-postedByTime"><?php echo $time_message; ?></div>
                </div>
                <div class="comment_item-body"><?php echo $comment_body; ?></div>
            </div>
    <?php
        }
    } else {
        echo "<div class='comment_item-error'>No Comments</div>";
    }
    ?>

</body>

</html>