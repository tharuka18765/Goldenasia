<?php
if (isset($_POST['report_data'])) {
    $report_data = unserialize(html_entity_decode($_POST['report_data']));

    // Define the CSV filename
    $filename = "branch_report_" . date("Y-m-d") . ".csv";

    // Set headers to force download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename="' . $filename . '"');

    // Open output stream
    $output = fopen('php://output', 'w');

    // Output CSV column headings
    fputcsv($output, ['Name', 'Plot', 'Collection', 'New Loan', 'Renewed Loan', 'Balance']);

    // Output rows
    foreach ($report_data as $row) {
        fputcsv($output, $row);
    }

    // Close output stream
    fclose($output);
    exit();
} else {
    echo "No data available to download.";
}
?>
