<?php
class Notification
{
    private $user_obj;
    private $con;

    // in constructor, pass in connection variable to connect to the database and fetch the user row
    public function __construct($con, $user)
    {
        $this->con = $con;
        $this->user_obj = new User($con, $user);
    }

    public function insertNotification($post_id, $user_to, $type)
    {
        $userLoggedIn = $this->user_obj->getUsername();
        $userLoggedInName = $this->user_obj->getFirstAndLastName();

        $date_time = date("Y-m-d H:i:s");

        switch ($type) {
            case "comment":
                $message = $userLoggedInName . " commented on your post";
                break;
            case "like":
                $message = $userLoggedInName . " liked your post";
                break;
            case "profile":
                $message = $userLoggedInName . " posted on your profile";
                break;
            case "comment_non_owner":
                $message = $userLoggedInName . " commented on a post you commented on";
                break;
            case "profile_comment":
                $message = $userLoggedInName . " commented on your profile post";
                break;
        }

        $link = "post.php?id=" . $post_id;
        $insert_query = mysqli_query($this->con, "INSERT INTO notifications VALUES('', '$user_to', '$userLoggedIn', '$message', '$link', '$date_time', 'no', 'no')");
    }

    public function getNotificationsDropdown($data, $limit)
    {
        $page = $data['page'];  // url param sent from ajax request
        $userLoggedIn = $this->user_obj->getUsername();
        $return_string = "";

        if ($page == 1)
            $start = 0;
        else
            $start = ($page - 1) * $limit;

        // set messages to viewed
        $set_viewed_query = mysqli_query($this->con, "UPDATE notifications SET viewed='yes' WHERE user_to='$userLoggedIn'");

        $query = mysqli_query($this->con, "SELECT * FROM notifications WHERE user_to='$userLoggedIn' ORDER BY id DESC");

        if (mysqli_num_rows($query) == 0) {
            echo "You have no notifications";
            return;
        }

        $num_iterations = 0;    // number of message checked
        $count = 1;             // number of messages posted

        while ($row = mysqli_fetch_array($query)) {
            // keep looping until we have found our start position
            if ($num_iterations++ < $start)
                continue;

            if ($count > $limit)
                break;
            else
                $count++;

            $user_from = $row['user_from'];
            $query_user_data = mysqli_query($this->con, "SELECT * FROM users WHERE username='$user_from'");
            $user_data = mysqli_fetch_array($query_user_data);

            // get date and time
            $date_time_now = date("Y-m-d H:i:s");
            $start_date = new DateTime($row['datetime']);     // time of post
            $end_date = new DateTime($date_time_now);   // current time
            $interval = $start_date->diff($end_date);   // difference between dates
            // if posted 1 or more year ago
            if ($interval->y >= 1) {
                if ($interval == 1)
                    $time_message = $interval->y . " y";     // 1 year ago
                else
                    $time_message = $interval->y . " y";    // 1+ years ago
            } else if ($interval->m >= 1) {
                if ($interval->d == 0) {
                    $days = " ago";                                 // no additional days
                } else if ($interval->d == 1) {
                    $days = $interval->d . " d";              // 1 day ago
                } else {
                    $days = $interval->d . " d";             // 1+ days ago
                }

                if ($interval->m == 1) {
                    $time_message = $interval->m . "m" . $days;    // 1 month ago
                } else {
                    $time_message = $interval->m . "m" . $days;   // 1+ months ago
                }
            } else if ($interval->d >= 1) {
                if ($interval->d == 1) {
                    $time_message = "Yesterday";
                } else {
                    $time_message = $interval->d . "d";
                }
            } else if ($interval->h >= 1) {
                if ($interval->h == 1) {
                    $time_message = $interval->h . "h";
                } else {
                    $time_message = $interval->h . "h";
                }
            } else if ($interval->i >= 1) {
                if ($interval->i == 1) {
                    $time_message = $interval->i . "m";
                } else {
                    $time_message = $interval->i . "m";
                }
            } else {
                if ($interval->s < 30) {
                    $time_message = "Just now";    // less than 30 seconds ago - just now
                } else {
                    $time_message = $interval->s . "s";
                }
            }

            $opened = $row['opened'];
            $style = ($opened == 'no') ? "background-color: var(--primary-color-light);" : "";

            $return_string .=
                "<a class='dropdown_data_window-item' href='" . $row['link'] . "'>
                <div class='dropdown_data_window-convoItem' style='" . $style . "'>
                    <img src='" . $user_data['profile_pic'] . "' />
                    <div class='dropdown_data_window-convo'><div class='dropdown_data_window-header'><span class='dropdown_data_window-name'>" . $user_data['first_name'] . " " . $user_data['last_name'] . "</span>
                    <span class=dropdown_data_window-timestamp> " . $time_message . " </span></div>
                    <p class='dropdown_data_window-notif'>" . $row['message'] . "</p></div>
                </div>
            </a>";
        }

        // if convos loaded
        if ($count > $limit)
            $return_string .= "<input type='hidden' class='nextPageDropdownData' value='" . ($page + 1) . "' />
                                <input type='hidden' class='noMoreDropdownData' value='false' />
                                <input type='hidden' id='dropdown_data_type' value='notification' />";
        else
            $return_string .= "<input type='hidden' class='noMoreDropdownData' value='true' />
                                <p class='convos_item-end'>No more notifications</p>
                                <input type='hidden' id='dropdown_data_type' value='notification' />";

        return $return_string;
    }

    public function getUnreadNumber()
    {
        $userLoggedIn = $this->user_obj->getUsername();
        $query = mysqli_query($this->con, "SELECT * FROM notifications WHERE opened='no' AND user_to='$userLoggedIn'");
        return mysqli_num_rows($query);
    }
}
