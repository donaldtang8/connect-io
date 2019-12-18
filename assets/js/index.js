$(document).ready(function() {
  $("search_button").on("click", function() {
    document.search_form.submit();
  });
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

$(document).click(function(e) {
  if (
    e.target.class != "search_results" &&
    e.target.id != "search_text_input"
  ) {
    $(".search_results").html("");
    $(".search_results_footer").html("");
    $(".search_results_footer").toggleClass("search_results_footer_empty");
    $(".search_results_footer").toggleClass("search_results_footer");
  }

  if (e.target.class != "dropdown_data_window") {
    $(".dropdown_data_window").html("");
    if ($(".dropdown_data_window").css("visibility") == "hidden") {
      $(".dropdown_data_window").css("visibility", "visible");
    } else {
      $(".dropdown_data_window").css("visibility", "hidden");
    }
  }
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

var dropdown = "";
function getDropdownData(user, type) {
  // if dropdown is hidden, show
  if ($(".dropdown_data_window").css("visibility") == "hidden") {
    var pageName;
    dropdown = type;
    if (type == "notification") {
      pageName = "ajax_load_notifications.php";
      $("span").remove("#unread_notification"); // remove notification badge
    } else if (type == "message") {
      pageName = "ajax_load_messages.php";
      $("span").remove("#unread_message"); // remove notification badge
    }
    let ajaxReq = $.ajax({
      url: "includes/handlers/" + pageName,
      type: "POST",
      data: "page=1&userLoggedIn=" + user,
      cache: false,

      success: function(response) {
        $(".dropdown_data_window").html(response);
        $(".dropdown_data_window").css("visibility", "visible");
        $("#dropdown_data_type").val(type);
      }
    });
  } else {
    // if dropdown is showing, check if we are accessing a different dropdown
    // if clicking same dropdown, close
    if (type == dropdown) {
      $(".dropdown_data_window").html("");
      $(".dropdown_data_window").css("visibility", "hidden");
    }
    // if accessing other dropdown render dropdown
    else {
      var pageName;
      dropdown = type;
      if (type == "notification") {
        pageName = "ajax_load_notifications.php";
        $("span").remove("#unread_notification"); // remove notification badge
      } else if (type == "message") {
        pageName = "ajax_load_messages.php";
        $("span").remove("#unread_message"); // remove notification badge
      }
      let ajaxReq = $.ajax({
        url: "includes/handlers/" + pageName,
        type: "POST",
        data: "page=1&userLoggedIn=" + user,
        cache: false,

        success: function(response) {
          $(".dropdown_data_window").html(response);
          $(".dropdown_data_window").css("visibility", "visible");
          $("#dropdown_data_type").val(type);
        }
      });
    }
  }
}

function getLiveSearchUsers(value, user) {
  // make ajax call with two parameters (query, user)
  $.post(
    "includes/handlers/ajax_search.php",
    { query: value, userLoggedIn: user },
    function(data) {
      // toggle footer
      if ($(".search_results_footer_empty")[0]) {
        $(".search_results_footer_empty").toggleClass("search_results_footer");
        $(".search_results_footer_empty").toggleClass(
          "search_results_footer_empty"
        );
      }
      // populate results div with results
      $(".search_results").html(data);
      $(".search_results_footer").html(
        "<a href='search.php?q=" + value + "'> See All Results</a>"
      );

      // if no results returned
      if (data == "") {
        $(".search_results_footer").html("");
        $(".search_results_footer").toggleClass("search_results_footer_empty");
        $(".search_results_footer").toggleClass("search_results_footer");
      }
    }
  );
}
