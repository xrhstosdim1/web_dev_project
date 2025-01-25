<?php
header('Content-Type: application/json');

// Example Data (Replace with your database queries)
$response = [
    'success' => true,
    'yearlyDistribution' => [
        'years' => ['2019', '2020', '2021', '2022', '2023'],
        'counts' => [10, 15, 20, 25, 30]
    ],
    'requestsAccepted' => 40,
    'requestsRejected' => 10,
    'responseTimes' => [
        'dates' => ['2023-01-01', '2023-02-01', '2023-03-01', '2023-04-01'],
        'times' => [2, 3, 1.5, 2.5]
    ],
    'gradesAbove9' => 70,
    'gradesBelow9' => 30
];

echo json_encode($response);
?>