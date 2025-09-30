# Backend Architecture

This document describes the refactored backend architecture following the Controller - Service - Query - Repository - Resource pattern.

## Architecture Overview

The backend follows a layered architecture with clear separation of concerns:

```
┌─────────────────┐
│   Controllers   │ ← HTTP layer (thin, handles requests/responses)
├─────────────────┤
│    Services     │ ← Business logic layer
├─────────────────┤
│ Queries/Repos   │ ← Data access layer
├─────────────────┤
│     Models      │ ← Eloquent models
└─────────────────┘
```

## Layer Responsibilities

### 1. Controllers (`app/Http/Controllers/`)

- **Purpose**: Handle HTTP requests and responses only
- **Responsibilities**:
    - Route handling
    - Request validation (via Form Requests)
    - Coordinate with Services
    - Return responses (Inertia or JSON)
- **Rules**:
    - Keep thin - no business logic
    - Use dependency injection for Services
    - Use Form Requests for validation
    - Use Resources for API responses

### 2. Services (`app/Services/`)

- **Purpose**: Contain business logic and orchestrate operations
- **Responsibilities**:
    - Business rules and validation
    - Coordinate between Repositories and Queries
    - Complex calculations and transformations
    - Transaction management
- **Examples**:
    - `BillService`: Manages bill operations, calculates averages
    - `ContainerService`: Handles weight calculations, container logic
    - `CuttingTestService`: Validates test types, calculates ratios

### 3. Repositories (`app/Repositories/`)

- **Purpose**: Encapsulate database write operations (CRUD)
- **Responsibilities**:
    - Create, Update, Delete operations
    - Simple queries by ID
    - Data persistence logic
- **Rules**:
    - One repository per main model
    - Focus on write operations
    - Use Eloquent models

### 4. Queries (`app/Queries/`)

- **Purpose**: Handle complex read operations and reporting
- **Responsibilities**:
    - Complex queries with filters
    - Pagination
    - Aggregations and statistics
    - Search functionality
- **Rules**:
    - Focus on read operations
    - Return Eloquent Collections or Builders
    - Handle filtering and sorting

### 5. Resources (`app/Http/Resources/`)

- **Purpose**: Transform models for API responses
- **Responsibilities**:
    - Data transformation
    - Conditional field inclusion
    - Relationship loading
    - Consistent API format

### 6. Form Requests (`app/Http/Requests/`)

- **Purpose**: Handle input validation
- **Responsibilities**:
    - Validation rules
    - Custom validation logic
    - Error messages
    - Authorization checks

### 7. Enums (`app/Enums/`)

- **Purpose**: Define domain constants and types
- **Examples**:
    - `CuttingTestType`: Defines test types with labels and behavior

## Key Classes

### Bills Domain

- `BillController`: HTTP handling
- `BillService`: Business logic (averages, statistics)
- `BillRepository`: CRUD operations
- `BillQuery`: Complex queries (search, filtering)
- `BillResource`: API transformation
- `StoreBillRequest`/`UpdateBillRequest`: Validation

### Containers Domain

- `ContainerController`: HTTP handling
- `ContainerService`: Weight calculations, moisture alerts
- `ContainerRepository`: CRUD operations
- `ContainerQuery`: Container queries with filters
- `ContainerResource`: API transformation
- `StoreContainerRequest`: Validation

### Cutting Tests Domain

- `CuttingTestController`: HTTP handling
- `CuttingTestService`: Test validation, ratio calculations
- `CuttingTestRepository`: CRUD operations
- `CuttingTestQuery`: Test queries, moisture distribution
- `CuttingTestResource`: API transformation
- `StoreCuttingTestRequest`: Validation

## Service Registration

All services are registered in `RepositoryServiceProvider`:

- Repositories as singletons
- Queries as singletons
- Services as singletons

## API Endpoints

Dashboard API endpoints (`routes/api.php`):

- `GET /api/dashboard/stats` - Overall statistics
- `GET /api/dashboard/recent-bills` - Recent bills widget
- `GET /api/dashboard/bills-pending-final-tests` - Pending tests alert
- `GET /api/dashboard/bills-missing-final-samples` - Missing samples alert
- `GET /api/dashboard/containers-high-moisture` - High moisture alert
- `GET /api/dashboard/moisture-distribution` - Moisture statistics

## Benefits

1. **Separation of Concerns**: Each layer has a single responsibility
2. **Testability**: Easy to unit test individual layers
3. **Maintainability**: Clear structure makes code easier to understand
4. **Reusability**: Services and Queries can be reused across controllers
5. **Consistency**: Standardized patterns across all domains
6. **Type Safety**: Full type hints and strict typing

## Usage Examples

### Creating a Bill

```php
// Controller
public function store(StoreBillRequest $request): RedirectResponse
{
    $bill = $this->billService->createBill($request->validated());
    return redirect()->route('bills.show', $bill->id);
}

// Service
public function createBill(array $data): Bill
{
    return $this->billRepository->create($data);
}
```

### Complex Query

```php
// Service
public function getAllBills(array $filters = [], int $perPage = 15): LengthAwarePaginator
{
    return $this->billQuery->paginate($filters, $perPage);
}

// Query
public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
{
    $bills = $this->search($filters)->paginate($perPage);
    // Transform data...
    return $bills;
}
```

This architecture provides a solid foundation for the CFCCashew Inspection System while maintaining Laravel best practices and ensuring code quality.
