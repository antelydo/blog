<?php
/**
 * Test controller related language pack
 */
return [
    'test' => [
        // Common
        'test_successful' => 'Test successful',
        'pagination_test_completed' => 'Pagination test completed',
        'pagination_combinations_test_completed' => 'Pagination combinations test completed',
        
        // Test results
        'test_success_no_articles' => 'Test successful, but no articles in this category',
        
        // Validation messages
        'missing_pagination_field' => 'Missing required pagination field',
        'page_mismatch' => 'Returned page number does not match requested page number',
        'limit_mismatch' => 'Returned limit does not match requested limit',
        'item_count_mismatch' => 'Returned item count does not match expected count',
        'page_out_of_range' => 'Requested page number exceeds maximum page number',
        'validation_passed' => 'Passed',
        
        // Test cases
        'test_case_format' => 'Page %d, %d items per page',
    ]
];
