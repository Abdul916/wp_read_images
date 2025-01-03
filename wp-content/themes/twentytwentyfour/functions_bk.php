<?php
// Add a shortcode to display the custom form
function custom_form_shortcode() {
	ob_start();
	?>
	<form id="custom-form" method="post">
		<label for="name">Name:</label>
		<input type="text" id="name" name="name" required>
		<br>
		<label for="email">Email:</label>
		<input type="email" id="email" name="email" required>
		<br>
		<label for="message">Message:</label>
		<textarea id="message" name="message" required></textarea>
		<input type="hidden" name="extracted_text" id="hidden_extracted_text">
		<input type="hidden" name="custom_form_nonce" value="<?php echo wp_create_nonce('custom_form_nonce'); ?>">
		<br>
		<input type="submit" value="Submit">
		<div class="etfi-extract-text-container">
			<?php echo do_shortcode('[etfi-extract-text]'); ?>
		</div>
	</form>
	<script>
		document.getElementById('custom-form').addEventListener('submit', function() {
			// Copy extracted text into the hidden input field
			const extractedText = document.getElementById('extracted_text').value;
			document.getElementById('hidden_extracted_text').value = extractedText;
		});
	</script>
	<?php
	return ob_get_clean();
}
add_shortcode('custom_form', 'custom_form_shortcode');
// Handle form submission and save data to the database
function handle_custom_form_submission() {
	if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['custom_form_nonce'])) {
		echo "<pre>";
		print_r($_POST);
		exit;
		global $wpdb;
		$table_name = $wpdb->prefix . 'custom_form_data';
		// Create the table if it doesn't exist
		if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
			$charset_collate = $wpdb->get_charset_collate();
			$sql = "CREATE TABLE $table_name (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				name varchar(100) NOT NULL,
				email varchar(100) NOT NULL,
				message text NOT NULL,
				extracted_text text NOT NULL,
				submitted_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
				PRIMARY KEY (id)
			) $charset_collate;";
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
		}

		// Insert data into the table
		$wpdb->insert(
			$table_name,
			[
				'name' => sanitize_text_field($_POST['name']),
				'email' => sanitize_email($_POST['email']),
				'message' => sanitize_textarea_field($_POST['message']),
				'extracted_text' => sanitize_textarea_field($_POST['extracted_text']),
			]
		);
		// Redirect after submission
		wp_redirect($_SERVER['HTTP_REFERER'] . '?submission=success');
		exit;
	}
}

// add_action('admin_post_handle_custom_form_submission', 'handle_custom_form_submission');
// add_action('admin_post_nopriv_handle_custom_form_submission', 'handle_custom_form_submission');
add_action('init', 'handle_custom_form_submission');
// Add a custom admin menu to view form submissions
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
// Display form submissions in the admin panel
function display_custom_form_submissions() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'custom_form_data';
	$results = $wpdb->get_results("SELECT * FROM $table_name");
	echo '<div class="wrap"><h1>Form Submissions</h1>';
	echo '<table class="widefat"><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Message</th><th>Extracted Text</th><th>Date</th></tr></thead><tbody>';
	if (!empty($results)) {
		foreach ($results as $row) {
			echo "<tr>
			<td>{$row->id}</td>
			<td>{$row->name}</td>
			<td>{$row->email}</td>
			<td>{$row->message}</td>
			<td>{$row->extracted_text}</td>
			<td>{$row->submitted_at}</td>
			</tr>";
		}
	} else {
		echo '<tr><td colspan="6">No submissions found.</td></tr>';
	}
	echo '</tbody></table></div>';
}
// Add an "Export to Excel" button in the admin menu
function add_export_to_excel_button() {
	if (isset($_GET['export_to_excel']) && $_GET['export_to_excel'] == 'true') {
		export_custom_form_data_to_excel();
	}
}
add_action('admin_init', 'add_export_to_excel_button');
// Function to export data to Excel
function export_custom_form_data_to_excel() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'custom_form_data';
	$results = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
	if (empty($results)) {
		wp_die('No data available to export.');
	}
	// Set headers for the Excel file
	header("Content-Type: application/vnd.ms-excel");
	header("Content-Disposition: attachment; filename=form_submissions_" . date('Y-m-d') . ".xls");
	header("Pragma: no-cache");
	header("Expires: 0");
	// Open output stream
	$output = fopen('php://output', 'w');
	// Write column headers
	fputcsv($output, array_keys($results[0]), "\t");
	// Write data rows
	foreach ($results as $row) {
		fputcsv($output, $row, "\t");
	}
	fclose($output);
	exit;
}
// Add "Export to Excel" link to the admin menu
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