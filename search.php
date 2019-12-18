<?php
include("includes/header.php");

if (isset($_GET["q"])) {
    $query = $_GET["q"];
} else {
    $query = "";
}

if (isset($_GET["type"])) {
    $type = $_GET["type"];
} else {
    $type = "name";
}
?>

<div class="search">
    <div class="search_main">
        <?php
        if ($query == "")
            echo "You must enter something to search for";
        else {
            if ($type == "username")
                $usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE username LIKE '$query%' AND user_closed='no' LIMIT 8");
            else {
                $names = explode(" ", $query);  // splits string into array of elements divided by space characters " "
                // if there are two words, assume they are searching first and last names respectively
                if (count($names) == 3)
                    $usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE (first_name LIKE '$names[0]%' AND last_name LIKE '$names[2]%') AND  user_closed='no'");
                // if query has one word only, search first names and last names
                else if (count($names) == 2)
                    $usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE (first_name LIKE '$names[0]%' OR last_name LIKE '$names[1]%') AND  user_closed='no'");
                else
                    $usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE (first_name LIKE '$names[0]%' OR last_name LIKE '$names[0]%') AND  user_closed='no'");
            }

            if (mysqli_num_rows($usersReturnedQuery) == 0)
                echo "We can't find anyone with a " . $type . " like: " . $query;
            else
                echo "<div class='search_header'><div>" . mysqli_num_rows($usersReturnedQuery) . " results found</div>";

            echo "<p> Try searching for:</p>";
            echo "<a href='search.php?q=" . $query . "&type=name'>Names</a>, <a href='search.php?q=" . $query . "&type=username'>Usernames</a></div><hr>";

            while ($row = mysqli_fetch_array($usersReturnedQuery)) {
                $user_obj = new User($con, $user['username']);

                $button = "";
                $mutual_friends = "";

                if ($user['username'] != $row['username']) {
                    if ($user_obj->isFriend($row['username']))
                        $button = "<input type='submit' name='" . $row['username'] . "' class='btn btn-friend' value='Remove Friend' />";
                    else if ($user_obj->didReceiveRequest($row['username']))
                        $button = "<input type='submit' name='" . $row['username'] . "' class=' btn btn-friend' value='Respond to request' />";
                    else if ($user_obj->didSendRequest($row['username']))
                        $button = "<input class='default' value='Request sent' />";
                    else
                        $button = "<input type='submit' name='" . $row['username'] . "' class=' btn btn-friend' value='Add Friend' />";

                    $mutual_friends = $user_obj->getMutualFriends($row['username']) . " friends in common";

                    if (isset($_POST[$row['username']])) {
                        if ($user_obj->isFriend($row['username'])) {
                            $user_obj->removeFriend($row['username']);
                            header("Location: https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
                        } else if ($user_obj->didReceiveRequest($row['username'])) {
                            header("Location: requests.php");
                        } else if ($user_obj->didSendRequest($row['username'])) {
                        } else {
                            $user_obj->sendRequest($row['username']);
                            header("Location: https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
                        }
                    }
                }

                echo "<div class='search_page_result'>
                        <div class='search_page_result-pic'>
                            <a href='" . $row['username'] . "'><img src='" . $row['profile_pic'] . "' /></a>
                        </div>
                        <div class='search_page_result-details'>
                            <div class='search_page_result-header'>
                                <div>
                                    <a class='search_page_result-name' href='" . $row['username'] . "'>" . $row['first_name'] . " " . $row['last_name'] . "</a>
                                    <p class='search_page_result-username'>" . $row['username'] . "</p>
                                </div>
                                <div class='search_page_friend-button'>
                                    <form action='' method='POST'>
                                        " . $button . "
                                    </form>
                                </div>
                            </div>
                            <div class='search_page_result-mutuals'>" . $mutual_friends . "</div>
                        </div>
                    </div>";
            }
        }
        ?>
    </div>
</div>