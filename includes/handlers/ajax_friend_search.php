<?php
include("../../config/config.php");
include("../classes/User.php");

$query = $_POST['query'];
$userLoggedIn = $_POST['userLoggedIn'];

$names = explode(" ", $query);

if (strpos($query, "_") !== false) {
    // return results similar to autosuggest pattern - "bar" will return "bart", "g" will return "geo", returns all results that matches query after %
    $usersReturned = mysqli_query($con, "SELECT * FROM users WHERE username LIKE '$query%' AND user_closed='no' LIMIT 8");
}
// 2 names - means they are searching for first and last names
else if (count($names) == 2) {
    $usersReturned = mysqli_query($con, "SELECT * FROM users WHERE (first_name LIKE '%$names[0]%' AND last_name LIKE '%$names[1]%') AND  user_closed='no' LIMIT 8");
} else {
    $usersReturned = mysqli_query($con, "SELECT * FROM users WHERE (first_name LIKE '%$names[0]%' OR last_name LIKE '%$names[0]%') AND  user_closed='no' LIMIT 8");
}

if ($query != "") {
    while ($row = mysqli_fetch_array($usersReturned)) {
        $user = new User($con, $userLoggedIn);
        if ($row['username'] != $userLoggedIn) {
            $mutual_friends = $user->getMutualFriends($row['username']) . " mutual friends";
        } else {
            $mutual_friends = "";
        }

        if ($user->isFriend($row['username'])) {
            echo "<div class='search_result'>
                    <a href='messages.php?u=" . $row['username'] . " ' >
                        <div class='search_result-pic'>
                            <img src='" . $row['profile_pic'] . "' />
                        </div>
                        <div class='search_result-text'>
                            <div class='search_result-name'> " . $row['first_name'] . " " . $row['last_name'] . " </div>
                            <div class='search_result-username'> " . $row['username'] . " </div>
                            <div class='search_result-mutual'> " . $mutual_friends . " </div>
                        </div>
                    </a>
                    </div>";
        }
    }
}
