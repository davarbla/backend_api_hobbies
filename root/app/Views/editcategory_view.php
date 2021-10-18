<?php require_once 'header_view.php';?>

<body class="has-sidebar has-fixed-sidebar-and-header">
    <!-- Header -->
    <?php require_once 'header_body_view.php';?>
    <!-- End Header -->

    <main class="main">
        <!-- Sidebar Nav -->
        <?php require_once 'menu_left.php';?>
        <!-- End Sidebar Nav -->

        <div class="content">
            <div class="py-4 px-3 px-md-4">
                <div class="card mb-3 mb-md-4">

                    <div class="card-body">
                        <!-- Breadcrumb -->
                        <nav class="d-none d-md-block" aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="#">Category</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">Create New Category</li>
                            </ol>
                        </nav>
                        <!-- End Breadcrumb -->

                        <div class="mb-3 mb-md-4 d-flex justify-content-between">
                            <div class="h3 mb-0">Create New Category</div>
                        </div>

                        <!-- Form -->
                        <div>
                            <form id="formCategory" action="/public/category/add_update" method="post">
                                <input type="hidden" id="id" name="id" value="<?php echo $row['id_category'];?>" />

                                <div class="form-row">
                                    <div class="form-group col-12 col-md-6">
                                        <label for="name">Title</label>
                                        <input type="text" class="form-control" value="<?php echo $row['title'];?>"
                                            id="title" name="title" placeholder="Title" />
                                    </div>
                                    <div class="form-group col-12 col-md-6">
                                        <label for="image">Image Url</label>
                                        <input type="text" class="form-control" value="<?php echo $row['image'];?>"
                                            id="image" name="image" placeholder="Image Url Address">
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-12 col-md-12">
                                        <label for="description">Description</label>
                                        <textarea type="text" class="form-control" rows="5" value="" id="description"
                                            name="description"
                                            placeholder="Description"><?php echo $row['description'];?></textarea>
                                    </div>
                                </div>
                                <div class="custom-control custom-switch mb-2">
                                    <input type="checkbox" class="custom-control-input" id="status" name="status"
                                        <?php echo ($row['status'] == 1) ? 'checked' : '';?>>
                                    <label class="custom-control-label" for="status">Active/NonActive</label>
                                </div>

                                <button type="submit" class="btn btn-primary float-right">Create</button>
                            </form>
                        </div>
                        <!-- End Form -->
                    </div>
                </div>


            </div>

            <!-- Footer -->
            <?php require_once 'footer_view.php';?>
            <!-- End Footer -->
        </div>
    </main>


    <?php require_once 'script_view.php';?>

    <script src="graindashboard/js/graindashboard.js"></script>
    <script src="graindashboard/js/graindashboard.vendor.js"></script>
    <script src="graindashboard/js/graindashboard.custom.js"></script>

</body>

</html>