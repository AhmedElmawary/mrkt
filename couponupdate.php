<?php 
require_once('includes/config.php');
require_once('includes/utils.php');
$records=array();

if($_GET['id']){
    $id= $_GET['id'];

    $con = get_db_con();

                 ///var_dump($con);
                 $query="SELECT * FROM coupons WHERE id=$id ORDER BY id DESC;";
                 //var_dump($query);
                 $result=mysqli_query($con,$query);
                 while($data=mysqli_fetch_array($result)){
                    $records[]=$data; 
                 }

			    // mysqli_close($con);
                 //var_dump($records);
                
}
if($_POST['submit']){
    $id= $_POST['id'];
    $title=$_POST["title"];
    $present=$_POST["present"];
    $start=$_POST["start"];
    $end=$_POST["end"];
    $con = get_db_con();

    ///var_dump($con);
    $query="UPDATE  coupons SET title='$title',present='$present',start='$start',end='$end'  WHERE id=$id";
    //var_dump($query);
    $result=mysqli_query($con,$query);
    // $results=mysqli_fetch_all($result);
    //var_dump($result);
    if($result){
              header("Location:counponindex.php");
    }else{
        

    } 
    mysqli_close($con);
}
?>
<div id="page-container" class="sidebar-l sidebar-o side-scroll header-navbar-fixed">
        <nav id="sidebar">
            <!-- Sidebar Scroll Container -->
            <div id="sidebar-scroll">
                <!-- Sidebar Content -->
                <!-- Adding .sidebar-mini-hide to an element will hide it when the sidebar is in mini mode -->
                <?php
					
					$logo = 'mrkt/assets/img/logo.png';
					
					if(is_file($logo))
					{
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
									'add'=>'add_coupon'
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

							if(!empty($data)) $function = $data['function'];
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
                                <a tabindex="-1" href="mrkt/logout">
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
                    <button class="btn btn-default" data-toggle="layout" data-action="sidebar_mini_toggle"
                        type="button">
                        <i class="fa fa-ellipsis-v"></i>
                    </button>
                </li>
            </ul>
            <!-- END Header Navigation Left -->
        </header>
    <?php require_once('includes/header.php'); ?>

<?php
foreach($records as $record){ 
?>
 <form class="form-horizontal push-10-t form-notify" action="couponupdate.php" method="post" autocomplete="off"
        enctype="multipart/form-data">
        <div class="col-sm-12">
            <div     class="block">
                <div class="block-content block-content-narrow">
                    
                    <div class="block-content tab-content">
                        <div class="tab-pane active" id="btabs-alt-static-basic">
                <div class="form-group">
					<div class="col-sm-12">
						<div class="form-material form-material-primary">
							<input id="name" name="title" type="text" value="<?=$record['title']?>" maxlength="50" required autofocus class="form-control js-maxlength" data-always-show="true" />
							<label for="name">Title</label>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-12">
						<div class="form-material form-material-primary">
								<input id="present" name="present" type="number" value="<?=$record['present']?>" min="0" required class="form-control" data-always-show="true" />
								<label for="present">Discount Present</label>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-12">
						<div class="form-material form-material-primary">
								<input id="start date" name="start" type="date" value="<?=$record['start']?>"  required class="form-control" data-always-show="true" />
								<label for="start date">Start Date</label>
						</div>
					</div>
				</div>
                <input type="hidden" value="<?=$record['id']?>" name="id">
				<div class="form-group">
					<div class="col-sm-12">
						<div class="form-material form-material-primary">
								<input id="end date" name="end" type="date" value="<?=$record['end']?>"  required class="form-control" data-always-show="true" />
								<label for="end date">End Date</label>
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
            <?php 
}
            ?>