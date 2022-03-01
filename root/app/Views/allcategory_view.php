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
                                <li class="breadcrumb-item active" aria-current="page">All Categories</li>
                            </ol>
                        </nav>
                        <!-- End Breadcrumb -->

                        <div class="mb-3 mb-md-4 d-flex justify-content-between">
                            <div class="h3 mb-0">Categories</div>
                        </div>


                        <!-- Users -->
                        <div class="table-responsive-xl">
                            <table class="table text-nowrap mb-0" id="table1">
                                <thead>
                                    <tr>
                                        <th class="font-weight-semi-bold border-top-0 py-2">Title</th>
                                        <th class="font-weight-semi-bold border-top-0 py-2">Image</th>
                                        <th class="font-weight-semi-bold border-top-0 py-2">Tot. Interest</th>
                                        <th class="font-weight-semi-bold border-top-0 py-2">Tot. Post</th>
                                        <th class="font-weight-semi-bold border-top-0 py-2">Update Date</th>
                                        <th class="font-weight-semi-bold border-top-0 py-2">Status</th>
                                        <th class="font-weight-semi-bold border-top-0 py-2">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($result as $row) {
                                        $id = $row['id_category'];
                                        $sta = '<span class="badge bg-danger">Inactive</span>';
                                        if ($row['status'] == '1') {
                                            $sta = '<span class="badge bg-success">Active</span>';
                                        }

                                        $img = '<a href="'.$row['image'].'" target="_blank">
                                            <img src="'.$row['image'].'" border="0" style="width: 100px; height: 100px;"/></a>';
                                        
                                        echo '<tr>
                                                <td>'.$row['title'].'<br/>ID: '.$row['id_category'].'</td>
                                                <td>'.$img.'</td>
                                                <td>'.number_format($row['total_interest']).'</td>
                                                <td>'.number_format($row['total_post']).'</td>
                                                <td>'.$row['date_updated'].'</td>
                                                <td>'.$sta.'</td>
                                                <td class="py-3">
                                                    <div class="position-relative">
                                                        <a class="link-dark d-inline-block" href="category/?ac=edit&id='.$row['id_category'].'">
                                                            <i class="gd-pencil icon-text"></i>
                                                        </a>
                                                        <a class="link-dark d-inline-block" href="#"
                                                            onclick="return confirmDelete('.$id.');">
                                                            <i class="gd-trash icon-text"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>';
                                    } ?>

                                </tbody>

                            </table>


                        </div>
                        <!-- End Users -->
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

    <script src="vendors/simple-datatables/simple-datatables.js"></script>

    <script>
    // Simple Datatable
    let table1 = document.querySelector('#table1');
    let dataTable = new simpleDatatables.DataTable(table1);

    confirmDelete = function(id) {
        let dataSession = JSON.parse('<?php echo json_encode($dataSess['user']); ?>')
        //console.log(dataSession);
        console.log(dataSession['flag']);

        if (dataSession['flag'] == '1') {
            alert('Action not allowed, only SuperAdmin level');
            return false;
        }

        console.log("id: " + id);
        if (id != '') {
            var r = confirm("Are you sure to delete this item?");
            if (r == true) {
                window.location.href = '/public/category/delete?id=' + id;
            }
        }

        return false;
    }

    /*$('#example').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": "../server_side/scripts/server_processing.php"
    });*/
    </script>

</body>

</html>