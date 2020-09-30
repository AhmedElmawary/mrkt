<?php
require_once('includes/config.php');
require_once('includes/utils.php');
if ($_POST['delete_id']) {

    $con = get_db_con();
    $id = $_POST['delete_id'];
    ///var_dump($con);
    $query = "DELETE FROM charge WHERE id=$id";
    //var_dump($query);
    $result = mysqli_query($con, $query);
    // $results=mysqli_fetch_all($result);
    //var_dump($result);
    if ($result) {
        header("Location:charge.php");
    } else {
    }
    mysqli_close($con);
}
?>
<?php require_once('includes/header.php'); ?>
<div id="page-container" class="sidebar-l sidebar-o side-scroll header-navbar-fixed">
    <nav id="sidebar">
        <!-- Sidebar Scroll Container -->
        <div id="sidebar-scroll">
            <!-- Sidebar Content -->
            <!-- Adding .sidebar-mini-hide to an element will hide it when the sidebar is in mini mode -->
            <?php

            $logo = 'mrkt/assets/img/logo.png';

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
    <div class="content">
        <div class="block">
            <div class="block-content">
                <a href="chargecreate.php" style="margin-top:10px; margin-bottom:10px;" class="btn btn-primary">ADD +</a>
                <table width="70%" class="table table-bordered table-striped table-hover js-dataTable-full-pagination">


                    <thead>
                        <tr>
                            <th>id</th>
                            <th>Title</th>
                            <th>charge pricent</th>
                            <th>Area ID</th>

                        </tr>
                    </thead>
                    <tbody align="center">
                        <?php
                        $records = array();
                        $con = get_db_con();

                        ///var_dump($con);
                        $query = "SELECT * FROM charge ORDER BY id DESC;";
                        //var_dump($query);
                        $result = mysqli_query($con, $query);
                        while ($data = mysqli_fetch_array($result)) {
                            $records[] = $data;
                        }
                        $con->close();


                        foreach ($records as $record) {
                        ?>
                            <tr>

                                <td><?php echo $record['id']; ?></td>
                                <td><?php echo $record['title']; ?></td>
                                <td><?php echo $record['present']; ?></td>
                                <td><?php echo $record['charge_area_id']; ?></td>
                                <td style="display:flex;"> <a href="chargeedit.php/?id='<?php echo $record['id'] ?>'">
                                        <button type="button" class="btn btn-xs btn-info push-10-r"><i class="fa fa-edit"></i>
                                            Edit</button>
                                    </a>
                                    <form action="charge.php" method="POST">
                                        <input type="hidden" name="delete_id" value="<?= $record['id'] ?>" class="options-form" />
                                        <button type="submit"><i class="fa fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>