<?php

require_once('includes/utils.php');

if (!isset($_SESSION['admin_id'])) {
	header('Location: /index');
	exit;
}
?>
<!DOCTYPE html>
<html class="no-focus" lang="en">

<head>
	<title><?php echo APP_NAME; ?> | Admin panel</title>
	<?php require_once('includes/header.php'); ?>

	<link rel="stylesheet" type="text/css" href="assets/OneUI/src/assets/js/plugins/datatables/jquery.dataTables.min.css" />
	<link rel="stylesheet" type="text/css" href="assets/OneUI/src/assets/js/plugins/magnific-popup/magnific-popup.min.css" />
	<link rel="stylesheet" type="text/css" href="assets/OneUI/src/assets/js/plugins/select2/select2.min.css">
	<link rel="stylesheet" type="text/css" href="assets/OneUI/src/assets/js/plugins/select2/select2-bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="assets/css/main.css" />

	<!-- Page JS Plugins -->
	<script defer src="assets/OneUI/src/assets/js/plugins/datatables/jquery.dataTables.min.js"></script>
	<script defer src="assets/OneUI/src/assets/js/plugins/magnific-popup/magnific-popup.min.js"></script>
	<script defer src="assets/OneUI/src/assets/js/plugins/bootstrap-notify/bootstrap-notify.min.js"></script>
	<script defer src="assets/OneUI/src/assets/js/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js"></script>
	<script defer src="assets/OneUI/src/assets/js/plugins/select2/select2.full.min.js"></script>
	<script defer src="assets/OneUI/src/assets/js/plugins/flot/jquery.flot.min.js"></script>
	<script defer src="assets/OneUI/src/assets/js/plugins/flot/jquery.flot.pie.min.js"></script>
	<script defer src="assets/js/main.js"></script>

	<script>
		function initScripts() {
			// Init page helpers
			App.initHelpers(['magnific-popup', 'notify', 'maxlength', 'select2']);
			addFormNotify('form-notify');
		}
	</script>
</head>

<body onload="initScripts();">
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
							'tech-info' => 'tech_info',

						];

						$sodic_functions = [
							'dashboard' => 'dashboard',

							'orders' => [
								'list_sodic_orders',
								'view' => 'view_sodic_order',
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
								]
							],
							'delivery-staff' => [
								'list_delivery_staff',
								'add' => 'add_delivery_staff'
							],
							'users' => [
								'list_sodic_users',
								// 'add' => 'add_sodic_user'
							],
						];

						if (($_SESSION["admin_email"] == "sodic@mrkt.ws") || ($_SESSION["admin_role_id"] == 9)) {
							$data = process_api_call($_GET, $sodic_functions);
						} else {
							$data = process_api_call($_GET, $functions);
						}

						if (!empty($data)) $function = $data['function'];

						else $function = 'dashboard';

						$role_functions = get_role_functions();
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

		<main id="main-container">
			<?php

			if (in_array($function, $role_functions)) call_user_func($function, $data['get'], $data['vars']);
			else default_page();

			?>
		</main>
	</div>
</body>

</html>
<?php

function dashboard($get = [], $vars = [])
{
?>
	<div class="content">
		<?php add_add_button(__FUNCTION__, false); ?>
		<div class="block">
			<div class="block-content">
				<ul class="nav nav-tabs nav-tabs-alt" data-toggle="tabs">
					<li class="active" onclick="updateStats();">
						<a href="#btabs-alt-static-basic">Basic</a>
					</li>
					<li onclick="updateGraphs();">
						<a href="#btabs-alt-static-graphs">Charts</a>
					</li>
					<li class="pull-right">
						<ul class="block-options push-10-t push-10-r">
							<li>
								<button type="button" onclick="updateDasboard();" title="Refresh"><i class="si si-refresh"></i></button>
							</li>
						</ul>
					</li>
				</ul>
				<div class="block-content tab-content">
					<div class="tab-pane active" id="btabs-alt-static-basic">
						<?php

						$state_confirmed = $state_fetched = $state_completed = '';
						$selected_year = $user_min_year = $orders_min_year = date('Y');
						$selected_month = date('n');
						$selected_day = '%';

						$con = get_db_con();

						$query = "SELECT 
						(SELECT YEAR(MIN(created_at)) FROM user) AS user_min_year,
						(SELECT YEAR(MIN(created_at)) FROM orders) AS orders_min_year,
						
						(SELECT name FROM order_states WHERE state_num='" . ORDER_STATE_CONFIRMED . "') AS state_confirmed,
						(SELECT name FROM order_states WHERE state_num='" . ORDER_STATE_FETCHED . "') AS state_fetched,
						(SELECT name FROM order_states WHERE state_num= 6) AS state_completed";

						$result = db_query($con, $query);

						if ($result && db_num_rows($result) == 1) {
							$row = db_fetch_assoc($result);
							db_free_result($result);

							$state_confirmed = strtoupper($row['state_confirmed']);
							$state_fetched = strtoupper($row['state_fetched']);
							$state_completed = strtoupper($row['state_completed']);
							if (!empty($row['user_min_year'])) $user_min_year = $row['user_min_year'];
							if (!empty($row['orders_min_year'])) $orders_min_year = $row['orders_min_year'];
						}

						close_db_con($con);

						$min_year = min([$user_min_year, $orders_min_year]);
						$years = [];

						$months = $days = [
							[
								'value' => '%',
								'label' => 'All'
							]
						];

						for ($i = 1; $i <= 12; $i++) {
							$months[] = $days[] = [
								'value' => $i,
								'label' => $i
							];
						}

						for (; $i <= 31; $i++) {
							$days[] = [
								'value' => $i,
								'label' => $i
							];
						}

						for ($i = $min_year; $i <= $selected_year; $i++) {
							$years[] = [
								'value' => $i,
								'label' => $i . '.'
							];
						}

						$fields_year = [
							[
								'label' => 'Year',
								'tag' => 'select',
								'options' => $years,
								'selected' => $selected_year,
								'attributes' => [
									'id' => 'stats_year',
									'onchange' => 'updateStats();'
								]
							]
						];

						$fields_month = [
							[
								'label' => 'Month',
								'tag' => 'select',
								'options' => $months,
								'selected' => $selected_month,
								'attributes' => [
									'id' => 'stats_month',
									'onchange' => 'updateStats();'
								]
							]
						];

						$fields_day = [
							[
								'label' => 'Day',
								'tag' => 'select',
								'options' => $days,
								'selected' => $selected_day,
								'attributes' => [
									'id' => 'stats_day',
									'onchange' => 'updateStats();'
								]
							]
						];

						$tiles = [
							[
								'label' => 'Stores',
								'icon' => 'fa fa-2x fa-shopping-basket',
								'bg_class' => 'bg-danger',
								'count' => '<span id="shop_count">-</span>',
								'path' => 'stores'
							],
							[
								'label' => 'Delivery staff',
								'icon' => 'fa fa-2x fa-users',
								'bg_class' => 'bg-danger',
								'count' => '<span id="delivery_staff_count">-</span>',
								'path' => 'delivery-staff'
							],
							[
								'label' => 'Users',
								'icon' => 'fa fa-2x fa-users',
								'bg_class' => 'bg-danger',
								'count' => '<span id="user_count">-</span>',
								'path' => 'users'
							],
							[
								'label' => 'Delivered orders',
								'icon' => 'fa fa-2x fa-calendar',
								'bg_class' => 'bg-info',
								'count' => '<span id="delivered_orders_count">-</span>',
								'path' => 'orders'
							],
							[
								'label' => 'Undelivered orders',
								'icon' => 'fa fa-2x fa-calendar',
								'bg_class' => 'bg-info',
								'count' => '<span id="undelivered_orders_count">-</span>',
								'path' => 'orders'
							],
							[
								'label' => 'Total revenue',
								'icon' => 'fa fa-2x fa-balance-scale',
								'bg_class' => 'bg-success',
								'count' => '<span id="total_revenue">-</span> ' . CURRENCY_CODE,
								'path' => 'orders'
							],
							[
								'label' => 'Upcoming revenue',
								'icon' => 'fa fa-2x fa-balance-scale',
								'bg_class' => 'bg-success',
								'count' => '<span id="upcoming_revenue">-</span> ' . CURRENCY_CODE,
								'path' => 'orders'
							],
							[
								'label' => 'Average order price',
								'icon' => 'fa fa-2x fa-balance-scale',
								'bg_class' => 'bg-success',
								'count' => '<span id="avg_total_price">-</span> ' . CURRENCY_CODE,
								'path' => 'orders'
							],
							[
								'label' => 'Average delivery time: ' . $state_confirmed . ' -> ' . $state_completed,
								'icon' => 'fa fa-2x fa-clock-o',
								'bg_class' => 'bg-warning',
								'count' => '<span id="avg_delivery_time_confirmed">-</span>',
								'path' => 'orders'
							],
							[
								'label' => 'Average delivery time: ' . $state_fetched . ' -> ' . $state_completed,
								'icon' => 'fa fa-2x fa-clock-o',
								'bg_class' => 'bg-warning',
								'count' => '<span id="avg_delivery_time_fetched">-</span>',
								'path' => 'orders'
							]
						];

						?>
						<div class="row">
							<div class="col-sm-3">
								<?php generate_form_fields($fields_year); ?>
							</div>
							<div class="col-sm-3">
								<?php generate_form_fields($fields_month); ?>
							</div>
							<div class="col-sm-3">
								<?php generate_form_fields($fields_day); ?>
							</div>
							<div class="col-sm-3" align="center">
								<button class="btn btn-sm btn-success" type="button" onclick="updateStats();"><i class="fa fa-refresh"></i> Fetch data</button>
							</div>
						</div>
						<?php generate_dashboard_tiles($tiles); ?>
					</div>
					<div class="tab-pane" id="btabs-alt-static-graphs">
						<?php

						$fields_year = [
							[
								'label' => 'Year',
								'tag' => 'select',
								'options' => $years,
								'selected' => $selected_year,
								'attributes' => [
									'id' => 'graph_year',
									'onchange' => 'updateGraphs();'
								]
							]
						];

						$fields_month = [
							[
								'label' => 'Month',
								'tag' => 'select',
								'options' => $months,
								'selected' => $selected_month,
								'attributes' => [
									'id' => 'graph_month',
									'onchange' => 'updateGraphs();'
								]
							]
						];

						$fields_day = [
							[
								'label' => 'Day',
								'tag' => 'select',
								'options' => $days,
								'selected' => $selected_day,
								'attributes' => [
									'id' => 'graph_day',
									'onchange' => 'updateGraphs();'
								]
							]
						];

						$graphs = [
							[
								'title' => 'New users',
								'graph_area_id' => 'graph-user-count'
							],
							[
								'title' => 'Orders',
								'graph_area_id' => 'graph-orders-count'
							],
							[
								'title' => 'Total revenue (' . CURRENCY_CODE . ')',
								'graph_area_id' => 'graph-orders-price-sum'
							],
							[
								'title' => 'Payment methods',
								'graph_area_id' => 'graph-orders-payment-method'
							],
							[
								'title' => 'Average revenue per store (' . CURRENCY_CODE . ')',
								'graph_area_id' => 'graph-avg-revenue-per-store'
							],
							[
								'title' => 'Total revenue per store (' . CURRENCY_CODE . ')',
								'graph_area_id' => 'graph-revenue-per-store'
							]
						];

						?>
						<div class="row">
							<div class="col-sm-4">
								<?php generate_form_fields($fields_year); ?>
							</div>
							<div class="col-sm-4">
								<?php generate_form_fields($fields_month); ?>
							</div>
							<div class="col-sm-4">
								<?php generate_form_fields($fields_day); ?>
							</div>
						</div>
						<?php generate_dashboard_graphs($graphs); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php
}

function add_product($get = [], $vars = [])
{
	$form_submit = __FUNCTION__;

	if (!$_SESSION['admin_view_all']) $admin_id = $_SESSION['admin_id'];
	else $admin_id = '%';

	$mode = 'UPDATE';
	$product_id = 0;
	$name = $price = $price_discount = $quantity = $special_feature = $description = $shop_id = $supplier_id = $category_id = $user_id = $featured_image = '';
	$active = true;
	$featured = $promoted = $out_of_stock = false;

	$con = get_db_con();

	if (isset($get['add_id'])) {
		$product_id = $get['add_id'];

		$query = "SELECT * FROM product WHERE id='" . $product_id . "' AND admin_id LIKE '" . $admin_id . "'";
		$result = db_query($con, $query);

		if ($result && db_num_rows($result) == 1) {
			$row = db_fetch_assoc($result);
			db_free_result($result);

			$name = $row['name'];
			if ($row['price']) $price = $row['price'];
			if ($row['price_discount']) $price_discount = $row['price_discount'];
			$quantity = $row['quantity'];
			$special_feature = $row['special_feature'];
			$description = $row['description'];
			$shop_id = $row['shop_id'];
			$supplier_id = $row['supplier_id'];
			$category_id = $row['category_id'];
			$user_id = $row['admin_id'];
			$featured_image = $row['featured_image'];
			$active = boolval($row['active']);
			$featured = boolval($row['featured']);
			$promoted = boolval($row['promoted']);
			$out_of_stock = boolval($row['out_of_stock']);
		} else $product_id = 0;
	}

	$shops = $suppliers = $categories = $users = [
		[
			'value' => '',
			'label' => '-'
		]
	];

	$query = "SELECT shop.id AS shop_id,shop.name AS shop_name,area.name AS area_name,city.name AS city_name FROM shop,area,city WHERE 
	city_id=city.id AND area_id=area.id AND admin_id LIKE '" . $admin_id . "' ORDER BY city.name,area.name,shop.name";
	$result = db_query($con, $query);

	if ($result && db_num_rows($result)) {
		while ($row = db_fetch_assoc($result)) {
			$shops[$row['city_name'] . ' - ' . $row['area_name']][] = [
				'value' => $row['shop_id'],
				'label' => $row['shop_name']
			];
		}

		db_free_result($result);
	}

	$query = "SELECT id,name FROM supplier ORDER BY name";
	$result = db_query($con, $query);

	if ($result && db_num_rows($result)) {
		while ($row = db_fetch_assoc($result)) {
			$suppliers[] = [
				'value' => $row['id'],
				'label' => $row['name']
			];
		}

		db_free_result($result);
	}

	$query = "SELECT id,name FROM category ORDER BY name";
	$result = db_query($con, $query);

	if ($result && db_num_rows($result)) {
		while ($row = db_fetch_assoc($result)) {
			$categories[] = [
				'value' => $row['id'],
				'label' => $row['name']
			];
		}

		db_free_result($result);
	}

	$selected_user = '';

	$query = "SELECT id,full_name FROM admin WHERE id LIKE '" . $admin_id . "' ORDER BY full_name";
	$result = db_query($con, $query);

	if ($result && db_num_rows($result)) {
		while ($row = db_fetch_assoc($result)) {
			$curr_id = $row['id'];
			if (($product_id && $user_id == $curr_id) || (!$product_id && $_SESSION['admin_id'] == $curr_id)) $selected_user = $curr_id;

			$users[] = [
				'value' => $curr_id,
				'label' => $row['full_name']
			];
		}

		db_free_result($result);
	}

	close_db_con($con);

	$fields_basic = [
		[
			'label' => 'Name',
			'tag' => 'input',
			'attributes' => [
				'id' => 'name',
				'name' => 'name',
				'type' => 'text',
				'value' => $name,
				'maxlength' => 50,
				'required' => true,
				'autofocus' => true
			]
		],
		[
			'label' => 'Store',
			'tag' => 'select',
			'options' => $shops,
			'selected' => $shop_id,
			'attributes' => [
				'id' => 'shop_id',
				'name' => 'shop_id',
				'required' => true
			]
		],
		[
			'label' => 'Supplier',
			'tag' => 'select',
			'options' => $suppliers,
			'selected' => $supplier_id,
			'attributes' => [
				'id' => 'supplier_id',
				'name' => 'supplier_id',
				'class' => 'js-select2',
				'required' => true
			]
		],
		[
			'label' => 'Category',
			'tag' => 'select',
			'options' => $categories,
			'selected' => $category_id,
			'attributes' => [
				'id' => 'category_id',
				'name' => 'category_id',
				'class' => 'js-select2',
				'required' => true
			]
		],
		[
			'label' => 'Price',
			'tag' => 'input',
			'group' => [
				'right' => CURRENCY_CODE
			],
			'attributes' => [
				'id' => 'price',
				'name' => 'price',
				'type' => 'number',
				'value' => $price,
				'min' => 0.01,
				'step' => 0.01,
				'required' => true,
			]
		],
		[
			'label' => 'Discount price',
			'tag' => 'input',
			'group' => [
				'right' => CURRENCY_CODE
			],
			'attributes' => [
				'id' => 'price_discount',
				'name' => 'price_discount',
				'type' => 'number',
				'value' => $price_discount,
				'min' => 0.01,
				'step' => 0.01
			]
		],
		[
			'label' => 'Quantity',
			'tag' => 'input',
			'attributes' => [
				'id' => 'quantity',
				'name' => 'quantity',
				'type' => 'text',
				'value' => $quantity,
				'maxlength' => 50
			]
		],
		[
			'label' => 'Special feature',
			'tag' => 'input',
			'attributes' => [
				'id' => 'special_feature',
				'name' => 'special_feature',
				'type' => 'text',
				'value' => $special_feature,
				'maxlength' => 50
			]
		],
		[
			'label' => 'Description',
			'tag' => 'textarea',
			'value' => $description,
			'attributes' => [
				'id' => 'description',
				'name' => 'description',
				'maxlength' => 500,
				'rows' => 10,
				'cols' => 50
			]
		],
		[
			'label' => 'Admin',
			'tag' => 'select',
			'options' => $users,
			'selected' => $selected_user,
			'attributes' => [
				'id' => 'admin_id',
				'name' => 'admin_id',
				'required' => true
			]
		],
		[
			'label' => 'Out of stock',
			'tag' => 'checkbox',
			'side_label' => 'Yes',
			'attributes' => [
				'id' => 'out_of_stock',
				'name' => 'out_of_stock',
				'class' => '',
				'type' => 'checkbox',
				'value' => 1,
				'checked' => $out_of_stock
			]
		],
		[
			'label' => 'Featured',
			'tag' => 'checkbox',
			'side_label' => 'Yes',
			'attributes' => [
				'id' => 'featured',
				'name' => 'featured',
				'class' => '',
				'type' => 'checkbox',
				'value' => 1,
				'checked' => $featured
			]
		],
		[
			'label' => 'Promoted',
			'tag' => 'checkbox',
			'side_label' => 'Yes',
			'attributes' => [
				'id' => 'promoted',
				'name' => 'promoted',
				'class' => '',
				'type' => 'checkbox',
				'value' => 1,
				'checked' => $promoted
			]
		],
		[
			'label' => 'Active',
			'tag' => 'checkbox',
			'side_label' => 'Yes',
			'attributes' => [
				'id' => 'active',
				'name' => 'active',
				'class' => '',
				'type' => 'checkbox',
				'value' => 1,
				'checked' => $active
			]
		]
	];

?>
	<div class="content">
		<?php

		$actions = [
			[
				'type' => 'remove',
				'function' => 'remove_product'
			]
		];

		if (!$product_id) {
			$mode = 'ADD';
			$actions = null;
		}

		add_close_button($mode . ' PRODUCT', $actions);

		?>
		<form class="form-horizontal push-10-t form-notify" action="manage" method="post" autocomplete="off" enctype="multipart/form-data">
			<div class="col-sm-12">
				<div class="block">
					<div class="block-content block-content-narrow">
						<ul class="nav nav-tabs nav-tabs-alt" data-toggle="tabs">
							<li class="active">
								<a href="#btabs-alt-static-basic">Basic</a>
							</li>
							<li>
								<a href="#btabs-alt-static-media">Image</a>
							</li>
						</ul>
						<div class="block-content tab-content">
							<div class="tab-pane active" id="btabs-alt-static-basic">
								<?php generate_form_fields($fields_basic); ?>
							</div>
							<div class="tab-pane" id="btabs-alt-static-media">
								<?php

								generate_file_upload(false);
								generate_image_galery($product_id, PRODUCT_UPLOAD_DIR, $featured_image);

								?>
							</div>
							<?php add_submit_buttons($form_submit); ?>
						</div>
					</div>
				</div>
			</div>
			<input type="hidden" value="<?php echo $product_id; ?>" name="id" />
		</form>

		<input type="hidden" value="<?php echo $product_id; ?>" name="values[]" form="options_form" />
	</div>
<?php
}

function list_product($get = [], $vars = [])
{
	$edit_tag = 'products/add';

?>
	<div class="content">
		<?php

		add_add_button($edit_tag);
		$admin_id = $shop_id = $supplier_id = $category_id = '%';
		$offset = 0;
		$limit = ITEMS_PER_PAGE;

		if (!$_SESSION['admin_view_all']) $admin_id = $_SESSION['admin_id'];
		if (isset($vars['shop_id'])) $shop_id = $vars['shop_id'];
		if (isset($vars['supplier_id'])) $supplier_id = $vars['supplier_id'];
		if (isset($vars['category_id'])) $category_id = $vars['category_id'];
		if (isset($vars['offset'])) $offset = $vars['offset'];

		$shops = $suppliers = $categories = [
			[
				'value' => '%',
				'label' => 'All'
			]
		];

		$con = get_db_con();

		$query = "SELECT shop.id AS shop_id,shop.name AS shop_name,area.name AS area_name,city.name AS city_name FROM shop,area,city WHERE 
		city_id=city.id AND area_id=area.id AND admin_id LIKE '" . $admin_id . "' ORDER BY city.name,area.name,shop.name";
		$result = db_query($con, $query);

		if ($result && db_num_rows($result)) {
			while ($row = db_fetch_assoc($result)) {
				$shops[$row['city_name'] . ' - ' . $row['area_name']][] = [
					'value' => $row['shop_id'],
					'label' => $row['shop_name']
				];
			}

			db_free_result($result);
		}

		$query = "SELECT id,name FROM supplier ORDER BY name";
		$result = db_query($con, $query);

		if ($result && db_num_rows($result)) {
			while ($row = db_fetch_assoc($result)) {
				$suppliers[] = [
					'value' => $row['id'],
					'label' => $row['name']
				];
			}

			db_free_result($result);
		}

		$query = "SELECT id,name FROM category ORDER BY name";
		$result = db_query($con, $query);

		if ($result && db_num_rows($result)) {
			while ($row = db_fetch_assoc($result)) {
				$categories[] = [
					'value' => $row['id'],
					'label' => $row['name']
				];
			}

			db_free_result($result);
		}

		$offsets = [];

		$query = "SELECT COUNT(*) AS product_count FROM product WHERE shop_id LIKE '" . $shop_id . "' AND supplier_id LIKE '" . $supplier_id . "' AND category_id LIKE '" . $category_id . "'";
		$result = db_query($con, $query);

		if ($result && db_num_rows($result) == 1) {
			$row = db_fetch_assoc($result);
			db_free_result($result);

			$product_count = $row['product_count'];
			if ($offset > $product_count) $offset = 0;

			for ($i = 0; $i < $product_count; $i += $limit) {
				$offsets[] = [
					'value' => $i,
					'label' => $i . ' - ' . ($i + $limit - 1)
				];
			}
		}

		$fields1 = [
			[
				'label' => 'Store',
				'tag' => 'select',
				'expected' => false,
				'options' => $shops,
				'selected' => $shop_id,
				'attributes' => [
					'id' => 'shop_id',
					'name' => 'shop_id',
					'required' => true,
					'onchange' => 'this.form.submit();'
				]
			]
		];

		$fields2 = [
			[
				'label' => 'Supplier',
				'tag' => 'select',
				'expected' => false,
				'options' => $suppliers,
				'selected' => $supplier_id,
				'attributes' => [
					'id' => 'supplier_id',
					'name' => 'supplier_id',
					'class' => 'js-select2',
					'required' => true,
					'onchange' => 'this.form.submit();'
				]
			]
		];

		$fields3 = [
			[
				'label' => 'Category',
				'tag' => 'select',
				'expected' => false,
				'options' => $categories,
				'selected' => $category_id,
				'attributes' => [
					'id' => 'category_id',
					'name' => 'category_id',
					'class' => 'js-select2',
					'required' => true,
					'onchange' => 'this.form.submit();'
				]
			]
		];

		$fields4 = [
			[
				'label' => 'Loaded products',
				'tag' => 'select',
				'expected' => false,
				'options' => $offsets,
				'selected' => $offset,
				'attributes' => [
					'id' => 'offset',
					'name' => 'offset',
					'required' => true,
					'onchange' => 'this.form.submit();'
				]
			]
		];

		?>
		<div class="block">
			<div class="block-content">
				<form class="form-horizontal push-10-t" method="get">
					<div class="row">
						<div class="col-sm-3">
							<?php generate_form_fields($fields1); ?>
						</div>
						<div class="col-sm-3">
							<?php generate_form_fields($fields2); ?>
						</div>
						<div class="col-sm-3">
							<?php generate_form_fields($fields3); ?>
						</div>
						<div class="col-sm-3">
							<?php generate_form_fields($fields4); ?>
						</div>
					</div>
				</form>
				<?php

				$query = "SELECT product.id AS product_id,product.name AS product_name,shop_id,shop.name AS shop_name,supplier_id,supplier.name AS supplier_name,category_id,category.name AS category_name,
				product.active AS product_active,product.featured AS product_featured,product.promoted AS product_promoted,product.out_of_stock AS product_out_of_stock
				FROM product,shop,supplier,category WHERE product.shop_id=shop.id AND 
				product.supplier_id=supplier.id AND product.category_id=category.id AND product.shop_id LIKE '" . $shop_id . "' AND product.supplier_id LIKE '" . $supplier_id . "' AND 
				product.category_id LIKE '" . $category_id . "' AND product.admin_id LIKE '" . $admin_id . "' ORDER BY product.name LIMIT " . $offset . "," . $limit;
				$result = db_query($con, $query);

				if ($result && db_num_rows($result)) {
					$actions = [
						[
							'type' => 'remove',
							'function' => 'remove_product'
						],
						[
							'type' => 'activate',
							'function' => 'activate_product'
						],
						[
							'type' => 'deactivate',
							'function' => 'deactivate_product'
						]
					];

					$role_functions = get_role_functions();
					add_action_buttons($actions, $role_functions);

				?>
					<table width="100%" class="table table-bordered table-striped table-hover js-dataTable-full-pagination">
						<thead>
							<tr>
								<th>id</th>
								<th>Name</th>
								<th>Store</th>
								<th>Supplier</th>
								<th>Category</th>
								<th>Out of stock</th>
								<th>Featured</th>
								<th>Promoted</th>
								<th>Active</th>
								<th><?php options_column_header(); ?></th>
							</tr>
						</thead>
						<tbody align="center">
							<?php

							while ($row = db_fetch_assoc($result)) {
								$active = $featured = $promoted = $out_of_stock = 'No';

								if ($row['product_active']) $active = 'Yes';
								if ($row['product_featured']) $featured = 'Yes';
								if ($row['product_promoted']) $promoted = 'Yes';
								if ($row['product_out_of_stock']) $out_of_stock = 'Yes';

							?>
								<tr>
									<td><?php echo $row['product_id']; ?></td>
									<td><?php echo $row['product_name']; ?></td>
									<td><a href="admin/stores/add/<?php echo $row['shop_id']; ?>" class="link-effect"><?php echo $row['shop_name']; ?></a></td>
									<td><a href="admin/suppliers/add/<?php echo $row['supplier_id']; ?>" class="link-effect"><?php echo $row['supplier_name']; ?></a></td>
									<td><a href="admin/categories/add/<?php echo $row['category_id']; ?>" class="link-effect"><?php echo $row['category_name']; ?></a></td>
									<td><?php echo $out_of_stock; ?></td>
									<td><?php echo $featured; ?></td>
									<td><?php echo $promoted; ?></td>
									<td><?php echo $active; ?></td>
									<td><?php add_options_button($edit_tag, $row['product_id'], $role_functions); ?></td>
								</tr>
							<?php
							}

							db_free_result($result);

							?>
						</tbody>
					</table>
				<?php
				} else no_data_message();

				close_db_con($con);

				?>
			</div>
		</div>
	</div>
<?php
}

function add_product_import($get = [], $vars = [])
{
	$form_submit = __FUNCTION__;

	if (!$_SESSION['admin_view_all']) $admin_id = $_SESSION['admin_id'];
	else $admin_id = '%';

	$input_files_accept = str_replace(DELIMITER, ', .', '.' . PRODUCT_IMPORT_FILTER);
	$archive_accept = str_replace(DELIMITER, ', .', '.' . ARCHIVE_FILTER);

	$shops = $users = [
		[
			'value' => '',
			'label' => '-'
		]
	];

	$con = get_db_con();

	$query = "SELECT shop.id AS shop_id,shop.name AS shop_name,area.name AS area_name,city.name AS city_name FROM shop,area,city WHERE 
	city_id=city.id AND area_id=area.id AND admin_id LIKE '" . $admin_id . "' ORDER BY city.name,area.name,shop.name";
	$result = db_query($con, $query);

	if ($result && db_num_rows($result)) {
		while ($row = db_fetch_assoc($result)) {
			$shops[$row['city_name'] . ' - ' . $row['area_name']][] = [
				'value' => $row['shop_id'],
				'label' => $row['shop_name']
			];
		}

		db_free_result($result);
	}

	$selected_user = '';

	$query = "SELECT id,full_name FROM admin WHERE id LIKE '" . $admin_id . "' ORDER BY full_name";
	$result = db_query($con, $query);

	if ($result && db_num_rows($result)) {
		while ($row = db_fetch_assoc($result)) {
			$curr_id = $row['id'];
			if ($_SESSION['admin_id'] == $curr_id) $selected_user = $curr_id;

			$users[] = [
				'value' => $curr_id,
				'label' => $row['full_name']
			];
		}

		db_free_result($result);
	}

	close_db_con($con);

	$fields = [
		[
			'label' => 'Store',
			'tag' => 'select',
			'options' => $shops,
			'attributes' => [
				'id' => 'shop_id',
				'name' => 'shop_id',
				'required' => true,
				'autofocus' => true
			]
		],
		[
			'label' => 'Admin',
			'tag' => 'select',
			'options' => $users,
			'selected' => $selected_user,
			'attributes' => [
				'id' => 'admin_id',
				'name' => 'admin_id',
				'required' => true
			]
		],
		[
			'label' => 'Out of stock',
			'tag' => 'checkbox',
			'side_label' => 'Yes',
			'expected' => false,
			'attributes' => [
				'id' => 'out_of_stock',
				'name' => 'out_of_stock',
				'class' => '',
				'type' => 'checkbox',
				'value' => 1,
				'checked' => false
			]
		],
		[
			'label' => 'Featured',
			'tag' => 'checkbox',
			'side_label' => 'Yes',
			'expected' => false,
			'attributes' => [
				'id' => 'featured',
				'name' => 'featured',
				'class' => '',
				'type' => 'checkbox',
				'value' => 1,
				'checked' => false
			]
		],
		[
			'label' => 'Promoted',
			'tag' => 'checkbox',
			'side_label' => 'Yes',
			'expected' => false,
			'attributes' => [
				'id' => 'promoted',
				'name' => 'promoted',
				'class' => '',
				'type' => 'checkbox',
				'value' => 1,
				'checked' => false
			]
		],
		[
			'label' => 'Active',
			'tag' => 'checkbox',
			'side_label' => 'Yes',
			'expected' => false,
			'attributes' => [
				'id' => 'active',
				'name' => 'active',
				'class' => '',
				'type' => 'checkbox',
				'value' => 1,
				'checked' => true
			]
		],
		[
			'label' => 'Files (' . $input_files_accept . ')',
			'tag' => 'input',
			'expected' => false,
			'attributes' => [
				'id' => 'input_files',
				'name' => 'input_files[]',
				'class' => '',
				'type' => 'file',
				'accept' => $input_files_accept,
				'required' => true,
				'multiple' => true
			]
		],
		[
			'label' => 'Image archives (' . $archive_accept . ')',
			'tag' => 'input',
			'expected' => false,
			'attributes' => [
				'id' => 'images_archives',
				'name' => 'images_archives[]',
				'class' => '',
				'type' => 'file',
				'accept' => $archive_accept,
				'multiple' => true
			]
		]
	];

?>
	<div class="content">
		<?php add_close_button('IMPORT PRODUCTS'); ?>
		<form class="form-horizontal push-10-t form-notify" action="manage" method="post" autocomplete="off" enctype="multipart/form-data">
			<div class="col-sm-12">
				<div class="block">
					<div class="block-content block-content-narrow">
						<?php

						generate_form_fields($fields);
						add_submit_buttons($form_submit);

						?>
					</div>
				</div>
			</div>
		</form>
	</div>
<?php
}

function list_product_import($get = [], $vars = [])
{
	$edit_tag = 'products/imports/add';

?>
	<div class="content">
		<?php

		add_add_button($edit_tag);
		$shop_id = $admin_id = '%';

		if (!$_SESSION['admin_view_all']) $admin_id = $_SESSION['admin_id'];
		if (isset($vars['shop_id'])) $shop_id = $vars['shop_id'];

		$shops = [
			[
				'value' => '%',
				'label' => 'All'
			]
		];

		$con = get_db_con();
		$query = "SELECT shop.id AS shop_id,shop.name AS shop_name,area.name AS area_name,city.name AS city_name FROM shop,area,city WHERE 
		city_id=city.id AND area_id=area.id AND admin_id LIKE '" . $admin_id . "' ORDER BY city.name,area.name,shop.name";
		$result = db_query($con, $query);

		if ($result && db_num_rows($result)) {
			while ($row = db_fetch_assoc($result)) {
				$shops[$row['city_name'] . ' - ' . $row['area_name']][] = [
					'value' => $row['shop_id'],
					'label' => $row['shop_name']
				];
			}

			db_free_result($result);
		}

		$fields = [
			[
				'label' => 'Store',
				'tag' => 'select',
				'expected' => false,
				'options' => $shops,
				'selected' => $shop_id,
				'attributes' => [
					'id' => 'shop_id',
					'name' => 'shop_id',
					'required' => true,
					'onchange' => 'this.form.submit();'
				]
			]
		];

		?>
		<div class="block">
			<div class="block-content">
				<form class="form-horizontal push-10-t" method="get">
					<div class="row">
						<div class="col-sm-6">
							<?php generate_form_fields($fields); ?>
						</div>
					</div>
				</form>
				<?php

				$query = "SELECT product_import_log.id AS import_id,shop_id,shop.name AS shop_name,admin.id AS admin_id,admin.full_name AS admin_name,
				product_import_log.created_at AS import_timestamp,(SELECT COUNT(*) FROM product WHERE import_id=product_import_log.id) AS product_count FROM 
				shop,admin,product_import_log WHERE shop.id=shop_id AND admin.id=product_import_log.admin_id AND admin.id LIKE '" . $admin_id . "' AND shop_id LIKE '" . $shop_id . "' 
				ORDER BY product_import_log.created_at DESC";
				$result = db_query($con, $query);

				if ($result && db_num_rows($result)) {
					$actions = [
						[
							'type' => 'rollback',
							'function' => 'remove_product_import'
						]
					];

					$role_functions = get_role_functions();
					add_action_buttons($actions, $role_functions);

				?>
					<table width="100%" class="table table-bordered table-striped table-hover js-dataTable-full-pagination">
						<thead>
							<tr>
								<th>Imported at</th>
								<th>Store</th>
								<th>Admin</th>
								<th>Products</th>
								<th><?php options_column_header(); ?></th>
							</tr>
						</thead>
						<tbody align="center">
							<?php

							while ($row = db_fetch_assoc($result)) {
								$timestamp = format_date($row['import_timestamp']);

							?>
								<tr>
									<td><?php echo $timestamp; ?></td>
									<td><a href="admin/stores/add/<?php echo $row['shop_id']; ?>" class="link-effect"><?php echo $row['shop_name']; ?></a></td>
									<td><a href="admin/admins/add/<?php echo $row['admin_id']; ?>" class="link-effect"><?php echo $row['admin_name']; ?></a></td>
									<td><?php echo $row['product_count']; ?></td>
									<td><?php add_options_button(null, $row['import_id'], $role_functions); ?></td>
								</tr>
							<?php
							}

							db_free_result($result);

							?>
						</tbody>
					</table>
				<?php
				} else no_data_message();

				close_db_con($con);

				?>
			</div>
		</div>
	</div>
<?php
}

function add_shop($get = [], $vars = [])
{
	$form_submit = __FUNCTION__;

	if (!$_SESSION['admin_view_all']) $admin_id = $_SESSION['admin_id'];
	else $admin_id = '%';

	$mode = 'UPDATE';
	$shop_id = 0;
	$name = $area_id = $shop_type_id = $user_id = $featured_image = $latitude = $longitude = '';
	$update = false;

	$con = get_db_con();

	if (isset($get['add_id'])) {
		$shop_id = $get['add_id'];

		$query = "SELECT * FROM shop WHERE id='" . $shop_id . "' AND admin_id LIKE '" . $admin_id . "'";
		$result = db_query($con, $query);

		if ($result && db_num_rows($result) == 1) {
			$row = db_fetch_assoc($result);
			db_free_result($result);

			$name = $row['name'];
			$area_id = $row['area_id'];
			$shop_type_id = $row['shop_type_id'];
			$user_id = $row['admin_id'];
			$latitude = $row['latitude'];
			$longitude = $row['longitude'];
			$active = boolval($row['active']);

			$featured_image = [
				'featured_image' => ['value' => $row['featured_image'], 'description' => 'Featured'],
				'front_image' => ['value' => $row['front_image'], 'description' => 'Front']
			];
		} else $shop_id = 0;
	}

	$areas = $users = $shop_types = [
		[
			'value' => '',
			'label' => '-'
		]
	];

	$query = "SELECT area.id AS area_id,city.name AS city_name,area.name AS area_name FROM city,area WHERE city_id=city.id ORDER BY city.name,area.name";
	$result = db_query($con, $query);

	if ($result && db_num_rows($result)) {
		while ($row = db_fetch_assoc($result)) {
			$areas[$row['city_name']][] = [
				'value' => $row['area_id'],
				'label' => $row['area_name']
			];
		}

		db_free_result($result);
	}

	$selected_user = '';

	$query = "SELECT * FROM shop_type ORDER BY name";
	$result = db_query($con, $query);

	if ($result && db_num_rows($result)) {
		while ($row = db_fetch_assoc($result)) {
			$shop_types[] = [
				'value' => $row['id'],
				'label' => $row['name']
			];
		}

		db_free_result($result);
	}

	$query = "SELECT id,full_name FROM admin WHERE id LIKE '" . $admin_id . "' ORDER BY full_name";
	$result = db_query($con, $query);

	if ($result && db_num_rows($result)) {
		while ($row = db_fetch_assoc($result)) {
			$curr_id = $row['id'];
			if (($shop_id && $user_id == $curr_id) || (!$shop_id && $_SESSION['admin_id'] == $curr_id)) $selected_user = $curr_id;

			$users[] = [
				'value' => $curr_id,
				'label' => $row['full_name']
			];
		}

		db_free_result($result);
	}

	close_db_con($con);

	$fields_basic = [
		[
			'label' => 'Name',
			'tag' => 'input',
			'attributes' => [
				'id' => 'map_name',
				'name' => 'name',
				'type' => 'text',
				'value' => $name,
				'maxlength' => 50,
				'required' => true,
				'autofocus' => true
			]
		],
		[
			'label' => 'Store type',
			'tag' => 'select',
			'options' => $shop_types,
			'selected' => $shop_type_id,
			'attributes' => [
				'id' => 'shop_type_id',
				'name' => 'shop_type_id',
				'required' => true
			]
		],
		[
			'label' => 'Admin',
			'tag' => 'select',
			'options' => $users,
			'selected' => $selected_user,
			'attributes' => [
				'id' => 'admin_id',
				'name' => 'admin_id',
				'required' => true
			]
		],
		[
			'label' => 'Active',
			'tag' => 'checkbox',
			'side_label' => 'Yes',
			'attributes' => [
				'id' => 'active',
				'name' => 'active',
				'class' => '',
				'type' => 'checkbox',
				'value' => 1,
				'checked' => $active
			]
		]
	];

	$fields_area = [
		[
			'label' => 'Area',
			'tag' => 'select',
			'options' => $areas,
			'selected' => $area_id,
			'attributes' => [
				'id' => 'area_id',
				'name' => 'area_id',
				'required' => true
			]
		]
	];

	$fields_map_lat = [
		[
			'label' => 'Latitude',
			'tag' => 'input',
			'attributes' => [
				'id' => 'map_latitude',
				'name' => 'latitude',
				'type' => 'number',
				'value' => $latitude,
				'min' => -90,
				'max' => 90,
				'step' => 'any',
				'onchange' => 'coordinateChange();',
				'required' => true
			]
		]
	];

	$fields_map_lng = [
		[
			'label' => 'Longitude',
			'tag' => 'input',
			'attributes' => [
				'id' => 'map_longitude',
				'name' => 'longitude',
				'type' => 'number',
				'value' => $longitude,
				'min' => -180,
				'max' => 180,
				'step' => 'any',
				'onchange' => 'coordinateChange();',
				'required' => true
			]
		]
	];

?>
	<div class="content">
		<?php

		$actions = [
			[
				'type' => 'remove',
				'function' => 'remove_shop'
			]
		];

		if (!$shop_id) {
			$mode = 'ADD';
			$actions = null;
		}

		add_close_button($mode . ' STORE', $actions);

		?>
		<form class="form-horizontal push-10-t form-notify" action="manage" method="post" autocomplete="off" enctype="multipart/form-data">
			<div class="col-sm-12">
				<div class="block">
					<div class="block-content block-content-narrow">
						<ul class="nav nav-tabs nav-tabs-alt" data-toggle="tabs">
							<li class="active">
								<a href="#btabs-alt-static-basic">Basic</a>
							</li>
							<li onclick="mapResize();">
								<a href="#btabs-alt-static-location">Location</a>
							</li>
							<li>
								<a href="#btabs-alt-static-media">Images</a>
							</li>
						</ul>
						<div class="block-content tab-content">
							<div class="tab-pane active" id="btabs-alt-static-basic">
								<?php generate_form_fields($fields_basic); ?>
							</div>
							<div class="tab-pane" id="btabs-alt-static-location">
								<?php generate_form_fields($fields_area); ?>
								<div class="col-sm-6">
									<?php generate_form_fields($fields_map_lat); ?>
								</div>
								<div class="col-sm-6">
									<?php generate_form_fields($fields_map_lng); ?>
								</div>
								<div class="form-group">
									<div id="map_container" class="map"></div>
								</div>
							</div>
							<div class="tab-pane" id="btabs-alt-static-media">
								<?php

								// Logo Image
								generate_file_upload();
								generate_image_galery($shop_id, SHOP_UPLOAD_DIR, $featured_image);
								// Background Image
								// generate_file_upload(false, IMAGE_FILTER, 2);
								// generate_image_galery($shop_id, SHOP_UPLOAD_DIR, $featured_image, 2);

								?>
							</div>
							<?php add_submit_buttons($form_submit); ?>
						</div>
					</div>
				</div>
			</div>
			<input type="hidden" value="<?php echo $shop_id; ?>" name="id" />
		</form>

		<input type="hidden" value="<?php echo $shop_id; ?>" name="values[]" form="options_form" />
		<script defer src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_API_KEY; ?>&callback=initMap"></script>
	</div>
<?php
}

function list_shop($get = [], $vars = [])
{
	$edit_tag = 'stores/add';

?>
	<div class="content">
		<?php

		add_add_button($edit_tag);
		$area_id = $admin_id = $shop_type_id = '%';

		if (!$_SESSION['admin_view_all']) $admin_id = $_SESSION['admin_id'];
		if (isset($vars['area_id'])) $area_id = $vars['area_id'];
		if (isset($vars['shop_type_id'])) $shop_type_id = $vars['shop_type_id'];

		$con = get_db_con();
		$query = "SELECT area.id AS area_id,city.name AS city_name,area.name AS area_name FROM city,area WHERE city_id=city.id ORDER BY city.name,area.name";
		$result = db_query($con, $query);

		$areas = $shop_types = [
			[
				'value' => '%',
				'label' => 'All'
			]
		];

		if ($result && db_num_rows($result)) {
			while ($row = db_fetch_assoc($result)) {
				$areas[$row['city_name']][] = [
					'value' => $row['area_id'],
					'label' => $row['area_name']
				];
			}

			db_free_result($result);
		}

		$query = "SELECT * FROM shop_type ORDER BY name";
		$result = db_query($con, $query);

		if ($result && db_num_rows($result)) {
			while ($row = db_fetch_assoc($result)) {
				$shop_types[] = [
					'value' => $row['id'],
					'label' => $row['name']
				];
			}

			db_free_result($result);
		}

		$fields_area = [
			[
				'label' => 'Area',
				'tag' => 'select',
				'expected' => false,
				'options' => $areas,
				'selected' => $area_id,
				'attributes' => [
					'id' => 'area_id',
					'name' => 'area_id',
					'required' => true,
					'onchange' => 'this.form.submit();'
				]
			]
		];

		$fields_shop_type = [
			[
				'label' => 'Store type',
				'tag' => 'select',
				'expected' => false,
				'options' => $shop_types,
				'selected' => $shop_type_id,
				'attributes' => [
					'id' => 'shop_type_id',
					'name' => 'shop_type_id',
					'required' => true,
					'onchange' => 'this.form.submit();'
				]
			]
		];

		?>
		<div class="block">
			<div class="block-content">
				<form class="form-horizontal push-10-t" method="get">
					<div class="row">
						<div class="col-sm-6">
							<?php generate_form_fields($fields_area); ?>
						</div>
						<div class="col-sm-6">
							<?php generate_form_fields($fields_shop_type); ?>
						</div>
					</div>
				</form>
				<?php

				$query = "SELECT shop.id AS shop_id,shop.name AS shop_name,shop.active AS shop_active,area_id,shop_type_id,area.name AS area_name,city.name AS city_name,admin_id,admin.full_name AS admin_name,
				shop_type.name AS shop_type_name,(SELECT COUNT(*) FROM product WHERE product.shop_id=shop.id) AS product_count FROM shop,city,area,shop_type,admin 
				WHERE city_id=city.id AND area_id=area.id AND shop_type_id=shop_type.id AND admin.id=shop.admin_id AND admin_id LIKE '" . $admin_id . "' AND area_id LIKE '" . $area_id . "' 
				AND shop_type_id LIKE '" . $shop_type_id . "' ORDER BY shop.name";
				$result = db_query($con, $query);

				if ($result && db_num_rows($result)) {
					$actions = [
						[
							'type' => 'remove',
							'function' => 'remove_shop'
						],
						[
							'type' => 'activate',
							'function' => 'activate_shop'
						],
						[
							'type' => 'deactivate',
							'function' => 'deactivate_shop'
						]
					];

					$role_functions = get_role_functions();
					add_action_buttons($actions, $role_functions);

				?>
					<table width="100%" class="table table-bordered table-striped table-hover js-dataTable-full-pagination">
						<thead>
							<tr>
								<th>Name</th>
								<th>Area</th>
								<th>Type</th>
								<th>Admin</th>
								<th>Products</th>
								<th>Active</th>
								<th><?php options_column_header(); ?></th>
							</tr>
						</thead>
						<tbody align="center">
							<?php

							while ($row = db_fetch_assoc($result)) {
								if ($row['shop_active']) $active = 'Yes';
								else $active = 'No';

							?>
								<tr>
									<td><?php echo $row['shop_name']; ?></td>
									<td><a href="admin/cities/areas/add/<?php echo $row['area_id']; ?>" class="link-effect"><?php echo $row['city_name']; ?> - <?php echo $row['area_name']; ?></a></td>
									<td><a href="admin/stores/types/add/<?php echo $row['shop_type_id']; ?>" class="link-effect"><?php echo $row['shop_type_name']; ?></a></td>
									<td><a href="admin/admins/add/<?php echo $row['admin_id']; ?>" class="link-effect"><?php echo $row['admin_name']; ?></a></td>
									<td><?php echo $row['product_count']; ?></td>
									<td><?php echo $active; ?></td>
									<td><?php add_options_button($edit_tag, $row['shop_id'], $role_functions); ?></td>
								</tr>
							<?php
							}

							db_free_result($result);

							?>
						</tbody>
					</table>
				<?php
				} else no_data_message();

				close_db_con($con);

				?>
			</div>
		</div>
	</div>
<?php
}

function add_shop_type($get = [], $vars = [])
{
	$form_submit = __FUNCTION__;

	$mode = 'UPDATE';
	$id = 0;
	$name = '';

	if (isset($get['add_id'])) {
		$id = $get['add_id'];

		$con = get_db_con();
		$query = "SELECT * FROM shop_type WHERE id='" . $id . "'";
		$result = db_query($con, $query);

		if ($result && db_num_rows($result) == 1) {
			$row = db_fetch_assoc($result);
			db_free_result($result);

			$name = $row['name'];
		} else $id = 0;

		close_db_con($con);
	}

	$fields = [
		[
			'label' => 'Name',
			'tag' => 'input',
			'attributes' => [
				'id' => 'name',
				'name' => 'name',
				'type' => 'text',
				'value' => $name,
				'maxlength' => 50,
				'required' => true,
				'autofocus' => true
			]
		]
	];

?>
	<div class="content">
		<?php

		$actions = [
			[
				'type' => 'remove',
				'function' => 'remove_shop_type'
			]
		];

		if (!$id) {
			$mode = 'ADD';
			$actions = null;
		}

		add_close_button($mode . ' STORE TYPE', $actions);

		?>
		<form class="form-horizontal push-10-t form-notify" action="manage" method="post" autocomplete="off">
			<div class="col-sm-12">
				<div class="block">
					<div class="block-content block-content-narrow">
						<?php

						generate_form_fields($fields);
						add_submit_buttons($form_submit);

						?>
					</div>
				</div>
			</div>
			<input type="hidden" value="<?php echo $id; ?>" name="id" />
		</form>

		<input type="hidden" value="<?php echo $id; ?>" name="values[]" form="options_form" />
	</div>
<?php
}

function list_shop_type($get = [], $vars = [])
{
	$edit_tag = 'stores/types/add';

?>
	<div class="content">
		<?php add_add_button($edit_tag); ?>
		<div class="block">
			<div class="block-content">
				<?php

				$con = get_db_con();
				$query = "SELECT id,name,(SELECT COUNT(*) FROM shop WHERE shop.shop_type_id=shop_type.id) AS shop_count FROM shop_type ORDER BY name";
				$result = db_query($con, $query);

				if ($result && db_num_rows($result)) {
					$actions = [
						[
							'type' => 'remove',
							'function' => 'remove_shop_type'
						]
					];

					$role_functions = get_role_functions();
					add_action_buttons($actions, $role_functions);

				?>
					<table width="100%" class="table table-bordered table-striped table-hover js-dataTable-full-pagination">
						<thead>
							<tr>
								<th>Name</th>
								<th>Stores</th>
								<th><?php options_column_header(); ?></th>
							</tr>
						</thead>
						<tbody align="center">
							<?php

							while ($row = db_fetch_assoc($result)) {
							?>
								<tr>
									<td><?php echo $row['name']; ?></td>
									<td><?php echo $row['shop_count']; ?></td>
									<td><?php add_options_button($edit_tag, $row['id'], $role_functions); ?></td>
								</tr>
							<?php
							}

							db_free_result($result);

							?>
						</tbody>
					</table>
				<?php
				} else no_data_message();

				close_db_con($con);

				?>
			</div>
		</div>
	</div>
<?php
}

function add_supplier($get = [], $vars = [])
{
	$form_submit = __FUNCTION__;

	$mode = 'UPDATE';
	$id = 0;
	$name = $featured_image = '';
	$active = true;

	if (isset($get['add_id'])) {
		$id = $get['add_id'];

		$con = get_db_con();
		$query = "SELECT * FROM supplier WHERE id='" . $id . "'";
		$result = db_query($con, $query);

		if ($result && db_num_rows($result) == 1) {
			$row = db_fetch_assoc($result);
			db_free_result($result);

			$name = $row['name'];
			$featured_image = $row['featured_image'];
			$active = boolval($row['active']);
		} else $id = 0;

		close_db_con($con);
	}

	$fields_basic = [
		[
			'label' => 'Name',
			'tag' => 'input',
			'attributes' => [
				'id' => 'name',
				'name' => 'name',
				'type' => 'text',
				'value' => $name,
				'maxlength' => 50,
				'required' => true,
				'autofocus' => true
			]
		],
		[
			'label' => 'Active',
			'tag' => 'checkbox',
			'side_label' => 'Yes',
			'attributes' => [
				'id' => 'active',
				'name' => 'active',
				'class' => '',
				'type' => 'checkbox',
				'value' => 1,
				'checked' => $active
			]
		]
	];

?>
	<div class="content">
		<?php

		$actions = [
			[
				'type' => 'remove',
				'function' => 'remove_supplier'
			]
		];

		if (!$id) {
			$mode = 'ADD';
			$actions = null;
		}

		add_close_button($mode . ' SUPPLIER', $actions);

		?>
		<form class="form-horizontal push-10-t form-notify" action="manage" method="post" autocomplete="off" enctype="multipart/form-data">
			<div class="col-sm-12">
				<div class="block">
					<div class="block-content block-content-narrow">
						<ul class="nav nav-tabs nav-tabs-alt" data-toggle="tabs">
							<li class="active">
								<a href="#btabs-alt-static-basic">Basic</a>
							</li>
							<li>
								<a href="#btabs-alt-static-media">Image</a>
							</li>
						</ul>
						<div class="block-content tab-content">
							<div class="tab-pane active" id="btabs-alt-static-basic">
								<?php generate_form_fields($fields_basic); ?>
							</div>
							<div class="tab-pane" id="btabs-alt-static-media">
								<?php

								generate_file_upload(false);
								generate_image_galery($id, SUPPLIER_UPLOAD_DIR, $featured_image);

								?>
							</div>
							<?php add_submit_buttons($form_submit); ?>
						</div>
					</div>
				</div>
			</div>
			<input type="hidden" value="<?php echo $id; ?>" name="id" />
		</form>

		<input type="hidden" value="<?php echo $id; ?>" name="values[]" form="options_form" />
	</div>
<?php
}

function list_supplier($get = [], $vars = [])
{
	$edit_tag = 'suppliers/add';

?>
	<div class="content">
		<?php

		add_add_button($edit_tag);

		$offset = 0;
		$limit = ITEMS_PER_PAGE;

		if (isset($vars['offset'])) $offset = $vars['offset'];

		$con = get_db_con();
		$query = "SELECT COUNT(*) AS supplier_count FROM supplier";
		$result = db_query($con, $query);

		$offsets = [];

		if ($result && db_num_rows($result) == 1) {
			$row = db_fetch_assoc($result);
			db_free_result($result);

			$supplier_count = $row['supplier_count'];
			if ($offset > $supplier_count) $offset = 0;

			for ($i = 0; $i < $supplier_count; $i += $limit) {
				$offsets[] = [
					'value' => $i,
					'label' => $i . ' - ' . ($i + $limit - 1)
				];
			}
		}

		$fields = [
			[
				'label' => 'Loaded suppliers',
				'tag' => 'select',
				'expected' => false,
				'options' => $offsets,
				'selected' => $offset,
				'attributes' => [
					'id' => 'offset',
					'name' => 'offset',
					'required' => true,
					'onchange' => 'this.form.submit();'
				]
			]
		];

		?>
		<div class="block">
			<div class="block-content">
				<form class="form-horizontal push-10-t" method="get">
					<div class="row">
						<div class="col-sm-6">
							<?php generate_form_fields($fields); ?>
						</div>
					</div>
				</form>
				<?php

				$query = "SELECT id,name,active,(SELECT COUNT(*) FROM product WHERE product.supplier_id=supplier.id) AS product_count FROM supplier ORDER BY name LIMIT " . $offset . "," . $limit;
				$result = db_query($con, $query);

				if ($result && db_num_rows($result)) {
					$actions = [
						[
							'type' => 'remove',
							'function' => 'remove_supplier'
						],
						[
							'type' => 'activate',
							'function' => 'activate_supplier'
						],
						[
							'type' => 'deactivate',
							'function' => 'deactivate_supplier'
						]
					];

					$role_functions = get_role_functions();
					add_action_buttons($actions, $role_functions);

				?>
					<table width="100%" class="table table-bordered table-striped table-hover js-dataTable-full-pagination">
						<thead>
							<tr>
								<th>Name</th>
								<th>Products</th>
								<th>Active</th>
								<th><?php options_column_header(); ?></th>
							</tr>
						</thead>
						<tbody align="center">
							<?php

							while ($row = db_fetch_assoc($result)) {
								if ($row['active']) $active = 'Yes';
								else $active = 'No';

							?>
								<tr>
									<td><?php echo $row['name']; ?></td>
									<td><?php echo $row['product_count']; ?></td>
									<td><?php echo $active; ?></td>
									<td><?php add_options_button($edit_tag, $row['id'], $role_functions); ?></td>
								</tr>
							<?php
							}

							db_free_result($result);

							?>
						</tbody>
					</table>
				<?php
				} else no_data_message();

				close_db_con($con);

				?>
			</div>
		</div>
	</div>
<?php
}

function add_admin($get = [], $vars = [])
{
	$form_submit = __FUNCTION__;

	$mode = 'UPDATE';
	$id = 0;
	$username = $full_name = $email = $role_id = '';
	$password = substr(md5(mt_rand()), 0, MIN_PASS_LEN);
	$active = $pass_required = true;

	$con = get_db_con();

	if (isset($get['add_id'])) {
		$id = $get['add_id'];

		$query = "SELECT * FROM admin WHERE id='" . $id . "'";
		$result = db_query($con, $query);

		if ($result && db_num_rows($result) == 1) {
			$row = db_fetch_assoc($result);
			db_free_result($result);

			$username = $row['username'];
			$full_name = $row['full_name'];
			$email = strtolower($row['email']);
			$role_id = $row['role_id'];
			$active = boolval($row['active']);
			$password = '';
			$pass_required = false;
		} else $id = 0;
	}

	$roles = [
		[
			'value' => '',
			'label' => '-'
		]
	];

	$query = "SELECT * FROM role ORDER BY name";
	$result = db_query($con, $query);

	if ($result && db_num_rows($result)) {
		while ($row = db_fetch_assoc($result)) {
			$roles[] = [
				'value' => $row['id'],
				'label' => $row['name']
			];
		}

		db_free_result($result);
	}

	close_db_con($con);

	$fields = [
		[
			'label' => 'Username (must be unique)',
			'tag' => 'input',
			'attributes' => [
				'id' => 'username',
				'name' => 'username',
				'type' => 'text',
				'value' => $username,
				'maxlength' => 50,
				'required' => true,
				'autofocus' => true
			]
		],
		[
			'label' => 'Full name',
			'tag' => 'input',
			'attributes' => [
				'id' => 'full_name',
				'name' => 'full_name',
				'type' => 'text',
				'value' => $full_name,
				'maxlength' => 50,
				'required' => true,
			]
		],
		[
			'label' => 'E-mail (must be unique)',
			'tag' => 'input',
			'attributes' => [
				'id' => 'email',
				'name' => 'email',
				'type' => 'email',
				'value' => $email,
				'maxlength' => 50,
				'required' => true,
			]
		],
		[
			'label' => 'Role',
			'tag' => 'select',
			'options' => $roles,
			'selected' => $role_id,
			'attributes' => [
				'id' => 'role_id',
				'name' => 'role_id',
				'required' => true
			]
		],
		[
			'label' => 'Password (min. ' . MIN_PASS_LEN . ' characters)',
			'tag' => 'input',
			'attributes' => [
				'id' => 'password',
				'name' => 'password',
				'type' => 'text',
				'value' => $password,
				'pattern' => '.{' . MIN_PASS_LEN . ',}',
				'required' => $pass_required
			]
		],
		[
			'label' => 'Active',
			'tag' => 'checkbox',
			'side_label' => 'Yes',
			'attributes' => [
				'id' => 'active',
				'name' => 'active',
				'class' => '',
				'type' => 'checkbox',
				'value' => 1,
				'checked' => $active
			]
		]
	];

?>
	<div class="content">
		<?php

		$actions = [
			[
				'type' => 'remove',
				'function' => 'remove_admin'
			]
		];

		if (!$id) {
			$mode = 'ADD';
			$actions = null;
		}

		add_close_button($mode . ' ADMIN', $actions);

		?>
		<form class="form-horizontal push-10-t form-notify" action="manage" method="post" autocomplete="off">
			<div class="col-sm-12">
				<div class="block">
					<div class="block-content block-content-narrow">
						<?php

						generate_form_fields($fields);
						add_submit_buttons($form_submit);

						?>
					</div>
				</div>
			</div>
			<input type="hidden" value="<?php echo $id; ?>" name="id" />
		</form>

		<input type="hidden" value="<?php echo $id; ?>" name="values[]" form="options_form" />
	</div>
<?php
}

function list_admin($get = [], $vars = [])
{
	$edit_tag = 'admins/add';

?>
	<div class="content">
		<?php add_add_button($edit_tag); ?>
		<div class="block">
			<div class="block-content">
				<?php

				$con = get_db_con();
				$query = "SELECT admin.id AS admin_id,username,full_name,email,active,role_id,role.name AS role_name FROM admin,role WHERE role_id=role.id ORDER BY username";
				$result = db_query($con, $query);

				if ($result && db_num_rows($result)) {
					$actions = [
						[
							'type' => 'remove',
							'function' => 'remove_admin'
						],
						[
							'type' => 'activate',
							'function' => 'activate_admin'
						],
						[
							'type' => 'deactivate',
							'function' => 'deactivate_admin'
						]
					];

					$role_functions = get_role_functions();
					add_action_buttons($actions, $role_functions);

				?>
					<table width="100%" class="table table-bordered table-striped table-hover js-dataTable-full-pagination">
						<thead>
							<tr>
								<th>Username</th>
								<th>Full name</th>
								<th>E-mail</th>
								<th>Role</th>
								<th>Active</th>
								<th><?php options_column_header(); ?></th>
							</tr>
						</thead>
						<tbody align="center">
							<?php

							while ($row = db_fetch_assoc($result)) {
								if ($row['active']) $active = 'Yes';
								else $active = 'No';

							?>
								<tr>
									<td><?php echo $row['username']; ?></td>
									<td><?php echo $row['full_name']; ?></td>
									<td><a href="mailto:<?php echo $row['email']; ?>" class="link-effect"><?php echo $row['email']; ?></a></td>
									<td><a href="admin/roles/add/<?php echo $row['role_id']; ?>" class="link-effect"><?php echo $row['role_name']; ?></a></td>
									<td><?php echo $active; ?></td>
									<td><?php add_options_button($edit_tag, $row['admin_id'], $role_functions); ?></td>
								</tr>
							<?php
							}

							db_free_result($result);

							?>
						</tbody>
					</table>
				<?php
				} else no_data_message();

				close_db_con($con);

				?>
			</div>
		</div>
	</div>
<?php
}
function add_user($get = [], $vars = [])
{
	$form_submit = __FUNCTION__;

	$mode = 'UPDATE';
	$id = 0;
	$first_name = $last_name = $email = $phone = $country = $area_id = $house_number = $address = '';
	$password = substr(md5(mt_rand()), 0, MIN_PASS_LEN);
	$active = $pass_required = true;

	$con = get_db_con();

	if (isset($get['add_id'])) {
		$user_id = $id = $get['add_id'];

		$query = "SELECT * FROM user WHERE id='" . $id . "'";
		$result = db_query($con, $query);

		if ($result && db_num_rows($result) == 1) {
			$row = db_fetch_assoc($result);
			db_free_result($result);

			$user_id = $row['id'];

			$first_name = $row['first_name'];
			$last_name = $row['last_name'];
			$email = strtolower($row['email']);
			$phone = $row['phone'];
			$country = $row['country'];
			$street = $row['street'];
			$house_number = $row['house_number'];
			$area_id = $row['area_id'];
			$active = boolval($row['active']);
			$password = '';
			$pass_required = false;
		} else $id = 0;
	}

	$areas = [
		[
			'value' => '',
			'label' => '-'
		]
	];
	$query = "SELECT area.id AS area_id,city.name AS city_name,area.name AS area_name FROM city,area WHERE city_id=city.id ORDER BY city.name,area.name";
	$result = db_query($con, $query);

	// SELECT count(*) FROM orders,user,states_per_order
	// WHERE user.id =620 and orders.user_id=620 and orders.id=order_id AND order_state_id=4
	// AND states_per_order.created_at=(SELECT MAX(created_at) FROM states_per_order WHERE order_id=orders.id)
	// ;

	$query2 = "SELECT count(*) as orders_count FROM orders,user,states_per_order
	WHERE user.id =${id} and orders.user_id=${id} and orders.id=order_id AND order_state_id=4
	AND states_per_order.created_at=(SELECT MAX(created_at) FROM states_per_order WHERE order_id=orders.id);  ";
	$result2 = db_query($con, $query2);
	$row2 = db_fetch_assoc($result2);

	$query3 = "SELECT SUM(total_price) 
	AS total_revenue 
	FROM orders,user,states_per_order
	WHERE user.id =${id} and orders.user_id=${id} and orders.id=order_id AND order_state_id=4
	AND states_per_order.created_at=(SELECT MAX(created_at) FROM states_per_order WHERE order_id=orders.id);  ";



	$result3 = db_query($con, $query3);
	$row3 = db_fetch_assoc($result3);
	$row3['total_revenue'] = format_money($row3['total_revenue']) . " " . CURRENCY_CODE;



	if ($result && db_num_rows($result)) {
		while ($row = db_fetch_assoc($result)) {
			$areas[$row['city_name']][] = [
				'value' => $row['area_id'],
				'label' => $row['area_name']
			];
		}

		db_free_result($result);
	}

	close_db_con($con);

	$fields = [

		[
			'label' => 'Total revenu',
			'tag' => 'input',
			'attributes' => [
				'id' => 'total_revnue',
				'name' => 'total_revnue',
				'type' => 'text',
				'value' =>   $row3["total_revenue"],
				'maxlength' => 20,
				'required' => false,
				'disabled' => true,

			]
		],
		[
			'label' => 'Orders Count',
			'tag' => 'input',
			'attributes' => [
				'id' => 'orders_count',
				'name' => 'orders_count',
				'type' => 'text',
				'value' => $row2["orders_count"],
				'maxlength' => 20,
				'required' => false,
				'disabled' => true

			]
		],
		[
			'label' => 'First name',
			'tag' => 'input',
			'attributes' => [
				'id' => 'first_name',
				'name' => 'first_name',
				'type' => 'text',
				'value' => $first_name,
				'maxlength' => 20,
				'required' => true,
				'autofocus' => true
			]
		],
		[
			'label' => 'Last name',
			'tag' => 'input',
			'attributes' => [
				'id' => 'last_name',
				'name' => 'last_name',
				'type' => 'text',
				'value' => $last_name,
				'maxlength' => 20,
				'required' => true
			]
		],
		[
			'label' => 'E-mail (must be unique)',
			'tag' => 'input',
			'attributes' => [
				'id' => 'email',
				'name' => 'email',
				'type' => 'email',
				'value' => $email,
				'maxlength' => 50,
				'required' => true
			]
		],
		[
			'label' => 'Phone',
			'tag' => 'input',
			'attributes' => [
				'id' => 'phone',
				'name' => 'phone',
				'type' => 'text',
				'value' => $phone,
				'maxlength' => 20,
				'required' => true
			]
		],
		[
			'label' => 'Country',
			'tag' => 'input',
			'attributes' => [
				'id' => 'country',
				'name' => 'country',
				'type' => 'text',
				'value' => $country,
				'maxlength' => 50
			]
		],
		[
			'label' => 'Area',
			'tag' => 'select',
			'options' => $areas,
			'selected' => $area_id,
			'attributes' => [
				'id' => 'area_id',
				'name' => 'area_id',
				'required' => true
			]
		],
		[
			'label' => 'Street',
			'tag' => 'input',
			'attributes' => [
				'id' => 'street',
				'name' => 'street',
				'type' => 'text',
				'value' => $street,
				'maxlength' => 50,
				'required' => true
			]
		],
		[
			'label' => 'House number',
			'tag' => 'input',
			'attributes' => [
				'id' => 'house_number',
				'name' => 'house_number',
				'type' => 'text',
				'value' => $house_number,
				'maxlength' => 20,
				'required' => true
			]
		],
		[
			'label' => 'Password (min. ' . MIN_PASS_LEN . ' characters)',
			'tag' => 'input',
			'attributes' => [
				'id' => 'password',
				'name' => 'password',
				'type' => 'text',
				'value' => $password,
				'pattern' => '.{' . MIN_PASS_LEN . ',}',
				'required' => $pass_required
			]
		],
		[
			'label' => 'Active',
			'tag' => 'checkbox',
			'side_label' => 'Yes',
			'attributes' => [
				'id' => 'active',
				'name' => 'active',
				'class' => '',
				'type' => 'checkbox',
				'value' => 1,
				'checked' => $active
			]
		]
	];

?>
	<div class="content">
		<?php

		$actions = [
			[
				'type' => 'remove',
				'function' => 'remove_user'
			]
		];

		if (!$id) {
			$mode = 'ADD';
			$actions = null;
		}

		add_close_button($mode . ' USER', $actions);

		?>
		<form class="form-horizontal push-10-t form-notify" action="manage" method="post" autocomplete="off">
			<div class="col-sm-12">
				<div class="block">
					<div class="block-content block-content-narrow">
						<?php

						generate_form_fields($fields);
						add_submit_buttons($form_submit);

						?>
					</div>
				</div>
			</div>
			<input type="hidden" value="<?php echo $id; ?>" name="id" />
		</form>

		<input type="hidden" value="<?php echo $id; ?>" name="values[]" form="options_form" />
	</div>
<?php
}

function list_sodic_orders($get = [], $vars = [])
{
?>

	<div class="content">


	<form action="<?=CMS_BASE?>orders_export"  method="GET" >  
	<div class="row">
		<div class="content">
			<div class="row">
				<div class="col-sm-6">
					<h1 class="page-heading"> ORDERS  </h1>
				</div>
				<div align="right" class="col-sm-6">
							<button class="btn btn-success push-5" type="submit"><i class="fa "></i> EXPORT</button>
					<?php 
					$keys ="";
					foreach ($_GET as $key => $value) {
						$keys.= " ".$key." ".$value;
					} ?>
							<input type="hidden" name="query_infos" value="SODIC<?=$keys?>">
						</div>
			</div>
		</div>
	</div>
	</form>


		<?php

		add_add_button('orders', false);

		$order_state_id = $area_id = '%';

		$search_start_month=$search_month = date('Y-m');

		$search_start_day=$search_day = date('d');
		
		$start_day = 1;

		$area_id = "%";

		if (isset($vars['order_state_id'])) $order_state_id = $vars['order_state_id'];
		if (isset($vars['area_id'])) $area_id = $vars['area_id'];
		if (isset($vars['search_month'])) $search_month = $vars['search_month'];
		if (isset($vars['search_day'])) $search_day = $vars['search_day'];
		
		if (isset($vars['search_start_month'])) $search_start_month = $vars['search_start_month'];
		if (isset($vars['start_day'])) $start_day = $vars['start_day'];
		if (isset($vars['project'])) $project = $vars['project'];

		
		$search_date = $search_month . '-' . $search_day;

		$search_start_date= $search_start_month . '-' . $search_start_day;

		

		$order_states = $projects = $payment_methods = $areas = [
			[
				'value' => '%',
				'label' => 'All'
			]
		];



		$con = get_db_con();

		$query = "SELECT id,name FROM order_states ORDER BY state_num";
		$result = db_query($con, $query);

		if ($result && db_num_rows($result)) {
			while ($row = db_fetch_assoc($result)) {
				$order_states[] = [
					'value' => $row['id'],
					'label' => $row['name']
				];
			}

			db_free_result($result);
		}

		$query = "SELECT name FROM area where id =${area_id}";
		$result = db_query($con, $query);
		if ($result && db_num_rows($result))
			$row = mysqli_fetch_assoc($result);
		$project_area_name = $row['name'];

		$query = "SELECT 
		projects.id as id , projects.name as name,
		area.name as area_name , 
		projects.name 
		FROM mrkt_db.projects  
		JOIN area ON area.id = projects.area_id 
		WHERE area.id  LIKE '${area_id}' ";
				$result = db_query($con, $query);

		if ($result && db_num_rows($result)) {
			while ($row = db_fetch_assoc($result)) {
					if ( $row['area_name'] ==="New-Cairo" ) $row["area_name"]= str_replace('-','',$row['area_name']);
				$projects[$row["area_name"]][] = [
					'value' => $row['id'],
					'label' => $row['name']
				];
			}
			db_free_result($result);
		}



		$query = "SELECT area.id AS area_id,city.name AS city_name,area.name AS area_name FROM city,area where city.id=3 AND city_id=3";
		$result = db_query($con, $query);

		if ($result && db_num_rows($result)) {
			while ($row = db_fetch_assoc($result)) {
				$areas[$row['city_name']][] = [
					'value' => $row['area_id'],
					'label' => str_replace('-'," ",$row['area_name'])
				];
			}

			db_free_result($result);
		}

		$fields_project = [
			[
				'label' => 'Projects',
				'tag' => 'select',
				'expected' => false,
				'options' => $projects,
				// 'selected' => $area_id,
				'attributes' => [
					'id' => 'project',
					'name' => 'project',
					// 'required' => true,
					'onclick' => 'this.form.submit();'
				]
			]
		];
		
		$fields_area = [
			[
				'label' => 'Area',
				'tag' => 'select',
				'expected' => false,
				'options' => $areas,
				'selected' => $area_id,
				'attributes' => [
					'id' => 'area_id',
					'name' => 'area_id',
					'required' => true,
					'onchange' => 'this.form.submit();',
				]
			]
		];



		$fields_order_states = [
			[
				'label' => 'Order state',
				'tag' => 'select',
				'expected' => false,
				'options' => $order_states,
				'selected' => $order_state_id,
				'attributes' => [
					'id' => 'order_state_id',
					'name' => 'order_state_id',
					'required' => true,
					'onchange' => 'this.form.submit();'
				]
			]
		];

		$start_days =[];
		for($i=1; $i<=31; ){
			$start_days [] =[
					'value' => $i,
					'label' => $i++
				];	
		}

		$fields_start_day = [
			[
				'label' => 'Starting Day',
				'tag' => 'select',
				'expected' => false,
				'options' => $start_days,
				'selected' => $start_day,
				'attributes' => [
					'id' => 'start_day',
					'name' => 'start_day',
					'onchange' => 'this.form.submit();'
				]
			]
		];
		
			?>
		<div class="block">
			<div class="block-content">
				<form class="form-horizontal push-10-t" method="get">
					<div class="row">

						<div class="col-sm-3">
							<?php generate_form_fields($fields_area); ?>

						</div>

						<div class="col-sm-3">
							<?php generate_form_fields($fields_order_states); ?>
						</div>

						<div class="col-sm-3">
							<?php generate_form_fields($fields_project); ?>
						</div>


						<div class="col-sm-3">
							<?php generate_form_fields($fields_start_day); ?>
						</div>




						<div class="col-sm-3">
							<div class="form-group">
								<div class="col-sm-12">
									<div class="form-material form-material-primary">
										<select class="form-control" id="search_start_month" name="search_start_month" onchange="this.form.submit();">
											<?php

											$curr_year = date('Y');
											$curr_month = date('n');
											$max_date = date('Y-m-d');

											$query = "SELECT MIN(created_at) AS min_date,MAX(created_at) AS max_date FROM orders";
											$result = db_query($con, $query);

											if ($result && db_num_rows($result) == 1) {
												$row = db_fetch_assoc($result);
												db_free_result($result);

												if ($row['min_date']) {
													$curr_year = date('Y', strtotime($row['min_date']));
													$curr_month = date('n', strtotime($row['min_date']));
													$min_date = $row['min_date'];
													$max_date = $row['max_date'];
												}
											}

											$min_date = strtotime($min_date);
											$max_date = strtotime($max_date);
											$group_flag = true;

											if (strtotime($search_date) > $max_date) $search_date = date('Y-m-d', $max_date);
											else if (strtotime($search_date) < $min_date) $search_date = date('Y-m-d', $min_date);

											$search_year = date('Y', strtotime($search_date));
											$search_month = date('n', strtotime($search_date));
											
											 $start_month_no_zero =date('n', strtotime($search_start_month));
											 $start_year =date('Y', strtotime($search_start_month));
											
										

											while (strtotime($curr_year . '-' . $curr_month . '-01') <= $max_date) {
												if ($curr_year == $start_year && $curr_month == $start_month_no_zero) $selected = 'selected';
												else $selected = '';

												if ($curr_month < 10) $zero = '0';
												else $zero = '';

												if ($group_flag) {
													echo '<optgroup label="' . $curr_year . '.">';
													$group_flag = false;
												}
											

												echo '<option value="' . $curr_year . '-' . $zero . $curr_month . '" ' . $selected . '>' . $zero . $curr_month . '.' . $curr_year . '.</option>';

												if ($curr_month == 12) {
													echo '</optgroup>';
													$group_flag = true;

													$curr_year++;
													$curr_month = 1;
												} else $curr_month++;
											}

											if (!$group_flag) echo '</optgroup>';

											?>
										</select>
										<label for="search_start_date">Starting Month</label>
									</div>
								</div>
							</div>
						</div>







						<div class="col-sm-3">
							<div class="form-group">
								<div class="col-sm-12">
									<div class="form-material form-material-primary">
										<select class="form-control" id="search_day" name="search_day" onchange="this.form.submit();">
											<?php

											for ($i = 1; $i <= 31; $i++) {
												if (intval($search_day) == $i) $selected = 'selected';
												else $selected = '';

												echo '<option value="' . $i . '" ' . $selected . '>' . $i . '</option>';
											}

											?>
										</select>
										<label for="search_day">Ending Day</label>
									</div>
								</div>
							</div>
						</div>






						<div class="col-sm-3">
							<div class="form-group">
								<div class="col-sm-12">
									<div class="form-material form-material-primary">
										<select class="form-control" id="search_month" name="search_month" onchange="this.form.submit();">
											<?php

											$curr_year = date('Y');
											$curr_month = date('n');
											$max_date = date('Y-m-d');

											$query = "SELECT MIN(created_at) AS min_date,MAX(created_at) AS max_date FROM orders";
											$result = db_query($con, $query);

											if ($result && db_num_rows($result) == 1) {
												$row = db_fetch_assoc($result);
												db_free_result($result);

												if ($row['min_date']) {
													$curr_year = date('Y', strtotime($row['min_date']));
													$curr_month = date('n', strtotime($row['min_date']));
													$min_date = $row['min_date'];
													$max_date = $row['max_date'];
												}
											}
										$search_start_month; 
											$min_date = strtotime($min_date);
											$max_date = strtotime($max_date);
											$group_flag = true;
											
											if (strtotime($search_date) > $max_date) $search_date = date('Y-m-d', $max_date);
											else if (strtotime($search_date) < $min_date) $search_date = date('Y-m-d', $min_date);

											$search_year = date('Y', strtotime($search_date));
											$search_month = date('n', strtotime($search_date));

											while (strtotime($curr_year . '-' . $curr_month . '-01') <= $max_date) {
												if ($curr_year == $search_year && $curr_month == $search_month) $selected = 'selected';
												else $selected = '';

												if ($curr_month < 10) $zero = '0';
												else $zero = '';

												if ($group_flag) {
													echo '<optgroup label="' . $curr_year . '.">';
													$group_flag = false;
												}

												echo '<option value="' . $curr_year . '-' . $zero . $curr_month . '" ' . $selected . '>' . $zero . $curr_month . '.' . $curr_year . '.</option>';

												if ($curr_month == 12) {
													echo '</optgroup>';
													$group_flag = true;

													$curr_year++;
													$curr_month = 1;
												} else $curr_month++;
											}

											if (!$group_flag) echo '</optgroup>';

											?>
										</select>
										<label for="search_date">Ending Month</label>

									</div>
								</div>
							</div>
						</div>


					
						
					</div>
				</form>
				<?php

				$max_date = $search_date;
				$search_date .= ' 00:00:00';
				$max_date .= ' 23:59:59';
				$date_of_filter= $search_start_month. '-'. $start_day ." 00:00:00";
				// $$date_of_filter = date('Y-m-01 00:00:00');

				if(! empty ($vars['project'])) $project = $vars['project']; else $project= '%'; 	
				if(! empty ($vars['order_state_id'])) $order_state_id = $vars['order_state_id']; else $order_state_id= '%'; 	
		
				// $query =
				// "SELECT user_id, orders.id AS order_id,delivery_staff_id,total_price,delivery_time,payment_method,is_paid,orders.created_at AS order_timestamp,
				// order_states.name AS order_state_name,color,projects.name as project_name,
				// city.name AS city_name,area.name AS area_name,area.id AS area_id 
				// FROM orders,user,order_states,states_per_order,city,area, projects,orders_projects op
				// WHERE city_id=city.id 
				// AND order_states.id=order_state_id 
				// AND op.project_id = projects.id 
				// AND projects.area_id = area.id
				// AND op.order_id = orders.id 
				// AND order_states.id LIKE '" . $order_state_id . "' 
				// AND orders.id=states_per_order.order_id 
				// AND user.id=user_id 
				// AND orders.created_at BETWEEN  '" . $date_of_filter . "' 
				// AND '" . $max_date . "' 
				// AND orders.area_id =area.id 
				// AND area.id LIKE '" . $area_id . "' 
				// AND op.project_id LIKE '${project}'
				// AND states_per_order.created_at=(SELECT MAX(created_at) 
				// FROM states_per_order 
				// WHERE states_per_order.order_id=orders.id) 
				// ORDER BY orders.created_at DESC";


			$query= "SELECT user_id,
					orders.id AS order_id,
					delivery_staff_id,
					total_price,
					delivery_time,
					payment_method,
					is_paid,
					orders.created_at AS order_timestamp,
					order_states.name AS order_state_name,
					color,
					projects.name as project_name,
					city.name AS city_name,
					area.name AS area_name,area.id AS area_id 
					FROM orders,
						user,
						order_states,
						states_per_order,
						projects,
						city,
						area
					WHERE
							orders.user_id = user.id
					AND     orders.id  = states_per_order.order_id
					AND     states_per_order.order_state_id = order_states.id
					AND     orders.area_id = area.id
					AND     orders.project_id = projects.id
					AND     city.id = area.city_id
					AND     area.id in(select id from area where city_id=3)
					AND     states_per_order.created_at= (SELECT MAX(created_at) from states_per_order WHERE orders.id = order_id)
					AND     projects.id LIKE '${project}'
					AND     orders.created_at BETWEEN  '" . $date_of_filter . "' AND '" . $max_date . "' 
					AND     area.id LIKE '" . $area_id . "' 
				    AND     order_states.id LIKE '" . $order_state_id . "' 

					order by orders.created_at desc;";
				$result = db_query($con, $query);

				if ($result && db_num_rows($result)) {

					$actions = [
						[
							'type' => 'change_order_state',
							'function' => 'change_order_state'
						],
						[
							'type' => 'refund_order',
							'function' => 'refund_order'
						],
						[
							'type' => 'remove',
							'function' => 'remove_order'
						]
					];
					$role_functions = get_role_functions();
					add_action_buttons($actions, $role_functions);
				?>
					<table width="100%" class="table table-bordered table-striped table-hover js-dataTable-full-pagination">
						<thead>
							<tr>
								<th>Order ID</th>
								<th>Coupon</th>
								<th>Created at</th>
								<th>Delivery time</th>
								<th>Order state</th>
								<th>Payment method</th>
								<th>Is paid</th>
								<th>Area</th>
								<th>Receiver</th> 
								<th>Project</th>
								<th>Total price</th>
								<th>Delivery staff</th>
								<th><?php options_column_header(); ?></th>
							</tr>
						</thead>
						<tbody align="center">
							<?php

							while ($row = db_fetch_assoc($result)) {

								$created_at = format_date($row['order_timestamp']);

								$delivery_time = format_date($row['delivery_time']);

								$cou_or_id = $row['order_id'];

								$get_offers = "SELECT coupon_id FROM coupuser WHERE order_id=$cou_or_id";

								$co_orders = db_query($con, $get_offers);

								$cou_orders = db_fetch_assoc($co_orders);

								$id_id = $cou_orders['coupon_id'];

								$get_co = "SELECT  present FROM coupons WHERE id=$id_id";

								$co_get = db_query($con, $get_co);

								if (!empty($co_get)) {
									$coupon_get = db_fetch_assoc($co_get);
									$present = (float) $coupon_get['present'];
								} else $present = 0;



								$total_price = format_money($row['total_price']) . ' ' . CURRENCY_CODE;

								if ($row['is_paid']) $is_paid = 'Yes';
								else $is_paid = 'No';

								$delivery_staff_id = $row['delivery_staff_id'];
								$delivery_staff = '-';

								$query = "SELECT CONCAT(first_name, ' ' ,last_name) as 'full_name' FROM user WHERE id='" . $row['user_id'] . "'";
								  $result1 = db_query($con, $query);
								$user = mysqli_fetch_assoc($result1)["full_name"];

								if ($delivery_staff_id) {
									$query = "SELECT full_name FROM delivery_staff WHERE id='" . $delivery_staff_id . "'";
									$result1 = db_query($con, $query);

									if ($result1 && db_num_rows($result1) == 1) {
										$row1 = db_fetch_assoc($result1);
										db_free_result($result1);

										$delivery_staff = '<a href="admin/delivery-staff/add/' . $delivery_staff_id . '" class="link-effect">' . $row1['full_name'] . '</a>';
									}
								}
							?>
								<tr>
									<td><?php echo $row["order_id"]; ?></td>
									<td><?php echo $present . " " . CURRENCY_CODE; ?></td>
									<td><?php echo $created_at; ?></td>
									<td><?php echo $delivery_time; ?></td>
									<td><?php echo style_order_state($row['order_state_name'], $row['color']); ?></td>
									<td><?php echo $row['payment_method']; ?></td>
									<td><?php echo $is_paid; ?></td>
									<td><a href="admin/cities/areas/add/<?php echo $row['area_id']; ?>" class="link-effect"><?php echo $row['city_name']; ?> - <?php echo $row['area_name']; ?></a></td>
									<td><?=  $user ?? "user" ?></td>
									<td><?php echo $row['project_name']; ?></td>
									<td><?php echo $total_price; ?></td>
									<td><?php echo $delivery_staff; ?></td>
									<td><?php add_options_button('orders/view', $row['order_id'], $role_functions, ['label' => 'View', 'icon' => 'fa fa-info']); ?></td>
								</tr>
							<?php
							}
							db_free_result($result);
							?>
						</tbody>
					</table>
				<?php
				} else no_data_message();

				close_db_con($con);
				?>
			</div>
		</div>
	</div>
<?php

	change_order_state_modal();
}


function list_user($get = [], $vars = [])
{
	$edit_tag = 'users/add';

?>
	<div class="content">
		<?php

		add_add_button($edit_tag);
		add_post_export("/post_users_exporter");
		$area_id = '%';

		if (isset($vars['area_id'])) $area_id = $vars['area_id'];

		$con = get_db_con();
		$query = "SELECT area.id AS area_id,city.name AS city_name,area.name AS area_name FROM city,area WHERE city_id=city.id ORDER BY city.name,area.name";
		$result = db_query($con, $query);

		$areas = [
			[
				'value' => '%',
				'label' => 'All'
			]
		];

		if ($result && db_num_rows($result)) {
			while ($row = db_fetch_assoc($result)) {
				$areas[$row['city_name']][] = [
					'value' => $row['area_id'],
					'label' => $row['area_name']
				];
			}

			db_free_result($result);
		}

		$fields = [
			[
				'label' => 'Area',
				'tag' => 'select',
				'expected' => false,
				'options' => $areas,
				'selected' => $area_id,
				'attributes' => [
					'id' => 'area_id',
					'name' => 'area_id',
					'required' => true,
					'onchange' => 'this.form.submit();'
				]
			]
		];

		?>
		<div class="block">
			<div class="block-content">
				<form class="form-horizontal push-10-t" method="get">
					<div class="row">
						<div class="col-sm-6">
							<?php generate_form_fields($fields); ?>
						</div>
					</div>
				</form>
				<?php

				$query = "SELECT user.id AS user_id,first_name,last_name,email,phone,created_at,active,area_id,area.name AS area_name,
				city.name AS city_name
				
				
				
				FROM user,city,area WHERE city_id=city.id AND area_id=area.id AND area_id LIKE '" . $area_id . "' ORDER BY first_name,last_name";
				$result = db_query($con, $query);

				// (SELECT COUNT(*) FROM states_per_order,orders WHERE orders.id=order_id AND order_state_id=(SELECT id FROM order_states WHERE state_num=(SELECT MAX(state_num) FROM order_states)) AND user_id=user.id AND 
				// states_per_order.created_at=(SELECT MAX(created_at) FROM states_per_order WHERE order_id=orders.id)) AS orders_count,


				// (SELECT SUM(total_price) FROM states_per_order,orders WHERE orders.id=order_id AND order_state_id=(SELECT id FROM order_states WHERE state_num=(SELECT MAX(state_num) FROM order_states)) AND user_id=user.id AND 
				// states_per_order.created_at=(SELECT MAX(created_at) FROM states_per_order WHERE order_id=orders.id)) AS total_price_sum 

				if ($result && db_num_rows($result)) {
					$actions = [
						[
							'type' => 'remove',
							'function' => 'remove_user'
						],
						[
							'type' => 'activate',
							'function' => 'activate_user'
						],
						[
							'type' => 'deactivate',
							'function' => 'deactivate_user'
						],
					];

					$role_functions = get_role_functions();
					add_action_buttons($actions, $role_functions);

				?>
					<table width="100%" class="table table-bordered table-striped table-hover js-dataTable-full-pagination">
						<thead>
							<tr>
								<th>Name</th>
								<th>E-mail</th>
								<th>Phone</th>
								<th>Area</th>
								<th>User since</th>
								<!-- <th>Orders</th> -->
								<!-- <th>Total revenue</th> -->
								<th>Active</th>
								<th><?php options_column_header(); ?></th>
							</tr>
						</thead>
						<tbody align="center">
							<?php

							while ($row = db_fetch_assoc($result)) {
								if ($row['active']) $active = 'Yes';
								else $active = 'No';

								// $total_price_sum = format_money($row['total_price_sum']);
								$timestamp = format_date($row['created_at'], DATE_FORMAT);

							?>
								<tr>
									<td><?php echo $row['first_name']; ?> <?php echo $row['last_name']; ?></td>
									<td><a href="mailto:<?php echo $row['email']; ?>" class="link-effect"><?php echo $row['email']; ?></a></td>
									<td><?php echo $row['phone']; ?></td>
									<td><a href="admin/cities/areas/add/<?php echo $row['area_id']; ?>" class="link-effect"><?php echo $row['city_name']; ?> - <?php echo $row['area_name']; ?></a></td>
									<td><?php echo $timestamp; ?></td>
									<!-- <td><?php // echo $row['orders_count']; 
												?></td> -->
									<!-- <td><?php // echo $total_price_sum . ' ' . CURRENCY_CODE; 
												?></td> -->
									<td><?php echo $active; ?></td>
									<td><?php add_options_button($edit_tag, $row['user_id'], $role_functions); ?></td>
								</tr>
							<?php
							}

							db_free_result($result);

							?>
						</tbody>
					</table>
				<?php
				} else no_data_message();

				close_db_con($con);

				?>
			</div>
		</div>
	</div>
<?php
}

function add_category($get = [], $vars = [])
{
	$form_submit = __FUNCTION__;

	$mode = 'UPDATE';
	$id = $order_num = 0;
	$name = $featured_image = '';
	$featured = false;

	if (isset($get['add_id'])) {
		$id = $get['add_id'];

		$con = get_db_con();
		$query = "SELECT * FROM category WHERE id='" . $id . "'";
		$result = db_query($con, $query);

		if ($result && db_num_rows($result) == 1) {
			$row = db_fetch_assoc($result);
			db_free_result($result);

			$name = $row['name'];
			$order_num = $row['order_num'];
			$featured = boolval($row['is_featured']);
			$featured_image = $row['featured_image'];
		} else $id = 0;

		close_db_con($con);
	}

	$fields_basic = [
		[
			'label' => 'Name',
			'tag' => 'input',
			'attributes' => [
				'id' => 'name',
				'name' => 'name',
				'type' => 'text',
				'value' => $name,
				'maxlength' => 50,
				'required' => true,
				'autofocus' => true
			]
		],
		[
			'label' => 'Order',
			'tag' => 'input',
			'attributes' => [
				'id' => 'order_num',
				'name' => 'order_num',
				'type' => 'number',
				'value' => $order_num,
				'min' => 0,
				'required' => true
			]
		],
		[
			'label' => 'Featured',
			'tag' => 'checkbox',
			'side_label' => 'Yes',
			'attributes' => [
				'id' => 'is_featured',
				'name' => 'is_featured',
				'class' => '',
				'type' => 'checkbox',
				'value' => 1,
				'checked' => $featured
			]
		]
	];

?>
	<div class="content">
		<?php

		$actions = [
			[
				'type' => 'remove',
				'function' => 'remove_category'
			]
		];

		if (!$id) {
			$mode = 'ADD';
			$actions = null;
		}

		add_close_button($mode . ' CATEGORY', $actions);

		?>
		<form class="form-horizontal push-10-t form-notify" action="manage" method="post" autocomplete="off" enctype="multipart/form-data">
			<div class="col-sm-12">
				<div class="block">
					<div class="block-content block-content-narrow">
						<ul class="nav nav-tabs nav-tabs-alt" data-toggle="tabs">
							<li class="active">
								<a href="#btabs-alt-static-basic">Basic</a>
							</li>
							<li>
								<a href="#btabs-alt-static-media">Image</a>
							</li>
						</ul>
						<div class="block-content tab-content">
							<div class="tab-pane active" id="btabs-alt-static-basic">
								<?php generate_form_fields($fields_basic); ?>
							</div>
							<div class="tab-pane" id="btabs-alt-static-media">
								<?php

								generate_file_upload(false);
								generate_image_galery($id, CATEGORY_UPLOAD_DIR, $featured_image);

								?>
							</div>
							<?php add_submit_buttons($form_submit); ?>
						</div>
					</div>
				</div>
			</div>
			<input type="hidden" value="<?php echo $id; ?>" name="id" />
		</form>

		<input type="hidden" value="<?php echo $id; ?>" name="values[]" form="options_form" />
	</div>
<?php
}

function list_category($get = [], $vars = [])
{
	$edit_tag = 'categories/add';

?>
	<div class="content">
		<?php add_add_button($edit_tag); ?>
		<div class="block">
			<div class="block-content">
				<?php

				$con = get_db_con();
				$query = "SELECT id,name,order_num,is_featured,(SELECT COUNT(*) FROM product WHERE product.category_id=category.id) AS product_count FROM category ORDER BY order_num,name";
				$result = db_query($con, $query);

				if ($result && db_num_rows($result)) {
					$actions = [
						[
							'type' => 'remove',
							'function' => 'remove_category'
						]
					];

					$role_functions = get_role_functions();
					add_action_buttons($actions, $role_functions);

				?>
					<table width="100%" class="table table-bordered table-striped table-hover js-dataTable-full-pagination">
						<thead>
							<tr>
								<th>id</th>
								<th>Order</th>
								<th>Name</th>
								<th>Featured</th>
								<th>Products</th>
								<th><?php options_column_header(); ?></th>
							</tr>
						</thead>
						<tbody align="center">
							<?php

							while ($row = db_fetch_assoc($result)) {
								if ($row['is_featured']) $featured = 'Yes';
								else $featured = 'No';

							?>
								<tr>
									<td><?php echo $row['id']; ?></td>
									<td><?php echo $row['order_num']; ?></td>
									<td><?php echo $row['name']; ?></td>
									<td><?php echo $featured; ?></td>
									<td><?php echo $row['product_count']; ?></td>
									<td><?php add_options_button($edit_tag, $row['id'], $role_functions); ?></td>
								</tr>
							<?php
							}

							db_free_result($result);

							?>
						</tbody>
					</table>
				<?php
				} else no_data_message();

				close_db_con($con);

				?>
			</div>
		</div>
	</div>
<?php
}

function add_order_state($get = [], $vars = [])
{
	$form_submit = __FUNCTION__;

	$mode = 'UPDATE';
	$id = 0;
	$name = $state_num = $color = '';

	if (isset($get['add_id'])) {
		$id = $get['add_id'];

		$con = get_db_con();
		$query = "SELECT * FROM order_states WHERE id='" . $id . "'";
		$result = db_query($con, $query);

		if ($result && db_num_rows($result) == 1) {
			$row = db_fetch_assoc($result);
			db_free_result($result);

			$name = $row['name'];
			$state_num = $row['state_num'];
			$color = $row['color'];
		} else $id = 0;

		close_db_con($con);
	}

	$fields = [
		[
			'label' => 'Number (must be unique)',
			'tag' => 'input',
			'attributes' => [
				'id' => 'state_num',
				'name' => 'state_num',
				'type' => 'number',
				'value' => $state_num,
				'min' => 0,
				'required' => true,
				'autofocus' => true
			]
		],
		[
			'label' => 'Name',
			'tag' => 'input',
			'attributes' => [
				'id' => 'name',
				'name' => 'name',
				'type' => 'text',
				'value' => $name,
				'maxlength' => 50,
				'required' => true
			]
		],
		[
			'label' => 'Color',
			'tag' => 'input',
			'attributes' => [
				'id' => 'color',
				'name' => 'color',
				'type' => 'color',
				'value' => $color,
				'required' => true
			]
		]
	];

?>
	<div class="content">
		<?php

		$actions = [
			[
				'type' => 'remove',
				'function' => 'remove_order_state'
			]
		];

		if (!$id) {
			$mode = 'ADD';
			$actions = null;
		}

		add_close_button($mode . ' ORDER STATE', $actions);

		?>
		<form class="form-horizontal push-10-t form-notify" action="manage" method="post" autocomplete="off">
			<div class="col-sm-12">
				<div class="block">
					<div class="block-content block-content-narrow">
						<?php

						generate_form_fields($fields);
						add_submit_buttons($form_submit);

						?>
					</div>
				</div>
			</div>
			<input type="hidden" value="<?php echo $id; ?>" name="id" />
		</form>

		<input type="hidden" value="<?php echo $id; ?>" name="values[]" form="options_form" />
	</div>
<?php
}


function list_order_state($get = [], $vars = [])
{
	$edit_tag = 'orders/states/add';

?>
	<div class="content">
		<?php add_add_button($edit_tag); ?>
		<div class="block">
			<div class="block-content">
				<?php

				$con = get_db_con();
				$query = "SELECT * FROM order_states ORDER BY state_num";
				$result = db_query($con, $query);

				if ($result && db_num_rows($result)) {
					$actions = [
						[
							'type' => 'remove',
							'function' => 'remove_order_state'
						]
					];

					$role_functions = get_role_functions();
					add_action_buttons($actions, $role_functions);

				?>
					<table width="100%" class="table table-bordered table-striped table-hover js-dataTable-full-pagination">
						<thead>
							<tr>
								<th>Number</th>
								<th>Name</th>
								<th><?php options_column_header(); ?></th>
							</tr>
						</thead>
						<tbody align="center">
							<?php

							while ($row = db_fetch_assoc($result)) {
							?>
								<tr>
									<td><?php echo $row['state_num']; ?></td>
									<td><?php echo style_order_state($row['name'], $row['color']); ?></td>
									<td><?php add_options_button($edit_tag, $row['id'], $role_functions); ?></td>
								</tr>
							<?php
							}

							db_free_result($result);

							?>
						</tbody>
					</table>
				<?php
				} else no_data_message();

				close_db_con($con);

				?>
			</div>
		</div>
	</div>
<?php
}


function list_sodic_users($get = [], $vars = [])
{

	// $edit_tag = 'users/add';

?>
	<div class="content">
	
	<form action="/users_export"  method="GET" >  
	<div class="row">
		<div class="content">
			<div class="row">
				<div class="col-sm-6">
					<h1 class="page-heading"> USERS <?php  ?></h1>
				</div>
				<div align="right" class="col-sm-6">
							<button class="btn btn-success push-5" type="submit"><i class="fa "></i> EXPORT</button>
							<input type="hidden" name="query_infos" value="SODIC <?= implode(' ',$_GET) ?>">
						</div>
			</div>
		</div>
	</div>
	</form>



<?php

		$areas = [
			[
				'value' => '%',
				'label' => 'All'
			]
		];


		$area_id= "%" ;

		if (isset($vars['area_id'])) $area_id = $vars['area_id'] ;

		
		$con = get_db_con();
		$query = "SELECT area.id AS area_id,city.name AS city_name,area.name 
		AS area_name FROM city JOIN area ON city_id=city.id WHERE city.id=3 ORDER BY city.name,area.name";
		$result = db_query($con, $query);


		if ($result && db_num_rows($result)) {
			while ($row = db_fetch_assoc($result)) {
				$areas[$row['city_name']][] = [
					'value' => $row['area_id'] ,
					'label' => $row['area_name']
				];

			}

			db_free_result($result);
		}


		$fields = [
			[
				'label' => 'Area',
				'tag' => 'select',
				'expected' => false,
				'options' => $areas,
				'selected' => $area_id,
				'attributes' => [
					'id' => 'area_id',
					'name' => 'area_id',
					'required' => true,
					'onchange' => 'this.form.submit();'
				]
			]
		];



		?>
		<div class="block">
			<div class="block-content">
				<form class="form-horizontal push-10-t" method="get">
					<div class="row">
						<div class="col-sm-6">
							<?php generate_form_fields($fields); ?>
						</div>
					</div>
				</form>
				<?php

				$query = 
				"SELECT user.id AS user_id,first_name, last_name,email,phone,created_at,active, area_id,area.name AS area_name, city.name AS city_name
				FROM user JOIN area ON area.id=user.area_id JOIN city ON area.city_id=city.id WHERE city.id=3 AND user.area_id like '${area_id}' ORDER BY area.name,first_name,last_name";
				$result = db_query($con, $query);

				if ($result && db_num_rows($result)) {
				?>
					<table width="100%" class="table table-bordered table-striped table-hover js-dataTable-full-pagination">
						<thead>
							<tr>
								<th>Name</th>
								<th>E-mail</th>
								<th>Phone</th>
								<th>Area</th>
								<th>User since</th>
								<!-- <th>Orders</th> -->
								<!-- <th>Total revenue</th> -->
								<th>Active</th>
								<!-- <th><?php // options_column_header();  ?></th> -->
							</tr>
						</thead>
						<tbody align="center">
							<?php

							while ($row = db_fetch_assoc($result)) {

								if ($row['active']) $active = 'Yes';

								else $active = 'No';

								// $total_price_sum = format_money($row['total_price_sum']);
								$timestamp = format_date($row['created_at'], DATE_FORMAT);

							?>
								<tr>
									<td><?php echo $row['first_name']; ?> <?php echo $row['last_name']; ?></td>
									<td><a href="mailto:<?php echo $row['email']; ?>" class="link-effect"><?php echo $row['email']; ?></a></td>
									<td><?php echo $row['phone']; ?></td>
									<td><a href="admin/cities/areas/add/<?php echo $row['area_id']; ?>" class="link-effect"><?php echo $row['city_name']; ?> - <?php echo $row['area_name']; ?></a></td>
									<td><?php echo $timestamp; ?></td>
									<!-- <td><?php // echo $row['orders_count']; 
												?></td> -->
									<!-- <td><?php // echo $total_price_sum . ' ' . CURRENCY_CODE; 
												?></td> -->
									<td><?php echo $active; ?></td>
									<!-- <td><?php // add_options_button($edit_tag, $row['user_id'], $role_functions); 
												?></td> -->
								</tr>
							<?php
							}

							db_free_result($result);

							?>
						</tbody>
					</table>
				<?php
				} else no_data_message();

				close_db_con($con);

				?>
			</div>
		</div>
	</div>

<?php

}

function list_order($get = [], $vars = [])
{
?>
	<div class="content">
		<?php

		add_add_button('orders', false);

		$order_state_id = $area_id = '%';
		$search_month = date('Y-m');
		$search_day = date('d');
		$search_date = $min_date = date('Y-m-01');

		if (isset($vars['order_state_id'])) $order_state_id = $vars['order_state_id'];
		if (isset($vars['area_id'])) $area_id = $vars['area_id'];
		if (isset($vars['search_month'])) $search_month = $vars['search_month'];
		if (isset($vars['search_day'])) $search_day = $vars['search_day'];

		$search_date = $search_month . '-' . $search_day;

		$order_states = $payment_methods = $areas = [
			[
				'value' => '%',
				'label' => 'All'
			]
		];

		$con = get_db_con();
		$query = "SELECT id,name FROM order_states ORDER BY state_num";
		$result = db_query($con, $query);

		if ($result && db_num_rows($result)) {
			while ($row = db_fetch_assoc($result)) {
				$order_states[] = [
					'value' => $row['id'],
					'label' => $row['name']
				];
			}

			db_free_result($result);
		}

		$query = "SELECT area.id AS area_id,city.name AS city_name,area.name AS area_name FROM city,area WHERE city_id=city.id ORDER BY city.name,area.name";
		$result = db_query($con, $query);

		if ($result && db_num_rows($result)) {
			while ($row = db_fetch_assoc($result)) {
				$areas[$row['city_name']][] = [
					'value' => $row['area_id'],
					'label' => $row['area_name']
				];
			}

			db_free_result($result);
		}

		$fields_area = [
			[
				'label' => 'Area',
				'tag' => 'select',
				'expected' => false,
				'options' => $areas,
				'selected' => $area_id,
				'attributes' => [
					'id' => 'area_id',
					'name' => 'area_id',
					'required' => true,
					'onchange' => 'this.form.submit();'
				]
			]
		];

		$fields_order_states = [
			[
				'label' => 'Order state',
				'tag' => 'select',
				'expected' => false,
				'options' => $order_states,
				'selected' => $order_state_id,
				'attributes' => [
					'id' => 'order_state_id',
					'name' => 'order_state_id',
					'required' => true,
					'onchange' => 'this.form.submit();'
				]
			]
		];

		?>

		
<div class="block">
            <div class="block-content">
            <div class="content">
        <label for="">Exporter Orders:</label>
        <br>
            <form action="manage" method="POST" > 
                <select name="mrkt_exporter" id="mrkt_exporter">
                    <option selected disabled>Select An Option</option>
                    <option value=1>Cuurnet Year</option>
                    <option value=2>Select Month</option>
                </select>
        <input type="month"  min="2019-01" max="2030-01" name="mrkt_exporter" id="export_month" style="visibility: hidden;">
        <button id=btn-ex>Export</button>

    </form>
        <script>
            let mrkt_exporter = document.getElementById("mrkt_exporter");
            let export_month = document.getElementById("export_month");
            
            mrkt_exporter.addEventListener('change',
            ()=> {
                let mrkt_exporter_value = mrkt_exporter.value;
                console.log(mrkt_exporter_value);
                if (mrkt_exporter_value == 2) {
                    export_month.style.visibility= "visible";
                    export_month.setAttribute("name", "mrkt_exporter")
                }else{
                    export_month.setAttribute("name", "foo")
                    var today = new Date();

                    mrkt_exporter.options[1].label = today.getFullYear();
                    mrkt_exporter.options[1].value = today.getFullYear();
                    export_month.style.visibility= "hidden";

                }
                
                });
        </script>
		<form class="form-horizontal push-10-t" method="get">
					<div class="row">
						<div class="col-sm-3">
							<?php generate_form_fields($fields_area); ?>
						</div>
						<div class="col-sm-3">
							<?php generate_form_fields($fields_order_states); ?>
						</div>
						<div class="col-sm-3">
							<div class="form-group">
								<div class="col-sm-12">
									<div class="form-material form-material-primary">
										<select class="form-control" id="search_month" name="search_month" onchange="this.form.submit();">
											<?php

											$curr_year = date('Y');
											$curr_month = date('n');
											$max_date = date('Y-m-d');

											$query = "SELECT MIN(created_at) AS min_date,MAX(created_at) AS max_date FROM orders";
											$result = db_query($con, $query);

											if ($result && db_num_rows($result) == 1) {
												$row = db_fetch_assoc($result);
												db_free_result($result);

												if ($row['min_date']) {
													$curr_year = date('Y', strtotime($row['min_date']));
													$curr_month = date('n', strtotime($row['min_date']));
													$min_date = $row['min_date'];
													$max_date = $row['max_date'];
												}
											}

											$min_date = strtotime($min_date);
											$max_date = strtotime($max_date);
											$group_flag = true;

											if (strtotime($search_date) > $max_date) $search_date = date('Y-m-d', $max_date);
											else if (strtotime($search_date) < $min_date) $search_date = date('Y-m-d', $min_date);

											$search_year = date('Y', strtotime($search_date));
											$search_month = date('n', strtotime($search_date));

											while (strtotime($curr_year . '-' . $curr_month . '-01') <= $max_date) {
												if ($curr_year == $search_year && $curr_month == $search_month) $selected = 'selected';
												else $selected = '';

												if ($curr_month < 10) $zero = '0';
												else $zero = '';

												if ($group_flag) {
													echo '<optgroup label="' . $curr_year . '.">';
													$group_flag = false;
												}

												echo '<option value="' . $curr_year . '-' . $zero . $curr_month . '" ' . $selected . '>' . $zero . $curr_month . '.' . $curr_year . '.</option>';

												if ($curr_month == 12) {
													echo '</optgroup>';
													$group_flag = true;

													$curr_year++;
													$curr_month = 1;
												} else $curr_month++;
											}

											if (!$group_flag) echo '</optgroup>';

											?>
										</select>
										<label for="search_date">Month</label>
									</div>
								</div>
							</div>
						</div>

						<div class="col-sm-3">
							<div class="form-group">
								<div class="col-sm-12">
									<div class="form-material form-material-primary">
										<select class="form-control" id="search_day" name="search_day" onchange="this.form.submit();">
											<?php

											for ($i = 1; $i <= 31; $i++) {
												if (intval($search_day) == $i) $selected = 'selected';
												else $selected = '';

												echo '<option value="' . $i . '" ' . $selected . '>' . $i . '</option>';
											}

											?>
										</select>
										<label for="search_day">Day</label>
									</div>
								</div>
							</div>
						</div>








					</div>
				</form>
				<?php

				$max_date = $search_date;
				$search_date .= ' 00:00:00';
				$max_date .= ' 23:59:59';

				$query =

			
				$query =
"SELECT orders.id AS order_id,delivery_staff_id,total_price,delivery_time,payment_method,is_paid,orders.created_at AS order_timestamp,
order_states.name AS order_state_name,color,city.name AS city_name,area.name AS area_name,area.id AS area_id 
FROM orders
JOIN user on user.id=orders.user_id 
JOIN states_per_order  on orders.id=states_per_order.order_id
JOIN area  on (CASE WHEN orders.area_id IS NULL THEN user.area_id ELSE orders.area_id END)=area.id 
JOIN city ON city_id=city.id 
JOIN order_states   on   order_states.id = states_per_order.order_state_id 
WHERE 
 orders.created_at BETWEEN '" . $search_date . "' 
AND '" . $max_date . "' 
AND order_states.id LIKE '" . $order_state_id . "' 
AND area.id LIKE '" . $area_id . "'
AND states_per_order.created_at=(SELECT MAX(created_at) 
FROM states_per_order 
WHERE order_id=orders.id) 
ORDER BY orders.created_at DESC";


/*
"SELECT orders.id AS order_id,delivery_staff_id,total_price,delivery_time,payment_method,is_paid,orders.created_at AS order_timestamp,
order_states.name AS order_state_name,color,
city.name AS city_name,area.name AS area_name,area.id AS area_id 
FROM orders,user,order_states,states_per_order,city,area
WHERE city_id=city.id 
AND order_states.id=order_state_id 
AND order_states.id LIKE '" . $order_state_id . "' 
AND orders.id=order_id 
AND user.id=user_id 
AND orders.created_at BETWEEN '" . $search_date . "' 
AND '" . $max_date . "' 
AND (CASE WHEN orders.area_id IS NULL THEN user.area_id ELSE orders.area_id END)=area.id 
AND area.id LIKE '" . $area_id . "' 
AND states_per_order.created_at=(SELECT MAX(created_at) 
FROM states_per_order 
WHERE order_id=orders.id) 
ORDER BY orders.created_at DESC";

*/
				$result = db_query($con, $query);

				if ($result && db_num_rows($result)) {

					$actions = [
						[
							'type' => 'change_order_state',
							'function' => 'change_order_state'
						],
						[
							'type' => 'refund_order',
							'function' => 'refund_order'
						],
						[
							'type' => 'remove',
							'function' => 'remove_order'
						]
					];
					$role_functions = get_role_functions();
					add_action_buttons($actions, $role_functions);
				?>
					<table width="100%" class="table table-bordered table-striped table-hover js-dataTable-full-pagination">
						<thead>
							<tr>
								<th>Order ID</th>
								<th>Coupon</th>
								<th>Created at</th>
								<th>Delivery time</th>
								<th>Order state</th>
								<th>Payment method</th>
								<th>Is paid</th>
								<th>Area</th>
								<th>Total price</th>
								<th>Delivery staff</th>
								<th><?php options_column_header(); ?></th>
							</tr>
						</thead>
						<tbody align="center">
							<?php

							while ($row = db_fetch_assoc($result)) {

								$created_at = format_date($row['order_timestamp']);

								$delivery_time = format_date($row['delivery_time']);

								$cou_or_id = $row['order_id'];

								$get_offers = "SELECT coupon_id FROM coupuser WHERE order_id=$cou_or_id";

								$co_orders = db_query($con, $get_offers);

								$cou_orders = db_fetch_assoc($co_orders);

								$id_id = $cou_orders['coupon_id'];

								$get_co = "SELECT  present FROM coupons WHERE id=$id_id";

								$co_get = db_query($con, $get_co);

								if (!empty($co_get)) {
									$coupon_get = db_fetch_assoc($co_get);
									$present = (float) $coupon_get['present'];
								} else $present = 0;



								if ($_SESSION['admin_view_all']) $total_price = format_money($row['total_price']) . ' ' . CURRENCY_CODE;
								else $total_price = '-';

								if ($row['is_paid']) $is_paid = 'Yes';
								else $is_paid = 'No';

								$delivery_staff_id = $row['delivery_staff_id'];
								$delivery_staff = '-';

								if ($delivery_staff_id) {
									$query = "SELECT full_name FROM delivery_staff WHERE id='" . $delivery_staff_id . "'";
									$result1 = db_query($con, $query);

									if ($result1 && db_num_rows($result1) == 1) {
										$row1 = db_fetch_assoc($result1);
										db_free_result($result1);

										$delivery_staff = '<a href="admin/delivery-staff/add/' . $delivery_staff_id . '" class="link-effect">' . $row1['full_name'] . '</a>';
									}
								}

							?>
								<tr>
									<td><?php echo $row["order_id"]; ?></td>
									<td><?php echo $present . " " . CURRENCY_CODE; ?></td>
									<td><?php echo $created_at; ?></td>
									<td><?php echo $delivery_time; ?></td>
									<td><?php echo style_order_state($row['order_state_name'], $row['color']); ?></td>
									<td><?php echo $row['payment_method']; ?></td>
									<td><?php echo $is_paid; ?></td>
									<td><a href="admin/cities/areas/add/<?php echo $row['area_id']; ?>" class="link-effect"><?php echo $row['city_name']; ?> - <?php echo $row['area_name']; ?></a></td>
									<td><?php echo $total_price; ?></td>
									<td><?php echo $delivery_staff; ?></td>
									<td><?php add_options_button('orders/view', $row['order_id'], $role_functions, ['label' => 'View', 'icon' => 'fa fa-info']); ?></td>
								</tr>
							<?php
							}

							db_free_result($result);

							?>
						</tbody>
					</table>
				<?php
				} else no_data_message();

				close_db_con($con);

				?>
			</div>
		</div>
	</div>
<?php

	change_order_state_modal();
}




function view_sodic_order($get = [], $vars = [])
{
	
	if (!isset($get['view_id'])) return;
	$order_id = $get['view_id'];

	$admin_id = $_SESSION['admin_id'];


	$con = get_db_con();

	$query = "SELECT client_ip,is_paid,delivery_staff_id,total_price,delivery_time,payment_method,notes,rating_number,rating_message,user_id,first_name,last_name,orders.created_at AS order_timestamp,is_express_delivery,order_states.name AS order_state_name,color,city.name AS city_name,
	
	area.name AS area_name,area.id AS area_id,
	
	(CASE WHEN orders.street IS NULL THEN user.street ELSE orders.street END) AS street,
	
	(CASE WHEN orders.house_number IS NULL THEN user.house_number ELSE orders.house_number END) AS house_number,
	
	(SELECT SUM(quantity) FROM products_per_order WHERE products_per_order.order_id=orders.id) AS product_count 
	
	FROM orders,user,order_states,states_per_order,city,area
	
	 WHERE city.id=city_id AND order_states.id=order_state_id AND 
	
	orders.id=order_id AND user.id=user_id 
	
	AND orders.id='" . $order_id . "' 
	
 AND (CASE WHEN orders.area_id IS NULL THEN user.area_id ELSE orders.area_id END)=area.id  
	
	AND states_per_order.created_at=(SELECT MAX(created_at) FROM states_per_order WHERE order_id=orders.id)
	
	 ORDER BY orders.created_at DESC";
	/* Coupon ADD */
	$coupons = [];

	$cou_or_id = $order_id;

	$get_offers = "SELECT coupon_id FROM coupuser WHERE order_id=$cou_or_id";

	$co_orders = db_query($con, $get_offers);

	$cou_orders = db_fetch_assoc($co_orders);

	$id_id = $cou_orders['coupon_id'];

	$get_co = "SELECT * FROM coupons WHERE id=$id_id";

	$co_get = db_query($con, $get_co);

	$coupon_get = db_fetch_assoc($co_get);

	/*****************************/

	$result = db_query($con, $query);
	if (!$result || db_num_rows($result) != 1) return;

	$row = db_fetch_assoc($result);
	db_free_result($result);

	$delivery_staff_id = $row['delivery_staff_id'];
	$delivery_staff = $rating_number = $rating_message = $notes = '-';
	$is_paid = $is_express_delivery = 'No';

	$query = "SELECT full_name FROM delivery_staff WHERE id='" . $delivery_staff_id . "'";
	$result = db_query($con, $query);

	if ($result && db_num_rows($result) == 1) {
		$row1 = db_fetch_assoc($result);
		db_free_result($result);

		$delivery_staff = '<a href="admin/delivery-staff/add/' . $delivery_staff_id . '" class="link-effect">' . $row1['full_name'] . '</a>';
	}

	$created_at = format_date($row['order_timestamp']);
	$delivery_time = format_date($row['delivery_time']);
	$payment_method = $row['payment_method'];
	if (!empty($row['rating_number'])) $rating_number = $row['rating_number'] . ' / ' . MAX_RATE_VALUE;
	if (!empty($row['rating_message'])) $rating_message = nl2br($row['rating_message']);
	if (!empty($row['notes'])) $notes = nl2br($row['notes']);
	if (!empty($row['is_paid'])) $is_paid = 'Yes';
	if (!empty($row['is_express_delivery'])) $is_express_delivery = 'Yes';
	$user_id = $row['user_id'];
	$first_name = $row['first_name'];
	$last_name = $row['last_name'];
	$area_id = $row['area_id'];
	$area_name = $row['area_name'];
	$city_name = $row['city_name'];
	$street = $row['street'];
	$house_number = $row['house_number'];
	$order_state_name = $row['order_state_name'];
	$order_state_color = $row['color'];
	$product_count = $row['product_count'];
	$client_ip = $row['client_ip'];
	$client_host = gethostbyaddr($client_ip);

	$res= mysqli_query($con, "SELECT project_id FROM orders_projects WHERE order_id=${order_id}");
	$row_p = mysqli_fetch_assoc($res);
   $project = $row_p['project_id'];
   $project = mysqli_fetch_assoc(mysqli_query($con, "SELECT name from projects where id=${project} AND area_id=${area_id} " ) )["name"];

	/* SERVICE ADD */
	$service_query = "SELECT * FROM charge WHERE charge_area_id=" . $area_id;
	$service_result = db_query($con, $service_query);
	$result_fetch = db_fetch_assoc($service_result);
	$charge = $result_fetch['present'] / 100;
	/**********************************/

	 $total_price=format_money($row['total_price']) . ' ' . CURRENCY_CODE;

	$table_basic = [
		['Created at:', $created_at],
		['Delivery time:', $delivery_time],
		['User:', '<a href="admin/users/add/' . $user_id . '" class="link-effect">' . $first_name . ' ' . $last_name . '</a>'],
		['Area:', '<a href="admin/cities/areas/add/' . $area_id . '" class="link-effect">' . $city_name . ' - ' . $area_name . '</a>'],
		['Address:', $street . " " . $house_number . ' ' . $project],
		['Express delivery:', $is_express_delivery],
		['Client IP and host:', $client_ip . ' (' . $client_host . ')'],
		['Payment method:', $payment_method],
		['Products:', $product_count],
		['Total price:', $total_price],
		['Is paid:', $is_paid],
		['Order state:', style_order_state($order_state_name, $order_state_color)],
		['Delivery staff:', $delivery_staff],
		['Notes:', $notes],
		['User rating:', $rating_number],
		['Rating message:', $rating_message],
		['Coupon:', $coupon_get['present'] . ' EGP'],
		['service charge:', $charge]
	];

?>
	<div class="content">
		<?php

		// $actions = [
		// 	[
		// 		'type' => 'change_order_state',
		// 		'function' => 'change_order_state'
		// 	],
		// 	[
		// 		'type' => 'refund_order',
		// 		'function' => 'refund_order'
		// 	],
		// 	[
		// 		'type' => 'remove',
		// 		'function' => 'remove_order'
		// 	]
		// ];

		// add_close_button('ORDER #' . $order_id, $actions);

		?>
		<form class="form-horizontal push-10-t">
			<div class="col-sm-12">
				<div class="block">
					<div class="block-content block-content-narrow">
						<ul class="nav nav-tabs nav-tabs-alt" data-toggle="tabs">
							<li class="active">
								<a href="#btabs-alt-static-basic">Basic</a>
							</li>
							<li>
								<a href="#btabs-alt-static-products">Products</a>
							</li>
							<li>
								<a href="#btabs-alt-static-timeline">Timeline</a>
							</li>
						</ul>
						<div class="block-content tab-content">
							<div class="tab-pane active" id="btabs-alt-static-basic">
								<table width="100%" class="table table-bordered table-striped table-hover">
									<tbody align="center">
										<?php

										foreach ($table_basic as $row) {
										?>
											<tr>
												<?php

												foreach ($row as $cell) {
												?>
													<td><?php echo $cell; ?></td>
												<?php
												}

												?>
											</tr>
										<?php
										}
										?>
									</tbody>
								</table>
							</div>
							<div class="tab-pane" id="btabs-alt-static-products">
								<?php

								$query = "SELECT product.featured_image,product.id AS product_id,product.name AS product_name,shop_id,
								
								shop.name AS shop_name,supplier_id,supplier.name AS supplier_name,
								
								category_id,category.name AS category_name,
							
								products_per_order.quantity AS product_quantity,
								
								is_fetched,(products_per_order.single_price*products_per_order.quantity) AS total_price_sum   FROM 

supplier,product,shop,products_per_order,category,admin

	WHERE category.id = category_id AND shop.id =product.shop_id AND supplier.id =supplier_id and 

	products_per_order.product_id =product.id AND products_per_order.order_id=${order_id}
 
 AND admin.id = shop.admin_id ORDER BY product.name";

								// "SELECT product.id AS product_id,product.name AS product_name,shop_id,

								// shop.name AS shop_name,supplier_id,supplier.name AS supplier_name,

								// category_id,category.name AS category_name,

								// products_per_order.quantity AS product_quantity,

								// is_fetched,(products_per_order.single_price*products_per_order.quantity) AS total_price_sum 

								// FROM product,shop,supplier,category,products_per_order WHERE product.shop_id=shop.id AND 

								// product.supplier_id=supplier.id 

								// AND product.category_id=category.id

								// AND product_id=product.id 

								// AND order_id='" . $order_id . "' AND shop.admin_id LIKE  '".$admin_id."' ORDER BY product.name";


								$result = db_query($con, $query);

								if ($result && db_num_rows($result)) {

								?>
									<table width="100%" class="table table-bordered table-striped table-hover js-dataTable-full-pagination">
										<thead>
											<tr>
												<th>Image</th>

												<th>Name</th>
												<th>Store</th>
												<th>Supplier</th>
												<th>Category</th>
												<th>Quantity</th>
												<th>Total price</th>
												<th>Is fetched</th>
											</tr>
										</thead>
										<tbody align="center">
											<?php

											while ($row = db_fetch_assoc($result)) {
												if ($row['is_fetched']) $is_fetched = 'Yes';
												else $is_fetched = 'No';

												$total_price = format_money($row['total_price_sum']);

											?>
												<tr>
													<td><img style="width:70%;height:25%" src="https://mrkt.ws/media/products/<?= $row['product_id'] ?>/thumbs/<?= $row['featured_image'] ?>" alt="<?= $row["product_name"] ?>"></td>
													<td><a href="admin/products/add/<?php echo $row['product_id']; ?>" class="link-effect"><?php echo $row['product_name']; ?></a></td>
													<td><a href="admin/stores/add/<?php echo $row['shop_id']; ?>" class="link-effect"><?php echo $row['shop_name']; ?></a></td>
													<td><a href="admin/suppliers/add/<?php echo $row['supplier_id']; ?>" class="link-effect"><?php echo $row['supplier_name']; ?></a></td>
													<td><a href="admin/categories/add/<?php echo $row['category_id']; ?>" class="link-effect"><?php echo $row['category_name']; ?></a></td>
													<td><?php echo $row['product_quantity']; ?></td>
													<td><?php echo $total_price . ' ' . CURRENCY_CODE; ?></td>
													<td><?php echo $is_fetched; ?></td>
												</tr>

											<?php
											}




											db_free_result($result);

											?>
										</tbody>
									</table>
								<?php
								} else no_data_message();

								?>
							</div>
							<div class="tab-pane" id="btabs-alt-static-timeline">
								<?php

								$query = "SELECT name,color,created_at,delivery_staff_id FROM order_states,states_per_order WHERE order_states.id=order_state_id AND order_id='" . $order_id . "' ORDER BY created_at DESC";
								$result = db_query($con, $query);

								if ($result && db_num_rows($result)) {
								?>
									<table width="100%" class="table table-bordered table-striped table-hover">
										<thead>
											<tr>
												<th>Time</th>
												<th>Order state</th>
												<th>Delivery staff</th>
											</tr>
										</thead>
										<tbody align="center">
											<?php

											while ($row = db_fetch_assoc($result)) {
												$created_at = format_date($row['created_at']);

												$delivery_staff_id = $row['delivery_staff_id'];
												$delivery_staff = '-';

												$query = "SELECT full_name FROM delivery_staff WHERE id='" . $delivery_staff_id . "'";
												$result1 = db_query($con, $query);

												if ($result1 && db_num_rows($result1) == 1) {
													$row1 = db_fetch_assoc($result1);
													db_free_result($result1);

													$delivery_staff = '<a href="admin/delivery-staff/add/' . $delivery_staff_id . '" class="link-effect">' . $row1['full_name'] . '</a>';
												}

											?>
												<tr>
													<td><?php echo $created_at; ?></td>
													<td><?php echo style_order_state($row['name'], $row['color']); ?></td>
													<td><?php echo $delivery_staff; ?></td>
												</tr>
											<?php
											}

											db_free_result($result);

											?>
										</tbody>
									</table>
								<?php
								} else no_data_message();

								close_db_con($con);

								?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>

		<input type="hidden" value="<?php echo $order_id; ?>" name="values[]" form="options_form" />
	</div>
<?php

	change_order_state_modal();
}


function view_order($get = [], $vars = [])
{
	if (!isset($get['view_id'])) return;
	$order_id = $get['view_id'];

	if (!$_SESSION['admin_view_all']) $admin_id = $_SESSION['admin_id'];
	else $admin_id = '%';


	$con = get_db_con();

	$query = "SELECT client_ip,is_paid,delivery_staff_id,total_price,delivery_time,payment_method,notes,rating_number,rating_message,user_id,first_name,last_name,orders.created_at AS order_timestamp,is_express_delivery,order_states.name AS order_state_name,color,city.name AS city_name,
	area.name AS area_name,area.id AS area_id,(CASE WHEN orders.street IS NULL THEN user.street ELSE orders.street END) AS street,(CASE WHEN orders.house_number IS NULL THEN user.house_number ELSE orders.house_number END) AS house_number,
	(SELECT SUM(quantity) FROM products_per_order WHERE products_per_order.order_id=orders.id) AS product_count FROM orders,user,order_states,states_per_order,city,area WHERE city.id=city_id AND order_states.id=order_state_id AND 
	orders.id=order_id AND user.id=user_id AND orders.id='" . $order_id . "' AND (CASE WHEN orders.area_id IS NULL THEN user.area_id ELSE orders.area_id END)=area.id AND 
	states_per_order.created_at=(SELECT MAX(created_at) FROM states_per_order WHERE order_id=orders.id) ORDER BY orders.created_at DESC";
	/* Coupon ADD */
	$coupons = [];

	$cou_or_id = $order_id;

	$get_offers = "SELECT coupon_id FROM coupuser WHERE order_id=$cou_or_id";

	$co_orders = db_query($con, $get_offers);

	$cou_orders = db_fetch_assoc($co_orders);

	$id_id = $cou_orders['coupon_id'];

	$get_co = "SELECT * FROM coupons WHERE id=$id_id";

	$co_get = db_query($con, $get_co);

	$coupon_get = db_fetch_assoc($co_get);

	/*****************************/

	$result = db_query($con, $query);
	if (!$result || db_num_rows($result) != 1) return;

	$row = db_fetch_assoc($result);
	db_free_result($result);

	$delivery_staff_id = $row['delivery_staff_id'];
	$delivery_staff = $rating_number = $rating_message = $notes = '-';
	$is_paid = $is_express_delivery = 'No';

	$query = "SELECT full_name FROM delivery_staff WHERE id='" . $delivery_staff_id . "'";
	$result = db_query($con, $query);

	if ($result && db_num_rows($result) == 1) {
		$row1 = db_fetch_assoc($result);
		db_free_result($result);

		$delivery_staff = '<a href="admin/delivery-staff/add/' . $delivery_staff_id . '" class="link-effect">' . $row1['full_name'] . '</a>';
	}

	$created_at = format_date($row['order_timestamp']);
	$delivery_time = format_date($row['delivery_time']);
	$payment_method = $row['payment_method'];
	if (!empty($row['rating_number'])) $rating_number = $row['rating_number'] . ' / ' . MAX_RATE_VALUE;
	if (!empty($row['rating_message'])) $rating_message = nl2br($row['rating_message']);
	if (!empty($row['notes'])) $notes = nl2br($row['notes']);
	if (!empty($row['is_paid'])) $is_paid = 'Yes';
	if (!empty($row['is_express_delivery'])) $is_express_delivery = 'Yes';
	$user_id = $row['user_id'];
	$first_name = $row['first_name'];
	$last_name = $row['last_name'];
	$area_id = $row['area_id'];
	$area_name = $row['area_name'];
	$city_name = $row['city_name'];
	$street = $row['street'];
	$house_number = $row['house_number'];
	$order_state_name = $row['order_state_name'];
	$order_state_color = $row['color'];
	$product_count = $row['product_count'];
	$client_ip = $row['client_ip'];
	$client_host = gethostbyaddr($client_ip);

	/* SERVICE ADD */
	$service_query = "SELECT * FROM charge WHERE charge_area_id=" . $area_id;
	$service_result = db_query($con, $service_query);
	$result_fetch = db_fetch_assoc($service_result);
	$charge = $result_fetch['present'] / 100;
	/**********************************/

	if ($_SESSION['admin_view_all']) $total_price = format_money($row['total_price']) . ' ' . CURRENCY_CODE;
	else $total_price = '-';

	$table_basic = [
		['Created at:', $created_at],
		['Delivery time:', $delivery_time],
		['User:', '<a href="admin/users/add/' . $user_id . '" class="link-effect">' . $first_name . ' ' . $last_name . '</a>'],
		['Area:', '<a href="admin/cities/areas/add/' . $area_id . '" class="link-effect">' . $city_name . ' - ' . $area_name . '</a>'],
		['Address:', $street . ' ' . $house_number],
		['Express delivery:', $is_express_delivery],
		['Client IP and host:', $client_ip . ' (' . $client_host . ')'],
		['Payment method:', $payment_method],
		['Products:', $product_count],
		['Total price:', $total_price],
		['Is paid:', $is_paid],
		['Order state:', style_order_state($order_state_name, $order_state_color)],
		['Delivery staff:', $delivery_staff],
		['Notes:', $notes],
		['User rating:', $rating_number],
		['Rating message:', $rating_message],
		['Coupon:', $coupon_get['present'] . ' EGP'],
		['service charge:', $charge]
	];

?>
	<div class="content">
		<?php

		$actions = [
			[
				'type' => 'change_order_state',
				'function' => 'change_order_state'
			],
			[
				'type' => 'refund_order',
				'function' => 'refund_order'
			],
			[
				'type' => 'remove',
				'function' => 'remove_order'
			]
		];

		add_close_button('ORDER #' . $order_id, $actions);

		?>
		<form class="form-horizontal push-10-t">
			<div class="col-sm-12">
				<div class="block">
					<div class="block-content block-content-narrow">
						<ul class="nav nav-tabs nav-tabs-alt" data-toggle="tabs">
							<li class="active">
								<a href="#btabs-alt-static-basic">Basic</a>
							</li>
							<li>
								<a href="#btabs-alt-static-products">Products</a>
							</li>
							<li>
								<a href="#btabs-alt-static-timeline">Timeline</a>
							</li>
						</ul>
						<div class="block-content tab-content">
							<div class="tab-pane active" id="btabs-alt-static-basic">
								<table width="100%" class="table table-bordered table-striped table-hover">
									<tbody align="center">
										<?php

										foreach ($table_basic as $row) {
										?>
											<tr>
												<?php

												foreach ($row as $cell) {
												?>
													<td><?php echo $cell; ?></td>
												<?php
												}

												?>
											</tr>
										<?php
										}

										?>
									</tbody>
								</table>
							</div>
							<div class="tab-pane" id="btabs-alt-static-products">
								<?php

								$query = "SELECT product.id AS product_id,product.name AS product_name,shop_id,shop.name AS shop_name,supplier_id,supplier.name AS supplier_name,category_id,category.name AS category_name,
								products_per_order.quantity AS product_quantity,is_fetched,(products_per_order.single_price*products_per_order.quantity) AS total_price_sum 
								FROM product,shop,supplier,category,products_per_order WHERE product.shop_id=shop.id AND 
								product.supplier_id=supplier.id AND product.category_id=category.id AND product_id=product.id AND order_id='" . $order_id . "' AND shop.admin_id LIKE '" . $admin_id . "' ORDER BY product.name";
								$result = db_query($con, $query);

								if ($result && db_num_rows($result)) {
								?>
									<table width="100%" class="table table-bordered table-striped table-hover js-dataTable-full-pagination">
										<thead>
											<tr>
												<th>Name</th>
												<th>Store</th>
												<th>Supplier</th>
												<th>Category</th>
												<th>Quantity</th>
												<th>Total price</th>
												<th>Is fetched</th>
											</tr>
										</thead>
										<tbody align="center">
											<?php

											while ($row = db_fetch_assoc($result)) {
												if ($row['is_fetched']) $is_fetched = 'Yes';
												else $is_fetched = 'No';

												$total_price = format_money($row['total_price_sum']);

											?>
												<tr>
													<td><a href="admin/products/add/<?php echo $row['product_id']; ?>" class="link-effect"><?php echo $row['product_name']; ?></a></td>
													<td><a href="admin/stores/add/<?php echo $row['shop_id']; ?>" class="link-effect"><?php echo $row['shop_name']; ?></a></td>
													<td><a href="admin/suppliers/add/<?php echo $row['supplier_id']; ?>" class="link-effect"><?php echo $row['supplier_name']; ?></a></td>
													<td><a href="admin/categories/add/<?php echo $row['category_id']; ?>" class="link-effect"><?php echo $row['category_name']; ?></a></td>
													<td><?php echo $row['product_quantity']; ?></td>
													<td><?php echo $total_price . ' ' . CURRENCY_CODE; ?></td>
													<td><?php echo $is_fetched; ?></td>
												</tr>
											<?php
											}

											db_free_result($result);

											?>
										</tbody>
									</table>
								<?php
								} else no_data_message();

								?>
							</div>
							<div class="tab-pane" id="btabs-alt-static-timeline">
								<?php

								$query = "SELECT name,color,created_at,delivery_staff_id FROM order_states,states_per_order WHERE order_states.id=order_state_id AND order_id='" . $order_id . "' ORDER BY created_at DESC";
								$result = db_query($con, $query);

								if ($result && db_num_rows($result)) {
								?>
									<table width="100%" class="table table-bordered table-striped table-hover">
										<thead>
											<tr>
												<th>Time</th>
												<th>Order state</th>
												<th>Delivery staff</th>
											</tr>
										</thead>
										<tbody align="center">
											<?php

											while ($row = db_fetch_assoc($result)) {
												$created_at = format_date($row['created_at']);

												$delivery_staff_id = $row['delivery_staff_id'];
												$delivery_staff = '-';

												$query = "SELECT full_name FROM delivery_staff WHERE id='" . $delivery_staff_id . "'";
												$result1 = db_query($con, $query);

												if ($result1 && db_num_rows($result1) == 1) {
													$row1 = db_fetch_assoc($result1);
													db_free_result($result1);

													$delivery_staff = '<a href="admin/delivery-staff/add/' . $delivery_staff_id . '" class="link-effect">' . $row1['full_name'] . '</a>';
												}

											?>
												<tr>
													<td><?php echo $created_at; ?></td>
													<td><?php echo style_order_state($row['name'], $row['color']); ?></td>
													<td><?php echo $delivery_staff; ?></td>
												</tr>
											<?php
											}

											db_free_result($result);

											?>
										</tbody>
									</table>
								<?php
								} else no_data_message();

								close_db_con($con);

								?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>

		<input type="hidden" value="<?php echo $order_id; ?>" name="values[]" form="options_form" />
	</div>
<?php

	change_order_state_modal();
}

function add_delivery_staff($get = [], $vars = [])
{
	$form_submit = __FUNCTION__;

	if (!$_SESSION['admin_view_all']) $admin_id = $_SESSION['admin_id'];
	else $admin_id = '%';

	$mode = 'UPDATE';
	$id = 0;
	$first_name = $last_name = $email = $phone = $area_id = $user_id = '';
	$password = substr(md5(mt_rand()), 0, MIN_PASS_LEN);
	$active = $pass_required = true;

	$con = get_db_con();

	if (isset($get['add_id'])) {
		$id = $get['add_id'];

		$query = "SELECT * FROM delivery_staff WHERE id='" . $id . "' AND admin_id LIKE '" . $admin_id . "'";
		$result = db_query($con, $query);

		if ($result && db_num_rows($result) == 1) {
			$row = db_fetch_assoc($result);
			db_free_result($result);

			$full_name = $row['full_name'];
			$email = strtolower($row['email']);
			$phone = $row['phone'];
			$area_id = $row['area_id'];
			$user_id = $row['admin_id'];
			$active = boolval($row['active']);
			$password = '';
			$pass_required = false;
		} else $id = 0;
	}

	$areas = $users = [
		[
			'value' => '',
			'label' => '-'
		]
	];

	$query = "SELECT area.id AS area_id,city.name AS city_name,area.name AS area_name FROM city,area WHERE city_id=city.id ORDER BY city.name,area.name";
	$result = db_query($con, $query);

	if ($result && db_num_rows($result)) {
		while ($row = db_fetch_assoc($result)) {
			$areas[$row['city_name']][] = [
				'value' => $row['area_id'],
				'label' => $row['area_name']
			];
		}

		db_free_result($result);
	}

	$selected_user = '';

	$query = "SELECT id,full_name FROM admin WHERE id LIKE '" . $admin_id . "' ORDER BY full_name";
	$result = db_query($con, $query);

	if ($result && db_num_rows($result)) {
		while ($row = db_fetch_assoc($result)) {
			$curr_id = $row['id'];
			if (($id && $user_id == $curr_id) || (!$id && $_SESSION['admin_id'] == $curr_id)) $selected_user = $curr_id;

			$users[] = [
				'value' => $curr_id,
				'label' => $row['full_name']
			];
		}

		db_free_result($result);
	}

	close_db_con($con);

	$fields = [
		[
			'label' => 'Full name',
			'tag' => 'input',
			'attributes' => [
				'id' => 'full_name',
				'name' => 'full_name',
				'type' => 'text',
				'value' => $full_name,
				'maxlength' => 50,
				'required' => true,
				'autofocus' => true
			]
		],
		[
			'label' => 'E-mail (must be unique)',
			'tag' => 'input',
			'attributes' => [
				'id' => 'email',
				'name' => 'email',
				'type' => 'email',
				'value' => $email,
				'maxlength' => 50,
				'required' => true
			]
		],
		[
			'label' => 'Phone',
			'tag' => 'input',
			'attributes' => [
				'id' => 'phone',
				'name' => 'phone',
				'type' => 'text',
				'value' => $phone,
				'maxlength' => 20,
				'required' => true
			]
		],
		[
			'label' => 'Admin',
			'tag' => 'select',
			'options' => $users,
			'selected' => $selected_user,
			'attributes' => [
				'id' => 'admin_id',
				'name' => 'admin_id',
				'required' => true
			]
		],
		[
			'label' => 'Area',
			'tag' => 'select',
			'options' => $areas,
			'selected' => $area_id,
			'attributes' => [
				'id' => 'area_id',
				'name' => 'area_id',
				'required' => true
			]
		],
		[
			'label' => 'Password (min. ' . MIN_PASS_LEN . ' characters)',
			'tag' => 'input',
			'attributes' => [
				'id' => 'password',
				'name' => 'password',
				'type' => 'text',
				'value' => $password,
				'pattern' => '.{' . MIN_PASS_LEN . ',}',
				'required' => $pass_required
			]
		],
		[
			'label' => 'Active',
			'tag' => 'checkbox',
			'side_label' => 'Yes',
			'attributes' => [
				'id' => 'active',
				'name' => 'active',
				'class' => '',
				'type' => 'checkbox',
				'value' => 1,
				'checked' => $active
			]
		]
	];

?>
	<div class="content">
		<?php

		$actions = [
			[
				'type' => 'remove',
				'function' => 'remove_delivery_staff'
			]
		];

		if (!$id) {
			$mode = 'ADD';
			$actions = null;
		}

		add_close_button($mode . ' DELIVERY STAFF', $actions);

		?>
		<form class="form-horizontal push-10-t form-notify" action="manage" method="post" autocomplete="off">
			<div class="col-sm-12">
				<div class="block">
					<div class="block-content block-content-narrow">
						<?php

						generate_form_fields($fields);
						add_submit_buttons($form_submit);

						?>
					</div>
				</div>
			</div>
			<input type="hidden" value="<?php echo $id; ?>" name="id" />
		</form>

		<input type="hidden" value="<?php echo $id; ?>" name="values[]" form="options_form" />
	</div>
<?php
}



function list_delivery_staff($get = [], $vars = [])
{
	$edit_tag = 'delivery-staff/add';

?>
	<div class="content">
		<?php

		add_add_button($edit_tag);
		$area_id = '%';

		if (!$_SESSION['admin_view_all']) $admin_id = $_SESSION['admin_id'];
		else $admin_id = '%';

		if (isset($vars['area_id'])) $area_id = $vars['area_id'];

		$con = get_db_con();
		$query = "SELECT area.id AS area_id,city.name AS city_name,area.name AS area_name 
		FROM city,area WHERE city_id=city.id 
		ORDER BY city.name,area.name";
		$result = db_query($con, $query);

		$areas = [
			[
				'value' => '%',
				'label' => 'All'
			]
		];

		if ($result && db_num_rows($result)) {
			while ($row = db_fetch_assoc($result)) {
				$areas[$row['city_name']][] = [
					'value' => $row['area_id'],
					'label' => $row['area_name']
				];
			}

			db_free_result($result);
		}

		$fields = [
			[
				'label' => 'Area',
				'tag' => 'select',
				'expected' => false,
				'options' => $areas,
				'selected' => $area_id,
				'attributes' => [
					'id' => 'area_id',
					'name' => 'area_id',
					'required' => true,
					'onchange' => 'this.form.submit();'
				]
			]
		];

		?>
		<div class="block">
			<div class="block-content">
				<form class="form-horizontal push-10-t" method="get">
					<div class="row">
						<div class="col-sm-6">
							<?php generate_form_fields($fields); ?>
						</div>
					</div>
				</form>
				<?php

				// SELECT delivery_staff.id AS delivery_staff_id,delivery_staff.full_name AS full_name,

				// delivery_staff.email AS email,delivery_staff.phone AS phone,delivery_staff.created_at AS created_at,

				// delivery_staff.active AS active,area_id,area.name AS area_name,city.name AS city_name,admin.full_name AS admin_name,admin_id,

				// (SELECT ROUND(AVG(TIMESTAMPDIFF(SECOND,(SELECT MIN(created_at) 

				// FROM states_per_order,order_states WHERE order_id=orders.id AND order_states.id=order_state_id AND state_num='" . ORDER_STATE_CONFIRMED . "'),

				// (SELECT MAX(created_at) FROM states_per_order,order_states WHERE order_id=orders.id AND order_states.id=order_state_id AND created_at=(SELECT MAX(created_at) 

				// FROM states_per_order WHERE order_id=orders.id) AND state_num=(SELECT MAX(state_num) FROM order_states))))) FROM states_per_order,orders WHERE orders.id=order_id AND delivery_staff_id=delivery_staff.id) AS avg_delivery_time_confirmed,

				// (SELECT COUNT(*) FROM states_per_order,orders WHERE orders.id=order_id AND order_state_id=(SELECT id FROM order_states WHERE state_num=(SELECT MAX(state_num) FROM order_states)) AND delivery_staff_id=delivery_staff.id AND 

				// states_per_order.created_at=(SELECT MAX(created_at) FROM states_per_order WHERE order_id=orders.id)) AS orders_count,

				// (SELECT ROUND(AVG(rating_number),2) FROM states_per_order,orders WHERE orders.id=order_id AND order_state_id=(SELECT id FROM order_states WHERE state_num=(SELECT MAX(state_num) FROM order_states)) AND delivery_staff_id=delivery_staff.id AND 

				// states_per_order.created_at=(SELECT MAX(created_at) FROM states_per_order WHERE order_id=orders.id)) AS avg_rate 

				// FROM delivery_staff,city,area,admin WHERE admin.id=admin_id AND city_id=city.id AND area_id=area.id AND area_id LIKE '" . $area_id . "' AND admin_id LIKE '" . $admin_id . "' ORDER BY delivery_staff.full_name";

				$query = "SELECT delivery_staff.id AS delivery_staff_id,delivery_staff.full_name AS full_name,

delivery_staff.email AS email,delivery_staff.phone AS phone,delivery_staff.created_at AS created_at,

delivery_staff.active AS active,area_id,area.name AS area_name,city.name AS city_name,admin.full_name AS admin_name,admin_id

FROM delivery_staff,city,area,admin WHERE admin.id=admin_id AND city_id=city.id AND area_id=area.id AND area_id LIKE '" . $area_id . "' AND admin_id LIKE '" . $admin_id . "' ORDER BY delivery_staff.full_name";

				$result = db_query($con, $query);

				if ($result && db_num_rows($result)) {
					$actions = [
						[
							'type' => 'change_delivery_staff_area',
							'function' => 'change_delivery_staff_area'
						],
						[
							'type' => 'remove',
							'function' => 'remove_delivery_staff'
						],
						[
							'type' => 'activate',
							'function' => 'activate_delivery_staff'
						],
						[
							'type' => 'deactivate',
							'function' => 'deactivate_delivery_staff'
						]
					];

					$role_functions = get_role_functions();
					add_action_buttons($actions, $role_functions);

				?>
					<table width="100%" class="table table-bordered table-striped table-hover js-dataTable-full-pagination">
						<thead>
							<tr>
								<th>Name</th>
								<th>E-mail</th>
								<th>Phone</th>
								<th>Admin</th>
								<th>Area</th>
								<!-- <th>Orders</th> -->
								<!-- <th>Average delivery time</th> -->
								<!-- <th>Average rating</th> -->
								<th>Active</th>
								<th><?php options_column_header(); ?></th>
							</tr>
						</thead>
						<tbody align="center">
							<?php

							while ($row = db_fetch_assoc($result)) {
								if ($row['active']) $active = 'Yes';
								else $active = 'No';

								$avg_delivery_time_confirmed = $avg_rate = '-';

								if (!empty($row['avg_delivery_time_confirmed'])) $avg_delivery_time_confirmed = gmdate('G\h i\m', $row['avg_delivery_time_confirmed']);
								if (!empty($row['avg_rate'])) $avg_rate = $row['avg_rate'];

							?>
								<tr>
									<td><?php echo $row['full_name']; ?></td>

									<td><a href="mailto:<?php echo $row['email']; ?>" class="link-effect"><?php echo $row['email']; ?></a></td>

									<td><?php echo $row['phone']; ?></td>

									<td><a href="admin/admins/add/<?php echo $row['admin_id']; ?>" class="link-effect"><?php echo $row['admin_name']; ?></a></td>

									<td><a href="admin/cities/areas/add/<?php echo $row['area_id']; ?>" class="link-effect"><?php echo $row['city_name']; ?> - <?php echo $row['area_name']; ?></a></td>

									<!-- <td><?php echo $row['orders_count']; ?></td> -->

									<!-- <td><?php echo $avg_delivery_time_confirmed; ?></td> -->

									<!-- <td><?php echo $avg_rate; ?></td> -->

									<td><?php echo $active; ?></td>

									<td><?php add_options_button($edit_tag, $row['delivery_staff_id'], $role_functions); ?></td>
								</tr>
							<?php
							}

							db_free_result($result);

							?>
						</tbody>
					</table>
				<?php
				} else no_data_message();

				close_db_con($con);

				?>
			</div>
		</div>
	</div>
<?php

	change_delivery_staff_area_modal();
}

function add_city($get = [], $vars = [])
{
	$form_submit = __FUNCTION__;

	$mode = 'UPDATE';
	$id = 0;
	$name = '';

	if (isset($get['add_id'])) {
		$id = $get['add_id'];

		$con = get_db_con();
		$query = "SELECT * FROM city WHERE id='" . $id . "'";
		$result = db_query($con, $query);

		if ($result && db_num_rows($result) == 1) {
			$row = db_fetch_assoc($result);
			db_free_result($result);

			$name = $row['name'];
		} else $id = 0;

		close_db_con($con);
	}

	$fields = [
		[
			'label' => 'Name',
			'tag' => 'input',
			'attributes' => [
				'id' => 'name',
				'name' => 'name',
				'type' => 'text',
				'value' => $name,
				'maxlength' => 50,
				'required' => true,
				'autofocus' => true
			]
		]
	];

?>
	<div class="content">
		<?php

		$actions = [
			[
				'type' => 'remove',
				'function' => 'remove_city'
			]
		];

		if (!$id) {
			$mode = 'ADD';
			$actions = null;
		}

		add_close_button($mode . ' CITY', $actions);

		?>
		<form class="form-horizontal push-10-t form-notify" action="manage" method="post" autocomplete="off">
			<div class="col-sm-12">
				<div class="block">
					<div class="block-content block-content-narrow">
						<?php

						generate_form_fields($fields);
						add_submit_buttons($form_submit);

						?>
					</div>
				</div>
			</div>
			<input type="hidden" value="<?php echo $id; ?>" name="id" />
		</form>

		<input type="hidden" value="<?php echo $id; ?>" name="values[]" form="options_form" />
	</div>
<?php
}

function list_city($get = [], $vars = [])
{
	$edit_tag = 'cities/add';

?>
	<div class="content">
		<?php add_add_button($edit_tag); ?>
		<div class="block">
			<div class="block-content">
				<?php

				$con = get_db_con();
				$query = "SELECT id,name,(SELECT COUNT(*) FROM area WHERE area.city_id=city.id) AS area_count FROM city ORDER BY name";
				$result = db_query($con, $query);

				if ($result && db_num_rows($result)) {
					$actions = [
						[
							'type' => 'remove',
							'function' => 'remove_city'
						]
					];

					$role_functions = get_role_functions();
					add_action_buttons($actions, $role_functions);

				?>
					<table width="100%" class="table table-bordered table-striped table-hover js-dataTable-full-pagination">
						<thead>
							<tr>
								<th>Name</th>
								<th>Areas</th>
								<th><?php options_column_header(); ?></th>
							</tr>
						</thead>
						<tbody align="center">
							<?php

							while ($row = db_fetch_assoc($result)) {
							?>
								<tr>
									<td><?php echo $row['name']; ?></td>
									<td><?php echo $row['area_count']; ?></td>
									<td><?php add_options_button($edit_tag, $row['id'], $role_functions); ?></td>
								</tr>
							<?php
							}

							db_free_result($result);

							?>
						</tbody>
					</table>
				<?php
				} else no_data_message();

				close_db_con($con);

				?>
			</div>
		</div>
	</div>
<?php
}

function add_area($get = [], $vars = [])
{
	$form_submit = __FUNCTION__;

	$mode = 'UPDATE';
	$id = 0;
	$name = $zip_code = $city_id = $latitude = $longitude = '';
	$express_delivery = true;

	$con = get_db_con();

	if (isset($get['add_id'])) {
		$area_id= $id = $get['add_id'];

		$query = "SELECT * FROM area WHERE id='" . $id . "'";
		$result = db_query($con, $query);

		if ($result && db_num_rows($result) == 1) {
			$row = db_fetch_assoc($result);
			db_free_result($result);

			$name = $row['name'];
			$tomorrow = boolval($row['tomorrow']);
			$zip_code = $row['zip_code'];
			$city_id = $row['city_id'];
			$latitude = $row['latitude'];
			$longitude = $row['longitude'];
			$express_delivery = boolval($row['express_delivery']);
		} else $id = 0;
	}

	$cities = [
		[
			'value' => '',
			'label' => '-'
		]
	];

	if (!empty($tomorrow)) {
		$tomorrow = new DateTime("tomorrow", new DateTimeZone(TIMEZONE));
		$tomorrow = $tomorrow->format(TIME_FORMAT);
	}


	$query = "SELECT id,name FROM city ORDER BY name";
	$result = db_query($con, $query);

	if ($result && db_num_rows($result)) {
		while ($row = db_fetch_assoc($result)) {
			$cities[] = [
				'value' => $row['id'],
				'label' => $row['name']
			];
		}

		db_free_result($result);
	}


	close_db_con($con);


	$fields_delivery_options =
		[
			[
				'label' => 'Express delivery',
				'tag' => 'checkbox',
				'side_label' => 'Yes',
				'attributes' => [
					'id' => 'express_delivery',
					'name' => 'express_delivery',
					'type' => 'checkbox',
					'value' => 1,
					'checked' => $express_delivery
				]
			],
			[
				'label' => 'Deliver Tomorrow',
				'tag' => 'checkbox',
				'side_label' => 'Yes',
				'attributes' => [
					'id' => 'tomorrow',
					'name' => 'tomorrow',
					'type' => 'checkbox',
					'value' => 1,
					'checked' => $tomorrow
				]
				]
		];




	$fields_basic = [
		[
			'label' => 'City',
			'tag' => 'select',
			'options' => $cities,
			'selected' => $city_id,
			'attributes' => [
				'id' => 'city_id',
				'name' => 'city_id',
				'required' => true,
				'autofocus' => true
			]
		], [
			'label' => 'Name',
			'tag' => 'input',
			'attributes' => [
				'id' => 'map_name',
				'name' => 'name',
				'type' => 'text',
				'value' => $name,
				'maxlength' => 50,
				'required' => true
			]
		], [
			'label' => 'ZIP code',
			'tag' => 'input',
			'attributes' => [
				'id' => 'zip_code',
				'name' => 'zip_code',
				'type' => 'text',
				'value' => $zip_code,
				'maxlength' => 20
			]
		],
	];

	$hours = [];
	$today_with_year = date('Y-m-d');
	// $h= date('H:i:s');

	if (empty($_GET["slots"]))  $s_h = 1;
	else $s_h = $slot_h= (int) $_GET["slots"];

	if (empty($_GET['delivery_schdule_on']))  $delivery_schdule_on = false;
	else 	$delivery_schdule_on =  ( $_GET['delivery_schdule_on'] == "false")  ? false : true ;


	
	

	if (empty($_GET["end_time"]))  $end_time = CLOSING_TIME ;
	else $end_time = (int) $_GET["end_time"];

	if (empty($_GET["start_time"]))  $start_time = OPENING_TIME ;
	else $start_time = $h = $srt_tm =(int) $_GET["start_time"];

	if (empty($srt_tm))  $srt_tm= OPENING_TIME;
	if ($srt_tm == null) $srt_tm= OPENING_TIME;

	$add_string = null;
	
	$h=  OPENING_TIME-1;
	
	for ($h; $h < CLOSING_TIME-1;) {
		$h = substr($h, 0, 2) + 1;
		$hours[] = [
			"value" => $h,
			"label" => $h . ":00"
		];

	}
	
	$lh= $start_time ??   OPENING_TIME ;
	for ($lh ; $lh <  CLOSING_TIME; ) {
		$lh = substr($lh, 0, 2) + 1;
		$end_hours[] = [
			"value" => $lh,
			"label" => $lh	 . ":00"
		];
	}
		 $slot_end =  $end_time - $start_time;
	$i=0;
	for ($i; $i++ < $slot_end;) {
		$add_string =	($i == 1) ? " Hour"  : " Hours";
		$slots[] = [
			"value" => $i,
			"label" => $i . $add_string
		];
	}
	$day = $_GET['at'] ??  	$today_with_year;
	if   ( empty ($area_id ) ) {
		$delivers_count = 0;		
	}else
	$delivers_count = mysqli_fetch_row(mysqli_query($con, "SELECT count(*) FROM delivery_staff WHERE  area_id like ${area_id}"))[0] ;
	$fields_delivery_schdule = [
		[
			'label' => 'Total Delivery Person',
			'tag' => 'input',
			'attributes' => [
				'id' => '',
				'name' => '',
				'disabled' => true,
		     	'value' => $delivers_count   ?? 0,
				'min' => $today_with_year,
			]
			],
		[
			'label' => 'Date',
			'tag' => 'input',
			'attributes' => [
				'id' => 'delivery_date',
				'name' => 'delivery_date',
				'type' => 'date',
				'value' => $day,
				'min' => $today_with_year,
			]
			], [
			'label' => 'Start Time',
			'tag' => 'select',
			'options' => $hours,
			'name' => 'start_time',
			'selected' => $start_time ?? OPENING_TIME,
			'attributes' => [
				'id' => 'start_time',
				// 'onchange' => 'updateStats();'
			]
		], [
			'label' => 'End Time',
			'tag' => 'select',
			'options' => $end_hours,
			'name' => 'end_time',
			'selected' => $end_time ?? CLOSING_TIME,
			'attributes' => [
				'id' => 'end_time',
				// 'onchange' => 'updateStats();'
			]
		],[
			'label' => 'Slot / Hours (To see all reseved data show them by an hour) ',
			'tag' => 'select',
			'options' => $slots,
			'name' => 'slots',
			'selected' => $s_h ?? $slots[0] ,
			'attributes' => [
				'id' => 'slots',
				// 'onchange' => ''
			]
		]
	];


	if ( (! empty($s_h)) && ($s_h > 0 ) && (! empty ($start_time) ) && (! empty ($end_time) ) && (! empty ($area_id)) ) {
		$day_values = mysqli_fetch_assoc (mysqli_query($con, "SELECT day.* FROM `slots` JOIN day on day.id = slots.day_id JOIN area  on slots.area_id = area.id WHERE  day.day='${day}'AND area.id = ${area_id}")); 

	  
	  for ($i=$start_time; $i<$end_time;)	{

		  $i+=$s_h;
	  $name_s =  ($i < $end_time) ? "F".$start_time .'=>'. $start_time=$i : "F".$start_time .'=>'. $end_time;
	  $name_s_f =str_replace('F','',strstr ($name_s,"=>",true)) ; 
	  $name_s_l = str_replace('=>','', str_replace('F','',strstr ($name_s,"=>")) ); 
	  $label= ($i < $end_time) ? "From ".$srt_tm ." to ". $srt_tm=$i :"From ".$srt_tm ." to ". $end_time;

	  if ($day_values){

	  foreach($day_values as $key => $value)
		  {
			  if ($key[0] == 'F' ){
				   $key_l = str_replace("=>","",str_replace("F",'', strstr($key,"=>")));
				  if($key_l == $name_s_l){
					  $ok["values"][$key]= $value;
					  $sum [$name_s] = $value;
					  }
			  }
		  }
	  }

		  $fields_delivery_schdule[] = [
			  'label' =>  $label,
			  'tag' => 'input',
			  'attributes' => [
				  'id' => 'delivery_per_slot',
				  'name' =>  $name_s,
				  'type' => 'text',
				  'value' => $sum[$name_s] ?? 0 ,
				  'minlength' => 1,
				  'min' => 1,
				  'max' => $delivers_count,
				  ]
		  ]	;	
	  }
  
  
  }

	$fields_delivery_schdule[] =	[
			'label' => 'You must save after editing',
			'tag' => 'checkbox',
			'side_label' => 'Save',
			'attributes' => [
				'id' => 'delivery_schdule_on',
				'name' => 'delivery_schdule_on',
				'type' => 'checkbox',
				"checked"=> $delivery_schdule_on,
				"value"=> $delivery_schdule_on
							]
			];
	$fields_map_lat = [
		[
			'label' => 'Latitude',
			'tag' => 'input',
			'attributes' => [
				'id' => 'map_latitude',
				'name' => 'latitude',
				'type' => 'number',
				'value' => $latitude,
				'min' => -90,
				'max' => 90,
				'step' => 'any',
				'onchange' => 'coordinateChange();',
				'required' => true
			]
		]
	];



	$fields_map_lng = [
		[
			'label' => 'Longitude',
			'tag' => 'input',
			'attributes' => [
				'id' => 'map_longitude',
				'name' => 'longitude',
				'type' => 'number',
				'value' => $longitude,
				'min' => -180,
				'max' => 180,
				'step' => 'any',
				'onchange' => 'coordinateChange();',
				'required' => true
			]
		]
	];


?>
	<div class="content">
		<?php

		$actions = [
			[
				'type' => 'remove',
				'function' => 'remove_area'
			]
		];

		if (!$id) {
			$mode = 'ADD';
			$actions = null;
		}

		add_close_button($mode . ' AREA', $actions);

		?>
		<form class="form-horizontal push-10-t form-notify" action="manage" method="post" autocomplete="off">
			<div class="col-sm-12">
				<div class="block">
					<div class="block-content block-content-narrow">
						<ul class="nav nav-tabs nav-tabs-alt" data-toggle="tabs">
							<li class=" <?php echo (  empty ($_GET["start_time"]) ) ? "active" : "" ?>">
								<a href="#btabs-alt-static-basic">Basic</a>
							</li>
							<li onclick="mapResize();">
								<a href="#btabs-alt-static-location">Location</a>
							</li>
							<li>
								<a href="#btabs-alt-static-delivery-options">Delivery Options</a>
							</li>

							<li class=" <?php echo ( ! empty ($_GET["start_time"]) ) ? "active": "" ?>">
								<a href="#btabs-alt-static-delivery-schdule">Delivery Schdule Slots</a>
							</li>

						</ul>
						<div class="block-content tab-content">

							<div class="tab-pane  <?php echo (   empty ($_GET["start_time"]) ) ? "active":  "" ?>" id="btabs-alt-static-basic">
								<?php generate_form_fields($fields_basic); ?>
							</div>

							<div class="tab-pane" id="btabs-alt-static-location">
								<div class="col-sm-6">
									<?php generate_form_fields($fields_map_lat); ?>
								</div>
								<div class="col-sm-6">
									<?php generate_form_fields($fields_map_lng); ?>
								</div>

								<div class="form-group">
									<div id="map_container" class="map"></div>
								</div>
							</div>


							<div class="tab-pane " id="btabs-alt-static-delivery-options">
								<?php generate_form_fields($fields_delivery_options); ?>
							</div>

							<div class="tab-pane <?php echo ( ! empty ($_GET["start_time"]) ) ?"active": "" ?>" id="btabs-alt-static-delivery-schdule">
								<?php generate_form_fields($fields_delivery_schdule);?>
							
							</div>
							
						<?php	
						
						?>
							<script>
								let slots = document.getElementById("slots"),
									start_time = document.getElementById("start_time"),
									end_time = document.getElementById("end_time"),
									day = document.getElementById("delivery_date"),
									delivery_schdule_on = document.getElementById("delivery_schdule_on"),
				 				    delivery_schdule_checked = delivery_schdule_on.checked,
									hours = 0,
									slot_hours = 0,
									at=null
									,url = location.pathname,
									xml = new XMLHttpRequest;
										
									day.addEventListener("change", () => {
								 	slot_hours = slots.value;
								 at = day.value;

									 location.replace(url + "?slots=" + slot_hours+"&end_time="+end_time.value+"&start_time="+start_time.value+"&delivery_schdule_on="+delivery_schdule_checked+"&at="+at)
								});

								slots.addEventListener("change", () => {
								 	slot_hours = slots.value;
								 at = day.value;

									 location.replace(url + "?slots=" + slot_hours+"&end_time="+end_time.value+"&start_time="+start_time.value+"&delivery_schdule_on="+delivery_schdule_checked+"&at="+at)
								});
									start_time.addEventListener("change", () => {
									hours = end_time.value - start_time.value;
									slot_hours = hours;
									at = day.value;
									

									//  location.replace(url + "?slots=" + slot_hours+"&end_time="+end_time.value+"&start_time="+start_time.value)
									 location.replace(url + "?slots=" + slot_hours+"&end_time="+end_time.value+"&start_time="+start_time.value+"&delivery_schdule_on="+delivery_schdule_checked+"&at="+at)
								});
									end_time.addEventListener("change", () => {
									slot_hours = (end_time.value - start_time.value);
									at = day.value;


									 location.replace(url + "?slots=" + slot_hours+"&end_time="+end_time.value+"&start_time="+start_time.value+"&delivery_schdule_on="+delivery_schdule_checked+"&at="+at)
								});
							</script>
							<?php echo   $s_h = "<script> slot_hours </script>"; ?>
							<?php add_submit_buttons($form_submit, null ,true); ?>
						</div>
					</div>
				</div>	
			</div>
			<input type="hidden" value="<?php echo $id; ?>" name="id" />
		</form>

		<input type="hidden" value="<?php echo $id; ?>" name="values[]" form="options_form" />
		<script defer src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_API_KEY; ?>&callback=initMap"></script>
	</div>
<?php
}

	
function list_area($get = [], $vars = [])
{
	$edit_tag = 'cities/areas/add';

?>
	<div class="content">
		<?php

		add_add_button($edit_tag);
		$city_id = '%';

		if (isset($vars['city_id'])) $city_id = $vars['city_id'];

		$cities = [
			[
				'value' => '%',
				'label' => 'All'
			]
		];

		$con = get_db_con();
		$query = "SELECT id,name FROM city ORDER BY name";
		$result = db_query($con, $query);

		if ($result && db_num_rows($result)) {
			while ($row = db_fetch_assoc($result)) {
				$cities[] = [
					'value' => $row['id'],
					'label' => $row['name']
				];
			}

			db_free_result($result);
		}

		$fields = [
			[
				'label' => 'City',
				'tag' => 'select',
				'expected' => false,
				'options' => $cities,
				'selected' => $city_id,
				'attributes' => [
					'id' => 'city_id',
					'name' => 'city_id',
					'required' => true,
					'onchange' => 'this.form.submit();'
				]
			]
		];

		?>
		<div class="block">
			<div class="block-content">
				<form class="form-horizontal push-10-t" method="get">
					<div class="row">
						<div class="col-sm-6">
							<?php generate_form_fields($fields); ?>
						</div>
					</div>
				</form>
				<?php

				$query = "SELECT area.id AS area_id,area.name AS area_name,city_id,city.name AS city_name,zip_code,express_delivery,
				(SELECT COUNT(*) FROM shop WHERE shop.area_id=area.id) AS shop_count,(SELECT COUNT(*) FROM user WHERE user.area_id=area.id) AS user_count,
				(SELECT COUNT(*) FROM delivery_staff WHERE delivery_staff.area_id=area.id) AS delivery_staff_count FROM area,city WHERE city_id=city.id AND city_id LIKE '" . $city_id . "' ORDER BY city.name,area.name";
				$result = db_query($con, $query);

				if ($result && db_num_rows($result)) {
					$actions = [
						[
							'type' => 'remove',
							'function' => 'remove_area'
						]
					];

					$role_functions = get_role_functions();
					add_action_buttons($actions, $role_functions);

				?>
					<table width="100%" class="table table-bordered table-striped table-hover js-dataTable-full-pagination">
						<thead>
							<tr>
								<th>City</th>
								<th>Name</th>
								<th>ZIP code</th>
								<th>Express delivery</th>
								<th>Stores</th>
								<th>Delivery staff</th>
								<th>Users</th>
								<th><?php options_column_header(); ?></th>
							</tr>
						</thead>
						<tbody align="center">
							<?php

							while ($row = db_fetch_assoc($result)) {
								if ($row['express_delivery']) $express_delivery = 'Yes';
								else $express_delivery = 'No';

							?>
								<tr>
									<td><a href="admin/cities/add/<?php echo $row['city_id']; ?>" class="link-effect"><?php echo $row['city_name']; ?></a></td>
									<td><?php echo $row['area_name']; ?></td>
									<td><?php echo $row['zip_code']; ?></td>
									<td><?php echo $express_delivery; ?></td>
									<td><?php echo $row['shop_count']; ?></td>
									<td><?php echo $row['delivery_staff_count']; ?></td>
									<td><?php echo $row['user_count']; ?></td>
									<td><?php add_options_button($edit_tag, $row['area_id'], $role_functions); ?></td>
								</tr>
							<?php
							}

							db_free_result($result);

							?>
						</tbody>
					</table>
				<?php
				} else no_data_message();

				close_db_con($con);

				?>
			</div>
		</div>
	</div>
<?php
}

function add_function($get = [], $vars = [])
{
	$form_submit = __FUNCTION__;

	$mode = 'UPDATE';
	$id = 0;
	$name = '';

	if (isset($get['add_id'])) {
		$id = $get['add_id'];

		$con = get_db_con();
		$query = "SELECT * FROM function WHERE id='" . $id . "'";
		$result = db_query($con, $query);

		if ($result && db_num_rows($result) == 1) {
			$row = db_fetch_assoc($result);
			db_free_result($result);

			$name = $row['name'];
		} else $id = 0;

		close_db_con($con);
	}

	$fields = [
		[
			'label' => 'Name',
			'tag' => 'input',
			'attributes' => [
				'id' => 'name',
				'name' => 'name',
				'type' => 'text',
				'value' => $name,
				'maxlength' => 50,
				'required' => true,
				'autofocus' => true
			]
		]
	];

?>
	<div class="content">
		<?php

		$actions = [
			[
				'type' => 'remove',
				'function' => 'remove_function'
			]
		];

		if (!$id) {
			$mode = 'ADD';
			$actions = null;
		}

		add_close_button($mode . ' FUNCTION', $actions);

		?>
		<form class="form-horizontal push-10-t form-notify" action="manage" method="post" autocomplete="off">
			<div class="col-sm-12">
				<div class="block">
					<div class="block-content block-content-narrow">
						<?php

						generate_form_fields($fields);
						add_submit_buttons($form_submit);

						?>
					</div>
				</div>
			</div>
			<input type="hidden" value="<?php echo $id; ?>" name="id" />
		</form>

		<input type="hidden" value="<?php echo $id; ?>" name="values[]" form="options_form" />
	</div>
<?php
}

function list_function($get = [], $vars = [])
{
	$edit_tag = 'functions/add';

?>
	<div class="content">
		<?php add_add_button($edit_tag); ?>
		<div class="block">
			<div class="block-content">
				<?php

				$con = get_db_con();
				$query = "SELECT * FROM function ORDER BY name";
				$result = db_query($con, $query);

				if ($result && db_num_rows($result)) {
					$actions = [
						[
							'type' => 'remove',
							'function' => 'remove_function'
						]
					];

					$role_functions = get_role_functions();
					add_action_buttons($actions, $role_functions);

				?>
					<table width="100%" class="table table-bordered table-striped table-hover js-dataTable-full-pagination">
						<thead>
							<tr>
								<th>Name</th>
								<th><?php options_column_header(); ?></th>
							</tr>
						</thead>
						<tbody align="center">
							<?php

							while ($row = db_fetch_assoc($result)) {
							?>
								<tr>
									<td><?php echo $row['name']; ?></td>
									<td><?php add_options_button($edit_tag, $row['id'], $role_functions); ?></td>
								</tr>
							<?php
							}

							db_free_result($result);

							?>
						</tbody>
					</table>
				<?php
				} else no_data_message();

				close_db_con($con);

				?>
			</div>
		</div>
	</div>
<?php
}

function add_role($get = [], $vars = [])
{
	$form_submit = __FUNCTION__;

	$mode = 'UPDATE';
	$id = 0;
	$role_name = '';
	$selected_functions = [];
	$view_all = false;

	$con = get_db_con();

	if (isset($get['add_id'])) {
		$id = $get['add_id'];

		$query = "SELECT * FROM role WHERE id='" . $id . "'";
		$result = db_query($con, $query);

		if ($result && db_num_rows($result) == 1) {
			$row = db_fetch_assoc($result);
			db_free_result($result);

			$role_name = $row['name'];
			$view_all = boolval($row['view_all']);

			$query = "SELECT * FROM function_per_role WHERE role_id='" . $id . "'";
			$result = db_query($con, $query);

			if ($result && db_num_rows($result)) {
				while ($row = db_fetch_assoc($result)) $selected_functions[] = $row['function_id'];
				db_free_result($result);
			}
		} else $id = 0;
	}

	$fields = [
		[
			'label' => 'Name',
			'tag' => 'input',
			'attributes' => [
				'id' => 'name',
				'name' => 'name',
				'type' => 'text',
				'value' => $role_name,
				'maxlength' => 50,
				'required' => true,
				'autofocus' => true
			]
		],
		[
			'label' => 'Can view all records',
			'tag' => 'checkbox',
			'side_label' => 'Yes',
			'attributes' => [
				'id' => 'view_all',
				'name' => 'view_all',
				'class' => '',
				'type' => 'checkbox',
				'value' => 1,
				'checked' => $view_all
			]
		]
	];

?>
	<div class="content">
		<?php

		$actions = [
			[
				'type' => 'remove',
				'function' => 'remove_role'
			]
		];

		if (!$id) {
			$mode = 'ADD';
			$actions = null;
		}

		add_close_button($mode . ' ROLE', $actions);

		?>
		<form class="form-horizontal push-10-t form-notify" action="manage" method="post" autocomplete="off" id="main_form">
			<div class="col-sm-12">
				<div class="block">
					<div class="block-content block-content-narrow">
						<?php generate_form_fields($fields); ?>
						<div class="form-group">
							<label class="col-xs-12">Functions</label>
							<div class="col-sm-12">
								<div class="form-material form-material-primary">
									<?php

									$query = "SELECT * FROM function ORDER BY name";
									$result = db_query($con, $query);

									if ($result && db_num_rows($result)) {
										while ($row = db_fetch_assoc($result)) {
											if (in_array($row['id'], $selected_functions)) $checked = 'checked';
											else $checked = '';

									?>
											<div class="checkbox">
												<label>
													<input value="<?php echo $row['id']; ?>" type="checkbox" name="functions[]" <?php echo $checked; ?> />
													<?php echo $row['name']; ?>
												</label>
											</div>
									<?php
										}

										db_free_result($result);
									}

									close_db_con($con);

									?>
								</div>
							</div>
						</div>
						<?php add_submit_buttons($form_submit); ?>
					</div>
				</div>
			</div>
			<input type="hidden" value="<?php echo $id; ?>" name="id" />
		</form>

		<input type="hidden" value="<?php echo $id; ?>" name="values[]" form="options_form" />
	</div>
<?php
}

function list_role($get = [], $vars = [])
{
	$edit_tag = 'roles/add';

?>
	<div class="content">
		<?php add_add_button($edit_tag); ?>
		<div class="block">
			<div class="block-content">
				<?php

				$con = get_db_con();
				$query = "SELECT id,name,view_all,(SELECT COUNT(*) FROM admin WHERE admin.role_id=role.id) AS admin_count FROM role ORDER BY name";
				$result = db_query($con, $query);

				if ($result && db_num_rows($result)) {
					$actions = [
						[
							'type' => 'remove',
							'function' => 'remove_role'
						]
					];

					$role_functions = get_role_functions();
					add_action_buttons($actions, $role_functions);

				?>
					<table width="100%" class="table table-bordered table-striped table-hover js-dataTable-full-pagination">
						<thead>
							<tr>
								<th>Name</th>
								<th>View all</th>
								<th>Admins</th>
								<th><?php options_column_header(); ?></th>
							</tr>
						</thead>
						<tbody align="center">
							<?php

							while ($row = db_fetch_assoc($result)) {
								if ($row['view_all']) $view_all = 'Yes';
								else $view_all = 'No';

							?>
								<tr>
									<td><?php echo $row['name']; ?></td>
									<td><?php echo $view_all; ?></td>
									<td><?php echo $row['admin_count']; ?></td>
									<td><?php add_options_button($edit_tag, $row['id'], $role_functions); ?></td>
								</tr>
							<?php
							}

							db_free_result($result);

							?>
						</tbody>
					</table>
				<?php
				} else no_data_message();

				close_db_con($con);

				?>
			</div>
		</div>
	</div>
<?php
}

function add_options($get = [], $vars = [])
{
	$form_submit = __FUNCTION__;

	$id = $opening_time = 0;
	$closing_time = 24;
	$registration_active = $maintenance_mode = $show_imageless_products = false;
	$app_name = $app_email = $url_protocol = $timezone = $date_format = $time_format = $dir_create_flags = $currency_code = $google_api_key = $min_pass_len = $max_rate_value = $registration_role_id = $api_list_mode = $api_ip_list = $jwt_secret_key = $jwt_expiration_time = $payfort_api_url_tokenization = $payfort_api_url_purchase = $payfort_api_url_check_status = $payfort_api_url_refund = $payfort_tokenization_command = $payfort_purchase_command = $payfort_check_status_command = $payfort_refund_command = $payfort_merchant_id = $payfort_access_code = $payfort_language_code = $payfort_currency_code = $payfort_currency_multiplier = $payfort_success_payment_code = $payfort_success_refund_code = $payfort_return_url_tokenization = $payfort_return_url_purchase = $payfort_success_url = $payfort_error_url = $payfort_sha_algo = $payfort_sha_phrase = $max_upload_size = $thumb_width_landscape = $thumb_width_portrait = $image_filter = $video_filter = $maintenance_text = $delivery_fee = $delivery_fee_express = $vat = '';

	$con = get_db_con();
	$query = "SELECT * FROM options";
	$result = db_query($con, $query);

	if ($result && db_num_rows($result) == 1) {
		$row = db_fetch_assoc($result);
		db_free_result($result);

		$id = $row['id'];
		$app_name = $row['app_name'];
		$app_email = strtolower($row['app_email']);
		$url_protocol = $row['url_protocol'];
		$timezone = $row['timezone'];
		$date_format = $row['date_format'];
		$time_format = $row['time_format'];
		$dir_create_flags = $row['dir_create_flags'];
		$currency_code = $row['currency_code'];
		$google_api_key = $row['google_api_key'];
		$min_pass_len = $row['min_pass_len'];
		$max_rate_value = $row['max_rate_value'];
		$delivery_fee = $row['delivery_fee'];
		$delivery_fee_express = $row['delivery_fee_express'];
		$vat = $row['vat'];
		$opening_time = $row['opening_time'];
		$closing_time = $row['closing_time'];
		$show_imageless_products = boolval($row['show_imageless_products']);
		$api_list_mode = $row['api_list_mode'];
		$api_ip_list = $row['api_ip_list'];
		$jwt_secret_key = $row['jwt_secret_key'];
		$jwt_expiration_time = $row['jwt_expiration_time'];
		$payfort_api_url_tokenization = $row['payfort_api_url_tokenization'];
		$payfort_api_url_purchase = $row['payfort_api_url_purchase'];
		$payfort_api_url_check_status = $row['payfort_api_url_check_status'];
		$payfort_api_url_refund = $row['payfort_api_url_refund'];
		$payfort_tokenization_command = $row['payfort_tokenization_command'];
		$payfort_purchase_command = $row['payfort_purchase_command'];
		$payfort_check_status_command = $row['payfort_check_status_command'];
		$payfort_refund_command = $row['payfort_refund_command'];
		$payfort_merchant_id = $row['payfort_merchant_id'];
		$payfort_access_code = $row['payfort_access_code'];
		$payfort_sha_algo = $row['payfort_sha_algo'];
		$payfort_sha_phrase = $row['payfort_sha_phrase'];
		$payfort_language_code = $row['payfort_language_code'];
		$payfort_currency_code = $row['payfort_currency_code'];
		$payfort_currency_multiplier = $row['payfort_currency_multiplier'];
		$payfort_success_payment_code = $row['payfort_success_payment_code'];
		$payfort_success_refund_code = $row['payfort_success_refund_code'];
		$payfort_return_url_tokenization = str_replace(CMS_BASE, '', $row['payfort_return_url_tokenization']);
		$payfort_return_url_purchase = str_replace(CMS_BASE, '', $row['payfort_return_url_purchase']);
		$payfort_success_url = str_replace(CMS_BASE, '', $row['payfort_success_url']);
		$payfort_error_url = str_replace(CMS_BASE, '', $row['payfort_error_url']);
		$max_upload_size = intval($row['max_upload_size'] / (1024 * 1024));
		$thumb_width_landscape = $row['thumb_width_landscape'];
		$thumb_width_portrait = $row['thumb_width_portrait'];
		$image_filter = str_replace(DELIMITER, "\n", $row['image_filter']);
		$video_filter = str_replace(DELIMITER, "\n", $row['video_filter']);
		$registration_active = boolval($row['registration_active']);
		$registration_role_id = $row['registration_role_id'];
		$maintenance_mode = boolval($row['maintenance_mode']);
		$maintenance_text = $row['maintenance_text'];
	}

	$roles = [];

	$query = "SELECT * FROM role ORDER BY name";
	$result = db_query($con, $query);

	if ($result && db_num_rows($result)) {
		while ($row = db_fetch_assoc($result)) {
			$roles[] = [
				'value' => $row['id'],
				'label' => $row['name']
			];
		}

		db_free_result($result);
	}

	close_db_con($con);

	$timezones = timezone_identifiers_list();
	sort($timezones);

	foreach ($timezones as &$value) {
		$value = [
			'value' => $value,
			'label' => $value
		];
	}

	$api_list_modes = [
		[
			'value' => API_LIST_MODE_OFF,
			'label' => 'Off'
		],
		[
			'value' => API_LIST_MODE_WHITE,
			'label' => 'Whitelist'
		],
		[
			'value' => API_LIST_MODE_BLACK,
			'label' => 'Blacklist'
		]
	];

	$url_protocols = [
		[
			'value' => 'http',
			'label' => 'HTTP'
		],
		[
			'value' => 'https',
			'label' => 'HTTPS'
		]
	];

	$languages = [
		[
			'value' => 'en',
			'label' => 'English'
		],
		[
			'value' => 'ar',
			'label' => 'Arabic'
		]
	];

	$sha_algos = [
		[
			'value' => 'sha256',
			'label' => 'SHA-256'
		],
		[
			'value' => 'sha512',
			'label' => 'SHA-512'
		]
	];

	$fields_basic = [
		[
			'label' => 'App name',
			'tag' => 'input',
			'attributes' => [
				'id' => 'app_name',
				'name' => 'app_name',
				'type' => 'text',
				'value' => $app_name,
				'maxlength' => 50,
				'required' => true,
				'autofocus' => true
			]
		],
		[
			'label' => 'App e-mail',
			'tag' => 'input',
			'attributes' => [
				'id' => 'app_email',
				'name' => 'app_email',
				'type' => 'email',
				'value' => $app_email,
				'maxlength' => 50
			]
		],
		[
			'label' => 'Server protocol',
			'tag' => 'select',
			'options' => $url_protocols,
			'selected' => $url_protocol,
			'attributes' => [
				'id' => 'url_protocol',
				'name' => 'url_protocol',
				'required' => true
			]
		],
		[
			'label' => 'Directory create mode (octal format)',
			'tag' => 'input',
			'attributes' => [
				'id' => 'dir_create_flags',
				'name' => 'dir_create_flags',
				'type' => 'text',
				'value' => $dir_create_flags,
				'maxlength' => 4,
				'pattern' => '0[0-7]{3}',
				'required' => true
			]
		],
		[
			'label' => 'Min. password length',
			'tag' => 'input',
			'attributes' => [
				'id' => 'min_pass_len',
				'name' => 'min_pass_len',
				'type' => 'number',
				'value' => $min_pass_len,
				'min' => 1,
				'required' => true
			]
		],
		[
			'label' => 'Show imageless products',
			'tag' => 'checkbox',
			'side_label' => 'Yes',
			'attributes' => [
				'id' => 'show_imageless_products',
				'name' => 'show_imageless_products',
				'class' => '',
				'type' => 'checkbox',
				'value' => 1,
				'checked' => $show_imageless_products
			]
		],
		[
			'label' => 'Google API key',
			'tag' => 'input',
			'attributes' => [
				'id' => 'google_api_key',
				'name' => 'google_api_key',
				'type' => 'text',
				'value' => $google_api_key,
				'maxlength' => 50,
				'required' => true
			]
		]
	];

	$fields_orders = [
		[
			'label' => 'Money currency code',
			'tag' => 'input',
			'attributes' => [
				'id' => 'currency_code',
				'name' => 'currency_code',
				'type' => 'text',
				'value' => $currency_code,
				'maxlength' => 3,
				'required' => true
			]
		],
		[
			'label' => 'VAT',
			'tag' => 'input',
			'group' => [
				'right' => '%'
			],
			'attributes' => [
				'id' => 'vat',
				'name' => 'vat',
				'type' => 'number',
				'value' => $vat,
				'min' => 0,
				'required' => true
			]
		],
		[
			'label' => 'Delivery fee (basic)',
			'tag' => 'input',
			'group' => [
				'right' => CURRENCY_CODE
			],
			'attributes' => [
				'id' => 'delivery_fee',
				'name' => 'delivery_fee',
				'type' => 'number',
				'value' => $delivery_fee,
				'min' => 0,
				'required' => true
			]
		],
		[
			'label' => 'Delivery fee (express)',
			'tag' => 'input',
			'group' => [
				'right' => CURRENCY_CODE
			],
			'attributes' => [
				'id' => 'delivery_fee_express',
				'name' => 'delivery_fee_express',
				'type' => 'number',
				'value' => $delivery_fee_express,
				'min' => 0,
				'required' => true
			]
		],
		[
			'label' => 'Max. rate value',
			'tag' => 'input',
			'attributes' => [
				'id' => 'max_rate_value',
				'name' => 'max_rate_value',
				'type' => 'number',
				'value' => $max_rate_value,
				'min' => 1,
				'required' => true
			]
		]
	];

	$fields_datetime = [
		[
			'label' => 'Timezone',
			'tag' => 'select',
			'options' => $timezones,
			'selected' => $timezone,
			'attributes' => [
				'id' => 'timezone',
				'name' => 'timezone',
				'required' => true
			]
		],
		[
			'label' => 'Date format (<a href="https://php.net/manual/en/function.date.php" target="_blank" rel="external" class="link-effect">help</a>)',
			'tag' => 'input',
			'attributes' => [
				'id' => 'date_format',
				'name' => 'date_format',
				'type' => 'text',
				'value' => $date_format,
				'maxlength' => 15,
				'required' => true
			]
		],
		[
			'label' => 'Time format (<a href="https://php.net/manual/en/function.date.php" target="_blank" rel="external" class="link-effect">help</a>)',
			'tag' => 'input',
			'attributes' => [
				'id' => 'time_format',
				'name' => 'time_format',
				'type' => 'text',
				'value' => $time_format,
				'maxlength' => 5,
				'required' => true
			]
		]
	];

	$fields_registration = [
		[
			'label' => 'Active',
			'tag' => 'checkbox',
			'side_label' => 'Yes',
			'attributes' => [
				'id' => 'registration_active',
				'name' => 'registration_active',
				'class' => '',
				'type' => 'checkbox',
				'value' => 1,
				'checked' => $registration_active
			]
		],
		[
			'label' => 'Registration role',
			'tag' => 'select',
			'options' => $roles,
			'selected' => $registration_role_id,
			'attributes' => [
				'id' => 'registration_role_id',
				'name' => 'registration_role_id',
				'required' => true
			]
		]
	];

	$fields_api_filter = [
		[
			'label' => 'Mode',
			'tag' => 'select',
			'options' => $api_list_modes,
			'selected' => $api_list_mode,
			'attributes' => [
				'id' => 'api_list_mode',
				'name' => 'api_list_mode',
				'required' => true
			]
		],
		[
			'label' => 'IP list (regex pattern, each in new line)',
			'tag' => 'textarea',
			'value' => $api_ip_list,
			'attributes' => [
				'id' => 'api_ip_list',
				'name' => 'api_ip_list',
				'maxlength' => 1000,
				'rows' => 10,
				'cols' => 50
			]
		]
	];

	$fields_jwt = [
		[
			'label' => 'Secret key',
			'tag' => 'input',
			'attributes' => [
				'id' => 'jwt_secret_key',
				'name' => 'jwt_secret_key',
				'type' => 'text',
				'value' => $jwt_secret_key,
				'maxlength' => 128,
				'required' => true
			]
		],
		[
			'label' => 'Expiration time',
			'tag' => 'input',
			'group' => [
				'right' => 'min'
			],
			'attributes' => [
				'id' => 'jwt_expiration_time',
				'name' => 'jwt_expiration_time',
				'type' => 'number',
				'value' => $jwt_expiration_time,
				'min' => 1,
				'required' => true
			]
		]
	];

	$fields_payfort = [
		[
			'label' => 'Tokenization request API URL',
			'tag' => 'input',
			'attributes' => [
				'id' => 'payfort_api_url_tokenization',
				'name' => 'payfort_api_url_tokenization',
				'type' => 'url',
				'value' => $payfort_api_url_tokenization,
				'maxlength' => 1000,
				'required' => true
			]
		],
		[
			'label' => 'Tokenization return URL',
			'tag' => 'input',
			'group' => [
				'left' => CMS_BASE
			],
			'attributes' => [
				'id' => 'payfort_return_url_tokenization',
				'name' => 'payfort_return_url_tokenization',
				'type' => 'text',
				'value' => $payfort_return_url_tokenization,
				'maxlength' => 1000,
				'required' => true
			]
		],
		[
			'label' => 'Tokenization command',
			'tag' => 'input',
			'attributes' => [
				'id' => 'payfort_tokenization_command',
				'name' => 'payfort_tokenization_command',
				'type' => 'text',
				'value' => $payfort_tokenization_command,
				'maxlength' => 15,
				'required' => true
			]
		],
		[
			'label' => 'Purchase request API URL',
			'tag' => 'input',
			'attributes' => [
				'id' => 'payfort_api_url_purchase',
				'name' => 'payfort_api_url_purchase',
				'type' => 'url',
				'value' => $payfort_api_url_purchase,
				'maxlength' => 1000,
				'required' => true
			]
		],
		[
			'label' => 'Purchase return URL',
			'tag' => 'input',
			'group' => [
				'left' => CMS_BASE
			],
			'attributes' => [
				'id' => 'payfort_return_url_purchase',
				'name' => 'payfort_return_url_purchase',
				'type' => 'text',
				'value' => $payfort_return_url_purchase,
				'maxlength' => 1000,
				'required' => true
			]
		],
		[
			'label' => 'Purchase command',
			'tag' => 'input',
			'attributes' => [
				'id' => 'payfort_purchase_command',
				'name' => 'payfort_purchase_command',
				'type' => 'text',
				'value' => $payfort_purchase_command,
				'maxlength' => 15,
				'required' => true
			]
		],
		[
			'label' => 'Check status request API URL',
			'tag' => 'input',
			'attributes' => [
				'id' => 'payfort_api_url_check_status',
				'name' => 'payfort_api_url_check_status',
				'type' => 'url',
				'value' => $payfort_api_url_check_status,
				'maxlength' => 1000,
				'required' => true
			]
		],
		[
			'label' => 'Check status command',
			'tag' => 'input',
			'attributes' => [
				'id' => 'payfort_check_status_command',
				'name' => 'payfort_check_status_command',
				'type' => 'text',
				'value' => $payfort_check_status_command,
				'maxlength' => 15,
				'required' => true
			]
		],
		[
			'label' => 'Refund request API URL',
			'tag' => 'input',
			'attributes' => [
				'id' => 'payfort_api_url_refund',
				'name' => 'payfort_api_url_refund',
				'type' => 'url',
				'value' => $payfort_api_url_refund,
				'maxlength' => 1000,
				'required' => true
			]
		],
		[
			'label' => 'Refund command',
			'tag' => 'input',
			'attributes' => [
				'id' => 'payfort_refund_command',
				'name' => 'payfort_refund_command',
				'type' => 'text',
				'value' => $payfort_refund_command,
				'maxlength' => 15,
				'required' => true
			]
		],
		[
			'label' => 'Payment success URL',
			'tag' => 'input',
			'group' => [
				'left' => CMS_BASE
			],
			'attributes' => [
				'id' => 'payfort_success_url',
				'name' => 'payfort_success_url',
				'type' => 'text',
				'value' => $payfort_success_url,
				'maxlength' => 1000,
				'required' => true
			]
		],
		[
			'label' => 'Payment error URL',
			'tag' => 'input',
			'group' => [
				'left' => CMS_BASE
			],
			'attributes' => [
				'id' => 'payfort_error_url',
				'name' => 'payfort_error_url',
				'type' => 'text',
				'value' => $payfort_error_url,
				'maxlength' => 1000,
				'required' => true
			]
		],
		[
			'label' => 'Merchant ID',
			'tag' => 'input',
			'attributes' => [
				'id' => 'payfort_merchant_id',
				'name' => 'payfort_merchant_id',
				'type' => 'text',
				'value' => $payfort_merchant_id,
				'maxlength' => 10,
				'required' => true
			]
		],
		[
			'label' => 'Access code',
			'tag' => 'input',
			'attributes' => [
				'id' => 'payfort_access_code',
				'name' => 'payfort_access_code',
				'type' => 'text',
				'value' => $payfort_access_code,
				'maxlength' => 50,
				'required' => true
			]
		],
		[
			'label' => 'Language',
			'tag' => 'select',
			'options' => $languages,
			'selected' => $payfort_language_code,
			'attributes' => [
				'id' => 'payfort_language_code',
				'name' => 'payfort_language_code',
				'required' => true
			]
		],
		[
			'label' => 'Currency ISO code',
			'tag' => 'input',
			'attributes' => [
				'id' => 'payfort_currency_code',
				'name' => 'payfort_currency_code',
				'type' => 'text',
				'value' => $payfort_currency_code,
				'maxlength' => 3,
				'pattern' => '[A-Z]{3}',
				'required' => true
			]
		],
		[
			'label' => 'Currency amount multiplier',
			'tag' => 'input',
			'attributes' => [
				'id' => 'payfort_currency_multiplier',
				'name' => 'payfort_currency_multiplier',
				'type' => 'number',
				'value' => $payfort_currency_multiplier,
				'min' => 2,
				'max' => 3,
				'required' => true
			]
		],
		[
			'label' => 'Success payment status code',
			'tag' => 'input',
			'attributes' => [
				'id' => 'payfort_success_payment_code',
				'name' => 'payfort_success_payment_code',
				'type' => 'text',
				'value' => $payfort_success_payment_code,
				'maxlength' => 2,
				'pattern' => '[0-9]{2}',
				'required' => true
			]
		],
		[
			'label' => 'Success refund status code',
			'tag' => 'input',
			'attributes' => [
				'id' => 'payfort_success_refund_code',
				'name' => 'payfort_success_refund_code',
				'type' => 'text',
				'value' => $payfort_success_refund_code,
				'maxlength' => 2,
				'pattern' => '[0-9]{2}',
				'required' => true
			]
		],
		[
			'label' => 'SHA algo',
			'tag' => 'select',
			'options' => $sha_algos,
			'selected' => $payfort_sha_algo,
			'attributes' => [
				'id' => 'payfort_sha_algo',
				'name' => 'payfort_sha_algo',
				'required' => true
			]
		],
		[
			'label' => 'SHA passphrase',
			'tag' => 'input',
			'attributes' => [
				'id' => 'payfort_sha_phrase',
				'name' => 'payfort_sha_phrase',
				'type' => 'text',
				'value' => $payfort_sha_phrase,
				'maxlength' => 50,
				'required' => true
			]
		]
	];

	$fields_media = [
		[
			'label' => 'Max. upload size (per file)',
			'tag' => 'input',
			'group' => [
				'right' => 'MB'
			],
			'attributes' => [
				'id' => 'max_upload_size',
				'name' => 'max_upload_size',
				'type' => 'number',
				'value' => $max_upload_size,
				'min' => 1,
				'required' => true
			]
		],
		[
			'label' => 'Thumbnail width - landscape',
			'tag' => 'input',
			'group' => [
				'right' => 'px'
			],
			'attributes' => [
				'id' => 'thumb_width_landscape',
				'name' => 'thumb_width_landscape',
				'type' => 'number',
				'value' => $thumb_width_landscape,
				'min' => 300,
				'max' => 1600,
				'required' => true
			]
		],
		[
			'label' => 'Thumbnail width - portrait',
			'tag' => 'input',
			'group' => [
				'right' => 'px'
			],
			'attributes' => [
				'id' => 'thumb_width_portrait',
				'name' => 'thumb_width_portrait',
				'type' => 'number',
				'value' => $thumb_width_portrait,
				'min' => 300,
				'max' => 1600,
				'required' => true
			]
		],
		[
			'label' => 'Image filter (each extension in new line)',
			'tag' => 'textarea',
			'value' => $image_filter,
			'attributes' => [
				'id' => 'image_filter',
				'name' => 'image_filter',
				'maxlength' => 500,
				'rows' => 10,
				'cols' => 50
			]
		],
		[
			'label' => 'Video filter (each extension in new line)',
			'tag' => 'textarea',
			'value' => $video_filter,
			'attributes' => [
				'id' => 'video_filter',
				'name' => 'video_filter',
				'maxlength' => 500,
				'rows' => 10,
				'cols' => 50
			]
		]
	];

	$fields_maintenance_mode = [
		[
			'label' => 'Active',
			'tag' => 'checkbox',
			'side_label' => 'Yes',
			'attributes' => [
				'id' => 'maintenance_mode',
				'name' => 'maintenance_mode',
				'class' => '',
				'type' => 'checkbox',
				'value' => 1,
				'checked' => $maintenance_mode
			]
		],
		[
			'label' => 'Message',
			'tag' => 'textarea',
			'value' => $maintenance_text,
			'attributes' => [
				'id' => 'maintenance_text',
				'name' => 'maintenance_text',
				'maxlength' => 500,
				'rows' => 10,
				'cols' => 50
			]
		]
	];

	$sms_fields =
		[
			[
				'label' => 'SMS Message',
				'tag' => 'textarea',
				"value" => mysqli_fetch_row(mysqli_query($con, "SELECT sms FROM options"))[0],
				'attributes' => [
					'id' => 'send_sms',
					'name' => 'sms',
					'maxlength' => 250,
					'rows' => 10,
					'cols' => 50
				]
			]
		];


?>
	<div class="content">
		<?php add_close_button('OPTIONS'); ?>

		<form class="form-horizontal push-10-t form-notify" action="manage" method="post" autocomplete="off">
			<div class="col-sm-12">
				<div class="block">
					<div class="block-content block-content-narrow">
						<ul class="nav nav-tabs nav-tabs-alt" data-toggle="tabs">
							<li class="active">
								<a href="#btabs-alt-static-basic">Basic</a>
							</li>
							<li>
								<a href="#btabs-alt-static-datetime">Date & time</a>
							</li>
							<li>
								<a href="#btabs-alt-static-orders">Orders</a>
							</li>
							<li>
								<a href="#btabs-alt-static-registration">Registration</a>
							</li>
							<li>
								<a href="#btabs-alt-static-api-filter">API filter</a>
							</li>
							<li>
								<a href="#btabs-alt-static-jwt">JWT</a>
							</li>
							<li>
								<a href="#btabs-alt-static-payfort">PayFort</a>
							</li>
							<li>
								<a href="#btabs-alt-static-media">Media</a>
							</li>

							<li>
								<a href="#btabs-alt-static-sms">SMS</a>
							</li>

							<li>
								<a href="#btabs-alt-static-maintenance-mode">Maintenance mode</a>
							</li>

						</ul>

						<div class="block-content tab-content">
							<div class="tab-pane active" id="btabs-alt-static-basic">
								<?php generate_form_fields($fields_basic); ?>
							</div>
							<div class="tab-pane" id="btabs-alt-static-datetime">
								<?php generate_form_fields($fields_datetime); ?>
							</div>
							<div class="tab-pane" id="btabs-alt-static-orders">
								<?php generate_form_fields($fields_orders); ?>
								<div class="form-group">
									<div class="col-sm-12">
										<div class="form-material form-material-primary">
											<div class="input-group">
												<input class="form-control" type="number" value="<?php echo $opening_time; ?>" id="opening_time" name="opening_time" required min="0" max="24" />
												<span class="input-group-addon"><i class="fa fa-chevron-right"></i></span>
												<input class="form-control" type="number" value="<?php echo $closing_time; ?>" id="closing_time" name="closing_time" required min="0" max="24" />
											</div>
											<label for="opening_time">Working hours</label>
										</div>
									</div>
								</div>
							</div>
							<div class="tab-pane" id="btabs-alt-static-registration">
								<?php generate_form_fields($fields_registration); ?>
							</div>
							<div class="tab-pane" id="btabs-alt-static-api-filter">
								<?php generate_form_fields($fields_api_filter); ?>
							</div>
							<div class="tab-pane" id="btabs-alt-static-jwt">
								<?php generate_form_fields($fields_jwt); ?>
							</div>
							<div class="tab-pane" id="btabs-alt-static-payfort">
								<?php generate_form_fields($fields_payfort); ?>
							</div>
							<div class="tab-pane" id="btabs-alt-static-media">
								<?php generate_form_fields($fields_media); ?>
							</div>

							<div class="tab-pane" id="btabs-alt-static-sms">
								<?php generate_form_fields($sms_fields); ?>
							</div>

							<div class="tab-pane" id="btabs-alt-static-maintenance-mode">
								<?php generate_form_fields($fields_maintenance_mode); ?>
							</div>
							<?php add_submit_buttons($form_submit); ?>
						</div>

					</div>
				</div>
			</div>

			<input type="hidden" value="opening_time" name="expected_fields[]" />
			<input type="hidden" value="closing_time" name="expected_fields[]" />
			<input type="hidden" value="<?php echo $id; ?>" name="id" />
		</form>
	</div>
<?php
}
function style_order_state($state_name, $state_color)
{
	return '<mark style="color: ' . trim($state_color) . ';"><b>' . strtoupper(trim($state_name)) . '</b></mark>';
}

function change_order_state_modal()
{
	if (!$_SESSION['admin_view_all']) $admin_id = $_SESSION['admin_id'];
	else $admin_id = '%';

	$order_states = $delivery_staff = [
		[
			'value' => '',
			'label' => '-'
		]
	];

	$con = get_db_con();
	$query = "SELECT id,name FROM order_states WHERE state_num>(SELECT MIN(state_num) FROM order_states) ORDER BY state_num";
	$result = db_query($con, $query);

	if ($result && db_num_rows($result)) {
		while ($row = db_fetch_assoc($result)) {
			$order_states[] = [
				'value' => $row['id'],
				'label' => $row['name']
			];
		}

		db_free_result($result);
	}

	$query = "SELECT delivery_staff.id AS delivery_staff_id,city.name AS city_name,area.name AS area_name,full_name FROM delivery_staff,city,area WHERE 
	admin_id LIKE '" . $admin_id . "' AND city_id=city.id AND area_id=area.id AND active='1' ORDER BY city.name,area.name,full_name";
	$result = db_query($con, $query);

	if ($result && db_num_rows($result)) {
		while ($row = db_fetch_assoc($result)) {
			$delivery_staff[$row['city_name'] . ' - ' . $row['area_name']][] = [
				'value' => $row['delivery_staff_id'],
				'label' => $row['full_name']
			];
		}

		db_free_result($result);
	}

	close_db_con($con);

	$fields = [
		[
			'label' => 'Order state',
			'tag' => 'select',
			'expected' => false,
			'options' => $order_states,
			'attributes' => [
				'id' => 'order_state_id',
				'name' => 'order_state_id',
				'form' => 'options_form',
				'required' => true
			]
		],
		[
			'label' => 'Delivery staff',
			'tag' => 'select',
			'expected' => false,
			'options' => $delivery_staff,
			'attributes' => [
				'id' => 'delivery_staff_id',
				'name' => 'delivery_staff_id',
				'form' => 'options_form',
				'required' => true
			]
		]
	];

?>
	<div class="modal fade" id="modal_change_order_state" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-dialog-popin">
			<div class="modal-content">
				<div class="block block-themed block-transparent remove-margin-b">
					<div class="block-header bg-primary-dark">
						<ul class="block-options">
							<li>
								<button data-dismiss="modal" type="button"><i class="si si-close"></i></button>
							</li>
						</ul>
						<h3 class="block-title">Change order state</h3>
					</div>
					<div class="block-content">
						<div class="row">
							<?php generate_form_fields($fields); ?>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button class="btn btn-sm btn-default" type="button" data-dismiss="modal"><i class="si si-close"></i> Cancel</button>
					<button class="btn btn-sm btn-primary" type="submit" form="options_form" name="change_order_state"><i class="fa fa-check"></i> Change</button>
				</div>
			</div>
		</div>
	</div>
<?php
}

function change_delivery_staff_area_modal()
{
	$areas = [
		[
			'value' => '',
			'label' => '-'
		]
	];

	$con = get_db_con();
	$query = "SELECT area.id AS area_id,city.name AS city_name,area.name AS area_name FROM city,area WHERE city_id=city.id ORDER BY city.name,area.name";
	$result = db_query($con, $query);

	if ($result && db_num_rows($result)) {
		while ($row = db_fetch_assoc($result)) {
			$areas[$row['city_name']][] = [
				'value' => $row['area_id'],
				'label' => $row['area_name']
			];
		}

		db_free_result($result);
	}

	close_db_con($con);

	$fields = [
		[
			'label' => 'Area',
			'tag' => 'select',
			'options' => $areas,
			'expected' => false,
			'attributes' => [
				'id' => 'area_id',
				'name' => 'area_id',
				'form' => 'options_form',
				'required' => true
			]
		]
	];

?>
	<div class="modal fade" id="modal_change_delivery_staff_area" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-dialog-popin">
			<div class="modal-content">
				<div class="block block-themed block-transparent remove-margin-b">
					<div class="block-header bg-primary-dark">
						<ul class="block-options">
							<li>
								<button data-dismiss="modal" type="button"><i class="si si-close"></i></button>
							</li>
						</ul>
						<h3 class="block-title">Change delivery staff area</h3>
					</div>
					<div class="block-content">
						<div class="row">
							<?php generate_form_fields($fields); ?>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button class="btn btn-sm btn-default" type="button" data-dismiss="modal"><i class="si si-close"></i> Cancel</button>
					<button class="btn btn-sm btn-primary" type="submit" form="options_form" name="change_delivery_staff_area"><i class="fa fa-check"></i> Change</button>
				</div>
			</div>
		</div>
	</div>
<?php
}

?>