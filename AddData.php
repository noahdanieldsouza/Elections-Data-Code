<?php

// Paths to the CSV files
$existingFilePath = 'simplified.csv';
$newDataFilePath = '1948.csv';

// Function to read a CSV file into an array
function readCsvToArray($filePath) {
    $data = [];
    if (($handle = fopen($filePath, 'r')) !== FALSE) {
        $header = fgetcsv($handle); // Read the header
        if ($header === FALSE) {
            die("Error: Unable to read header row from $filePath.");
        }

        // Read each row into an associative array
        while (($row = fgetcsv($handle)) !== FALSE) {
            if (count($row) == count($header)) {
                $data[] = array_combine($header, $row);
            }
        }
        fclose($handle);
    } else {
        die("Error: Unable to open the file at $filePath.");
    }
    return [$header, $data];
}

// Read main.csv
list($existingHeader, $existingData) = readCsvToArray($existingFilePath);

// Read 1972.csv
list($newHeader, $newData) = readCsvToArray($newDataFilePath);

// Convert headers to lowercase for case-insensitive matching
$existingHeaderLower = array_map('strtolower', $existingHeader);
$newHeaderLower = array_map('strtolower', $newHeader);

// Mapping of 1972 headers to main.csv headers
$columnMapping = [
    'total' => 'totalvotes',
    'democrat' => 'DEMOCRAT',
    'republican' => 'REPUBLICAN'
];

// Prepare new 1972 data with proper column alignment
$filteredNewData = [];
foreach ($newData as $row) {
    $filteredRow = array_fill_keys($existingHeader, ''); // Initialize with blanks

    foreach ($row as $key => $value) {
        $keyLower = strtolower($key);
        
        // If the column exists in main.csv, map it
        if (in_array($keyLower, $newHeaderLower)) {
            // Map known column names correctly
            if (isset($columnMapping[$keyLower])) {
                $filteredRow[$columnMapping[$keyLower]] = $value;
            } elseif (in_array($key, $existingHeader)) {
                $filteredRow[$key] = $value;
            }
        }
    }

    $filteredNewData[] = $filteredRow;
}

// **Prepend 1972 data to the existing data**
$updatedData = array_merge($filteredNewData, $existingData);

// Write the updated data back to main.csv
if (($handle = fopen($existingFilePath, 'w')) !== FALSE) {
    // Write the header row
    fputcsv($handle, $existingHeader);
    
    // Write the data rows
    foreach ($updatedData as $row) {
        fputcsv($handle, $row);
    }
    fclose($handle);
    echo "The 1972 election data has been successfully added to the top of the file.";
} else {
    die("Error: Unable to write to the file at $existingFilePath.");
}

?>
