<?php
class Message
{
    private $user_obj;
    private $con;

    // in constructor, pass in connection variable to connect to the database and fetch the user row
    public function __construct($con, $user)
    {
        $this->con = $con;
        $this->user_obj = new User($con, $user);
    }

    public function getMostRecentUser()
    {
        $userLoggedIn = $this->user_obj->getUsername();
        $query = mysqli_query($this->con, "SELECT user_to, user_from FROM messages WHERE user_to='$userLoggedIn' OR user_from='$userLoggedIn' ORDER BY id DESC LIMIT 1");

        if (mysqli_num_rows($query) == 0)
            return false;
        $row = mysqli_fetch_array($query);
        $user_to = $row['user_to'];
        $user_from = $row['user_from'];

        if ($user_to != $userLoggedIn)
            return $user_to;
        else
            return $user_from;
    }

    public function sendMessage($user_to, $body, $date)
    {
        if ($body != "") {
            $userLoggedIn = $this->user_obj->getUsername();
            $query = mysqli_query($this->con, "INSERT INTO messages VALUES('', '$user_to', '$userLoggedIn', '$body', '$date', 'no', 'no', 'no')");
        }
    }

    public function getMessages($otherUser)
    {
        $userLoggedIn = $this->user_obj->getUsername();
        $otherUser_obj = new User($this->con, $otherUser);
        $profile_pic = $otherUser_obj->getProfilePic();
        $data = "";
        $query = mysqli_query($this->con, "UPDATE messages SET opened='yes' WHERE user_to='$userLoggedIn' AND user_from='$otherUser'");
        $get_messages_query = mysqli_query($this->con, "SELECT * FROM messages WHERE (user_to='$userLoggedIn' AND user_from='$otherUser') OR (user_from='$userLoggedIn' AND user_to='$otherUser')");
        while ($row = mysqli_fetch_array($get_messages_query)) {
            $user_to = $row['user_to'];
            $user_from = $row['user_from'];
            $body = $row['body'];

            // if the message is being sent to user logged in
            $div_top = ($user_to == $userLoggedIn)
                ? "<div class='message'>
                <div class='message_item-pic'>
                    <img src='$profile_pic' />
                </div>
                <div class='received_message'>"
                : "<div class='message'><div class='sent_message'>";
            $data = $data . $div_top . $body . "</div></div>";
        }
        return $data;
    }

    public function getLatestMessage($userLoggedIn, $userTo)
    {
        $details_array = array();

        $query = mysqli_query($this->con, "SELECT body, user_to, date FROM messages WHERE (user_to='$userLoggedIn' AND user_from='$userTo') OR (user_to='$userTo' AND user_from='$userLoggedIn') ORDER BY id DESC LIMIT 1");

        $row = mysqli_fetch_array($query);
        $sent_by = ($row['user_to'] == $userLoggedIn) ? "They said: " : "You said: ";

        // get date and time
        $date_time_now = date("Y-m-d H:i:s");
        $start_date = new DateTime($row['date']);     // time of post
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

        array_push($details_array, $sent_by);
        array_push($details_array, $row['body']);
        array_push($details_array, $time_message);
        return $details_array;
    }

    public function getConvos()
    {
        $userLoggedIn = $this->user_obj->getUsername();
        $return_string = "";
        $convos = array();
        $query = mysqli_query($this->con, "SELECT user_to, user_from FROM messages WHERE user_to='$userLoggedIn' OR user_from='$userLoggedIn'");

        while ($row = mysqli_fetch_array($query)) {
            $user_to_push = ($row['user_to'] != $userLoggedIn) ? $row['user_to'] : $row['user_from'];

            if (!in_array($user_to_push, $convos)) {
                array_push($convos, $user_to_push);
            }
        }

        foreach ($convos as $username) {
            $user_found_obj = new User($this->con, $username);
            $latest_message_details = $this->getLatestMessage($userLoggedIn, $username);
            // if latest message body has text
            $dots = (strlen($latest_message_details[1]) >= 12) ? "..." : "";
            $split = str_split($latest_message_details[1], 12);
            $split = $split[0] . $dots;

            $return_string .=
                "<a href='messages.php?u=$username'>
                <div class='messages_convoList'>
                    <img src='" . $user_found_obj->getProfilePic() . "' />
                    <div class='messages_convoList-convo'><div class='messages_convoList-header'><span class='messages_convoList-name'>" . $user_found_obj->getFirstAndLastName() . "</span>
                    <span class='messages_convoList-timestamp'> " . $latest_message_details[2] . " </span></div>
                    <p class='messages_convoList-msg'>" . $latest_message_details[0] . $split . "</p></div>
                </div>
            </a>";
        }

        return $return_string;
    }
}
