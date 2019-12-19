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

    public function submitPost($body, $user_to, $imageName)
    {
        $body = strip_tags($body);  // removes html tags
        $body = mysqli_real_escape_string($this->con, $body);    // escapes special escape characters

        $body = str_replace('\r\n', "\n", $body);               // replace new line with line breaks
        $body = nl2br($body);

        $check_empty = preg_replace('/\s+/', "", $body);        // replaces all spaces with "", so this will remove all spaces
        // check if string is empty after removing all spaces
        if ($check_empty != "") {

            // split body at the spaces into words and put them in an array
            // ex. if someone posts: "Check this out! [youtube link]" Then we want an array with [Check, this, out!, [youtube link]]
            $body_array = preg_split('/\s+/', $body);
            foreach($body_array as $key => $value) {
                // check if there is a youtube link in body
                if(strpos($value, "www.youtube.com/watch?v=") !== false) {
                    $link = preg_split("!&!", $value);
                    $value = preg_replace("!watch\?v=!", "embed/", $link[0]);
                    $value = "<br><iframe class=\'post_item-video\' src=\'" . $value . "\'></iframe><br>";
                    $body_array[$key] = $value;
                }
            }
            
            // put array back in body
            $body = implode(" ", $body_array);

            // current data and time
            $date_added = date("Y-m-d H:i:s");  // get date in year, month, date, hour, minutes, and seconds

            // get username
            $added_by = $this->user_obj->getUsername();

            // if user is posting on own profile, there is no user_to
            if ($user_to == $added_by) {
                $user_to = "none";
            }

            // insert post to the database
            $query = mysqli_query($this->con, "INSERT INTO posts VALUES('', '$body', '$added_by', '$user_to', '$date_added', 'no', 'no', '0', '$imageName')");
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

            // create a list of stop words - words not to check for trending
            $stopWords = "a about above across after again against all almost alone along already
			 also although always among am an and another any anybody anyone anything anywhere are 
			 area areas around as ask asked asking asks at away b back backed backing backs be became
			 because become becomes been before began behind being beings best better between big 
			 both but by c came can cannot case cases certain certainly clear clearly come could
			 d did differ different differently do does done down down downed downing downs during
			 e each early either end ended ending ends enough even evenly ever every everybody
			 everyone everything everywhere f face faces fact facts far felt few find finds first
			 for four from full fully further furthered furthering furthers g gave general generally
			 get gets give given gives go going good goods got great greater greatest group grouped
			 grouping groups h had has have having he her here herself high high high higher
		     highest him himself his how however i im if important in interest interested interesting
			 interests into is it its itself j just k keep keeps kind knew know known knows
			 large largely last later latest least less let lets like likely long longer
			 longest m made make making man many may me member members men might more most
			 mostly mr mrs much must my myself n necessary need needed needing needs never
			 new new newer newest next no nobody non noone not nothing now nowhere number
			 numbers o of off often old older oldest on once one only open opened opening
			 opens or order ordered ordering orders other others our out over p part parted
			 parting parts per perhaps place places point pointed pointing points possible
			 present presented presenting presents problem problems put puts q quite r
			 rather really right right room rooms s said same saw say says second seconds
			 see seem seemed seeming seems sees several shall she should show showed
			 showing shows side sides since small smaller smallest so some somebody
			 someone something somewhere state states still still such sure t take
			 taken than that the their them then there therefore these they thing
			 things think thinks this those though thought thoughts three through
	         thus to today together too took toward turn turned turning turns two
			 u under until up upon us use used uses v very w want wanted wanting
			 wants was way ways we well wells went were what when where whether
			 which while who whole whose why will with within without work
			 worked working works would x y year years yet you young younger
			 youngest your yours z lol haha omg hey ill iframe wonder else like 
             hate sleepy reason for some little yes bye choose";

             // turn stop words into array by splitting all white spaces
             $stopWords = preg_split("/[\s,]+/", $stopWords);

             // remove all punctuation - anything that is not a letter or number
             $no_punct = preg_replace("/[^a-zA-Z 0-9]+/", "", $body);

             // we don't want to consider any links, articles, or videos as trends, so check to make sure it isn't any one of them
             if(strpos($no_punct, "http") === false) {
                 // turn post into array of words
                 $no_punct = preg_split("/[\s,]+/", $no_punct);

                 foreach($stopWords as $value) {
                     foreach($no_punct as $key => $value2) {
                         if(strtolower($value) === strtolower($value2)) {
                             $no_punct[$key] = "";
                         }
                     }
                 }

                 foreach($no_punct as $value) {
                     $this->calculateTrend(ucfirst($value));
                 }
             }

        }
    }

    public function calculateTrend($term) {
        if ($term != "") {
            $query = mysqli_query($this->con, "SELECT * FROM trends WHERE title='$term'");

            // if trend isn't in database, insert
            if (mysqli_num_rows($query) == 0) {
                $insert_query = mysqli_query($this->con, "INSERT INTO trends(title, hits) VALUES('$term', '1')");
            } else {
                $insert_query = mysqli_query($this->con, "UPDATE trends SET hits=hits+1 WHERE title='$term'");
            }
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
                $imagePath = $row['image'];

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
                                    // if post has image, create div element for it
                                    if ($imagePath != "") {
                                        $imageDiv = "<div class='post_item-image'>
                                                        <img src='$imagePath' />
                                                    </div>";
                                    } else {
                                        $imageDiv = "";
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
                            $imageDiv
                        </div>
                        <div class='post_item-actions'>
                            <div class='post_comment'><i class='fas fa-comment-alt'></i><div class='post_item-numComments'>$comments_num</div></div>
                            <iframe class='post_item-likeIFrame' src='like.php?post_id=$id' frameborder='0' scrolling='no'></iframe>
                        </div>
                        <div class='post_item-comments' id='toggleComment$id' style='display:none;'>
                            <iframe class='post_item-commentIFrame' src='comment_frame.php?post_id=$id' id='post_item-commentIFrame' frameborder='0'></iframe>
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
