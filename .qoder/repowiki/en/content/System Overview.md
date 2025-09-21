# System Overview

<cite>
**Referenced Files in This Document**   
- [Bill.php](file://app/Models/Bill.php)
- [Container.php](file://app/Models/Container.php)
- [CuttingTest.php](file://app/Models/CuttingTest.php)
- [CuttingTestType.php](file://app/Enums/CuttingTestType.php)
- [BillController.php](file://app/Http/Controllers/BillController.php)
- [ContainerController.php](file://app/Http/Controllers/ContainerController.php)
- [CuttingTestController.php](file://app/Http/Controllers/CuttingTestController.php)
- [DashboardController.php](file://app/Http/Controllers/Api/DashboardController.php)
- [BillService.php](file://app/Services/BillService.php)
- [ContainerService.php](file://app/Services/ContainerService.php)
- [CuttingTestService.php](file://app/Services/CuttingTestService.php)
- [web.php](file://routes/web.php)
- [api.php](file://routes/api.php)
- [app.ts](file://resources/js/app.ts)
- [app.blade.php](file://resources/views/app.blade.php)
- [ARCHITECTURE.md](file://ARCHITECTURE.md)
</cite>

## Table of Contents
1. [Introduction](#introduction)
2. [Project Structure](#project-structure)
3. [Core Components](#core-components)
4. [Architecture Overview](#architecture-overview)
5. [Detailed Component Analysis](#detailed-component-analysis)
6. [Dependency Analysis](#dependency-analysis)
7. [Performance Considerations](#performance-considerations)
8. [Troubleshooting Guide](#troubleshooting-guide)
9. [Conclusion](#conclusion)

## Introduction
The CFCCashew Inspection System is a full-stack web application designed to manage cashew processing operations. It provides comprehensive functionality for tracking bills, managing containers, and analyzing cutting test results. The system enables quality control through moisture and outturn rate metrics, supports user authentication with two-factor verification, and delivers real-time analytics via a dashboard interface. Built using Laravel and Vue 3 with Inertia.js, the application follows modern full-stack patterns to deliver a seamless user experience while maintaining clean separation of concerns between frontend and backend layers.

## Project Structure

```mermaid
graph TD
subgraph "Frontend"
A[Vue 3 Components]
B[Inertia.js Pages]
C[Composables]
D[Layouts]
end
subgraph "Backend"
E[Laravel Controllers]
F[Services]
G[Queries/Repositories]
H[Models]
I[Resources]
end
subgraph "Integration"
J[Inertia Responses]
K[API Routes]
L[Web Routes]
end
A --> J
B --> J
C --> B
D --> B
E --> J
F --> E
G --> F
H --> G
I --> E
J --> K
J --> L
```

**Diagram sources**
- [app.ts](file://resources/js/app.ts)
- [app.blade.php](file://resources/views/app.blade.php)
- [web.php](file://routes/web.php)
- [api.php](file://routes/api.php)

**Section sources**
- [app.ts](file://resources/js/app.ts)
- [app.blade.php](file://resources/views/app.blade.php)
- [web.php](file://routes/web.php)
- [api.php](file://routes/api.php)

## Core Components

The CFCCashew Inspection System consists of three primary business domains: Bills, Containers, and Cutting Tests. Each domain follows a consistent architectural pattern with dedicated models, controllers, services, repositories, queries, and frontend components. The system also includes authentication functionality with two-factor support and a dashboard for analytics and alerts. Key features include bill tracking with final sample calculations, container weight computations, cutting test analysis for quality control, and real-time statistics on moisture levels and processing status.

**Section sources**
- [Bill.php](file://app/Models/Bill.php)
- [Container.php](file://app/Models/Container.php)
- [CuttingTest.php](file://app/Models/CuttingTest.php)
- [CuttingTestType.php](file://app/Enums/CuttingTestType.php)

## Architecture Overview

```mermaid
graph TD
A[Vue 3 Frontend] --> |Inertia.js| B[Laravel Backend]
B --> C[Services]
C --> D[Repositories/Queries]
D --> E[Eloquent Models]
E --> F[Database]
G[API Routes] --> B
H[Web Routes] --> B
I[Dashboard API] --> C
style A fill:#4ade80,stroke:#16a34a
style B fill:#3b82f6,stroke:#1d4ed8
style C fill:#8b5cf6,stroke:#7c3aed
style D fill:#f97316,stroke:#ea580c
style E fill:#ec4899,stroke:#be185d
style F fill:#14b8a6,stroke:#0f766e
```

**Diagram sources**
- [ARCHITECTURE.md](file://ARCHITECTURE.md)
- [app.ts](file://resources/js/app.ts)
- [web.php](file://routes/web.php)
- [api.php](file://routes/api.php)

## Detailed Component Analysis

### Bill Management System
The bill management system handles the creation and tracking of cashew processing bills. Each bill can have multiple containers and cutting tests associated with it. The system calculates average outturn rates from final sample tests and provides filtering capabilities for recent bills, those pending final tests, or missing final samples. Bills are identified by a slug format that combines ID and bill number for clean URLs.

```mermaid
classDiagram
class Bill {
+string bill_number
+string seller
+string buyer
+string note
+getAverageOutturnAttribute() float
+getSlugAttribute() string
+resolveRouteBinding() Model
}
class BillController {
+index() Response
+create() Response
+store() RedirectResponse
+show() Response
+edit() Response
+update() RedirectResponse
+destroy() RedirectResponse
}
class BillService {
+getAllBills() LengthAwarePaginator
+getBillById() Bill
+createBill() Bill
+updateBill() bool
+deleteBill() bool
+getBillStatistics() array
}
BillController --> BillService : "uses"
BillService --> BillRepository : "uses"
BillService --> BillQuery : "uses"
Bill "1" --> "0..*" Container : "has"
Bill "1" --> "0..*" CuttingTest : "has"
Bill "1" --> "0..3" CuttingTest : "final samples"
```

**Diagram sources**
- [Bill.php](file://app/Models/Bill.php)
- [BillController.php](file://app/Http/Controllers/BillController.php)
- [BillService.php](file://app/Services/BillService.php)

**Section sources**
- [Bill.php](file://app/Models/Bill.php)
- [BillController.php](file://app/Http/Controllers/BillController.php)
- [BillService.php](file://app/Services/BillService.php)

### Container Management System
The container management system handles the tracking of individual cashew containers, including weight calculations and moisture analysis. It automatically computes gross weight, tare weight, and net weight based on input parameters. Containers are linked to bills and can have multiple cutting tests. The system identifies containers with high moisture levels (above 11%) and those pending cutting tests for quality control purposes.

```mermaid
classDiagram
class Container {
+integer bill_id
+string container_number
+integer quantity_of_bags
+decimal w_jute_bag
+integer w_total
+integer w_truck
+integer w_container
+integer w_gross
+integer w_dunnage_dribag
+decimal w_tare
+decimal w_net
+getAverageMoistureAttribute() float
+getOutturnRateAttribute() float
+resolveRouteBinding() Model
}
class ContainerController {
+index() Response
+create() Response
+store() RedirectResponse
+show() Response
+edit() Response
+update() RedirectResponse
+destroy() RedirectResponse
}
class ContainerService {
+getContainerById() Container
+getContainerByIdentifier() Container
+createContainer() Container
+updateContainer() bool
+deleteContainer() bool
+getContainersWithHighMoisture() Collection
+getContainerStatistics() array
+calculateWeights() array
}
ContainerController --> ContainerService : "uses"
ContainerService --> ContainerRepository : "uses"
ContainerService --> ContainerQuery : "uses"
Container "1" --> "0..*" CuttingTest : "has"
Container --> Bill : "belongs to"
```

**Diagram sources**
- [Container.php](file://app/Models/Container.php)
- [ContainerController.php](file://app/Http/Controllers/ContainerController.php)
- [ContainerService.php](file://app/Services/ContainerService.php)

**Section sources**
- [Container.php](file://app/Models/Container.php)
- [ContainerController.php](file://app/Http/Controllers/ContainerController.php)
- [ContainerService.php](file://app/Services/ContainerService.php)

### Cutting Test Analysis System
The cutting test analysis system manages quality control tests for cashew processing. It supports different test types including final samples (first, second, third cut) and container cuts. The system calculates moisture distribution and identifies tests with high moisture levels. Cutting tests are associated with either bills (for final samples) or specific containers, enabling comprehensive quality tracking throughout the processing workflow.

```mermaid
classDiagram
class CuttingTest {
+integer bill_id
+integer container_id
+integer type
+decimal moisture
+integer sample_weight
+integer nut_count
+integer w_reject_nut
+integer w_defective_nut
+integer w_defective_kernel
+integer w_good_kernel
+integer w_sample_after_cut
+decimal outturn_rate
+getTypeEnum() CuttingTestType
+isFinalSample() bool
+isContainerTest() bool
}
class CuttingTestType {
+FinalFirstCut = 1
+FinalSecondCut = 2
+FinalThirdCut = 3
+ContainerCut = 4
+label() string
+isFinalSample() bool
+isContainerTest() bool
}
class CuttingTestController {
+index() Response
+create() Response
+store() RedirectResponse
+show() Response
+edit() Response
+update() RedirectResponse
+destroy() RedirectResponse
}
CuttingTestController --> CuttingTestService : "uses"
CuttingTestService --> CuttingTestRepository : "uses"
CuttingTestService --> CuttingTestQuery : "uses"
CuttingTest --> Bill : "belongs to"
CuttingTest --> Container : "belongs to"
CuttingTest --> CuttingTestType : "enum"
```

**Diagram sources**
- [CuttingTest.php](file://app/Models/CuttingTest.php)
- [CuttingTestType.php](file://app/Enums/CuttingTestType.php)
- [CuttingTestController.php](file://app/Http/Controllers/CuttingTestController.php)
- [CuttingTestService.php](file://app/Services/CuttingTestService.php)

**Section sources**
- [CuttingTest.php](file://app/Models/CuttingTest.php)
- [CuttingTestType.php](file://app/Enums/CuttingTestType.php)
- [CuttingTestController.php](file://app/Http/Controllers/CuttingTestController.php)
- [CuttingTestService.php](file://app/Services/CuttingTestService.php)

### Dashboard and Analytics System
The dashboard system provides real-time analytics and alerts for cashew processing operations. It aggregates data from bills, containers, and cutting tests to display key performance indicators and highlight potential issues. The system includes widgets for recent bills, pending tests, missing samples, and high moisture alerts. Data is delivered through API endpoints that return structured JSON responses for frontend consumption.

```mermaid
sequenceDiagram
participant Frontend as Dashboard UI
participant Controller as DashboardController
participant Service as Bill/Container/CuttingTestService
participant Query as Bill/Container/CuttingTestQuery
Frontend->>Controller : GET /api/dashboard/stats
Controller->>Service : getBillStatistics()
Controller->>Service : getContainerStatistics()
Controller->>Service : getCuttingTestStatistics()
Service->>Query : getBillsPendingFinalTests()
Service->>Query : getContainersWithHighMoisture()
Service->>Query : getMoistureDistribution()
Query-->>Service : Collections
Service-->>Controller : Statistics arrays
Controller-->>Frontend : JSON response
Note over Controller,Query : Aggregates data from multiple services and queries
```

**Diagram sources**
- [DashboardController.php](file://app/Http/Controllers/Api/DashboardController.php)
- [BillService.php](file://app/Services/BillService.php)
- [ContainerService.php](file://app/Services/ContainerService.php)
- [CuttingTestService.php](file://app/Services/CuttingTestService.php)

**Section sources**
- [DashboardController.php](file://app/Http/Controllers/Api/DashboardController.php)
- [BillService.php](file://app/Services/BillService.php)
- [ContainerService.php](file://app/Services/ContainerService.php)
- [CuttingTestService.php](file://app/Services/CuttingTestService.php)

## Dependency Analysis

```mermaid
graph TD
A[Vue 3] --> B[Inertia.js]
B --> C[Laravel]
C --> D[BillService]
C --> E[ContainerService]
C --> F[CuttingTestService]
D --> G[BillRepository]
D --> H[BillQuery]
E --> I[ContainerRepository]
E --> J[ContainerQuery]
F --> K[CuttingTestRepository]
F --> L[CuttingTestQuery]
G --> M[Bill]
H --> M
I --> N[Container]
J --> N
K --> O[CuttingTest]
L --> O
M --> P[CuttingTestType]
N --> O
O --> P
style A fill:#4ade80,stroke:#16a34a
style B fill:#22d3ee,stroke:#0891b2
style C fill:#3b82f6,stroke:#1d4ed8
style D fill:#8b5cf6,stroke:#7c3aed
style E fill:#8b5cf6,stroke:#7c3aed
style F fill:#8b5cf6,stroke:#7c3aed
```

**Diagram sources**
- [package-lock.json](file://package-lock.json)
- [ARCHITECTURE.md](file://ARCHITECTURE.md)
- [app.ts](file://resources/js/app.ts)

**Section sources**
- [package-lock.json](file://package-lock.json)
- [ARCHITECTURE.md](file://ARCHITECTURE.md)

## Performance Considerations
The CFCCashew Inspection System implements several performance optimizations to ensure responsive operation with large datasets. The architecture separates read and write operations through dedicated Query and Repository classes, allowing optimized queries for reporting and analytics. Pagination is implemented across all list views to prevent memory issues with large result sets. Eager loading is used strategically to avoid N+1 query problems when retrieving related data. The dashboard API endpoints are designed to aggregate data efficiently, minimizing database queries while providing comprehensive statistics. Frontend components use Inertia.js for seamless page transitions without full reloads, enhancing perceived performance.

## Troubleshooting Guide
Common issues in the CFCCashew Inspection System typically relate to data consistency, routing, or authentication. For data issues, verify that weight calculations are correct and that required fields are populated. Routing problems may occur with container numbers that don't match the expected format (four letters followed by seven digits); ensure proper validation and error handling. Authentication issues, particularly with two-factor verification, should be checked in the user model and Fortify configuration. When debugging performance issues, examine query efficiency and consider adding indexes to frequently searched columns. For frontend issues, verify that Inertia.js is properly configured and that Vue components receive the expected data props.

**Section sources**
- [Container.php](file://app/Models/Container.php#L120-L140)
- [ContainerController.php](file://app/Http/Controllers/ContainerController.php#L70-L85)
- [app.ts](file://resources/js/app.ts)

## Conclusion
The CFCCashew Inspection System provides a comprehensive solution for managing cashew processing operations with robust features for bill tracking, container management, and quality control testing. The application follows a clean architectural pattern with clear separation of concerns between presentation, business logic, and data access layers. The integration of Laravel and Vue 3 through Inertia.js enables a responsive single-page application experience while maintaining server-side rendering benefits. Key strengths include the systematic approach to weight calculations, moisture monitoring, and outturn rate analysis, which support quality assurance in cashew processing. The dashboard analytics provide valuable insights into operational efficiency and potential issues, helping managers make informed decisions. The system's modular design allows for future enhancements and adaptation to evolving business requirements.