
$(document).ready(function () {
    $('#create').on('click', function () {
        var form = $('#create-form')[0] ?? null;
        if (!form) console.log('Something went wrong..');
        var url = $('#create-form').attr('action');
        if (form.checkValidity() && form.reportValidity()) {
            var formData = new FormData(form);
            // Perform AJAX request
            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                contentType: false, // Don't set content type
                processData: false, // Don't process the data
                dataType: 'json',
                success: function (response) {
                    showAlert(response.message, response.success ? 'primary' : 'danger');
                    if (response.success) {
                        $('#create-book-modal').modal('hide');
                        setTimeout(function () {
                            location.reload();
                        }, 1000);
                    }
                },
                error: function (error) {
                    // Handle the error
                    console.error('Error submitting the form:', error);
                    showAlert('Something went wrong..!', 'danger');
                },
                complete: function (response) {
                    // This will be executed regardless of success or error
                    console.log('Request complete:', response);

                }
            });


        } else {
            showAlert('Form is not valid. Please check your inputs.', 'danger');
        }
    });

    $('.edit-book-btn').on('click', async function () {
        var id = $(this).data('id');
        await getbookById(id);
    })

    $('.delete-book-btn').on('click', async function () {
        var id = $(this).data('id');
        var is_confirm = confirm('Are you sure,Do you want to delete?');
        if (is_confirm) await deleteById(id);
    })

    $('#update-book').on('click', function () {

        // Get the form element
        var form = $('#update-form')[0];
        form.reportValidity();

        // Check form validity
        if (form.checkValidity()) {
            // Serialize the form data
            var url = $('#update-form').attr('action');
            var formData = new FormData($('#update-form')[0]);

            // Perform AJAX request
            $.ajax({
                url: url,
                type: 'POST',
                data: formData, // Form data
                dataType: 'json',
                contentType: false,
                processData: false,
                success: function (response) {
                    showAlert(response.message, response.success ? 'primary' : 'danger', 'edit-alert-container');
                    if (response.success) {
                        $('#edit-book-modal').modal('hide');
                        setTimeout(function () {
                            location.reload();
                        }, 1000);
                    }
                },
                error: function (error) {
                    // Handle the error
                    console.error('Error submitting the form:', error);
                },
                complete: function (response) {
                    // This will be executed regardless of success or error
                    console.log('Request complete:', response);
                }
            });
        } else {
            var message = ('Form is not valid. Please check your inputs.');
            showAlert(message, 'danger');
        }
    });
});

async function getbookById(id) {
    var url = $('#update-form').attr('action');

    // Perform AJAX request
    $.ajax({
        url: url,
        type: 'GET',
        data: {
            book_id: id,
            action: 'get_book'
        }, // Form data
        dataType: 'json',
        success: function (response) {
            console.log(response);

            showAlert(response.message, response.success ? 'primary' : 'danger');
            if (response.success) {

                var id = response.data.id;
                var title = response.data.title;
                var category = response.data.category;
                var author = response.data.author;
                var description = response.data.description;
                var date_published = response.data.date_published;
                var qty = response.data.qty;

                $('#edit-book-modal #title').val(title);
                $('#edit-book-modal #author').val(author);
                $('#edit-book-modal #description').val(description);
                $('#edit-book-modal #category').val(category);
                $('#edit-book-modal #published_at').val(date_published);
                $('#edit-book-modal #qty').val(qty);
                $('#edit-book-modal #book_id').val(id);

                $('#edit-book-modal').modal('show');
            }
        },
        error: function (error) {
            // Handle the error
            console.error('Error submitting the form:', error);
        },
        complete: function (response) {
            // This will be executed regardless of success or error
            console.log('Request complete:', response);
        }
    });
}

async function deleteById(id) {
    var url = $('#update-form').attr('action');

    // Perform AJAX request
    $.ajax({
        url: url,
        type: 'GET',
        data: {
            id: id,
            action: 'delete_book'
        }, // Form data
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                setTimeout(function () {
                    location.reload();
                }, 1000);
            } else {
                showAlert(response.message, response.success ? 'primary' : 'danger', 'delete-alert-container');
            }
        },
        error: function (error) {
            // Handle the error
            console.error('Error submitting the form:', error);
        },
        complete: function (response) {
            // This will be executed regardless of success or error
            console.log('Request complete:', response);
        }
    });
}
