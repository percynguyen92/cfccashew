<?php

return [
    'custom' => [
        'bill_number' => [
            'required' => 'Bill number is required.',
            'max' => 'Bill number cannot exceed 20 characters.',
        ],
        'seller' => [
            'required' => 'Seller is required.',
            'max' => 'Seller name cannot exceed 255 characters.',
        ],
        'buyer' => [
            'required' => 'Buyer is required.',
            'max' => 'Buyer name cannot exceed 255 characters.',
        ],
        'bill_id' => [
            'required' => 'A valid bill is required.',
            'integer' => 'A valid bill is required.',
            'exists' => 'The selected bill does not exist.',
        ],
        'container_id' => [
            'exists' => 'The selected container does not exist.',
            'final_sample_forbidden' => 'Final sample tests cannot be associated with a container.',
            'container_required' => 'Container tests must be associated with a container.',
        ],
        'container_number' => [
            'size' => 'Container number must be exactly 11 characters.',
            'regex' => 'Container number must match the ISO format (4 letters + 7 digits).',
        ],
        'w_jute_bag' => [
            'max' => 'Jute bag weight cannot exceed 99.99 kg.',
        ],
        'weights' => [
            'min' => 'Weight values cannot be negative.',
        ],
        'type' => [
            'required' => 'Select a valid test type.',
            'integer' => 'Select a valid test type.',
            'in' => 'Select a valid test type.',
        ],
        'moisture' => [
            'min' => 'Moisture cannot be negative.',
            'max' => 'Moisture cannot exceed 100%.',
        ],
        'sample_weight' => [
            'required' => 'Sample weight must be between 1 and 65,535 grams.',
            'integer' => 'Sample weight must be a whole number.',
            'min' => 'Sample weight must be between 1 and 65,535 grams.',
            'max' => 'Sample weight must be between 1 and 65,535 grams.',
        ],
        'outturn_rate' => [
            'max' => 'Outturn rate cannot exceed 60 lbs/80kg.',
        ],
        'w_total' => [
            'must_be_greater_than_truck_container' => 'Total weight must be greater than truck and container weight combined.',
        ],
        'container_condition' => [
            'required' => 'Container condition is required.',
            'in' => 'Please select a valid container condition.',
        ],
        'seal_condition' => [
            'required' => 'Seal condition is required.',
            'in' => 'Please select a valid seal condition.',
        ],
        'quantity_of_bags' => [
            'max' => 'Quantity of bags cannot exceed 2000.',
        ],
    ],
];
