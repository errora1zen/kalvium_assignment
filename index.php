<?php
// Get the URL path
$urlPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove the leading and trailing slashes
$urlPath = trim($urlPath, '/');

// Split the URL path into segments
$segments = explode('/', $urlPath);

// If the URL is just '/', display a list of example endpoints
if (empty($urlPath)) {
    $examples = [
	 [
            'path' => '/history',
            'description' => 'Lists the last 20 operations performed on the server, and the answers in HTML'
        ],
        [
            'path' => '/5/plus/3',
            'description' => 'JSON: {question: "5+3", answer: 8}'
        ],
        [
            'path' => '/3/minus/5',
            'description' => 'JSON: {question: "3-5", answer: -2}'
        ],
        [
            'path' => '/3/minus/5/plus/8',
            'description' => 'JSON: {question: "3-5+8", answer: 6}'
        ],
        [
            'path' => '/3/into/5/plus/8/into/6',
            'description' => 'JSON: {question: "3*5+8*6", answer: 63}'
        ],
        // Add more examples as needed
    ];

    // Display the list of example endpoints in HTML format
    echo '<html><body>';
    echo '<h1>Example Endpoints</h1>';
    echo '<ul>';
    foreach ($examples as $example) {
        echo '<li>';
        echo '<strong>Path:</strong> ' . $example['path'] . '<br>';
        echo '<strong>Description:</strong> ' . $example['description'];
        echo '</li>';
    }
    echo '</ul>';
    echo '</body></html>';
} elseif ($urlPath === 'history') {
    // Read the history file
    $historyFilePath = 'history.txt';
    $history = [];

    if (file_exists($historyFilePath)) {
        $history = file($historyFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $history = array_slice($history, -20); // Get the last 20 calculations
        $history = array_reverse($history); // Reverse to show the latest calculations first
    }

    // Display the history in HTML format
    echo '<html><body>';
    echo '<h1>Calculation History</h1>';
    echo '<ul>';
    foreach ($history as $calculation) {
        echo '<li>' . $calculation . '</li>';
    }
    echo '</ul>';
    echo '</body></html>';
} else {
    $result = intval($segments[0]);
    $question = $result;

    for ($i = 1; $i < count($segments); $i += 2) {
        $operation = $segments[$i];
        $num = intval($segments[$i + 1]);

        if ($operation === 'plus') {
            $result += $num;
            $question .= "+$num";
        } elseif ($operation === 'minus') {
            $result -= $num;
            $question .= "-$num";
        } elseif ($operation === 'into') {
            $result *= $num;
            $question .= "*$num";
        } elseif ($operation === 'divide') {
            if ($num != 0) {
                $result /= $num;
                $question .= "/$num";
            } else {
                $response = [
                    'error' => 'Division by zero is not allowed'
                ];
                break;
            }
        } else {
            $response = [
                'error' => 'Invalid operation'
            ];
            break;
        }
    }

    $response = [
        'question' => $question,
        'answer' => $result
    ];

    // Set the content type to JSON
    header('Content-Type: application/json');

    // Return the response as JSON
    echo json_encode($response, JSON_PRETTY_PRINT);

    // Append the calculation to the history file
    $historyFilePath = 'history.txt';
    $calculationString = $response['question'] . ' = ' . $response['answer'];
    file_put_contents($historyFilePath, $calculationString . PHP_EOL, FILE_APPEND);
}
?>
