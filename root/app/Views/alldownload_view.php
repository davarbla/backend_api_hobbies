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
                                    <a href="#">Download</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">All Downloads</li>
                            </ol>
                        </nav>
                        <!-- End Breadcrumb -->

                        <div class="mb-3 mb-md-4 d-flex justify-content-between">
                            <div class="h3 mb-0">Download</div>
                        </div>


                        <!-- Users -->
                        <div class="table-responsive-xl">
                            <table class="table text-nowrap mb-0" id="table1">
                                <thead>
                                    <tr>
                                        <th class="font-weight-semi-bold border-top-0 py-2">Fullname</th>
                                        <th class="font-weight-semi-bold border-top-0 py-2">Image</th>
                                        <th class="font-weight-semi-bold border-top-0 py-2">Tot. View</th>
                                        <th class="font-weight-semi-bold border-top-0 py-2">Tot. Like</th>
                                        <th class="font-weight-semi-bold border-top-0 py-2">Tot. Download</th>
                                        <th class="font-weight-semi-bold border-top-0 py-2">Update Date</th>
                                        <th class="font-weight-semi-bold border-top-0 py-2">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($result as $row) {
                                        $id = $row['id_download'];
                                        $sta = '<span class="badge bg-danger">Inactive</span>';
                                        if ($row['status'] == '1') {
                                            $sta = '<span class="badge bg-success">Active</span>';
                                        }

                                        $img = '<a href="'.$row['url'].'" target="_blank">
                                            <img src="'.$row['url'].'" border="0" style="width: 30px; height: 30px;"/></a>';
                                        
                                        echo '<tr>
                                                <td>'.$row['fullname'].'</td>
                                                <td>'.$img.'</td>
                                                <td>'.number_format($row['total_view']).'</td>
                                                <td>'.number_format($row['total_like']).'</td>
                                                <td>'.number_format($row['total_download']).'</td>
                                                <td>'.$row['date_updated'].'</td>
                                                <td>'.$sta.'</td>
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
                window.location.href = 'category/delete?id=' + id;
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