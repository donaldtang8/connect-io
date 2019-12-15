$(document).ready(function() {
  // register page
  $(".redirect-reg").on("click", function() {
    state = "reg";
    $(".register").css("display", "flex");
    $(".register").addClass("show");
    $(".login").css("display", "none");
    $(".login").removeClass("show");
    console.log(state);
  });
  $(".redirect-log").on("click", function() {
    state = "log";
    $(".register").css("display", "none");
    $(".register").removeClass("show");
    $(".login").css("display", "flex");
    $(".login").addClass("show");
    console.log(state);
  });
  // button for profile post
  $("#submit_profile_post").on("click", function() {
    $.ajax({
      type: "POST",
      url: "includes/handlers/ajax_submit_profile_post.php",
      data: $("form.profile_post").serialize(),
      success: function(msg) {
        $("#post_form").modal("hide");
        location.reload();
      },
      error: function() {
        alert("Error");
      }
    });
  });
  $("form").on("change", ".file-upload-field", function() {
    $(this)
      .parent(".file-upload-wrapper")
      .attr(
        "data-text",
        $(this)
          .val()
          .replace(/.*(\/|\\)/, "")
      );
  });
});

function getUsers(value, user) {
  $.post(
    "includes/handlers/ajax_friend_search.php",
    { query: value, userLoggedIn: user },
    function(data) {
      $(".results").html(data);
    }
  );
}
