$(document).ready(function () {
  $(".edit-fine_fee-btn").on("click", async function () {
    var id = $(this).data("id");
    var data = $(this).closest("tr");
    var b_id = data.find(".b_id").text();
    var m_name = data.find(".m_name").text();
    var total = data.find(".total").text();

    $("#edit-fine_fee-modal #fine_id").val(id);
    $("#edit-fine_fee-modal #e_mem_name").text(m_name);
    $("#edit-fine_fee-modal #e_amount").text(total);
    $("#edit-fine_fee-modal #_amount").val(total);
    $("#edit-fine_fee-modal #e_borrow_id").text(b_id);
    // console.log(b_id, m_name, total);

    $("#edit-fine_fee-modal").modal("show");
  });

  $("#update-fine_fee").on("click", function () {
    // Get the form element
    var form = $("#update-form")[0];
    form.reportValidity();

    // Check form validity
    if (form.checkValidity()) {
      // Serialize the form data
      var url = $("#update-form").attr("action");
      var formData = new FormData($("#update-form")[0]);

      // Perform AJAX request
      $.ajax({
        url: url,
        type: "POST",
        data: formData, // Form data
        dataType: "json",
        contentType: false,
        processData: false,
        success: function (response) {
          showAlert(
            response.message,
            response.success ? "primary" : "danger",
            "edit-alert-container"
          );
          if (response.success) {
            $("#edit-fine_fee-modal").modal("hide");
            setTimeout(function () {
              location.reload();
            }, 1000);
          }
        },
        error: function (error) {
          // Handle the error
          console.error("Error submitting the form:", error);
        },
        complete: function (response) {
          // This will be executed regardless of success or error
          console.log("Request complete:", response);
        },
      });
    } else {
      var message = "Form is not valid. Please check your inputs.";
      showAlert(message, "danger");
    }
  });
});
