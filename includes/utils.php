<?php

require_once('config.php');
ini_set("dispaly_startup_errors", true);
ini_set("display_erros", true);
error_reporting(E_ALL);


function tech_info($get = [], $vars = [])
{
?>
	<div class="content">
		<?php

		add_add_button('tech-info', false);

		$db_size = $db_count = 0;

		$con = get_db_con();
		$query = "SHOW TABLE STATUS";
		$result = db_query($con, $query);

		if ($result && ($db_count = db_num_rows($result))) {
			while ($row = db_fetch_assoc($result)) $db_size += $row['Data_length'] + $row['Index_length'];
			db_free_result($result);

			$db_size = round($db_size / (1024 * 1024), 2);
		}

		$mysql_host = db_get_host_info($con);
		$mysql_ver = db_get_server_info($con);
		close_db_con($con);

		$process_user = get_current_user();

		if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS'])) $https = 'Yes';
		else $https = 'No';

		$disk_total = disk_total_space(getcwd());
		$disk_taken = $disk_total - disk_free_space(getcwd());

		$disk_taken_perc = round((100 * $disk_taken) / $disk_total, 2);
		$disk_taken = round($disk_taken / (1024 * 1024 * 1024), 2);
		$disk_total = round($disk_total / (1024 * 1024 * 1024), 2);

		$apache_modules = apache_get_modules();
		$apache_modules_count = count($apache_modules);
		sort($apache_modules);

		$php_exts = get_loaded_extensions();
		$php_exts_count = count($php_exts);
		sort($php_exts);

		$os = php_uname('s') . ' ' . php_uname('r') . ' ' . php_uname('v') . ' ' . php_uname('m');
		$memory = round(memory_get_usage(true) / (1024 * 1024), 2);
		if (function_exists('sys_getloadavg')) $cpu = round(sys_getloadavg()[0] * 100, 2);

		?>
		<div class="block">
			<div class="block-content">
				<ul class="nav nav-tabs nav-tabs-alt" data-toggle="tabs">
					<li class="active">
						<a href="#btabs-alt-static-basic">Basic</a>
					</li>
					<li>
						<a href="#btabs-alt-static-modules">Modules</a>
					</li>
				</ul>
				<div class="block-content tab-content">
					<div class="tab-pane active" id="btabs-alt-static-basic">
						<table width="100%" class="table table-bordered table-striped table-hover">
							<tbody align="center">
								<tr>
									<td>Server name:</td>
									<td><?php echo $_SERVER['SERVER_NAME']; ?></td>
								</tr>
								<tr>
									<td>Server software:</td>
									<td><?php echo $_SERVER['SERVER_SOFTWARE'] . ' via ' . PHP_SAPI; ?></td>
								</tr>
								<tr>
									<td>IP address and protocol:</td>
									<td><?php echo $_SERVER['SERVER_ADDR'] . ':' . $_SERVER['SERVER_PORT'] . ' via ' . $_SERVER['SERVER_PROTOCOL']; ?></td>
								</tr>
								<tr>
									<td>Secure connection (HTTPS):</td>
									<td><?php echo $https; ?></td>
								</tr>
								<tr>
									<td>PHP version:</td>
									<td><?php echo PHP_VERSION; ?></td>
								</tr>
								<tr>
									<td>PHP user:</td>
									<td><?php echo $process_user; ?></td>
								</tr>
								<tr>
									<td>MySQL server:</td>
									<td><?php echo $mysql_host; ?></td>
								</tr>
								<tr>
									<td>MySQL version:</td>
									<td><?php echo $mysql_ver; ?></td>
								</tr>
								<tr>
									<td>OS:</td>
									<td><?php echo $os; ?></td>
								</tr>
								<tr>
									<td>Disk usage:</td>
									<td><?php echo $disk_taken; ?> / <?php echo $disk_total; ?> GB (<?php echo $disk_taken_perc; ?>%)</td>
								</tr>
								<tr>
									<td>Database size:</td>
									<td><?php echo $db_size; ?> MB (<?php echo $db_count; ?> tables)</td>
								</tr>
								<tr>
									<td>Allocated memory (RAM):</td>
									<td><?php echo $memory; ?> MB</td>
								</tr>
								<?php

								if (function_exists('sys_getloadavg')) {
								?>
									<tr>
										<td>CPU usage:</td>
										<td><?php echo $cpu; ?>%</td>
									</tr>
								<?php
								}

								?>
							</tbody>
						</table>
					</div>
					<div class="tab-pane" id="btabs-alt-static-modules">
						<div class="col-sm-6">
							<h3>Loaded Apache server modules (<?php echo $apache_modules_count; ?>)</h3>
							<br />
							<ul>
								<?php

								foreach ($apache_modules as $value) {
								?>
									<li><?php echo $value; ?></li>
								<?php
								}

								?>
							</ul>
						</div>
						<div class="col-sm-6">
							<h3>Loaded PHP extensions (<?php echo $php_exts_count; ?>)</h3>
							<br />
							<ul>
								<?php

								foreach ($php_exts as $value) {
								?>
									<li><?php echo $value; ?></li>
								<?php
								}

								?>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php
}

function purge_db_tables($get = [], $vars = [])
{
	$form_submit = __FUNCTION__;

?>
	<div class="content">
		<?php add_close_button('PURGE DATABASE TABLES'); ?>
		<form class="form-horizontal push-10-t form-notify" action="manage" method="post" autocomplete="off">
			<div class="col-sm-12">
				<div class="block">
					<div class="block-content block-content-narrow">
						<div class="form-group">
							<label class="col-xs-12">Tables</label>
							<div class="col-sm-12">
								<div class="form-material form-material-primary">
									<?php

									$con = get_db_con();
									$query = "SHOW TABLES";
									$result = db_query($con, $query);

									if ($result && db_num_rows($result)) {
										while ($row = db_fetch_row($result)) {
									?>
											<div class="checkbox">
												<label>
													<input value="<?php echo $row[0]; ?>" type="checkbox" name="tables[]" />
													<?php echo $row[0]; ?>
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
		</form>
	</div>
<?php
}

function default_page()
{
?>
	<!-- Error Content -->
	<div class="content bg-white text-center pulldown overflow-hidden">
		<div class="row">
			<div class="col-sm-6 col-sm-offset-3">
				<!-- Error Titles -->
				<h1 class="font-s128 font-w300 text-flat animated bounceIn">403</h1>
				<h2 class="h3 font-w300 push-50 animated fadeInUp">We are sorry but you do not have permission to access this page.</h2>
				<!-- END Error Titles -->
			</div>
		</div>
	</div>
	<!-- END Error Content -->
<?php
}

function no_data_message()
{
?>
	<p><mark>No data.</mark></p>
<?php
}

function generate_dashboard_tiles($tiles, $role_functions = null)
{

	if (!is_array($tiles)) return;
	if (!$role_functions) $role_functions = get_role_functions();

?>
	<div class="row">
		<div class="content-grid">
			<?php


			foreach ($tiles as $tile) {
				$path = trim($tile['path']);

				$function = process_api_call(['path' => $path]);
				if ($function) $function = $function['function'];
						
				if (!in_array($function, $role_functions)) return;

				$label = trim($tile['label']);
				$icon = trim($tile['icon']);
				$bg_class = trim($tile['bg_class']);
				$count = trim($tile['count']);

				if (isset($tile['url_extra'])) $url_extra = trim($tile['url_extra']);
				else $url_extra = '';

			?>
				<div class="col-sm-6 col-lg-4">
					<a class="block block-bordered block-link-hover3 text-center" href="admin/<?php echo $path; ?>">
						<div class="block-content bg-gray-lighter border-b">
							<div class="h1 font-w700"><?php echo $count; ?></div>
							<div class="h5 text-muted text-uppercase push-5-t"><?php echo $label; ?></div>
						</div>
						<div class="block-content block-content-full block-content-mini <?php echo $bg_class; ?> text-white">
							<i class="<?php echo $icon; ?>"></i>
						</div>
					</a>
				</div>
			<?php
			}
			?>
		</div>
	</div>
<?php
}

function generate_dashboard_graphs($graphs)
{
	if (!is_array($graphs)) return;

?>
	<div class="row">
		<?php

		foreach ($graphs as $graph) {
			$graph_title = strtoupper($graph['title']);
			$graph_area_id = $graph['graph_area_id'];

		?>
			<div class="col-sm-6">
				<h3><?php echo $graph_title; ?></h3>
				<div class="row push-10">
					<div class="graph-area" id="<?php echo $graph_area_id; ?>">
						<?php no_data_message(); ?>
					</div>
				</div>
			</div>
		<?php
		}

		?>
	</div>
	<?php
}

function generate_form_fields($fields)
{
	if (!is_array($fields)) return;
	foreach ($fields as $field) {

		$tag = strtolower(trim($field['tag']));
		$label = trim($field['label']);
		$attributes = $field['attributes'];
		$id = $attributes['id'];

		if (isset($attributes['name'])) $name = $attributes['name'];
		else $name = null;

		if (isset($field['className'])) $divClass = trim($field['className']);
		else $divClass = 'col-sm-12';

		if (isset($field['expected'])) $expected = boolval($field['expected']);
		else $expected = true;

		if (!isset($attributes['class'])) $attributes['class'] = 'form-control';
		else if (!empty($attributes['class'])) $attributes['class'] .= ' form-control';

		if (isset($attributes['maxlength'])) $attributes['class'] .= ' js-maxlength';

		foreach ($attributes as $key => &$value) {
			if (is_bool($value)) {
				
				if ($value) $value = $key;
				else continue;
			} else $value = $key . '="' . $value . '"';
		}

		$attributes = implode(' ', $attributes);
		switch ($tag) {
			case 'input': {
	?>
					<div class="form-group">
						<div class="<?php echo $divClass; ?>">
							<div class="form-material form-material-primary">
								<?php

								if (isset($field['group'])) {
									$field_group = $field['group'];

								?>
									<div class="input-group">
										<?php

										if (isset($field_group['left'])) {
										?>
											<span class="input-group-addon"><?php echo $field_group['left']; ?></span>
										<?php
										}

										?>
										<input <?php echo $attributes; ?> data-always-show="true" />
										<?php

										if (isset($field_group['right'])) {
										?>
											<span class="input-group-addon"><?php echo $field_group['right']; ?></span>
										<?php
										}

										?>
									</div>
								<?php
								} else {
								?>
									<input <?php echo $attributes; ?> data-always-show="true" />
								<?php
								}

								?>
								<label for="<?php echo $id; ?>"><?php echo $label; ?></label>
							</div>
						</div>
					</div>
				<?php

					break;
				}
			case 'textarea': {
					$value = trim($field['value']);

				?>
					<div class="form-group">
						<div class="<?php echo $divClass; ?>">
							<div class="form-material form-material-primary">
								<textarea <?php echo $attributes; ?> data-always-show="true" onblur="this.value = this.value.trim();"><?php echo $value; ?></textarea>
								<label for="<?php echo $id; ?>"><?php echo $label; ?></label>
							</div>
						</div>
					</div>
				<?php

					break;
				}
			case 'checkbox':
			case 'radio': {
					$side_label = trim($field['side_label']);

				?>
					<div class="form-group">
						<label class="<?php echo $divClass; ?>"><?php echo $label; ?></label>
						<div class="<?php echo $divClass; ?>">
							<label class="css-input css-<?php echo $tag; ?> css-<?php echo $tag; ?>-primary">
								<input <?php echo $attributes; ?> /><span></span> <?php echo $side_label; ?>
							</label>
						</div>
					</div>
				<?php

					break;
				}
			case 'select': {
					$options = $selected = [];

					if (isset($field['options'])) $options = $field['options'];
					if (isset($field['selected'])) $selected = (array) $field['selected'];

				?>
					<div class="form-group">
						<div class="<?php echo $divClass; ?>">
							<div class="form-material form-material-primary">
								<select <?php echo $attributes; ?>>
									<?php generate_select_field($options, $selected); ?>
								</select>
								<label for="<?php echo $id; ?>"><?php echo $label; ?></label>
							</div>
						</div>
					</div>
			<?php

					break;
				}
		}

		if ($expected && $name) {
			?>
			<input type="hidden" name="expected_fields[]" value="<?php echo $name; ?>" />
		<?php
		}
	}
}

function generate_select_field($options, $selected)
{
	# key => ['value', 'lable']
	foreach ($options as $key => $option) {
		if (is_string($key)) {
		?>
			<optgroup label="<?php echo $key; ?>">
				<?php generate_select_field($option, $selected); ?>
			</optgroup>
		<?php
		} else {
			$value = $option['value'];
			$opt_label = trim($option['label']);

			if (in_array($value, $selected)) $checked = 'selected';
			else $checked = '';

		?>
			<option value="<?= $value ?>" <?= $checked ?> > <?= $opt_label ?> </option>
	<?php
		}
	}
}

function get_role_functions($role_id = null)
{
	static $functions = [];
	if (!empty($functions)) return $functions;

	if (!$role_id) $role_id = $_SESSION['admin_role_id'];

	$con = get_db_con();
	$query = "SELECT `function`.name AS function_name FROM `function`,function_per_role WHERE `function`.id=function_id AND role_id=6";
	$result = db_query($con, $query);

	if ($result && db_num_rows($result)) {
		while ($row = db_fetch_assoc($result)) $functions[] = $row['function_name'];
		db_free_result($result);
	}

	close_db_con($con);
	return $functions;
}


function generate_navbar($tab, $role_functions = null)
{
	//var_dump($role_functions);
	if (!$role_functions) $role_functions = get_role_functions();
	// var_dump($tab);
	$pages = [
		[
			'type' => 'divider',
			'title' => 'PAGES'
		],
		[
			'title' => 'Dashboard',
			'icon_class' => 'si si-grid',
			'function' => 'dashboard',
			'path' => 'dashboard'
		],
		[
			'title' => 'Orders',
			'icon_class' => 'si si-calendar',
			'function' => 'list_order',
			'path' => 'orders'
		],
			[
			'title' => 'Coupons',
			'icon_class' => 'si si-calendar',
			'function' => 'coupons',
			'remove_admin_from_path' => true,
			'path' => 'counponindex',
		],
			[
			'title' => 'Banners',
			'icon_class' => 'si si-calendar',
			'function' => 'banners',
			'remove_admin_from_path' => true,
			'path' => 'banner'
		],	
		[
			'title' => 'Service Charge',
			'icon_class' => 'si si-calendar',
			'function' => 'ser_charge',
			'remove_admin_from_path' => true,
			'path' => 'charge'
		],
		
		[
			'title' => 'Order states',
			'icon_class' => 'fa fa-list-ol',
			'items' => [
				[
					'title' => 'Add order state',
					'function' => 'add_order_state',
					'path' => 'orders/states/add'
				],
				[
					'title' => 'List order states',
					'function' => 'list_order_state',
					'path' => 'orders/states'
				]
			]
		],

		[
			'title' => 'Stores',
			'icon_class' => 'fa fa-shopping-basket',
			'items' => [
				[
					'title' => 'Add store type',
					'function' => 'add_shop_type',
					'path' => 'stores/types/add'
				],
				[
					'title' => 'List store types',
					'function' => 'list_shop_type',
					'path' => 'stores/types'
				],
				[
					'title' => 'Add store',
					'function' => 'add_shop',
					'path' => 'stores/add'
				],
				[
					'title' => 'List stores',
					'function' => 'list_shop',
					'path' => 'stores'
				]
			]
		],
		[
			'title' => 'Suppliers',
			'icon_class' => 'fa fa-industry',
			'items' => [
				[
					'title' => 'Add supplier',
					'function' => 'add_supplier',
					'path' => 'suppliers/add'
				],
				[
					'title' => 'List suppliers',
					'function' => 'list_supplier',
					'path' => 'suppliers'
				]
			]
		],
		[
			'title' => 'Products',
			'icon_class' => 'fa fa-cubes',
			'items' => [
				[
					'title' => 'Add product',
					'function' => 'add_product',
					'path' => 'products/add'
				],
				[
					'title' => 'List products',
					'function' => 'list_product',
					'path' => 'products'
				],
				[
					'title' => 'Import from files',
					'function' => 'add_product_import',
					'path' => 'products/imports/add'
				],
				[
					'title' => 'Imports log',
					'function' => 'list_product_import',
					'path' => 'products/imports'
				]
			]
		],
		[
			'title' => 'Categories',
			'icon_class' => 'fa fa-sitemap',
			'items' => [
				[
					'title' => 'Add category',
					'function' => 'add_category',
					'path' => 'categories/add'
				],
				[
					'title' => 'List categories',
					'function' => 'list_category',
					'path' => 'categories'
				]
			]
		],
		[
			'title' => 'Dashboard',
			'icon_class' => 'si si-grid',
			'function' => 'add_coupon',
			'path' => 'dashboard'
		],
		[
			'title' => 'Locations',
			'icon_class' => 'si si-pointer',
			'items' => [
				[
					'title' => 'Add city',
					'function' => 'add_city',
					'path' => 'cities/add'
				],
				[
					'title' => 'List cities',
					'function' => 'list_city',
					'path' => 'cities'
				],
				[
					'title' => 'Add area',
					'function' => 'add_area',
					'path' => 'cities/areas/add'
				],
				[
					'title' => 'List areas',
					'function' => 'list_area',
					'path' => 'cities/areas'
				]
			]
		],
		[
			'title' => 'Delivery staff',
			'icon_class' => 'si si-users',
			'items' => [
				[
					'title' => 'Add delivery staff',
					'function' => 'add_delivery_staff',
					'path' => 'delivery-staff/add'
				],
				[
					'title' => 'List delivery staff',
					'function' => 'list_delivery_staff',
					'path' => 'delivery-staff'
				]
			]
		],
		[
			'title' => 'Users',
			'icon_class' => 'si si-users',
			'items' => [
				[
					'title' => 'Add user',
					'function' => 'add_user',
					'path' => 'users/add'
				],
				[
					'title' => 'List users',
					'function' => 'list_user',
					'path' => 'users'
				],
			]

		],
		//// SODIC
		[
			'title' => 'Sodic Orders',
			'icon_class' => 'si si-calendar',
			'function' => 'list_sodic_orders',
			'path' => 'orders'
		],

		[
			'title' => 'Sodic Users',
			'icon_class' => 'si si-users',
			'function' => 'list_sodic_users',
			'path' => 'users'
		],
//////	
		[
				'title' => 'Admins',
			'icon_class' => 'si si-users',
			'items' => [
				[
					'title' => 'Add /admin',
					'function' => 'add_admin',
					'path' => 'admins/add'
				],
				[
					'title' => 'List admins',
					'function' => 'list_admin',
					'path' => 'admins'
				]
			]
		],
		[
			'title' => 'Roles',
			'icon_class' => 'fa fa-ban',
			'items' => [
				[
					'title' => 'Add role',
					'function' => 'add_role',
					'path' => 'roles/add'
				],
				[
					'title' => 'List roles',
					'function' => 'list_role',
					'path' => 'roles'
				]
			]
		],
		[
			'title' => 'Functions',
			'icon_class' => 'si si-list',
			'items' => [
				[
					'title' => 'Add function',
					'function' => 'add_function',
					'path' => 'functions/add'
				],
				[
					'title' => 'List functions',
					'function' => 'list_function',
					'path' => 'functions'
				]
			]
		],
		[
			'title' => 'Administration',
			'icon_class' => 'si si-settings',
			'items' => [
				[
					'title' => 'Options',
					'function' => 'add_options',
					'path' => 'options'
				],
				[
					'title' => 'Purge DB tables',
					'function' => 'purge_db_tables',
					'path' => 'purge-db'
				],
				[
					'title' => 'Technical info',
					'function' => 'tech_info',
					'path' => 'tech-info'
				],
				[
					'title' => 'Backup The Database',
					'function' => 'backup_db',
					'path' => 'manage'
				]
			]
		],
		[
			'type' => 'divider',
			'title' => $_SESSION['admin_name']
		],
		[
			'type' => 'url',
			'title' => 'Log out',
			'url' => 'logout',
			'icon_class' => 'si si-logout'
		]
	];

	?>
	<ul class="nav-main">
		<?php
		$all = [];
		foreach ($pages as $key => $page) {
			array_push($all, $page);
			$type = $title = $icon_class = '';

			if (isset($page['type'])) $type = strtolower(trim($page['type']));
			if (isset($page['title'])) $title = trim($page['title']);
			if (! empty($page['remove_admin_from_path'])) $base_url = ''; else  $base_url = 'admin/';
			if (isset($page['icon_class'])) $icon_class = trim($page['icon_class']);

			if ($type == 'divider') {
				if (empty($title)) {
		?>
					<li class="<?php echo $type; ?>"></li>
				<?php
				} else {
				?>
					<li class="nav-main-heading"><span class="sidebar-mini-hide"><?php echo $title; ?></span></li>
				<?php
				}
			} else if ($type == 'url') {
				$url = trim($page['url']);

				?>
				<li>
					<a href="<?php echo $url; ?>"><i class="<?php echo $icon_class; ?>"></i><span class="sidebar-mini-hide"><?php echo $title; ?></span></a>
				</li>
			<?php
			} else if (!isset($page['items']) || !count($page['items'])) {
				$function = trim($page['function']);
				if (!in_array($function, $role_functions)) continue;

				$url = $base_url . $page['path'];

				if ($function == $tab) $is_active_class = 'active';
				else $is_active_class = '';

			?>
				<li>
					<a class="<?php echo $is_active_class; ?>" href="<?php echo $url; ?>"><i class="<?php echo $icon_class; ?>"></i><span class="sidebar-mini-hide"><?php echo $title; ?></span></a>
				</li>
		
		<?php
			} else {
				$items = $page['items'];
				$items_check = false;
				$is_open_class = '';

				foreach ($items as $item) {
					$function = trim($item['function']);
					if (in_array($function, $role_functions) && function_exists($function)) {
						if ($function == $tab) $is_open_class = 'open';
						$items_check = true;
					}
				}

				if (!$items_check) continue;

			?>
				<li class="<?php echo $is_open_class; ?>">
					<a class="nav-submenu" data-toggle="nav-submenu" href="#"><i class="<?php echo $icon_class; ?>"></i><span class="sidebar-mini-hide"><?php echo $title; ?></span></a>
					<ul>
						<?php

						foreach ($items as $item) {
							$function = trim($item['function']);
							if (!in_array($function, $role_functions) || !function_exists($function)) continue;

							$url = $base_url . $item['path'];
							$title = trim($item['title']);

							if ($function == $tab) $is_active_class = 'active';
							else $is_active_class = '';

						?>
							<li><a class="<?php echo $is_active_class; ?>" href="<?php echo $url; ?>"><?php echo $title; ?></a></li>
						<?php
						}

						?>
					</ul>
				</li>
		<?php
			}
		}

		?>
	</ul>
<?php
}


function generate_file_upload($multiple = true, $accept = IMAGE_FILTER)
{
	$max_mb = floatval(number_format(MAX_UPLOAD_SIZE / (1024 * 1024), 2));
	$accept = str_replace(DELIMITER, ',.', '.' . $accept);

	$fields_landscape = [
		[
			'label' => 'Thumbnail width - landscape',
			'tag' => 'input',
			'group' => [
				'right' => 'px'
			],
			'expected' => false,
			'attributes' => [
				'id' => 'thumb_width_landscape',
				'name' => 'thumb_width_landscape',
				'type' => 'number',
				'value' => CROP_W_LANDSCAPE,
				'min' => 300,
				'max' => 1600,
				'required' => true
			]
		]
	];

	$fields_portrait = [
		[
			'label' => 'Thumbnail width - portrait',
			'tag' => 'input',
			'group' => [
				'right' => 'px'
			],
			'expected' => false,
			'attributes' => [
				'id' => 'thumb_width_portrait',
				'name' => 'thumb_width_portrait',
				'type' => 'number',
				'value' => CROP_W_PORTRAIT,
				'min' => 300,
				'max' => 1600,
				'required' => true
			]
		]
	];

	$fields_upload = [
		[
			'label' => 'Media (max. ' . $max_mb . ' MB each)',
			'tag' => 'input',
			'expected' => false,
			'attributes' => [
				'id' => 'images_input',
				'name' => 'images[]',
				'class' => '',
				'type' => 'file',
				'accept' => $accept,
				'multiple' => $multiple,
				'onchange' => 'showMedia(this, \'upload-gallery\');'
			]
		]
	];

?>
	<div class="col-sm-6">
		<?php generate_form_fields($fields_landscape); ?>
	</div>
	<div class="col-sm-6">
		<?php generate_form_fields($fields_portrait); ?>
	</div>
	<?php generate_form_fields($fields_upload); ?>

	<div id="upload-gallery" class="row items-push js-gallery"></div>
	<hr />
<?php
}

function generate_image_galery($record_id, $base_dir, $featured_media = null)
{
	if (!$record_id) return;

	$con = get_db_con();
	$query = "SELECT * FROM media WHERE upload_dir='" . $base_dir . "' AND record_id='" . $record_id . "' ORDER BY name";
	$result = db_query($con, $query);

	if (!$result || !db_num_rows($result)) return;

	$thumb_dir = $base_dir . $record_id . THUMBS_DIR;
	$base_dir .= $record_id . '/';

	if (!is_dir($thumb_dir)) $thumb_dir = $base_dir;

	$image_filter = explode(DELIMITER, IMAGE_FILTER);
	$video_filter = explode(DELIMITER, VIDEO_FILTER);

	if (!is_array($featured_media)) $featured_media = ['featured_image' => ['value' => trim($featured_media), 'description' => 'Featured']];

?>
	<div class="row items-push js-gallery">
		<?php

		$key = 0;
		while ($row = db_fetch_assoc($result)) {
			$image = $row['name'];
			$title = $row['title'];

			if (!is_file($base_dir . $image)) continue;
			$size = round(filesize($base_dir . $image) / (1024 * 1024), 2);

			$image_name = pathinfo($image, PATHINFO_FILENAME);
			$image_ext = strtolower(pathinfo($image, PATHINFO_EXTENSION));

		?>
			<div class="row">
				<div class="col-sm-6 col-md-6 col-lg-4 animated fadeIn">
					<?php

					if (in_array($image_ext, $image_filter)) {
					?>
						<a class="img-link img-thumb" href="<?php echo $base_dir . $image; ?>">
							<img class="img-responsive lazy-load" data-src="<?php echo $thumb_dir . $image; ?>" />
						</a>
					<?php
					} else if (in_array($image_ext, $video_filter)) {
					?>
						<video class="img-responsive" controls>
							<source class="lazy-load" data-src="<?php echo $base_dir . $image; ?>" type="video/<?php echo $image_ext; ?>" />
						</video>
					<?php
					}

					?>
					<div align="center"><?php echo $size; ?> MB</div>
				</div>
				<div class="col-sm-6 col-md-6 col-lg-8">
					<div class="form-group">
						<div class="col-sm-6">
							<div class="form-material form-material-primary">
								<div class="input-group">
									<input class="form-control js-maxlength" type="text" maxlength="250" value="<?php echo $image_name; ?>" id="image_name_<?php echo $key; ?>" name="image_name_<?php echo $key; ?>" required onblur="this.value = this.value.trim().replace(/\s+/g, '<?php echo FILENAME_GLUE; ?>');" data-always-show="true" />
									<span class="input-group-addon">.<?php echo $image_ext; ?></span>
								</div>
								<label for="image_name_<?php echo $key; ?>">Name</label>
							</div>
						</div>
						<div class="col-sm-6">
							<div class="form-material form-material-primary">
								<input class="form-control js-maxlength" type="text" maxlength="50" value="<?php echo $title; ?>" id="image_title_<?php echo $key; ?>" name="image_title_<?php echo $key; ?>" data-always-show="true" />
								<label for="image_title_<?php echo $key; ?>">Title</label>
							</div>
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-6">
							<div class="checkbox">
								<label for="image_<?php echo $key; ?>" class="css-input css-checkbox css-checkbox-primary">
									<input type="checkbox" id="image_<?php echo $key; ?>" name="image_<?php echo $key; ?>" value="<?php echo $image; ?>" checked /><span></span>Active
								</label>
							</div>
						</div>
						<div class="col-sm-6">
							<?php

							if (!empty($featured_media) && in_array($image_ext, $image_filter)) {
								foreach ($featured_media as $feature => $value) {
									$checked = $required = '';
									$description = trim($value['description']);

									if ($image == $value['value']) $checked = 'checked';
									if (isset($value['required']) && $value['required']) $required = 'required';

							?>
									<div class="radio">
										<label for="<?php echo $feature; ?>_<?php echo $key; ?>" class="css-input css-radio css-radio-primary">
											<input type="radio" name="<?php echo $feature; ?>" id="<?php echo $feature; ?>_<?php echo $key; ?>" value="<?php echo $image; ?>" <?php echo $checked; ?> <?php echo $required; ?> />
											<span></span><?php echo $description; ?>
										</label>
									</div>
							<?php
								}
							}

							?>
						</div>
					</div>
				</div>
			</div>
			<hr />
		<?php

			$key++;
		}

		db_free_result($result);
		close_db_con($con);

		foreach ($featured_media as $feature => $value) echo '<input type="hidden" name="expected_fields[]" value="' . $feature . '" />';

		?>
	</div>

	<script>
		window.addEventListener('load', function() {
			lazyLoadMedia('lazy-load');
		});
	</script>
<?php
}


function options_column_header()
{
?>
	Options
	<label class="css-input css-checkbox css-checkbox-primary push-10-l" title="Toggle all">
		<input type="checkbox" onclick="toggleCheckboxes(this.checked, 'options-form');" /><span></span>
	</label>
	<?php
}

function add_options_button($path = null, $value = null, $role_functions = null, $mode = null, $view_edit = null)
{
	$tab = null;

	if ($path) $tab = process_api_call(['path' => $path]);
	if ($tab) $tab = $tab['function'];

	if (!$role_functions) $role_functions = get_role_functions();
	if ($tab && in_array($tab, $role_functions)) {
	if(! empty ($view_edit) )	$label = $view_edit ; else $label = "Edit";
		$icon_class = 'fa fa-edit';

		if (!empty($mode)) {
			$label = $mode['label'];
			$icon_class = $mode['icon'];
		}

	?>
		<a href="admin/<?php echo $path; ?>/<?php echo $value; ?>"  target="__blank" rel="noopener noreferrer"    >
			<button type="button"  class="btn btn-xs btn-info push-10-r"><i class="<?php echo $icon_class; ?>"></i> <?php echo $label; ?></button>
		</a>
	<?php
	}

	if ($value) {
	?>
		<label class="css-input css-checkbox css-checkbox-primary">
			<input type="checkbox" name="values[]" value="<?php echo $value; ?>" class="options-form" form="options_form" /><span></span>
		</label>
	<?php
	}
}

function add_action_buttons($actions, $role_functions = null)
{
	if (!is_array($actions)) return;

	$items_check = false;
	if (!$role_functions) $role_functions = get_role_functions();

	$action_types = [
		'remove' => [
			'label' => 'Remove',
			'btn_class' => 'danger',
			'icon_class' => 'fa fa-trash'
		],
		'remove' => [
			'label' => 'Remove',
			'btn_class' => 'danger',
			'icon_class' => 'fa fa-trash'
		],
		'activate' => [
			'label' => 'Activate',
			'btn_class' => 'warning',
			'icon_class' => 'fa fa-toggle-on'
		],
		'deactivate' => [
			'label' => 'Deactivate',
			'btn_class' => 'warning',
			'icon_class' => 'fa fa-toggle-off'
		],
		'refund_order' => [
			'label' => 'Refund',
			'btn_class' => 'warning',
			'icon_class' => 'fa fa-repeat'
		],
		'rollback' => [
			'label' => 'Rollback',
			'btn_class' => 'danger',
			'icon_class' => 'fa fa-repeat'
		],
		'change_order_state' => [
			'label' => 'Change state',
			'btn_class' => 'success',
			'icon_class' => 'fa fa-refresh',
			'attributes' => [
				'type' => 'button',
				'data-toggle' => 'modal',
				'data-target' => '#modal_change_order_state'
			]
		],
		'change_delivery_staff_area' => [
			'label' => 'Change area',
			'btn_class' => 'success',
			'icon_class' => 'fa fa-refresh',
			'attributes' => [
				'type' => 'button',
				'data-toggle' => 'modal',
				'data-target' => '#modal_change_delivery_staff_area'
			]
			]	

	];

	foreach ($actions as $action) {
		$function = trim($action['function']);
		$type = trim($action['type']);

		if (in_array($function, $role_functions) && array_key_exists($type, $action_types)) {
			$items_check = true;
			break;
		}
	}

	if (!$items_check) return;

	?>
	<div align="right">
		<form class="form-notify" action="manage" method="post" id="options_form" onsubmit="return warnUser();">
			<div class="btn-group push-5">
				<button class="btn btn-info dropdown-toggle" data-toggle="dropdown" type="button">
					Actions <span class="caret"></span>
				</button>
				<ul class="dropdown-menu dropdown-menu-right">
					<?php

					foreach ($actions as $action) {
						$function = trim($action['function']);
						$type = trim($action['type']);

						if (!in_array($function, $role_functions) || !array_key_exists($type, $action_types)) continue;

						$btn_class = $action_types[$type]['btn_class'];
						$icon_class = $action_types[$type]['icon_class'];
						$label = $action_types[$type]['label'];

						if (isset($action_types[$type]['attributes'])) {
							$attributes = $action_types[$type]['attributes'];
							foreach ($attributes as $key => &$value) {
								if (is_bool($value)) {
									if ($value) $value = $key;
									else continue;
								} else $value = $key . '="' . $value . '"';
							}

							$attributes = implode(' ', $attributes);
						} else $attributes = 'type="submit"';

					?>
						<li class="text-center">
							<button <?php echo $attributes; ?> formnovalidate name="<?php echo $function; ?>" class="push-5 col-sm-12 btn btn-sm btn-<?php echo $btn_class; ?>">
								<i class="<?php echo $icon_class; ?>"></i> <?php echo $label; ?>
							</button>
						</li>
					<?php
					}

					?>
				</ul>
			</div>
		</form>
	</div>
<?php
}
/*
function add_submit_buttons($form_submit,$form_action=null)
{

	?>
	<div class="form-group">
		<div class="col-sm-12">
			<button class="btn btn-sm btn-primary push-5-r"  <?php if(!empty($form_action)): ?> formaction="<?=$form_action?> <?php endif?>" type="submit" name="<?php echo $form_submit; ?>"><i class="fa fa-save"></i> Save</button>
			<button class="btn btn-sm btn-warning" type="button" onclick="location.reload();"><i class="fa fa-repeat"></i> Reset</button>
		</div>
	</div>
<?php
}
*/

function add_submit_buttons($form_submit, $form_action=null , $no_reload= false)
{

	?>
	<div class="form-group">
		<div class="col-sm-12">
			<button class="btn btn-sm btn-primary push-5-r"  <?php if(!empty($form_action)): ?> formaction="<?=$form_action?> <?php endif?>" type="submit" name="<?php echo $form_submit; ?>"><i class="fa fa-save"></i> Save</button>
			<?php 
			 if ($no_reload == false):   ?> 
				<button class="btn btn-sm btn-warning" type="button" onclick="location.reload();"><i class="fa fa-repeat"></i> Reset</button>
					<?php endif ?>
			</div>
	</div>
<?php
}

function add_add_button($path, $button = true)
{
	$tab = process_api_call(['path' => $path]);
	if ($tab) $tab = $tab['function'];

	$heading = get_tab_heading($tab);
	if ($button && !in_array($tab, get_role_functions())) $button = false;

?>
	<div class="row">
		<div class="content">
			<div class="row">
				<div class="col-sm-6">
					<h1 class="page-heading"><?php echo $heading; ?></h1>
				</div>
				<div align="right" class="col-sm-6">
					<button class="btn btn-success push-5" type="button" onclick="location.reload();"><i class="fa fa-refresh"></i> REFRESH</button>
					<?php

					if ($button) {
					?>
						<a href="admin/<?php echo $path; ?>">
							<button class="btn btn-success push-5" type="button"><i class="fa fa-plus"></i> ADD</button>
						</a>
					<?php
					}

					?>
				</div>
			</div>
		</div>
	</div>
<?php
}

function add_close_button($heading = '', $actions = null)
{
	if (isset($_SERVER['HTTP_REFERER'])) $url = $_SERVER['HTTP_REFERER'];
	else $url = '/admin';

?>
	<div class="row">
		<div class="content">
			<div class="row">
				<div class="col-lg-10 col-md-8 col-sm-6">
					<h1 class="page-heading"><?php echo strtoupper($heading); ?></h1>
				</div>
				<div align="right" class="col-lg-2 col-md-4 col-sm-6">
					<div class="col-sm-6">
						<?php

						if ($actions) add_action_buttons($actions);

						?>
					</div>
					<div class="col-sm-6">
						<a href="<?php echo $url; ?>">
							<button class="btn btn-danger push-5" type="button"><i class="fa fa-close"></i> CLOSE</button>
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php
}

function process_api_call($get, $functions = null)
{
	static $static_functions = null;

	if (!$static_functions) $static_functions = $functions;

	if (!isset($get['path']) || empty($get['path'])) return null;


	$path = explode('/', $get['path']);

	
	if (empty($path)) return null;

	unset($get['path']);

	$data = [];
	$last_node = '';
	$function = $static_functions;

	foreach ($path as &$node) {
		$node = trim($node);

		if (empty($node)) continue;
		else if (is_numeric($node)) $data[$last_node . '_id'] = intval($node);
		else if (!isset($function[$node])) return null;
		else {
			$last_node = $node;
			$functions = $function = $function[$node];
		}
	}

	if (is_array($function)) $function = $function[0];
	if (!is_string($function)) return null;

	$data = [
		'function' => $function ,
		'get' => $data,
		'vars' => $get
	];

	return $data;

}


function get_tab_heading($tab)
{
	$tabs = [
		'dashboard' => 'Dashboard',
		'add_shop' => 'Stores',
		'add_shop_type' => 'Store types',
		'add_supplier' => 'Suppliers',
		'add_product' => 'Products',
		'add_product_import' => 'Product imports log',
		'add_category' => 'Categories',
		// 'list_order' => 'Orders',
		'add_coupon' => 'Coupons',
		'add_order_state' => 'Order states',
		'add_delivery_staff' => 'Delivery staff',
		'add_city' => 'Cities',
		'add_area' => 'Areas',
		'add_user' => 'Users',
		'add_admin' => 'Admins',
		'add_role' => 'Roles',
		'add_function' => 'Functions',
		'tech_info' => 'Technical info'
	];

	if (isset($tabs[$tab])) return strtoupper($tabs[$tab]);
	else return '';
}

function add_post_export(String $path, String $label = "Export", String $heading = null) :void
{
    ?>
    <form action="<?=$path?>"  method="POST" >  
    <div class="row">
        <div class="content">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="page-heading"> <?=$heading?> </h1>
                </div>
                <div align="right" class="col-sm-6">
                        <button class="btn btn-success push-5" name="user" value="true" type="submit"><i class="fa ">
                            </i> <?= $label ?>
                        </button>
                        </div>
            </div>
        </div>
    </div>
    </form>
    <?php
}

?>