<?php
// create new post class
class Post
{
    private $user_obj;
    private $con;

    // in constructor, pass in connection variable to connect to the database and fetch the user row
    public function __construct($con, $user)
    {
        $this->con = $con;
        $this->user_obj = new User($con, $user);
    }

    public function submitPost($body, $user_to)
    {
        $body = strip_tags($body);  // removes html tags
        $body = mysqli_real_escape_string($this->con, $body);    // escapes special escape characters

        $body = str_replace("\r\n", "\n", $body);               // replace new line with line breaks
        $body = nl2br($body);

        $check_empty = preg_replace("/\s+/", "", $body);        // replaces all spaces with "", so this will remove all spaces
        // check if string is empty after removing all spaces
        if ($check_empty != "") {
            // current data and time
            $date_added = date("Y-m-d H:i:s");  // get date in year, month, date, hour, minutes, and seconds

            // get username
            $added_by = $this->user_obj->getUsername();

            // if user is posting on own profile, there is no user_to
            if ($user_to == $added_by) {
                $user_to = "none";
            }

            // insert post to the database
            $query = mysqli_query($this->con, "INSERT INTO posts VALUES('', '$body', '$added_by', '$user_to', '$date_added', 'no', 'no', '0')");
            // returns id of post just inserted
            $returned_id = mysqli_insert_id($this->con);

            // insert notification
            // if posting to someone, create a notification for that person
            if ($user_to != 'none') {
                $notification = new Notification($this->con, $added_by);
                $notification->insertNotification($returned_id, $user_to, "profile_post");
            }

            // update post count for user
            $num_posts = $this->user_obj->getNumPosts();
            $num_posts++;
            $update_query = mysqli_query($this->con, "UPDATE users SET num_posts='$num_posts' WHERE username='$added_by'");
        }
    }

    public function loadPostsFriends($data, $limit)
    {
        $page = $data['page'];
        $userLoggedIn = $this->user_obj->getUsername();

        if ($page == 1)
            $start = 0;
        else
            $start = ($page - 1) * $limit;

        $str = "";  // string to return
        $data_query = mysqli_query($this->con, "SELECT * FROM posts WHERE deleted='no' ORDER BY id DESC");

        if (mysqli_num_rows($data_query) > 0) {

            $num_iterations = 0;    // number of results checked
            $count = 1;

            while ($row = mysqli_fetch_array($data_query)) {
                $id = $row['id'];
                $body = $row['body'];
                $added_by = $row['added_by'];
                $date_time = $row['date_added'];

                // prepare user_to string so it can be included even if not posted to a user
                if ($row['user_to'] == "none") {
                    $user_to = "";
                } else {
                    $user_to_obj = new User($this->con, $row['user_to']);
                    $user_to_name = $user_to_obj->getFirstAndLastName();
                    $user_to = "to <a href='" . $row['user_to'] . "'>" . $user_to_name . "</a>";
                }

                // check if user who posted is a closed account
                $added_by_obj = new User($this->con, $added_by);
                if ($added_by_obj->isClosed()) {
                    continue;
                }

                // only show posts of friends
                $user_logged_obj = new User($this->con, $userLoggedIn);
                if ($user_logged_obj->isFriend($added_by)) {

                    // if we haven't reached $start (index of row we are loading next), we keep looping
                    if ($num_iterations++ < $start) {
                        continue;
                    }

                    // once 10 posts have been loaded, break
                    if ($count > $limit) {
                        break;
                    } else {
                        $count++;
                    }

                    if ($userLoggedIn == $added_by)
                        $delete_button = "<button class='btn delete_button' id='post$id'><i class='fas fa-times'></i></button>";
                    else
                        $delete_button = "";

                    $user_details_query = mysqli_query($this->con, "SELECT first_name, last_name, profile_pic FROM users WHERE username='$added_by'");
                    $user_row = mysqli_fetch_array($user_details_query);
                    $first_name = $user_row['first_name'];
                    $last_name = $user_row['last_name'];
                    $profile_pic = $user_row['profile_pic'];
                    ?>

                    <script>
                        function toggle<?php echo $id; ?>() {
                            var target = $(event.target);
                            if (!target.is("a")) {
                                var element = document.getElementById("toggleComment<?php echo $id; ?>");
                                if (element.style.display == "block")
                                    element.style.display = "none";
                                else
                                    element.style.display = "block";
                            }
                        }
                    </script>
                <?php
                                    $comments_check = mysqli_query($this->con, "SELECT * FROM comments where post_id='$id'");
                                    $comments_num = mysqli_num_rows($comments_check);


                                    // get date and time
                                    $date_time_now = date("Y-m-d H:i:s");
                                    $start_date = new DateTime($date_time);     // time of post
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

                                    $str .= "<div class='post_item' onClick='toggle$id();'>
                        <div class='post_item-pic'>
                            <img src='$profile_pic' />
                        </div>
                         <div class='post_item-header'>
                            <div class='post_item-postedBy'>
                                <div class='post_item-postedByName'>
                                    <a href='$added_by'> $first_name $last_name</a>
                                    $user_to
                                </div>
                                <div class='post_item-postedByTime'>$time_message</div>
                            </div>
                             $delete_button
                        </div>
                        <div class='post_item-body'>
                            $body
                        </div>
                        <div class='post_item-actions'>
                            <div class='post_comment'><i class='fas fa-comment-alt'></i><div class='post_item-numComments'>$comments_num</div></div>
                            <iframe class='post_item-likeIFrame' src='like.php?post_id=$id' frameborder='0' scrolling='no'></iframe>
                        </div>
                        <div class='post_item-comments' id='toggleComment$id' style='display:none;'>
                            <iframe class='post_item-commentIFrame' src='comment_frame.php?post_id=$id' id='post_item-commentIFrame' frameborder='0' scrolling='no'></iframe>
                        </div>
                    </div>
                    <hr>";
                                }

                                ?>
                <script>
                    $(document).ready(function() {
                        $('#post<?php echo $id; ?>').on("click", function() {
                            bootbox.confirm("Are you sure you want to delete this post?", function(result) {
                                $.post("includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>", {
                                    result: result
                                });

                                if (result)
                                    location.reload();
                            });
                        });
                    });
                </script>
            <?php
                        }

                        if ($count > $limit)
                            $str .= "<input type='hidden' class='nextPage' value='" . ($page + 1) . "'>
                            <input type='hidden' class='noMorePosts' value='false'>";
                        else
                            $str .= "<input type='hidden' class='noMorePosts' value='true'><p>No more posts</p>";
                    }
                    echo $str;
                }

                public function loadProfilePosts($data, $limit)
                {
                    $page = $data['page'];
                    $profileUser = $data['profileUsername'];
                    $userLoggedIn = $this->user_obj->getUsername();

                    if ($page == 1)
                        $start = 0;
                    else
                        $start = ($page - 1) * $limit;

                    $str = "";  // string to return
                    $data_query = mysqli_query($this->con, "SELECT * FROM posts WHERE deleted='no' AND ((added_by='$profileUser' AND user_to='none') OR user_to='$profileUser') ORDER BY id DESC");

                    if (mysqli_num_rows($data_query) > 0) {

                        $num_iterations = 0;    // number of results checked
                        $count = 1;

                        while ($row = mysqli_fetch_array($data_query)) {
                            $id = $row['id'];
                            $body = $row['body'];
                            $added_by = $row['added_by'];
                            $date_time = $row['date_added'];

                            // if we haven't reached $start (index of row we are loading next), we keep looping
                            if ($num_iterations++ < $start) {
                                continue;
                            }

                            // once 10 posts have been loaded, break
                            if ($count > $limit) {
                                break;
                            } else {
                                $count++;
                            }

                            if ($userLoggedIn == $added_by)
                                $delete_button = "<button class='btn delete_button' id='post$id'><i class='fas fa-times'></i></button>";
                            else
                                $delete_button = "";

                            $user_details_query = mysqli_query($this->con, "SELECT first_name, last_name, profile_pic FROM users WHERE username='$added_by'");
                            $user_row = mysqli_fetch_array($user_details_query);
                            $first_name = $user_row['first_name'];
                            $last_name = $user_row['last_name'];
                            $profile_pic = $user_row['profile_pic'];
                            ?>

                <script>
                    function toggle<?php echo $id; ?>() {
                        var target = $(event.target);
                        if (!target.is("a")) {
                            var element = document.getElementById("toggleComment<?php echo $id; ?>");
                            if (element.style.display == "block")
                                element.style.display = "none";
                            else
                                element.style.display = "block";
                        }
                    }
                </script>
                <?php
                                $comments_check = mysqli_query($this->con, "SELECT * FROM comments where post_id='$id'");
                                $comments_num = mysqli_num_rows($comments_check);


                                // get date and time
                                $date_time_now = date("Y-m-d H:i:s");
                                $start_date = new DateTime($date_time);     // time of post
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

                                $str .= "<div class='post_item' onClick='toggle$id();'>
                        <div class='post_item-pic'>
                            <img src='$profile_pic' />
                        </div>
                         <div class='post_item-header'>
                            <div class='post_item-postedBy'>
                                <div class='post_item-postedByName'>
                                    <a href='$added_by'> $first_name $last_name</a>
                                </div>
                                <div class='post_item-postedByTime'>$time_message</div>
                            </div>
                             $delete_button
                        </div>
                        <div class='post_item-body'>
                            $body
                        </div>
                        <div class='post_item-actions'>
                            <div class='post_comment'><i class='fas fa-comment-alt'></i><div class='post_item-numComments'>$comments_num</div></div>
                            <iframe class='post_item-likeIFrame' src='like.php?post_id=$id' frameborder='0' scrolling='no'></iframe>
                        </div>
                        <div class='post_item-comments' id='toggleComment$id' style='display:none;'>
                            <iframe class='post_item-commentIFrame' src='comment_frame.php?post_id=$id' id='post_item-commentIFrame' frameborder='0' scrolling='no'></iframe>
                        </div>
                    </div>
                    <hr>";

                                ?>
                <script>
                    $(document).ready(function() {
                        $('#post<?php echo $id; ?>').on("click", function() {
                            bootbox.confirm("Are you sure you want to delete this post?", function(result) {
                                $.post("includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>", {
                                    result: result
                                });

                                if (result)
                                    location.reload();
                            });
                        });
                    });
                </script>
<?php
            }

            if ($count > $limit)
                $str .= "<input type='hidden' class='nextPage' value='" . ($page + 1) . "'>
                            <input type='hidden' class='noMorePosts' value='false'>";
            else
                $str .= "<input type='hidden' class='noMorePosts' value='true'><p class='post_item-end'>No more posts</p>";
        }
        echo $str;
    }

    public function getSinglePost($post_id) {
        $userLoggedIn = $this->user_obj->getUsername();

        // set messages to viewed
        $set_opened_query = mysqli_query($this->con, "UPDATE notifications SET opened='yes' WHERE user_to='$userLoggedIn' AND link LIKE '%=$post_id'");

        $str = "";  // string to return
        $data_query = mysqli_query($this->con, "SELECT * FROM posts WHERE deleted='no' AND id='$post_id'");

        if (mysqli_num_rows($data_query) > 0) {

                $row = mysqli_fetch_array($data_query);
                $id = $row['id'];
                $body = $row['body'];
                $added_by = $row['added_by'];
                $date_time = $row['date_added'];

                // prepare user_to string so it can be included even if not posted to a user
                if ($row['user_to'] == "none") {
                    $user_to = "";
                } else {
                    $user_to_obj = new User($this->con, $row['user_to']);
                    $user_to_name = $user_to_obj->getFirstAndLastName();
                    $user_to = "to <a href='" . $row['user_to'] . "'>" . $user_to_name . "</a>";
                }

                // check if user who posted is a closed account
                $added_by_obj = new User($this->con, $added_by);
                if ($added_by_obj->isClosed()) {
                    return;
                }

                // only show posts of friends
                $user_logged_obj = new User($this->con, $userLoggedIn);
                if ($user_logged_obj->isFriend($added_by)) {

                    if ($userLoggedIn == $added_by)
                        $delete_button = "<button class='btn delete_button' id='post$id'><i class='fas fa-times'></i></button>";
                    else
                        $delete_button = "";

                    $user_details_query = mysqli_query($this->con, "SELECT first_name, last_name, profile_pic FROM users WHERE username='$added_by'");
                    $user_row = mysqli_fetch_array($user_details_query);
                    $first_name = $user_row['first_name'];
                    $last_name = $user_row['last_name'];
                    $profile_pic = $user_row['profile_pic'];
                    ?>

                    <script>
                        function toggle<?php echo $id; ?>() {
                            var target = $(event.target);
                            if (!target.is("a")) {
                                var element = document.getElementById("toggleComment<?php echo $id; ?>");
                                if (element.style.display == "block")
                                    element.style.display = "none";
                                else
                                    element.style.display = "block";
                            }
                        }
                    </script>
                <?php
                                    $comments_check = mysqli_query($this->con, "SELECT * FROM comments where post_id='$id'");
                                    $comments_num = mysqli_num_rows($comments_check);


                                    // get date and time
                                    $date_time_now = date("Y-m-d H:i:s");
                                    $start_date = new DateTime($date_time);     // time of post
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

                                    $str .= "<div class='post_item' onClick='toggle$id();'>
                        <div class='post_item-pic'>
                            <img src='$profile_pic' />
                        </div>
                         <div class='post_item-header'>
                            <div class='post_item-postedBy'>
                                <div class='post_item-postedByName'>
                                    <a href='$added_by'> $first_name $last_name</a>
                                    $user_to
                                </div>
                                <div class='post_item-postedByTime'>$time_message</div>
                            </div>
                             $delete_button
                        </div>
                        <div class='post_item-body'>
                            $body
                        </div>
                        <div class='post_item-actions'>
                            <div class='post_comment'><i class='fas fa-comment-alt'></i><div class='post_item-numComments'>$comments_num</div></div>
                            <iframe class='post_item-likeIFrame' src='like.php?post_id=$id' frameborder='0' scrolling='no'></iframe>
                        </div>
                        <div class='post_item-comments' id='toggleComment$id' style='display:none;'>
                            <iframe class='post_item-commentIFrame' src='comment_frame.php?post_id=$id' id='post_item-commentIFrame' frameborder='0' scrolling='no'></iframe>
                        </div>
                    </div>
                    <hr>";
                

                ?>
                <script>
                    $(document).ready(function() {
                        $('#post<?php echo $id; ?>').on("click", function() {
                            bootbox.confirm("Are you sure you want to delete this post?", function(result) {
                                $.post("includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>", {
                                    result: result
                                });

                                if (result)
                                    location.reload();
                            });
                        });

                        $(".loading").hide();
                    });
                </script>
            <?php
            } 
            // if not friends, cannot view post
            else {
                echo "<div class='post_item-error'>You cannot see this post because you are not friends with this user</div>";
                return;
            }
        } 
        else {
            echo "<div class='post_item-error'>No post found</div>";
            return;
        }
        echo $str;
    }
}
