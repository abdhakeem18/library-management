<?php

require_once BASE_PATH . "/models/book.php";
require_once BASE_PATH . "/views/layouts/app.php";


function content()
{

    $booksModel = new book();
    $data = $booksModel->getAll();


?>

    <div class="container-xxl container-p-y">

        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Dashboard /</span> book

            <button
                type="button"
                class="btn btn-primary float-end"
                data-bs-toggle="modal"
                data-bs-target="#create-user-modal">
                Add New book
            </button>
        </h4>

        <div class="row m-3">
            <div class="col-6">
                <div class="d-flex align-items-center m-3">
                    <input type="text" id="searchInput" class="form-control border-0 shadow-none" placeholder="Search Book Name  " aria-label="Search..." />
                </div>
            </div>
            <div class="col-2">
                <div class="form-group my-3">
                    <button class="btn btn-outline-danger d-inline" id="clear">x</button>
                </div>
            </div>
        </div>
        <hr>
        <!-- Basic Bootstrap Table -->
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped mb-4">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>description</th>
                        <th>Category</th>
                        <th>published_at</th>
                        <th>qty</th>
                    </tr>
                </thead>
                <tbody>


                    <?php
                    foreach ($data as $key => $book) {
                    ?>
                        <tr>
                            <td><i class="fab fa-angular fa-lg text-danger me-3"></i> <strong><?= $book['title'] ?? '' ?></strong></td>
                            <td><?= $book['author'] ?? '' ?></td>
                            <td><?= $book['description'] ?? '' ?></td>
                            <td><?= $book['Category'] ?? '' ?></td>
                            <td><?= $book['date_published'] ?? '' ?></td>
                            <td><?= $book['qty'] ?? '' ?></td>
                            <td>
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <div class="dropdown-menu">

                                    <a class="dropdown-item edit-book-btn" data-id="<?= $book['id']; ?>"><i class="bx bx-edit-alt me-1"></i> Edit</a>
                                    <a class="dropdown-item delete-book-btn" data-id="<?= $book['id']; ?>"><i class="bx bx-trash me-1"></i> Delete</a>

                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <hr class="my-5" />

    </div>

    <!--/ Basic Bootstrap Table -->


    <!-- create books -->
    <div class="modal fade" id="create-user-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="row">
                    <form id="create-form" action="<?= url('services/ajax_functions.php') ?>" enctype="multipart/form-data">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalCenterTitle">Add New book</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">

                            <input type="hidden" name="action" value="create-book">

                            <div class="col mb-3">
                                <label for="title" class="form-label"> Title:</label>
                                <input required type="text" name="title" class="form-control" placeholder="Enter Title" />
                            </div>

                            <div class="col mb-3">
                                <label for="isbn" class="form-label"> ISBN:</label>
                                <input type="text" name="isbn" class="form-control" placeholder="Enter ISBN" />
                            </div>

                            <div class="col mb-3">
                                <label for="author" class="form-label"> Author:</label>
                                <input required type="text" name="author" class="form-control" placeholder="Enter Author Name" />
                            </div>


                            <div class="col mb-3">
                                <label for="category" class="form-label">Category:</label>
                                <input required type="text" name="category" class="form-control" placeholder="Enter Category" />
                            </div>

                            <div class="col mb-3">
                                <label for="description" class="form-label">description:</label>
                                <input required type="text" name="description" class="form-control" placeholder="Enter description" />
                            </div>

                            <div class="col mb-3">
                                <div class="col mb-3">
                                    <label for="published_at" class="form-label">pubished_at:</label>
                                    <input
                                        required
                                        type="date"
                                        name="date_published"
                                        id=""
                                        class="form-control" />

                                </div>
                            </div>

                            <div class="col mb-3">
                                <label for="qty" class="form-label">qty:</label>
                                <input required type="number" name="qty" class="form-control" placeholder="Enter qty" />
                            </div>



                            <div class="mb-3 mt-3">
                                <div id="alert-container"></div>
                            </div>
                            <div class="mb-3 mt-3">
                                <div id="additional-fields">

                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" id="create">Create</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Udpate books -->
    <div class="modal fade" id="edit-book-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form id="update-form" action="<?= url('services/ajax_functions.php') ?>" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalCenterTitle">Update book</h5>
                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_book">
                        <input type="hidden" id="book_id" name="id" value="">

                        <div class="col mb-3">
                            <label for="title" class="form-label"> Title:</label>
                            <input required type="text" name="title" id="title" class="form-control" placeholder="Enter Title" />
                        </div>

                        <div class="col mb-3">
                            <label for="isbn" class="form-label"> ISBN:</label>
                            <input type="text" name="isbn" id="isbn" class="form-control" placeholder="Enter ISBN" />
                        </div>

                        <div class="col mb-3">
                            <label for="author" class="form-label"> Author:</label>
                            <input required type="text" name="author" id="author" class="form-control" placeholder="Enter Author Name" />
                        </div>


                        <div class="col mb-3">
                            <label for="category" class="form-label">Category:</label>
                            <input required type="text" name="category" id="category" class="form-control" placeholder="Enter Category" />
                        </div>

                        <div class="col mb-3">
                            <label for="description" class="form-label">description:</label>
                            <input required type="text" name="description" id="description" class="form-control" placeholder="Enter description" />
                        </div>

                        <div class="col mb-3">
                            <label for="published_at" class="form-label">pubished_at:</label>
                            <input
                                required
                                type="date"
                                name="date_published"
                                id=""
                                class="form-control" />

                        </div>

                        <div class="col mb-3">
                            <label for="qty" class="form-label">qty:</label>
                            <input required type="number" name="qty" id="qty" class="form-control" placeholder="Enter qty" />
                        </div>



                        <div class="mb-3 mt-3">
                            <div id="alert-container"></div>
                        </div>
                        <div class="mb-3 mt-3">
                            <div id="additional-fields">

                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="update-book">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script src="<?= asset('forms-js/book.js') ?>"></script>

<script>
$(document).ready(function() {
    $("#searchInput").on("input", function() {
        var searchTerm = $(this).val().toLowerCase();

        $("tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(searchTerm) > -1);
        });
    });

    $('#clear').on('click', function() {
        $("#searchInput").val('');
        $("tbody tr").show();
    });
});
</script>

<?php
}
?>
