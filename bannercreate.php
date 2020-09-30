<?php
require_once('includes/config.php');
require_once('includes/utils.php');

if ($_POST['submit']) {
    //var_dump('dsaasdsa');
    $message = '';
    $title = $_POST["title"];
    $url = $_POST["url"];
    if (isset($_FILES['image'])) {
        $errors = array();
        $file_name = $_FILES['image']['name'];
        $file_size = $_FILES['image']['size'];
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_type = $_FILES['image']['type'];
        $file_ext = strtolower(end(explode('.', $_FILES['image']['name'])));

        move_uploaded_file($file_tmp, "images/" . $file_name);
    }
    $path = "images/" . $file_name;
    $con = get_db_con();
    $query = "INSERT INTO banner (title,path,url) VALUES ('" . $title . "','" . $path . "','" . $url . "')";
    //var_dump($query);
    $result = db_query($con, $query);

    // $results=mysqli_fetch_all($result);
    //var_dump($result);
    if ($result) {
        header("Location:banner.php");
    } else {
    }
}
?>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<div id="page-container" class="sidebar-l sidebar-o side-scroll header-navbar-fixed">
    <nav id="sidebar">
        <!-- Sidebar Scroll Container -->
        <div id="sidebar-scroll">
            <!-- Sidebar Content -->
            <!-- Adding .sidebar-mini-hide to an element will hide it when the sidebar is in mini mode -->
            <?php

            $logo = 'assets/img/logo.png';

            if (is_file($logo)) {
            ?>
                <div class="side-header side-content">
                    <a href="admin">
                        <img src="<?php echo $logo; ?>" width="100%" />
                    </a>
                </div>
            <?php
            }

            ?>
            <div class="sidebar-content">
                <!-- Side Content -->
                <div class="side-header side-content">

                    <?php

                    $functions = [
                        'dashboard' => 'dashboard',
                        'orders' => [
                            'list_order',
                            'view' => 'view_order',
                            'states' => [
                                'list_order_state',
                                'add' => 'add_order_state'
                            ]
                        ],
                        'stores' => [
                            'list_shop',
                            'add' => 'add_shop',
                            'types' => [
                                'list_shop_type',
                                'add' => 'add_shop_type'
                            ]
                        ],
                        'suppliers' => [
                            'list_supplier',
                            'add' => 'add_supplier'
                        ],
                        'categories' => [
                            'list_category',
                            'add' => 'add_category'
                        ],
                        'coupons' => [
                            'list_coupon',
                            'add' => 'add_coupon'
                        ],
                        'products' => [
                            'list_product',
                            'add' => 'add_product',
                            'imports' => [
                                'list_product_import',
                                'add' => 'add_product_import'
                            ]
                        ],
                        'cities' => [
                            'list_city',
                            'add' => 'add_city',
                            'areas' => [
                                'list_area',
                                'add' => 'add_area'
                            ]
                        ],
                        'delivery-staff' => [
                            'list_delivery_staff',
                            'add' => 'add_delivery_staff'
                        ],
                        'users' => [
                            'list_user',
                            'add' => 'add_user'
                        ],
                        'admins' => [
                            'list_admin',
                            'add' => 'add_admin'
                        ],
                        'roles' => [
                            'list_role',
                            'add' => 'add_role'
                        ],
                        'functions' => [
                            'list_function',
                            'add' => 'add_function'
                        ],
                        'options' => 'add_options',
                        'purge-db' => 'purge_db_tables',
                        'tech-info' => 'tech_info'
                    ];

                    $data = process_api_call($_GET, $functions);
                    //var_dump($data);

                    if (!empty($data)) $function = $data['function'];
                    else $function = 'dashboard';

                    $role_functions = get_role_functions();
                    //var_dump($role_functions);
                    generate_navbar($function, $role_functions);

                    ?>

                </div>

                <!-- END Side Content -->
            </div>
            <!-- Sidebar Content -->
        </div>
        <!-- END Sidebar Scroll Container -->
    </nav>

    <header id="header-navbar" class="content-mini content-mini-full">
        <!-- Header Navigation Right -->
        <ul class="nav-header pull-right">
            <li>
                <div class="btn-group">
                    <button class="btn btn-default dropdown-toggle" data-toggle="dropdown" type="button">
                        <?php echo $_SESSION['admin_name']; ?>
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right">
                        <li>
                            <a tabindex="-1" href="admin/admins/add/<?php echo $_SESSION['admin_id']; ?>">
                                <i class="si si-user pull-right"></i>Profile
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a tabindex="-1" href="logout">
                                <i class="si si-logout pull-right"></i>Log out
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
        </ul>
        <!-- END Header Navigation Right -->

        <!-- Header Navigation Left -->
        <ul class="nav-header pull-left">
            <li class="hidden-md hidden-lg">
                <!-- Layout API, functionality initialized in App() -> uiLayoutApi() -->
                <button class="btn btn-default" data-toggle="layout" data-action="sidebar_toggle" type="button">
                    <i class="fa fa-navicon"></i>
                </button>
            </li>
            <li class="hidden-xs hidden-sm">
                <!-- Layout API, functionality initialized in App() -> uiLayoutApi() -->
                <button class="btn btn-default" data-toggle="layout" data-action="sidebar_mini_toggle" type="button">
                    <i class="fa fa-ellipsis-v"></i>
                </button>
            </li>
        </ul>
        <!-- END Header Navigation Left -->
    </header>
    <?php require_once('includes/header.php'); ?>

    <form class="form-horizontal push-10-t form-notify" action="bannercreate.php" method="post" autocomplete="off" enctype="multipart/form-data">
        <div class="col-sm-12">
            <div class="block">
                <div class="block-content block-content-narrow">

                    <div class="block-content tab-content">
                        <div class="tab-pane active" id="btabs-alt-static-basic">
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <div class="form-material form-material-primary">
                                        <input id="name" name="title" type="text" value="" maxlength="50" required autofocus class="form-control js-maxlength" data-always-show="true" />
                                        <label for="name">Title</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <div class="form-material form-material-primary">
                                        <input id="present" name="image" type="file" required class="form-control" data-always-show="true" />
                                        <label for="present">Image</label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-sm-12">
                                    <div class="form-material form-material-primary">
                                        <select class="form-control" name="area" id="areaSelect">
                                            <?php
                                            $areas = [];
                                            $query = "SELECT * FROM area";
                                            $result = db_query($con, $query);
                                            while ($row = db_fetch_assoc($result)) {
                                                $areas[] = $row;
                                            }
                                            foreach ($areas as $area) {
                                            ?>
                                                <option value="<?= $area['id'] ?>"><?= $area['name'] ?></option>
                                            <?php } ?>
                                        </select>
                                        <label for="">Area</label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-sm-12">
                                    <div class="form-material form-material-primary">
                                        <select class="form-control" name="url" id="shops">
                                            <?php
                                            $recodes = [];
                                            $query = "SELECT * FROM shop WHERE area_id=1";
                                            $result = db_query($con, $query);
                                            while ($row = db_fetch_assoc($result)) {
                                                $recodes[] = $row;
                                            }
                                            foreach ($recodes as $recode) {
                                            ?>
                                                <option value=" <?= $recode['id'] ?>"><?= $recode['name'] ?></option>
                                            <?php } ?>
                                        </select>
                                        <label for="">Store</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <div class="form-material form-material-primary">
                                        <input class="btn btn-sm btn-primary push-5-r" name="submit" type="submit">

                                    </div>
                                </div>
                            </div>

    </form>
    <script>
        // $('#type').click(function(){
        //     var selected=$(this).children("option:selected").val();
        //     //console.log(selected);
        //     if(selected == 'category'){
        //     var data=new FormData();
        //     data.append('category',selected);

        //     $.ajax({
        //         url:'selected.php',
        //         type:'POST',
        //         data:data,
        //         processData: false,
        //         contentType: false,
        //         success:function(res){
        //             var env=JSON.parse(res);
        //             var last=[env];
        //             console.log(last);
        //             last[0].forEach(da=>{
        //                 $('#typevalue').append(`<option value="${data.id}">${data.name}</option>`);
        //             });
        //             $('.typevalue').fadeIn();
        //         }
        //     });
        // }
        // });

        $('#areaSelect').change(function() {
            var selected = $(this).children("option:selected").val();
            const data = {
                areaId: selected
            }
            $.ajax({
                url: 'selectedAreaForBanner.php',
                type: 'POST',
                data: data,
                // contentType: 'application/json',
                dataType: "JSON",
                success: function(resultData) {
                    $('#shops').html('');
                    for (let i = 0, len = resultData.length; i < len; i++) {
                        $('#shops').append(`<option value="${resultData[i].id}">${resultData[i].name}</option>`);
                    }
                }
            });
        });
    </script>