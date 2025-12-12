$(document).ready(function () {
  $("#create").on("click", function () {
    var form = $("#create-form")[0] ?? null;
    if (!form) console.log("Something went wrong..");

    var url = $("#create-form").attr("action");
    if (form.checkValidity() && form.reportValidity()) {
      var formData = new FormData(form);
      // Perform AJAX request
      $.ajax({
        url: url,
        type: "POST",
        data: formData,
        contentType: false, // Don't set content type
        processData: false, // Don't process the data
        dataType: "json",
        success: function (response) {
          showAlert(response.message, response.success ? "primary" : "danger");
          if (response.success) {
            $("#edit-borrow-modal").modal("hide");
            setTimeout(function () {
              location.reload();
            }, 1000);
          }
        },
        error: function (error) {
          // Handle the error
          console.error("Error submitting the form:", error);
          showAlert("Something went wrong..!", "danger");
        },
        complete: function (response) {
          // This will be executed regardless of success or error
          console.log("Request complete:", response);
        },
      });
    } else {
      showAlert("Form is not valid. Please check your inputs.", "danger");
    }
  });

  $(".edit-borrow-btn").on("click", async function () {
    var id = $(this).data("id");
    await getborrowById(id);
  });

  $(".delete-borrow-btn").on("click", async function () {
    var id = $(this).data("id");
    var is_confirm = confirm("Are you sure,Do you want to delete?");
    if (is_confirm) await deleteById(id);
  });

  $("#update-borrow").on("click", function () {
    // Get the form element
    var form = $("#edit-form")[0];
    form.reportValidity();

    // Check form validity
    if (form.checkValidity()) {
      // Serialize the form data
      var url = $("#edit-form").attr("action");
      var formData = new FormData($("#edit-form")[0]);

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
            $("#edit-borrow-modal").modal("hide");
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

// borrow update form

async function getborrowById(id) {
  var url = $("#edit-form").attr("action");
  $("#edit-additional-fields").empty();
  //   console.log(id);
  // Perform AJAX request
  $.ajax({
    url: url,
    type: "GET",
    data: {
      id: id,
      action: "get_borrow",
    }, // Form data
    dataType: "json",
    success: function (response) {
      console.log(response);

      //   showAlert(response.message, response.success ? "primary" : "danger");
      if (response.success) {
        var borrow_id = response.data.id;
        var book_id = response.data.book_id;
        var member_no = response.data.user_id || response.data.member_no;
        var borrow_date = response.data.borrow_date;
        var due_date = response.data.due_date;

        var date = new Date(borrow_date);
        date.setDate(date.getDate() + 5);
        var newDate = date.toISOString().split("T")[0];

        console.log(book_id);
        $('#edit-borrow-modal #book_id option[value="' + book_id + '"]').prop(
          "selected",
          true
        );
        $(
          '#edit-borrow-modal #member_no option[value="' + member_no + '"]'
        ).prop("selected", true);
        $("#edit-borrow-modal #borrow_id").val(borrow_id);
        // $("#edit-borrow-modal #borrow_date").val(borrow_date);
        $("#edit-borrow-modal #due_date").val(due_date).attr("min", newDate);

        $("#edit-borrow-modal").modal("show");
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
}

// borrow delete form

async function deleteById(id) {
  var url = $("#edit-form").attr("action");

  // Perform AJAX request
  $.ajax({
    url: url,
    type: "GET",
    data: {
      id: id,
      action: "delete_borrow",
    }, // Form data
    dataType: "json",
    success: function (response) {
      if (response.success) {
        setTimeout(function () {
          location.reload();
        }, 1000);
      } else {
        showAlert(
          response.message,
          response.success ? "primary" : "danger",
          "delete-alert-container"
        );
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
}
