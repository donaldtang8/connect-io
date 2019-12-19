<?php
include("includes/header.php");
$errorMessage = "";
if (isset($_POST["post"])) {

    $uploadStatus = 1;
    $imageName = $_FILES['fileToUpload']['name'];   // retrieve name of file posted
    $errorMessage = "";

    // if post text or image is empty, put error
    if (strlen($_POST["post_text"]) == 0 && $imageName == "") {
        $errorMessage = "Post content cannot be empty.";
    }

    // if image uploaded
    if ($imageName != "") {

        // check if text is empty
        if (strlen($_POST["post_text"]) == 0) {
            $errorMessage = "Post content cannot be empty.";
        }

        $targetDir = "assets/images/posts/";
        $imageNameOld = $imageName;
        $imageName = $targetDir . uniqid() . basename($imageName);
        $imageFileType = pathinfo($imageName, PATHINFO_EXTENSION);

        // check if image greater than max size allowed
        if ($_FILES['fileToUpload']['size'] > 2097152) {
            $errorMessage = "File too large";
            $uploadStatus = 0;
        }

        // check file type
        if (strtolower($imageFileType) != "jpeg" && strtolower($imageFileType) != "png" && strtolower($imageFileType) != "jpg") {
            $errorMessage = "Only jpeg, jpg, and png files allowed";
            $uploadStatus = 0;
        }

        if ($uploadStatus) {
            if (move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $imageName)) {
                // image uploaded successfully
                $errorMessage = "Uploaded " . $imageNameOld;
            } else {
                // image did not upload
                $uploadStatus = 0;
                $errorMessage = "Error uploading image";
            }
        } else {
            $errorMessage = "Error uploading image";
        }
    }

    if ($uploadStatus) {
        $post = new Post($con, $userLoggedIn);
        $post->submitPost($_POST["post_text"], "none", $imageName);
        $errorMessage = "Uploaded " . $imageName;
    } else {
        echo "";
    }
}

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
        <div class="home_sidebar--trends">
            <div class="heading-2">Popular Trends</div>
            <?php
                                                                                                            $query = mysqli_query($con, "SELECT * FROM trends ORDER BY hits DESC LIMIT 9");
                                                                                                            // loop through first 9 trending words
                                                                                                            foreach ($query as $row) {
                                                                                                                $word = $row['title'];
                                                                                                                $word_dot = strlen($word) >= 15 ? "..." : "";
                                                                                                                // trim words to first 15 characters
                                                                                                                $trimmed_word = str_split($word, 15);
                                                                                                                $trimmed_word = $trimmed_word[0];

                                                                                                                echo "<div class='trends_word'>";
                                                                                                                echo $trimmed_word . $word_dot;
                                                                                                                echo "</div>";
                                                                                                            }
            ?>
        </div>
    </div>
    <div class="home_main">
        <form class="post_form" action="index.php" method="POST" enctype="multipart/form-data">
            <a class="post_pic" href="<?php echo $userLoggedIn ?>"> <img src="<?php echo $user["profile_pic"]; ?>" alt="<?php echo $user["first_name"] . " " . $user["last_name"]; ?>"></a>
            <textarea class="post_text" name="post_text" id="post_text" placeholder="What are you up to today?"></textarea>
            <div class="post_upload_file"><i class="far fa-image fa-lg"></i><input type="file" name="fileToUpload" class="fileToUpload" id="fileToUpload" /></div>
            <input class="btn btn-primary post_button" type="submit" name="post" id="post_button" value="Post">
            <div class="post_upload_file-error"><?php echo $errorMessage; ?></div>
        </form>
        <hr />
        <div class="posts">
        </div>
        <div class="loading">Loading...</div>
    </div>
    <script>
        var userLoggedIn = '<?php echo $userLoggedIn; ?>';

        $(document).ready(function() {
            $(".loading").show(); // show spinner

            // ajax request for loading first posts
            $.ajax({
                url: "includes/handlers/ajax_load_posts.php",
                type: "POST",
                data: "page=1&userLoggedIn=" + userLoggedIn,
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
                        url: "includes/handlers/ajax_load_posts.php",
                        type: "POST",
                        data: "page=" + page + "&userLoggedIn=" + userLoggedIn,
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
</div>
</div>
</body>

</html>