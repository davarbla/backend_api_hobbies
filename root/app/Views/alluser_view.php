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
                                    <a href="#">User</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">All Users</li>
                            </ol>
                        </nav>
                        <!-- End Breadcrumb -->

                        <div class="mb-3 mb-md-4 d-flex justify-content-between">
                            <div class="h3 mb-0">User</div>
                        </div>


                        <!-- Users -->
                        <div class="table-responsive-xl">
                            <table class="table text-nowrap mb-0" id="table1">
                                <thead>
                                    <tr>
                                        <th class="font-weight-semi-bold border-top-0 py-2">Fullname</th>
                                        <th class="font-weight-semi-bold border-top-0 py-2">Image</th>
                                        <th class="font-weight-semi-bold border-top-0 py-2">ImageP2</th>
                                        <th class="font-weight-semi-bold border-top-0 py-2">ImageP3</th>
                                        <th class="font-weight-semi-bold border-top-0 py-2">ImageP4</th>
                                        <th class="font-weight-semi-bold border-top-0 py-2">ImageFr5</th>
                                        <th class="font-weight-semi-bold border-top-0 py-2">ImageFr6</th>
                                        <th class="font-weight-semi-bold border-top-0 py-2">ImageFr7</th>
                                        <th class="font-weight-semi-bold border-top-0 py-2">ImageFu8</th>
                                        <th class="font-weight-semi-bold border-top-0 py-2">ImageFu9</th>
                                        <th class="font-weight-semi-bold border-top-0 py-2">ImageFu10</th>
                                        <th class="font-weight-semi-bold border-top-0 py-2">Tot. Post</th>
                                        <th class="font-weight-semi-bold border-top-0 py-2">Tot. Follower</th>
                                        <th class="font-weight-semi-bold border-top-0 py-2">Platform</th>
                                        <th class="font-weight-semi-bold border-top-0 py-2">Update Date</th>
                                        <th class="font-weight-semi-bold border-top-0 py-2">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($result as $row) {
                                        $id = $row['id_user'];
                                        $sta = '<span class="badge bg-danger">Inactive</span>';
                                        if ($row['status'] == '1') {
                                            $sta = '<span class="badge bg-success">Active</span>';
                                        }

                                        $img = '<a href="'.$row['image'].'" target="_blank">
                                            <img src="'.$row['image'].'" border="0" style="width: 90px; height: 90px;"/></a>';
                                        $img2 = '<a href="'.$row['image2'].'" target="_blank">
                                           <img src="'.$row['image2'].'" border="0" style="width: 90px; height: 90px;"/></a>';
                                        $img3 = '<a href="'.$row['image3'].'" target="_blank">
                                           <img src="'.$row['image3'].'" border="0" style="width: 90px; height: 90px;"/></a>';
                                        $img4 = '<a href="'.$row['image4'].'" target="_blank">
                                           <img src="'.$row['image4'].'" border="0" style="width: 90px; height: 90px;"/></a>';
                                        $img5 = '<a href="'.$row['image5'].'" target="_blank">
                                           <img src="'.$row['image5'].'" border="0" style="width: 90px; height: 90px;"/></a>';
                                        $img6 = '<a href="'.$row['image6'].'" target="_blank">
                                           <img src="'.$row['image6'].'" border="0" style="width: 90px; height: 90px;"/></a>';
                                        $img7 = '<a href="'.$row['image7'].'" target="_blank">
                                           <img src="'.$row['image7'].'" border="0" style="width: 90px; height: 90px;"/></a>';
                                        $img8 = '<a href="'.$row['image8'].'" target="_blank">
                                           <img src="'.$row['image8'].'" border="0" style="width: 90px; height: 90px;"/></a>';
                                        $img9 = '<a href="'.$row['image9'].'" target="_blank">
                                           <img src="'.$row['image9'].'" border="0" style="width: 90px; height: 90px;"/></a>';
                                        $img10 = '<a href="'.$row['image10'].'" target="_blank">
                                           <img src="'.$row['image10'].'" border="0" style="width: 90px; height: 90px;"/></a>';


                                        echo '<tr>
                                                <td>'.$row['fullname'].'<br/><strong>@'.$row['username'].'</strong>
                                                <br/>Country: '.$row['country'].'<br/>ID: '.$row['id_user'].'</strong></td>
                                                <td>'.$img.'</td>
                                                <td>'.$img2.'</td>
                                                <td>'.$img3.'</td>
                                                <td>'.$img4.'</td>
                                                <td>'.$img5.'</td>
                                                <td>'.$img6.'</td>
                                                <td>'.$img7.'</td>
                                                <td>'.$img8.'</td>
                                                <td>'.$img9.'</td>
                                                <td>'.$img10.'</td>
                                                <td>'.number_format($row['total_post']).'</td>
                                                <td>'.number_format($row['total_follower']).'</td>
                                                <td>'.$row['os_platform'].'</td>
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