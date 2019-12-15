<?php
require "config/config.php";
include("includes/classes/User.php");
include("includes/classes/Post.php");
include("includes/classes/Message.php");

// if there is a username in session, then user is logged in
if (isset($_SESSION["username"])) {
    $userLoggedIn = $_SESSION["username"];
    $user_details_query = mysqli_query($con, "SELECT * FROM users WHERE username='$userLoggedIn'");
    $user = mysqli_fetch_array($user_details_query);
}
// else if there is no user set, send back to register page
else {
    header("Location: register.php");
}

?>
<html>

<head>
    <title>Social Media</title>

    <!-- JAVASCRIPT -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/5.3.3/bootbox.min.js"></script>
    <script src="assets/js/index.js"></script>
    <script src="assets/js/jquery.jcrop.js"></script>
    <script src="assets/js/jcrop_bits.js"></script>

    <!-- CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.9.0/css/all.css" integrity="sha256-PF6MatZtiJ8/c9O9HQ8uSUXr++R9KBYu4gbNG5511WE=" crossorigin="anonymous" />
    <link rel="stylesheet" type="text/css" href="assets/css/index.css">
    <link rel="stylesheet" type="text/css" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/jquery.Jcrop.css" type="text/css" />
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand" href="index.php">Social Media</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <form class="form-inline my-2 my-lg-0">
            <input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search">
            <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
        </form>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <img class="nav-pic" src="<?php echo $user["profile_pic"]; ?>" alt="<?php echo $user["first_name"] . " " . $user["last_name"]; ?>">
                    <a class="nav-link" href="<?php echo $userLoggedIn ?>"><?php echo $user['first_name']; ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php"><i class="fas fa-home fa-lg"></i></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="javascript:void(0);" onclick="getDropdownData('<?php echo $userLoggedIn; ?>', 'message')"><i class="fas fa-comment fa-lg"></i></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="requests.php"><i class="fas fa-users fa-lg"></i></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#"><i class="fas fa-bell fa-lg"></i></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#"><i class="fas fa-cog fa-lg"></i></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="includes/handlers/logout.php"><i class="fas fa-sign-out-alt fa-lg"></i></a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="dropdown_data_window">
        <input type="hidden" id="dropdown_data_type" />
    </div>

    <div class=".container">