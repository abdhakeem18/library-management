$(document).ready(function () {
    
    // Create Reservation
    $("#create-reservation-form").submit(function (e) {
        e.preventDefault();
        var formData = new FormData(this);

        $.ajax({
            type: "POST",
            url: $(this).attr("action"),
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                showAlert(response.message, response.success ? 'primary' : 'danger');
                if (response.success) {
                    $("#create-reservation-modal").modal("hide");
                    $("#create-reservation-form")[0].reset();
                    setTimeout(function () {
                        location.reload();
                    }, 1000);
                }
            },
            error: function (xhr, status, error) {
                console.error("Error creating reservation:", error);
                showAlert("Something went wrong!", "danger");
            }
        });
    });

    // Fulfill Reservation
    $(".fulfill-reservation-btn").on("click", function () {
        const id = $(this).data("id");
        
        if (confirm("Are you sure you want to fulfill this reservation?")) {
            $.ajax({
                type: "POST",
                url: "services/ajax_functions.php",
                data: {
                    action: "fulfill_reservation",
                    reservation_id: id
                },
                dataType: 'json',
                success: function (response) {
                    showAlert(response.message, response.success ? 'primary' : 'danger');
                    if (response.success) {
                        setTimeout(function () {
                            location.reload();
                        }, 1000);
                    }
                },
                error: function () {
                    showAlert("Error fulfilling reservation!", "danger");
                }
            });
        }
    });

    // Cancel Reservation
    $(".cancel-reservation-btn").on("click", function () {
        const id = $(this).data("id");
        
        if (confirm("Are you sure you want to cancel this reservation?")) {
            $.ajax({
                type: "POST",
                url: "services/ajax_functions.php",
                data: {
                    action: "cancel_reservation",
                    reservation_id: id
                },
                dataType: 'json',
                success: function (response) {
                    showAlert(response.message, response.success ? 'primary' : 'danger');
                    if (response.success) {
                        setTimeout(function () {
                            location.reload();
                        }, 1000);
                    }
                },
                error: function () {
                    showAlert("Error canceling reservation!", "danger");
                }
            });
        }
    });

    // Delete Reservation
    $(".delete-reservation-btn").on("click", function () {
        const id = $(this).data("id");
        
        if (confirm("Are you sure you want to delete this reservation?")) {
            $.ajax({
                type: "POST",
                url: "services/ajax_functions.php",
                data: {
                    action: "delete_reservation",
                    reservation_id: id
                },
                dataType: 'json',
                success: function (response) {
                    showAlert(response.message, response.success ? 'primary' : 'danger');
                    if (response.success) {
                        setTimeout(function () {
                            location.reload();
                        }, 1000);
                    }
                },
                error: function () {
                    showAlert("Error deleting reservation!", "danger");
                }
            });
        }
    });
});
