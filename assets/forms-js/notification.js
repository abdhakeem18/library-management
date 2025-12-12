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
            success: function (response) {
                const data = JSON.parse(response);
                if (data.success) {
                    toastr.success(data.message);
                    $("#create-notification-modal").modal("hide");
                    $("#create-notification-form")[0].reset();
                    setTimeout(function () {
                        location.reload();
                    }, 1500);
                } else {
                    toastr.error(data.message);
                }
            },
            error: function () {
                toastr.error("Error sending notification!");
            }
        });
    });

    // Mark as Read
    $(".mark-read-btn").on("click", function () {
        const id = $(this).data("id");
        
        $.ajax({
            type: "POST",
            url: "services/ajax_functions.php",
            data: {
                action: "mark_notification_read",
                id: id
            },
            success: function (response) {
                const data = JSON.parse(response);
                if (data.success) {
                    toastr.success(data.message);
                    setTimeout(function () {
                        location.reload();
                    }, 1500);
                } else {
                    toastr.error(data.message);
                }
            },
            error: function () {
                toastr.error("Error marking notification as read!");
            }
        });
    });

    // View Notification
    $(".view-notification-btn").on("click", function () {
        const message = $(this).data("message");
        $("#notification-message").text(message);
        $("#view-notification-modal").modal("show");
    });

    // Delete Notification
    $(".delete-notification-btn").on("click", function () {
        const id = $(this).data("id");
        
        if (confirm("Are you sure you want to delete this notification?")) {
            $.ajax({
                type: "POST",
                url: "services/ajax_functions.php",
                data: {
                    action: "delete_notification",
                    id: id
                },
                success: function (response) {
                    const data = JSON.parse(response);
                    if (data.success) {
                        toastr.success(data.message);
                        setTimeout(function () {
                            location.reload();
                        }, 1500);
                    } else {
                        toastr.error(data.message);
                    }
                },
                error: function () {
                    toastr.error("Error deleting notification!");
                }
            });
        }
    });
});
