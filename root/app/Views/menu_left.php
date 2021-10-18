<?php 
$activeIndex = "";
//print_r($menu);
if ($menu['activeIndex'] == '1') {
    $activeIndex = "active";
}

//install
$activeInstall = "";
if ($menu['activeInstall'] == '1') {
    $activeInstall = "active";
}

//category
$activeCategory = "";
$activeMenuCategory = "";
$activeDisplayCategory = "display: none;";

$activeAddCategory = "";

if ($menu['activeCategory'] == '1') {
    $activeMenuCategory = 'active side-nav-opened';
    $activeDisplayCategory = "display: block;";
    $activeCategory = "active";
}
else if ($menu['activeAddCategory'] == '1') {
    $activeMenuCategory = 'active side-nav-opened';
    $activeDisplayCategory = "display: block;";
    $activeAddCategory = "active";
}

//user
$activeUser = "";
if ($menu['activeUser'] == '1') {
    $activeUser = "active";
}

//download
$activeDownload = "";
if ($menu['activeDownload'] == '1') {
    $activeDownload = "active";
}

//feedback
$activeFeedback = "";
if ($menu['activeFeedback'] == '1') {
    $activeFeedback = "active";
}

//post
$activePost = "";
$activeMenuPost = "";
$activeDisplayPost = "display: none;";

$activeReportedPost = "";
$activeDeletedPost = "";

if ($menu['activePost'] == '1') {
    $activeMenuPost = 'active side-nav-opened';
    $activeDisplayPost = "display: block;";
    $activePost = "active";
}
else if ($menu['activeReportedPost'] == '1') {
    $activeMenuPost = 'active side-nav-opened';
    $activeDisplayPost = "display: block;";
    $activeReportedPost = "active";
}
else if ($menu['activeDeletedPost'] == '1') {
    $activeMenuPost = 'active side-nav-opened';
    $activeDisplayPost = "display: block;";
    $activeDeletedPost = "active";
}

?>

<aside id="sidebar" class="js-custom-scroll side-nav">
    <ul id="sideNav" class="side-nav-menu side-nav-menu-top-level mb-0">
        <!-- Title -->
        <li class="sidebar-heading h6">Dashboard</li>
        <!-- End Title -->

        <!-- Dashboard -->
        <li class="side-nav-menu-item <?php echo $activeIndex;?>">
            <a class="side-nav-menu-link media align-items-center" href="<?php echo base_url().'/public';?>">
                <span class="side-nav-menu-icon d-flex mr-3">
                    <i class="gd-dashboard"></i>
                </span>
                <span class="side-nav-fadeout-on-closed media-body">Dashboard</span>
            </a>
        </li>
        <!-- End Dashboard -->

        <!-- Installed -->
        <li class="side-nav-menu-item <?php echo $activeInstall;?>">
            <a class="side-nav-menu-link media align-items-center" href="install">
                <span class="side-nav-menu-icon d-flex mr-3">
                    <i class="gd-file"></i>
                </span>
                <span class="side-nav-fadeout-on-closed media-body">Installed</span>
            </a>
        </li>
        <!-- End Installed -->

        <!-- Title -->
        <li class="sidebar-heading h6">Master</li>
        <!-- End Title -->

        <!-- Category -->
        <li class="side-nav-menu-item side-nav-has-menu <?php echo $activeMenuCategory;?>">
            <a class="side-nav-menu-link media align-items-center" href="#" data-target="#subCategories">
                <span class="side-nav-menu-icon d-flex mr-3">
                    <i class="gd-book"></i>
                </span>
                <span class="side-nav-fadeout-on-closed media-body">Category</span>
                <span class="side-nav-control-icon d-flex">
                    <i class="gd-angle-right side-nav-fadeout-on-closed"></i>
                </span>
                <span class="side-nav__indicator side-nav-fadeout-on-closed"></span>
            </a>

            <!-- Users: subCategories -->
            <ul id="subCategories" class="side-nav-menu side-nav-menu-second-level mb-0"
                style="<?php echo $activeDisplayCategory;?>">
                <li class="side-nav-menu-item <?php echo $activeCategory;?>">
                    <a class="side-nav-menu-link" href="category">All Categories</a>
                </li>
                <li class="side-nav-menu-item <?php echo $activeAddCategory;?>">
                    <a class="side-nav-menu-link" href="category?ac=add">Add new</a>
                </li>
            </ul>
            <!-- End Users: subCategories -->
        </li>
        <!-- End Category -->

        <!-- Installed -->
        <li class="side-nav-menu-item <?php echo $activeUser;?>">
            <a class="side-nav-menu-link media align-items-center" href="user">
                <span class="side-nav-menu-icon d-flex mr-3">
                    <i class="gd-user"></i>
                </span>
                <span class="side-nav-fadeout-on-closed media-body">User</span>
            </a>
        </li>
        <!-- End Installed -->


        <!-- Title -->
        <li class="sidebar-heading h6">Activity</li>
        <!-- End Title -->

        <!-- Posts -->
        <li class="side-nav-menu-item side-nav-has-menu <?php echo $activeMenuPost;?>">
            <a class="side-nav-menu-link media align-items-center" href="#" data-target="#subPosts">
                <span class="side-nav-menu-icon d-flex mr-3">
                    <i class="gd-rss"></i>
                </span>
                <span class="side-nav-fadeout-on-closed media-body">Posts</span>
                <span class="side-nav-control-icon d-flex">
                    <i class="gd-angle-right side-nav-fadeout-on-closed"></i>
                </span>
                <span class="side-nav__indicator side-nav-fadeout-on-closed"></span>
            </a>

            <!-- Users: subPosts -->
            <ul id="subPosts" class="side-nav-menu side-nav-menu-second-level mb-0"
                style="<?php echo $activeDisplayPost;?>">
                <li class="side-nav-menu-item <?php echo $activePost;?>">
                    <a class="side-nav-menu-link" href="post">All Posts</a>
                </li>
                <li class="side-nav-menu-item <?php echo $activeReportedPost;?>">
                    <a class="side-nav-menu-link" href="post?ac=reported">Reported</a>
                </li>
                <li class="side-nav-menu-item <?php echo $activeDeletedPost;?>">
                    <a class="side-nav-menu-link" href="post?ac=deleted">Deleted</a>
                </li>
            </ul>
            <!-- End Users: subPosts -->
        </li>
        <!-- End Posts -->

        <!-- Download -->
        <li class="side-nav-menu-item <?php echo $activeDownload;?>">
            <a class="side-nav-menu-link media align-items-center" href="download">
                <span class="side-nav-menu-icon d-flex mr-3">
                    <i class="gd-download"></i>
                </span>
                <span class="side-nav-fadeout-on-closed media-body">Download</span>
            </a>
        </li>
        <!-- End Download -->

        <!-- Feedback -->
        <li class="side-nav-menu-item <?php echo $activeFeedback;?>">
            <a class="side-nav-menu-link media align-items-center" href="feedback">
                <span class="side-nav-menu-icon d-flex mr-3">
                    <i class="gd-bell"></i>
                </span>
                <span class="side-nav-fadeout-on-closed media-body">Feedback</span>
            </a>
        </li>
        <!-- End Feedback -->

    </ul>
</aside>