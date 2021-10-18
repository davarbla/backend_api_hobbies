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
                                    <a href="#">Users</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">Create New User</li>
                            </ol>
                        </nav>
                        <!-- End Breadcrumb -->

                        <div class="mb-3 mb-md-4 d-flex justify-content-between">
                            <div class="h3 mb-0">Create New User</div>
                        </div>


                        <!-- Form -->
                        <div>
                            <form>
                                <div class="form-row">
                                    <div class="form-group col-12 col-md-6">
                                        <label for="name">Name</label>
                                        <input type="text" class="form-control" value="" id="name" name="name"
                                            placeholder="User Name">
                                    </div>
                                    <div class="form-group col-12 col-md-6">
                                        <label for="email">Email</label>
                                        <input type="email" class="form-control" value="" id="email" name="email"
                                            placeholder="User Email">
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-12 col-md-6">
                                        <label for="password">Password</label>
                                        <input type="password" class="form-control" value="" id="password"
                                            name="password" placeholder="New Password">
                                    </div>
                                    <div class="form-group col-12 col-md-6">
                                        <label for="password_confirm">Confirm Password</label>
                                        <input type="password" class="form-control" value="" id="password_confirm"
                                            name="password_confirm" placeholder="Confirm Password">
                                    </div>
                                </div>
                                <div class="custom-control custom-switch mb-2">
                                    <input type="checkbox" class="custom-control-input" id="randomPassword">
                                    <label class="custom-control-label" for="randomPassword">Set Random Password</label>
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