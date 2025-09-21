
# API Reference

<cite>
**Referenced Files in This Document**   
- [api.php](file://routes/api.php)
- [DashboardController.php](file://app/Http/Controllers/Api/DashboardController.php)
- [BillController.php](file://app/Http/Controllers/BillController.php)
- [ContainerController.php](file://app/Http/Controllers/ContainerController.php)
- [CuttingTestController.php](file://app/Http/Controllers/CuttingTestController.php)
- [BillResource.php](file://app/Http/Resources/BillResource.php)
- [ContainerResource.php](file://app/Http/Resources/ContainerResource.php)
- [CuttingTestResource.php](file://app/Http/Resources/CuttingTestResource.php)
- [StoreBillRequest.php](file://app/Http/Requests/StoreBillRequest.php)
- [UpdateBillRequest.php](file://app/Http/Requests/UpdateBillRequest.php)
- [StoreContainerRequest.php](file://app/Http/Requests/StoreContainerRequest.php)
- [UpdateContainerRequest.php](file://app/Http/Requests/UpdateContainerRequest.php)
- [StoreCuttingTestRequest.php](file://app/Http/Requests/StoreCuttingTestRequest.php)
- [CuttingTestType.php](file://app/Enums/CuttingTestType.php)
</cite>

## Table of Contents
1. [Introduction](#introduction)
2. [Authentication](#authentication)
3. [Dashboard API](#dashboard-api)
4. [Bill Management API](#bill-management-api)
5. [Container Tracking API](#container-tracking-api)
6. [Cutting Test API](#cutting-test-api)
7. [Resource Transformation](#resource-transformation)
8. [Error Response Format](#error-response-format)
9. [Query Parameters](#query-parameters)
10. [Usage Examples](#usage-examples)
11. [Rate Limiting and Security](#rate-limiting-and-security)

## Introduction
The CFCCashew API provides RESTful endpoints for managing cashew processing operations including bill management, container tracking, cutting tests, and dashboard statistics. All API endpoints require authentication and return JSON responses. The API follows REST conventions with appropriate HTTP methods and status codes.

## Authentication
All API endpoints require authentication using Laravel Sanctum tokens. Users must first authenticate through the web interface to obtain a valid API token. The token should be included in the Authorization header of all requests:

```
Authorization: Bearer <your-api-token>
```

The `/api/user` endpoint (not documented here) can be used to verify authentication status.

## Dashboard API

Provides statistical data and key performance indicators for the dashboard interface.

### GET /api/dashboard/stats
Retrieves overall statistics for bills, containers, and cutting tests.

**Response Schema**
```json
{
  "bills": {
    "total": 0,
    "pending_final_tests": 0,
    "missing_final_samples": 0
  },
  "containers": {
    "high_moisture": 0,
    "pending_tests": 0
  },
  "cutting_tests": {
    "high_moisture": 0,
    "moisture_distribution": {
      "under_6": 0,
      "6_to_8": 0,
      "8_to_10": 0,
      "10_to_12": 0,
      "over_12": 0
    }
  }
}
```

**Section sources**
- [DashboardController.php](file://app/Http/Controllers/Api/DashboardController.php#L25-L45)

### GET /api/dashboard/recent-bills
Retrieves the 5 most recently created bills.

**Response Schema**
```json
{
  "data": [
    {
      "id": 0,
      "slug": "string",
      "bill_number": "string",
      "seller": "string",
      "buyer": "string",
      "note": "string",
      "containers_count": 0,
      "final_samples_count": 0,
      "average_outurn": 0.0,
      "created_at": "string",
      "updated_at": "string"
    }
  ]
}
```

**Section sources**
- [DashboardController.php](file://app/Http/Controllers/Api/DashboardController.php#L47-L57)

### GET /api/dashboard/bills-pending-final-tests
Retrieves bills that are pending final cutting tests.

**Response Schema**
Same as recent-bills endpoint.

**Section sources**
- [DashboardController.php](file://app/Http/Controllers/Api/DashboardController.php#L59-L69)

### GET /api/dashboard/bills-missing-final-samples
Retrieves bills that are missing final samples.

**Response Schema**
Same as recent-bills endpoint.

**Section sources**
- [DashboardController.php](file://app/Http/Controllers/Api/DashboardController.php#L71-L81)

### GET /api/dashboard/containers-high-moisture
Retrieves containers with high moisture content.

**Response Schema**
```json
{
  "data": [
    {
      "id": 0,
      "bill_id": 0,
      "truck": "string",
      "container_number": "string",
      "quantity_of_bags": 0,
      "w_jute_bag": 0.0,
      "w_total": 0,
      "w_truck": 0,
      "w_container": 0,
      "w_gross": 0,
      "w_dunnage_dribag": 0,
      "w_tare": 0,
      "w_net": 0,
      "note": "string",
      "average_moisture": 0.0,
      "outturn_rate": 0.0,
      "created_at": "string",
      "updated_at": "string"
    }
  ]
}
```

**Section sources**
- [DashboardController.php](file://app/Http/Controllers/Api/DashboardController.php#L83-L93)

### GET /api/dashboard/cutting-tests-high-moisture
Retrieves cutting tests with high moisture content.

**Response Schema**
```json
{
  "data": [
    {
      "id": 0,
      "bill_id": 0,
      "container_id": 0,
      "type": 0,
      "type_label": "string",
      "moisture": 0.0,
      "sample_weight": 0,
      "nut_count": 0,
      "w_reject_nut": 0,
      "w_defective_nut": 0,
      "w_defective_kernel": 0,
      "w_good_kernel": 0,
      "w_sample_after_cut": 0,
      "outturn_rate": 0.0,
      "note": "string",
      "defective_ratio": {
        "defective_nut": 0,
        "defective_kernel": 0,
        "ratio": 0.0,
        "formatted": "string"
      },
      "is_final_sample": false,
      "is_container_test": false,
      "created_at": "string",
      "updated_at": "string"
    }
  ]
}
```

**Section sources**
- [DashboardController.php](file://app/Http/Controllers/Api/DashboardController.php#L95-L105)

### GET /api/dashboard/moisture-distribution
Retrieves moisture distribution statistics across all cutting tests.

**Response Schema**
```json
{
  "under_6": 0,
  "6_to_8": 0,
  "8_to_10": 0,
  "10_to_12": 0,
  "over_12": 0
}
```

**Section sources**
- [DashboardController.php](file://app/Http/Controllers/Api/DashboardController.php#L107-L118)

## Bill Management API

Provides CRUD operations for bill management.

### GET /bills
Retrieves a paginated list of bills with optional filtering.

**Query Parameters**
- `search`: Text to search in bill number, seller, or buyer
- `sort_by`: Field to sort by (created_at, bill_number, seller, buyer)
- `sort_direction`: Sort direction (asc, desc)

**Response Schema**
```json
{
  "data": [
    {
      "id": 0,
      "slug": "string",
      "bill_number": "string",
      "seller": "string",
      "buyer": "string",
      "note": "string",
      "containers_count": 0,
      "final_samples_count": 0,
      "average_outurn": 0.0,
      "created_at": "string",
      "updated_at": "string"
    }
  ],
  "links": {
    "first": "string",
    "last": "string",
    "prev": "string",
    "next": "string"
  },
  "meta": {
    "current_page": 0,
    "from": 0,
    "last_page": 0,
    "links": [
      {
        "url": "string",
        "label": "string",
        "active": false
      }
    ],
    "path": "string",
    "per_page": 0,
    "to": 0,
    "total": 0
  }
}
```

**Section sources**
- [BillController.php](file://app/Http/Controllers/BillController.php#L30-L47)

### POST /bills
Creates a new bill.

**Request Body**
```json
{
  "bill_number": "string",
  "seller": "string",
  "buyer": "string",
  "note": "string"
}
```

**Validation Rules**
- `bill_number`: Optional, string, max 20 characters, unique
- `seller`: Optional, string, max 255 characters
- `buyer`: Optional, string, max 255 characters
- `note`: Optional, string, max 65535 characters

**Section sources**
- [StoreBillRequest.php](file://app/Http/Requests/StoreBillRequest.php#L25-L44)
- [BillController.php](file://app/Http/Controllers/BillController.php#L50-L59)

### GET /bills/{bill}
Retrieves a specific bill with all related data.

**Response Schema**
```json
{
  "id": 0,
  "slug": "string",
  "bill_number": "string",
  "seller": "string",
  "buyer": "string",
  "note": "string",
  "containers_count": 0,
  "final_samples_count": 0,
  "average_outurn": 0.0,
  "containers": [
    {
      "id": 0,
      "bill_id": 0,
      "truck": "string",
      "container_number": "string",
      "quantity_of_bags": 0,
      "w_jute_bag": 0.0,
      "w_total": 0,
      "w_truck": 0,
      "w_container": 0,
      "w_gross": 0,
      "w_dunnage_dribag": 0,
      "w_tare": 0,
      "w_net": 0,
      "note": "string",
      "average_moisture": 0.0,
      "outturn_rate": 0.0,
      "created_at": "string",
      "updated_at": "string"
    }
  ],
  "final_samples": [
    {
      "id": 0,
      "bill_id": 0,
      "container_id": 0,
      "type": 0,
      "type_label": "string",
      "moisture": 0.0,
      "sample_weight": 0,
      "nut_count": 0,
      "w_reject_nut": 0,
      "w_defective_nut": 0,
      "w_defective_kernel": 0,
      "w_good_kernel": 0,
      "w_sample_after_cut": 0,
      "outturn_rate": 0.0,
      "note": "string",
      "defective_ratio": {
        "defective_nut": 0,
        "defective_kernel": 0,
        "ratio": 0.0,
        "formatted": "string"
      },
      "is_final_sample": true,
      "is_container_test": false,
      "created_at": "string",
      "updated_at": "string"
    }
  ],
  "cutting_tests": [
    {
      "id": 0,
      "bill_id": 0,
      "container_id": 0,
      "type": 0,
      "type_label": "string",
      "moisture": 0.0,
      "sample_weight": 0,
      "nut_count": 0,
      "w_reject_nut": 0,
      "w_defective_nut": 0,
      "w_defective_kernel": 0,
      "w_good_kernel": 0,
      "w_sample_after_cut": 0,
      "outturn_rate": 0.0,
      "note": "string",
      "defective_ratio": {
        "defective_nut": 0,
        "defective_kernel": 0,
        "ratio": 0.0,
        "formatted": "string"
      },
      "is_final_sample": false,
      "is_container_test": true,
      "created_at": "string",
      "updated_at": "string"
    }
  ],
  "created_at": "string",
  "updated_at": "string"
}
```

**Section sources**
- [BillController.php](file://app/Http/Controllers/BillController.php#L62-L73)

### PUT /bills/{bill}
Updates an existing bill.

**Request Body**
Same as POST /bills.

**Section sources**
- [UpdateBillRequest.php](file://app/Http/Requests/UpdateBillRequest.php#L25-L50)
- [BillController.php](file://app/Http/Controllers/BillController.php#L76-L87)

### DELETE /bills/{bill}
Deletes a bill and all related containers and cutting tests.

**Section sources**
- [BillController.php](file://app/Http/Controllers/BillController.php#L90-L99)

## Container Tracking API

Provides CRUD operations for container tracking.

### GET /containers
Retrieves a paginated list of containers with filtering.

**Query Parameters**
- `container_number`: Filter by container number
- `truck`: Filter by truck number
- `bill_info`: Search in bill number, seller, or buyer
- `date_from`: Filter by creation date (YYYY-MM-DD)
- `date_to`: Filter by creation date (YYYY-MM-DD)

**Response Schema**
```json
{
  "data": [
    {
      "id": 0,
      "bill_id": 0,
      "truck": "string",
      "container_number": "string",
      "quantity_of_bags": 0,
      "w_jute_bag": 0.0,
      "w_total": 0,
      "w_truck": 0,
      "w_container": 0,
      "w_gross": 0,
      "w_dunnage_dribag": 0,
      "w_tare": 0,
      "w_net": 0,
      "note": "string",
      "average_moisture": 0.0,
      "outturn_rate": 0.0,
      "bill": {
        "id": 0,
        "slug": "string",
        "bill_number": "string",
        "seller": "string",
        "buyer": "string",
        "note": "string",
        "containers_count": 0,
        "final_samples_count": 0,
        "average_outurn": 0.0,
        "created_at": "string",
        "updated_at": "string"
      },
      "cutting_tests": [
        {
          "id": 0,
          "bill_id": 0,
          "container_id": 0,
          "type": 0,
          "type_label": "string",
          "moisture": 0.0,
          "sample_weight": 0,
          "nut_count": 0,
          "w_reject_nut": 0,
          "w_defective_nut": 0,
          "w_defective_kernel": 0,
          "w_good_kernel": 0,
          "w_sample_after_cut": 0,
          "outturn_rate": 0.0,
          "note": "string",
          "defective_ratio": {
            "defective_nut": 0,
            "defective_kernel": 0,
            "ratio": 0.0,
            "formatted": "string"
          },
          "is_final_sample": false,
          "is_container_test": true,
          "created_at": "string",
          "updated_at": "string"
        }
      ],
      "created_at": "string",
      "updated_at": "string"
    }
  ],
  "pagination": {
    "current_page": 0,
    "last_page": 0,
    "per_page": 0,
    "total": 0,
    "from": 0,
    "to": 0,
    "links": []
  },
  "filters": {}
}
```

**Section sources**
- [ContainerController.php](file://app/Http/Controllers/ContainerController.php#L30-L59)

### POST /containers
Creates a new container.

**Request Body**
```json
{
  "bill_id": 0,
  "truck": "string",
  "container_number": "string",
  "quantity_of_bags": 0,
  "w_jute_bag": 0.0,
  "w_total": 0,
  "w_truck": 0,
  "w_container": 0,
  "w_dunnage_dribag": 0,
  "note": "string"
}
```

**Validation Rules**
- `bill_id`: Required, integer, exists in bills table
- `container_number`: Optional, string, exactly 11 characters, must match ISO format (4 letters + 7 digits)
- `w_jute_bag`: Optional, numeric, minimum 0, maximum 99.99
- All weight fields: Optional, integer, minimum 0

**Section sources**
- [StoreContainerRequest.php](file://app/Http/Requests/StoreContainerRequest.php#L25-L57)
- [ContainerController.php](file://app/Http/Controllers/ContainerController.php#L85-L94)

### GET /containers/{container}
Retrieves a specific container with all related data.

**Response Schema**
Same as GET /containers response data structure.

**Section sources**
- [ContainerController.php](file://app/Http/Controllers/ContainerController.php#L97-L110)

### PUT /containers/{container}
Updates an existing container.

**Request Body**
Same as POST /containers.

**Section sources**
- [UpdateContainerRequest.php](file://app/Http/Requests/UpdateContainerRequest.php#L25-L57)
- [ContainerController.php](file://app/Http/Controllers/ContainerController.php#L113-L124)

### DELETE /containers/{container}
Deletes a container and all related cutting tests.

**Section sources**
- [ContainerController.php](file://app/Http/Controllers/ContainerController.php#L127-L135)

## Cutting Test API

Provides CRUD operations for cutting tests.

### GET /cutting-tests
Retrieves a list of cutting tests for a specific bill.

**Query Parameters**
- `bill_id`: Required, filters tests by bill ID

**Response Schema**
```json
{
  "data": [
    {
      "id": 0,
      "bill_id": 0,
      "container_id": 0,
      "type": 0,
      "type_label": "string",
      "moisture": 0.0,
      "sample_weight": 0,
      "nut_count": 0,
      "w_reject_nut": 0,
      "w_defective_nut": 0,
      "w_defective_kernel": 0,
      "w_good_kernel": 0,
      "w_sample_after_cut": 0,
      "outturn_rate": 0.0,
      "note": "string",
      "defective_ratio": {
        "defective_nut": 0,
        "defective_kernel": 0,
        "ratio": 0.0,
        "formatted": "string"
      },
      "is_final_sample": false,
      "is_container_test": false,
      "bill": {
        "id": 0,
        "slug": "string",
        "bill_number": "string",
        "seller": "string",
        "buyer": "string",
        "note": "string",
        "containers_count": 0,
        "final_samples_count": 0,
        "average_outurn": 0.0,
        "created_at": "string",
        "updated_at": "string"
      },
      "container": {
        "id": 0,
        "bill_id": 0,
        "truck": "string",
        "container_number": "string",
        "quantity_of_bags": 0,
        "w_jute_bag": 0.0,
        "w_total": 0,
        "w_truck": 0,
        "w_container": 0,
        "w_gross": 0,
        "w_dunnage_dribag": 0,
        "w_tare": 0,
        "w_net": 0,
        "note": "string",
        "average_moisture": 0.0,
        "outturn_rate": 0.0,
        "created_at": "string",
        "updated_at": "string"
      },
      "created_at": "string",
      "updated_at": "string"
    }
  ],
  "bill_id": 0
}
```

**Section sources**
- [CuttingTestController.php](file://app/Http/Controllers/CuttingTestController.php#L25-L37)

### POST /cutting-tests
Creates a new cutting test.

**Request Body**
```json
{
  "bill_id": 0,
  "container_id": 0,
  "type": 0,
  "moisture": 0.0,
  "sample_weight": 0,
  "nut_count": 0,
  "w_reject_nut": 0,
  "w_defective_nut": 0,
  "w_defective_kernel": 0,
  "w_good_kernel": 0,
  "w_sample_after_cut": 0,
  "outturn_rate": 0.0,
  "note": "string"
}
```

**Validation Rules**
- `bill_id`: Required, integer, exists in bills table
- `container_id`: Conditional validation based on type
- `type`: Required, integer, must be one of: 1 (Final First Cut), 2 (Final Second Cut), 3 (Final Third Cut), 4 (Container Cut)
- `moisture`: Optional, numeric, minimum 0, maximum 100
- `sample_weight`: Required, integer, minimum 1, maximum 65535
- `outturn_rate`: Optional, numeric, minimum 0, maximum 60
- All weight fields: Optional, integer, minimum 0

**Business Rules**
- Final sample tests (types 1-3) cannot have a container_id
- Container tests (type 4) must have a container_id

**Section sources**
- [CuttingTestType.php](file://app/Enums/CuttingTestType.php#L5-L37)
- [StoreCuttingTestRequest.php](file://app/Http/Requests/StoreCuttingTestRequest.php#L25-L87)
- [CuttingTestController.php](file://app/Http/Controllers/CuttingTestController.php#L40-L49)

### GET /cutting-tests/{cuttingTest}
Retrieves a specific cutting test with all related data.

**Response Schema**
Same as GET /cutting-tests response data structure.

**Section sources**
- [CuttingTestController.php](file://app/Http/Controllers/CuttingTestController.php#L52-L63)

### PUT /cutting-tests/{cuttingTest}
Updates an existing cutting test.

**Request Body**
Same as POST /cutting-tests.

**Section sources**
- [CuttingTestController.php](file://app/Http/Controllers/CuttingTestController.php#L66-L77)

### DELETE /cutting-tests/{cuttingTest}
Deletes a cutting test.

**Section sources**
- [CuttingTestController.php](file://app/Http/Controllers/CuttingTestController.php#L80-L91)

## Resource Transformation
The API uses Laravel Resources to transform model data into consistent JSON responses. Resources handle relationships, computed fields, and conditional data loading.

### BillResource
Transforms Bill models including computed fields:
- `containers_count`: Count of related containers
- `final_samples_count`: Count of related final samples
- `average_outurn`: Average outturn rate of final samples
- `containers`: Collection of related containers
- `final_samples`: Collection of related final samples
- `cutting_tests`: Collection of all related cutting tests

**Section sources**
- [BillResource.php](file://app/Http/Resources/BillResource.php#L25-L42)

### ContainerResource
Transforms Container models including computed fields:
- `average_moisture`: Average moisture of related cutting tests
- `outturn_rate`: Outturn rate from first cutting test
- `bill`: Related bill data
- `cutting_tests`: Collection of related cutting tests

**Section sources**
- [ContainerResource.php](file://app/Http/Resources/ContainerResource.php#L25-L62)

### CuttingTestResource
Transforms CuttingTest models including computed fields:
- `type_label`: Human-readable label for test type
- `defective_ratio`: Calculated ratio of defective kernel to defective nut
- `is_final_sample`: Boolean indicating if test is a final sample
- `is_container_test`: Boolean indicating if test is a container test
- `bill`: Related bill data
- `container`: Related container data

**Section sources**
- [CuttingTestResource.php](file://app/Http/Resources/CuttingTestResource.php#L25-L66)

## Error Response Format
All error responses follow a consistent format:

```json
{
  "message": "Error description",
  "errors": {
    "field_name": [
      "Specific validation error message"
    ]
  }
}
```

For validation errors, the `errors` object contains field-specific error messages. For other errors, only the `message` field is present.

## Query Parameters
The API supports various query parameters for filtering, sorting, and pagination:

### Filtering
- `search`: Global search across multiple fields
- `container_number`, `truck`, `bill_info`: Container-specific filters
- `date_from`, `date_to`: Date range filters

### Sorting
- `sort_by`: Field to sort by
- `sort_direction`: Direction of sort (asc/desc)

### Pagination
All list endpoints return Laravel's default pagination structure with links and meta information.

## Usage Examples

### Get Dashboard Statistics
```bash
curl -X GET "http://cfccashew.test/api/dashboard/stats" \
  -H "Authorization: Bearer your-api-token" \
  -H "Accept: application/json"
```

```javascript
fetch('/api/dashboard/stats', {
  method: 'GET',
  headers: {
    'Authorization': 'Bearer your-api-token',
    'Accept': 'application/json'
  }
})
.then(response => response.json())
.then(data => console.log(data));
```

### Create a New Bill
```bash
curl -X POST "http://cfccashew.test/bills" \
  -H "Authorization: Bearer your-api-token" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "bill_number": "BN001",
    "seller": "Cashew Supplier Inc",
    "buyer": "Nut Distributors Ltd"
  }'
```

```javascript
fetch('/bills', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer your-api-token',
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    bill_number: 'BN001',
    seller: 'Cashew Supplier Inc',
    buyer: 'Nut Distributors Ltd'
  })
})
.then(response => response.json())
.then(data => console.log(data));
```

### Get Containers with High Moisture
```bash
curl -X GET "http://cfccashew.test/api/dashboard/containers-high-moisture" \
  -H "Authorization: Bearer your-api-token" \
  -H "Accept: application/json"
```

```javascript
fetch('/api/dashboard/containers-high-moisture', {
  method: 'GET',
  headers: {
    'Authorization': 'Bearer your-api-token',
    'Accept': 'application/json'
  }
})
.then(response => response.json())
.then(data => console.log(data));
```

## Rate Limiting and Security
The API implements the following security measures:

- **Authentication**: All endpoints require Laravel Sanctum authentication
- **Authorization**: Users can only access data they have permission to view
- **Input Validation**: All