<?php
function custom_start_session() {
	if (!session_id()) {
		session_start();
	}
}
add_action('init', 'custom_start_session');
function custom_form_shortcode() {
	ob_start();
	if (isset($_SESSION['custom_error_message'])) {
		echo '<div class="error-message" style="color: red;">' . esc_html($_SESSION['custom_error_message']) . '</div>';
		unset($_SESSION['custom_error_message']);
		unset($_SESSION['custom_success_message']);
	}else if(isset($_SESSION['custom_success_message'])) {
		echo '<div class="success-message" style="color: green;">' . esc_html($_SESSION['custom_success_message']) . '</div>';
		unset($_SESSION['custom_error_message']);
		unset($_SESSION['custom_success_message']);
	}
	?>
	<form id="custom-form" class="read_image_form" method="post" enctype="multipart/form-data">
		<label for="name">Name:</label>
		<input type="text" id="name" name="name" required>
		<label for="email">Email:</label>
		<input type="email" id="email" name="email" required>
		<label for="message">Message:</label>
		<textarea id="message" name="message" required></textarea>
		<input type="hidden" name="extracted_text" id="hidden_extracted_text">
		<input type="hidden" name="custom_form_nonce" value="<?php echo wp_create_nonce('custom_form_nonce'); ?>">
		<input class="btn-submit-button" type="submit" value="Submit">
		<div class="etfi-extract-text-container">
			<?php echo do_shortcode('[etfi-extract-text]'); ?>
		</div>
	</form>
	<script>
		document.getElementById('custom-form').addEventListener('submit', function() {
			const extractedText = document.getElementById('extracted_text').value;
			document.getElementById('hidden_extracted_text').value = extractedText;
		});
	</script>
	<?php
	return ob_get_clean();
}
add_shortcode('custom_form', 'custom_form_shortcode');
function handle_custom_form_submission() {
	if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['custom_form_nonce'])) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'custom_form_data';
		if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
			$charset_collate = $wpdb->get_charset_collate();
			$sql = "CREATE TABLE $table_name (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				name varchar(100) NOT NULL,
				email varchar(100) NOT NULL,
				message text NOT NULL,
				extracted_text text NOT NULL,
				attachment text NOT NULL,
				submitted_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
				PRIMARY KEY (id)
			) $charset_collate;";
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
		}
		if (isset($_FILES['aksfileupload']) && $_FILES['aksfileupload']['error'] === UPLOAD_ERR_OK) {
			$upload_dir = wp_upload_dir();
			$upload_path = $upload_dir['path'];
			$upload_url = $upload_dir['url'];
			$filename = sanitize_file_name($_FILES['aksfileupload']['name']);
			$file_name = time().$filename;
			$file_path = $upload_path . '/' . $file_name;
			$file_url = $upload_url . '/' . $file_name;
			$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
			if (!in_array($_FILES['aksfileupload']['type'], $allowed_types)) {
				$error_message = 'Invalid file type. Please upload a JPEG, PNG, or GIF image.';
			} else {
				if (move_uploaded_file($_FILES['aksfileupload']['tmp_name'], $file_path)) {
					$wpdb->insert(
						$table_name,
						[
							'name' => sanitize_text_field($_POST['name']),
							'email' => sanitize_email($_POST['email']),
							'message' => sanitize_textarea_field($_POST['message']),
							'extracted_text' => sanitize_textarea_field($_POST['extracted_text']),
							'attachment' => $file_url,
						]
					);
					$_SESSION['custom_success_message'] = 'Form submitted successfully.';
					wp_redirect($_SERVER['HTTP_REFERER'] . '?submission=success');
					exit;
				} else {
					$error_message = 'Failed to upload the file. Please try again.';
				}
			}
		} else {
			$error_message = 'No file uploaded or an upload error occurred.';
		}
		if ($error_message) {
			$_SESSION['custom_error_message'] = $error_message;
			wp_redirect($_SERVER['HTTP_REFERER'] . '?submission=error');
			exit;
		}
	}
}
add_action('init', 'handle_custom_form_submission');
function add_custom_form_admin_menu() {
	add_menu_page(
		'Form Submissions',
		'Form Submissions',
		'manage_options',
		'custom-form-submissions',
		'display_custom_form_submissions',
		'dashicons-feedback',
		20
	);
}
add_action('admin_menu', 'add_custom_form_admin_menu');
function display_custom_form_submissions() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'custom_form_data';
	if (isset($_POST['delete_all_records'])) {
		$wpdb->query("TRUNCATE TABLE $table_name");
		echo '<div class="notice notice-success"><p>All records have been deleted successfully.</p></div>';
	}
	?>
	<style>
		.popup-overlay {
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background: rgba(0, 0, 0, 0.5);
			display: flex;
			justify-content: center;
			align-items: center;
			z-index: 1000;
		}
		.popup-content {
			background: white;
			padding: 20px;
			border-radius: 8px;
			text-align: center;
			box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
		}
		.button {
			margin: 5px;
			padding: 10px 15px;
			border: none;
			border-radius: 5px;
			cursor: pointer;
		}
		.button-danger {
			background-color: #e74c3c;
			color: white;
		}
	</style>
	<div class="wrap">
		<h1>Form Submissions</h1>
		<form method="post" id="deleteAllRecordsForm" style="margin-bottom: 20px;">
			<input type="hidden" name="delete_all_records" value="1">
			<button type="button" class="button button-danger" onclick="showConfirmationPopup()">Delete All Records</button>
		</form>
		<div id="confirmationPopup" class="popup-overlay" style="display: none;">
			<div class="popup-content">
				<p>Are you sure you want to delete all records?</p>
				<button onclick="submitForm()" class="button button-danger">Delete All Records</button>
				<button onclick="closePopup()" class="button">No</button>
			</div>
		</div>
		<script>
			function showConfirmationPopup() {
				document.getElementById('confirmationPopup').style.display = 'flex';
			}
			function closePopup() {
				document.getElementById('confirmationPopup').style.display = 'none';
			}
			function submitForm() {
				document.getElementById('deleteAllRecordsForm').submit();
			}
		</script>
	</div>
	<?php
	$results = $wpdb->get_results("SELECT * FROM $table_name");
	echo '<table class="widefat"><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Message</th><th>Extracted Text</th><th>Attachment</th><th>Date</th></tr></thead><tbody>';
	if (!empty($results)) {
		foreach ($results as $row) {
			echo "<tr>
			<td>{$row->id}</td>
			<td>{$row->name}</td>
			<td>{$row->email}</td>
			<td>{$row->message}</td>
			<td>{$row->extracted_text}</td>
			<td><a href='{$row->attachment}' target='_blank'>View Attachment</a></td>
			<td>{$row->submitted_at}</td>
			</tr>";
		}
	} else {
		echo '<tr><td colspan="6">No submissions found.</td></tr>';
	}
	echo '</tbody></table></div>';
}
function add_export_to_excel_button() {
	if (isset($_GET['export_to_excel']) && $_GET['export_to_excel'] == 'true') {
		export_custom_form_data_to_excel();
	}
}
add_action('admin_init', 'add_export_to_excel_button');
function export_custom_form_data_to_excel() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'custom_form_data';
	$results = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
	if (empty($results)) {
		wp_die('No data available to export.');
	}
	header("Content-Type: application/vnd.ms-excel");
	header("Content-Disposition: attachment; filename=form_submissions_" . date('Y-m-d') . ".xls");
	header("Pragma: no-cache");
	header("Expires: 0");
	$output = fopen('php://output', 'w');
	fputcsv($output, array_keys($results[0]), "\t");
	foreach ($results as $row) {
		fputcsv($output, $row, "\t");
	}
	fclose($output);
	exit;
}
function add_export_button_to_admin_page() {
	add_submenu_page(
		'custom-form-submissions',
		'Export to Excel',
		'Export to Excel',
		'manage_options',
		'custom-form-export',
		function () {
			echo '<div class="wrap"><h1>Export Form Submissions</h1>';
			echo '<a href="' . admin_url('admin.php?page=custom-form-export&export_to_excel=true') . '" class="button-primary">Export to Excel</a>';
			echo '</div>';
		}
	);
}
add_action('admin_menu', 'add_export_button_to_admin_page');