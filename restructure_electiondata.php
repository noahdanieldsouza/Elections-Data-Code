<?php

// Input and output file paths
$inputFile = 'presidential_election_results.csv';
$outputFile = 'restructured_election.csv';

// Open the input CSV file
if (($handle = fopen($inputFile, 'r')) !== FALSE) {
    $header = fgetcsv($handle);  // Read the header row

    // Identify column indexes
    $stateIndex = array_search('state_po', $header);
    $yearIndex = array_search('year', $header);
    $fipsIndex = array_search('state_fips', $header);
    $cenIndex = array_search('state_cen', $header);
    $icIndex = array_search('state_ic', $header);
    $partyIndex = array_search('party_detailed', $header);
    $votesIndex = array_search('candidatevotes', $header);
    $totalVotesIndex = array_search('totalvotes', $header);

    $data = [];

    // Read data from the CSV file and restructure it
    while (($row = fgetcsv($handle)) !== FALSE) {
        echo "Reading party column: " . $row[$partyIndex] . PHP_EOL;
        $state = $row[$stateIndex];
        $year = $row[$yearIndex];
        $state_fips = $row[$fipsIndex];
        $state_cen = $row[$cenIndex];
        $state_ic = $row[$icIndex];
        $office = "US PRESIDENT";
        $party = $row[$partyIndex];
        $votes = $row[$votesIndex];
        $totalVotes = $row[$totalVotesIndex];

        // Create a key for unique state-year combinations
        $key = $state . '_' . $year;

        // Initialize the row if not already present
        if (!isset($data[$key])) {
            $data[$key] = [
                'state' => $state,
                'year' => $year,
                'state_fips' => $state_fips,
                'state_cen' => $state_cen,
                'state_ic' => $state_ic,
                'office' => $office,
                'totalvotes' => $totalVotes,
            ];
        }

        // Assign votes to the respective party column
        $data[$key][$party] = $votes;
    }
    fclose($handle);

    // Determine all party columns dynamically
    $parties = [];
    foreach ($data as $row) {
        foreach ($row as $key => $value) {
            if (!in_array($key, ['state', 'year', 'totalvotes']) && !in_array($key, $parties)) {
                $parties[] = $key;
            }
        }
    }

    // Write the transformed data to a new CSV file
    $outputHandle = fopen($outputFile, 'w');

    // Create new header row with party columns
    $newHeader = array_merge(['state', 'year'], $parties, ['totalvotes']);
    fputcsv($outputHandle, $newHeader, ",", '"', "\\");

    // Write the transformed data
    foreach ($data as $row) {
        $outputRow = [
            $row['state'],
            $row['year'],
        ];
        foreach ($parties as $party) {
            $outputRow[] = $row[$party] ?? 0;  // Default to 0 if no votes
        }
        $outputRow[] = $row['totalvotes'];
        fputcsv($outputHandle, $outputRow, ",", '"', "\\");
    }

    fclose($outputHandle);
    echo "Data restructuring complete. File saved as {$outputFile}\n";
} else {
    echo "Failed to open the input file.\n";
}