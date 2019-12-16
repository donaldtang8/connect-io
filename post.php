<?php
include("includes/header.php");
// get id from url param
if (isset($_GET['id']))
    $post_id = $_GET['id'];
else
    $post_id = 0;
?>
<div class="home">
    <div class="home_sidebar">
        <div class="home_sidebar--user">
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
        <div class="home_sidebar--trends">Trends</div>
    </div>
    <div class="home_main">
        <div class="posts">
            <?php
                                                                                                            $post = new Post($con, $userLoggedIn);
                                                                                                            $post->getSinglePost($post_id);
            ?>
        </div>
        <div class="loading">Loading...</div>
    </div>
</div>
</div>
</body>

</html>