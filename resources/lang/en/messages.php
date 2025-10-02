<?php

return [
    // Flash messages
    'bill_created' => 'Bill created successfully.',
    'bill_updated' => 'Bill updated successfully.',
    'bill_deleted' => 'Bill deleted successfully.',
    'container_created' => 'Container created successfully.',
    'container_updated' => 'Container updated successfully.',
    'container_deleted' => 'Container deleted successfully.',
    'cutting_test_created' => 'Cutting test created successfully.',
    'cutting_test_updated' => 'Cutting test updated successfully.',
    'cutting_test_deleted' => 'Cutting test deleted successfully.',

    // Welcome page
    'welcome' => [
        'pageTitle' => 'Welcome to CFCCashew',
        'nav' => [
            'dashboard' => 'Dashboard',
            'login' => 'Log in',
            'register' => 'Register',
        ],
        'hero' => [
            'title' => 'CFCCashew Inspection System',
            'subtitle' => [
                'line1' => 'Manage Bills of Lading, containers, and cutting test results',
                'line2' => 'for comprehensive cashew quality control.',
            ],
        ],
        'tiles' => [
            'documentation' => [
                'intro' => 'Laravel has wonderful documentation covering every aspect of the framework.',
                'link' => 'Documentation',
            ],
            'laracasts' => [
                'link' => 'Laracasts',
            ],
        ],
    ],

    // Common
    'common' => [
        'actions' => [
            'save' => 'Save',
            'cancel' => 'Cancel',
            'delete' => 'Delete',
            'edit' => 'Edit',
            'create' => 'Create',
            'view' => 'View',
            'back' => 'Back',
            'close' => 'Close',
            'submit' => 'Submit',
            'reset' => 'Reset',
            'search' => 'Search',
            'filter' => 'Filter',
            'clear' => 'Clear',
            'export' => 'Export',
            'import' => 'Import',
        ],
        'states' => [
            'loading' => 'Loading...',
            'saving' => 'Saving...',
            'deleting' => 'Deleting...',
            'processing' => 'Processing...',
        ],
        'messages' => [
            'loading' => 'Loading...',
            'noData' => 'No data available',
            'error' => 'An error occurred',
            'success' => 'Operation completed successfully',
        ],
        'placeholders' => [
            'notAvailable' => 'N/A',
            'selectOption' => 'Select an option',
            'enterText' => 'Enter text',
            'search' => 'Search...',
        ],
    ],

    // Dashboard
    'dashboard' => [
        'title' => 'Dashboard',
        'ranges' => [
            'week' => 'This Week',
            'month' => 'This Month',
            'year' => 'This Year',
        ],
        'stats' => [
            'cards' => [
                'totalBills' => [
                    'label' => 'Total Bills',
                    'description' => 'Total number of bills in the system',
                ],
                'pendingFinalTests' => [
                    'label' => 'Pending Final Tests',
                    'description' => 'Bills awaiting final cutting tests',
                ],
                'missingFinalSamples' => [
                    'label' => 'Missing Final Samples',
                    'description' => 'Bills missing required final samples',
                ],
                'highMoistureContainers' => [
                    'label' => 'High Moisture Containers',
                    'description' => 'Containers with moisture > 11%',
                ],
            ],
        ],
    ],

    // Bills
    'bills' => [
        'index' => [
            'title' => 'Bills of Lading',
            'description' => 'Manage bills of lading and their associated containers',
            'actions' => [
                'create' => 'Create Bill',
            ],
            'search' => [
                'placeholder' => 'Search bills by number, seller, or buyer...',
                'total' => 'Showing {count} bills',
            ],
            'table' => [
                'headers' => [
                    'billNumber' => 'Bill Number',
                    'seller' => 'Seller',
                    'buyer' => 'Buyer',
                    'origin' => 'Origin',
                    'containers' => 'Containers',
                    'finalSamples' => 'Final Samples',
                    'averageOutturn' => 'Avg. Outturn',
                    'created' => 'Created',
                    'actions' => 'Actions',
                ],
                'finalSamples' => '{count}/3',
                'averageOutturnValue' => '{value} lbs/80kg',
            ],
            'empty' => [
                'title' => 'No bills found',
                'search' => 'Try adjusting your search criteria',
                'default' => 'Get started by creating your first bill',
            ],
            'pagination' => [
                'summary' => 'Showing {from} to {to} of {total} results',
                'previous' => 'Previous',
                'next' => 'Next',
            ],
            'dialog' => [
                'delete' => [
                    'title' => 'Delete Bill',
                    'description' => 'Are you sure you want to delete "{label}"? This action cannot be undone.',
                    'fallback' => 'Bill #{id}',
                ],
            ],
            'sr' => [
                'edit' => 'Edit bill',
                'delete' => 'Delete bill',
            ],
        ],
        'form' => [
            'fields' => [
                'billNumber' => [
                    'label' => 'Bill Number',
                ],
                'seller' => [
                    'label' => 'Seller',
                ],
                'buyer' => [
                    'label' => 'Buyer',
                ],
                'origin' => [
                    'label' => 'Origin',
                ],
                'samplingRatio' => [
                    'label' => 'Sampling Ratio',
                ],
                'netOnBl' => [
                    'label' => 'Net on B/L',
                ],
                'quantityOfBagsOnBl' => [
                    'label' => 'Quantity of Bags on B/L',
                ],
                'inspectionStartDate' => [
                    'label' => 'Inspection Start Date',
                ],
                'inspectionEndDate' => [
                    'label' => 'Inspection End Date',
                ],
                'inspectionLocation' => [
                    'label' => 'Inspection Location',
                ],
                'wDunnageDribag' => [
                    'label' => 'Dunnage/Dribag Weight',
                ],
                'wJuteBag' => [
                    'label' => 'Jute Bag Weight',
                ],
                'note' => [
                    'label' => 'Notes',
                ],
            ],
        ],
    ],

    // Containers
    'containers' => [
        'index' => [
            'title' => 'Containers',
            'description' => 'Manage container information and weight data',
            'summary' => [
                'total' => 'Total: {count} containers',
            ],
            'filters' => [
                'truck' => [
                    'placeholder' => 'Search by truck...',
                ],
                'billInfo' => [
                    'label' => 'Bill Information',
                ],
                'dateFrom' => [
                    'label' => 'Date From',
                ],
                'dateTo' => [
                    'label' => 'Date To',
                ],
                'actions' => [
                    'search' => 'Search',
                    'clear' => 'Clear',
                ],
            ],
            'table' => [
                'outturnValue' => '{value} lbs/80kg',
            ],
            'empty' => 'No containers found',
            'pagination' => [
                'summary' => 'Showing {from} to {to} of {total} results',
                'previous' => 'Previous',
                'next' => 'Next',
            ],
            'dialog' => [
                'delete' => [
                    'fallback' => 'Container #{id}',
                ],
            ],
        ],
        'create' => [
            'headTitle' => 'Create Container',
            'title' => 'Create New Container',
            'actions' => [
                'back' => 'Back',
            ],
        ],
        'edit' => [
            'headTitle' => 'Edit Container {identifier}',
            'title' => 'Edit Container {identifier}',
            'actions' => [
                'back' => 'Back',
            ],
            'bill' => [
                'title' => 'Associated Bill',
                'number' => 'Bill Number',
                'seller' => 'Seller',
                'fallback' => 'Bill #{id}',
            ],
        ],
        'show' => [
            'actions' => [
                'addCuttingTest' => 'Add Cutting Test',
            ],
            'cuttingTests' => [
                'title' => 'Cutting Tests',
                'add' => 'Add Test',
                'types' => [
                    'finalFirst' => 'Final Sample 1',
                    'finalSecond' => 'Final Sample 2',
                    'finalThird' => 'Final Sample 3',
                    'container' => 'Container Test',
                    'generic' => 'Type {type}',
                ],
                'headers' => [
                    'type' => 'Type',
                    'moisture' => 'Moisture',
                    'sampleWeight' => 'Sample Weight',
                    'nutCount' => 'Nut Count',
                    'defectiveRatio' => 'Defective Ratio',
                    'outturn' => 'Outturn',
                    'date' => 'Date',
                ],
                'empty' => [
                    'title' => 'No cutting tests yet',
                    'subtitle' => 'Add the first cutting test for this container',
                    'action' => 'Add Cutting Test',
                ],
            ],
        ],
        'form' => [
            'fields' => [
                'containerNumber' => [
                    'label' => 'Container Number',
                ],
                'truck' => [
                    'label' => 'Truck',
                ],
                'containerCondition' => [
                    'label' => 'Container Condition',
                ],
                'sealCondition' => [
                    'label' => 'Seal Condition',
                ],
                'totalWeight' => [
                    'label' => 'Total Weight',
                ],
                'truckWeight' => [
                    'label' => 'Truck Weight',
                ],
                'containerWeight' => [
                    'label' => 'Container Weight',
                ],
                'dunnageWeight' => [
                    'label' => 'Dunnage Weight',
                ],
            ],
            'calculated' => [
                'heading' => 'Weight Calculations',
                'gross' => [
                    'label' => 'Gross Weight',
                ],
            ],
        ],
    ],

    // Cutting Tests
    'cuttingTests' => [
        'show' => [
            'pageTitle' => '{type} - Test #{id}',
            'types' => [
                'finalFirst' => 'Final Sample 1',
                'finalSecond' => 'Final Sample 2',
                'finalThird' => 'Final Sample 3',
                'container' => 'Container Test',
                'generic' => 'Type {type}',
            ],
            'meta' => [
                'created' => 'Created: {date}',
                'updated' => 'Updated: {date}',
            ],
            'actions' => [
                'back' => 'Back',
                'edit' => 'Edit',
                'delete' => 'Delete',
            ],
            'dialog' => [
                'delete' => [
                    'confirm' => 'Are you sure you want to delete this cutting test?',
                ],
            ],
            'alerts' => [
                'weight' => 'Weight discrepancy: {difference}g difference',
                'defectiveRatio' => 'Defective ratio alert: {difference}g difference',
                'goodKernel' => 'Good kernel alert: {difference}g difference',
            ],
            'sections' => [
                'context' => [
                    'title' => 'Context',
                    'bill' => [
                        'title' => 'Bill of Lading',
                        'seller' => 'Seller: {value}',
                        'buyer' => 'Buyer: {value}',
                    ],
                    'container' => [
                        'title' => 'Container',
                        'finalSample' => 'Final sample (not associated with container)',
                        'truck' => 'Truck: {value}',
                    ],
                ],
                'calculations' => [
                    'formulas' => [
                        'outturn' => [
                            'title' => 'Outturn Rate:',
                            'body' => '(Defective Kernel/2 + Good Kernel) × 80 ÷ 453.6',
                        ],
                        'defective' => [
                            'title' => 'Defective Kernel:',
                            'body' => 'Defective Nut ÷ 3.3',
                        ],
                    ],
                ],
            ],
        ],
        'index' => [
            'title' => 'Cutting Tests',
            'description' => 'Manage cutting test results and quality control data',
            'actions' => [
                'create' => 'Create Test',
            ],
            'filters' => [
                'billNumber' => [
                    'label' => 'Bill Number',
                ],
                'testType' => [
                    'label' => 'Test Type',
                ],
                'container' => [
                    'label' => 'Container',
                ],
                'dateFrom' => [
                    'label' => 'Date From',
                ],
                'dateTo' => [
                    'label' => 'Date To',
                ],
                'actions' => [
                    'search' => 'Search',
                    'clear' => 'Clear',
                ],
            ],
            'summary' => [
                'total' => 'Total: {count} tests',
            ],
            'table' => [
                'billFallback' => 'Bill #{id}',
                'containerFallback' => 'Container #{id}',
                'containerPlaceholder' => 'Final Sample',
                'finalSampleLabel' => 'Final Sample {order}',
                'outturnValue' => '{value} lbs/80kg',
            ],
            'empty' => 'No cutting tests found',
            'pagination' => [
                'summary' => 'Showing {from} to {to} of {total} results',
                'previous' => 'Previous',
                'next' => 'Next',
            ],
            'dialog' => [
                'delete' => [
                    'confirm' => 'Are you sure you want to delete this cutting test?',
                ],
            ],
            'sr' => [
                'view' => 'View cutting test',
                'edit' => 'Edit cutting test',
            ],
        ],
    ],

    // Settings
    'settings' => [
        'profile' => [
            'title' => 'Profile Settings',
            'breadcrumb' => 'Profile',
            'heading' => [
                'title' => 'Profile Information',
                'description' => 'Update your account profile information and email address.',
            ],
            'fields' => [
                'name' => [
                    'label' => 'Name',
                    'placeholder' => 'Enter your name',
                ],
                'email' => [
                    'label' => 'Email',
                    'placeholder' => 'Enter your email address',
                ],
            ],
            'verification' => [
                'unverified' => 'Your email address is unverified.',
                'resend' => 'Click here to re-send the verification email.',
                'sent' => 'A new verification link has been sent to your email address.',
            ],
            'status' => [
                'saved' => 'Saved.',
            ],
        ],
        'twoFactor' => [
            'title' => 'Two-Factor Authentication',
            'breadcrumb' => 'Two-Factor Authentication',
            'heading' => [
                'title' => 'Two-Factor Authentication',
                'description' => 'Add additional security to your account using two-factor authentication.',
            ],
            'status' => [
                'enabled' => 'Enabled',
                'disabled' => 'Disabled',
            ],
            'copy' => [
                'enabled' => 'Two-factor authentication is currently enabled for your account.',
                'disabled' => 'Two-factor authentication is not enabled for your account.',
            ],
            'actions' => [
                'enable' => 'Enable',
                'disable' => 'Disable',
                'continueSetup' => 'Continue Setup',
            ],
        ],
    ],
];
