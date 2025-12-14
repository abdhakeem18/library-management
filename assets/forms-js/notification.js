$(document).ready(function () {
  // Create/Send Notification
  $("#create-notification-form").submit(function (e) {
    e.preventDefault();
    var formData = new FormData(this);

    $.ajax({
      type: "POST",
      url: $(this).attr("action"),
      data: formData,
      processData: false,
      contentType: false,
      dataType: "json",
      success: function (data) {
        if (data.success) {
          showAlert(data.message, "success");

          setTimeout(function () {
            $("#create-notification-modal").modal("hide");
            $("#create-notification-form")[0].reset();
            location.reload();
          }, 1500);
        } else {
          showAlert(data.message, "danger");
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX Error:", error);
        console.error("Response:", xhr.responseText);
        showAlert("Error sending notification!", "danger");
      },
    });
  });

  // Mark as Read (using event delegation)
  $(document).on("click", ".mark-read-btn", function (e) {
    e.preventDefault();
    const id = $(this).data("id");
    const $row = $(this).closest("tr");

    if (confirm("Are you sure you want to mark this notification as read?")) {
      $.ajax({
        type: "POST",
        url: "services/ajax_functions.php",
        data: {
          action: "mark_notification_read",
          notification_id: id,
        },
        dataType: "json",
        success: function (data) {
          if (data.success) {
            showAlert(data.message, "success");
            
            // Update the row without full page reload
            $row.removeClass("table-active");
            $row.find(".badge.bg-secondary").removeClass("bg-secondary").addClass("bg-success").text("Read");
            $row.find(".mark-read-btn").parent().remove(); // Remove the "Mark as Read" option
            
            // Update unread count
            const $unreadCount = $(".row.m-3 .col-md-3:nth-child(2) h3");
            const currentCount = parseInt($unreadCount.text());
            $unreadCount.text(currentCount - 1);
            
            setTimeout(function () {
              location.reload();
            }, 1500);
          } else {
            showAlert(data.message, "danger");
          }
        },
        error: function (xhr, status, error) {
          console.error("AJAX Error:", error);
          console.error("Response:", xhr.responseText);
          showAlert("Error marking notification as read!", "danger");
        },
      });
    }
  });
      

  // View Notification (using event delegation)
  $(document).on("click", ".view-notification-btn", function () {
    const message = $(this).data("message");
    $("#notification-message").text(message);
    $("#view-notification-modal").modal("show");
  });

  // Delete Notification (using event delegation)
  $(document).on("click", ".delete-notification-btn", function (e) {
    e.preventDefault();
    const id = $(this).data("id");
    const $row = $(this).closest("tr");

    if (confirm("Are you sure you want to delete this notification?")) {
      $.ajax({
        type: "POST",
        url: "services/ajax_functions.php",
        data: {
          action: "delete_notification",
          notification_id: id,
        },
        dataType: "json",
        success: function (data) {
          if (data.success) {
            showAlert(data.message, "success");
            
            // Remove the row with fade effect
            $row.fadeOut(400, function() {
              $(this).remove();
              
              // Update total count
              const $totalCount = $(".row.m-3 .col-md-3:nth-child(1) h3");
              const currentTotal = parseInt($totalCount.text());
              $totalCount.text(currentTotal - 1);
              
              // Check if table is empty
              if ($("tbody tr:visible").length === 0) {
                $("tbody").html('<tr><td colspan="7" class="text-center"><div class="alert alert-warning">No notifications found.</div></td></tr>');
              }
            });
          } else {
            showAlert(data.message, "danger");
          }
        },
        error: function () {
          showAlert("Error deleting notification!", "danger");
        },
      });
    }
  });
});
