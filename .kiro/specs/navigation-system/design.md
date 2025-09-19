# Navigation System Design Document

## Overview

The navigation system will provide a comprehensive interface for the CFCCashew Inspection System, integrating seamlessly with the existing Laravel Starter Kit dashboard. The system will use a sidebar navigation pattern with dedicated sections for Bills, Containers, and Cutting Tests, while maintaining the current dashboard as the primary landing page with enhanced KPI widgets and alerts.

**Key Design Principles:**
- Desktop-first responsive design optimized for inspection workflows
- Seamless integration with existing dashboard layout and styling
- Single Page Application (SPA) experience using Inertia.js
- Consistent UI patterns across all sections using shadcn-vue components
- Efficient data loading with pagination and filtering capabilities

## Architecture

### Frontend Architecture

The navigation system will be built using Vue 3 with TypeScript and Inertia.js, following the existing application structure:

```
resources/js/
├── components/
│   ├── navigation/
│   │   ├── Sidebar.vue           # Main navigation sidebar
│   │   ├── Breadcrumb.vue        # Breadcrumb navigation
│   │   └── QuickActions.vue      # Dashboard quick action buttons
│   ├── bills/
│   │   ├── BillsList.vue         # Bills listing page
│   │   ├── BillDetail.vue        # Bill detail page
│   │   ├── BillForm.vue          # Bill creation/edit form
│   │   └── BillCard.vue          # Bill summary card
│   ├── containers/
│   │   ├── ContainersList.vue    # Containers listing page
│   │   ├── ContainerDetail.vue   # Container detail page
│   │   ├── ContainerForm.vue     # Container creation/edit form
│   │   └── ContainerCard.vue     # Container summary card
│   ├── cutting-tests/
│   │   ├── CuttingTestsList.vue  # Cutting tests listing page
│   │   ├── CuttingTestForm.vue   # Cutting test creation/edit form
│   │   └── CuttingTestCard.vue   # Cutting test summary card
│   └── dashboard/
│       ├── KpiCards.vue          # Enhanced KPI widgets
│       ├── AlertsPanel.vue       # Alerts for missing data/issues
│       └── RecentActivity.vue    # Recent activity widget
├── composables/
│   ├── useNavigation.ts          # Navigation state management
│   ├── usePagination.ts          # Pagination logic
│   ├── useFiltering.ts           # Search and filter logic
│   └── useBreadcrumbs.ts         # Breadcrumb management
└── layouts/
    └── AppLayout.vue             # Main application layout with sidebar
```

### Backend Architecture

The backend will extend the existing Laravel structure with new controllers and resources:

```
app/Http/
├── Controllers/
│   ├── BillController.php        # Bills CRUD operations
│   ├── ContainerController.php   # Containers CRUD operations
│   ├── CuttingTestController.php # Cutting tests CRUD operations
│   └── DashboardController.php   # Enhanced dashboard data
├── Resources/
│   ├── BillResource.php          # Bill API resource
│   ├── ContainerResource.php     # Container API resource
│   └── CuttingTestResource.php   # Cutting test API resource
└── Requests/
    ├── StoreBillRequest.php      # Bill validation
    ├── StoreContainerRequest.php # Container validation
    └── StoreCuttingTestRequest.php # Cutting test validation
```

## Components and Interfaces

### Navigation Components

#### Sidebar Navigation
- **Location**: Fixed left sidebar, collapsible on smaller screens
- **Sections**: Dashboard, Bills, Containers, Cutting Tests
- **Active State**: Visual indication of current section
- **Styling**: Consistent with Laravel Starter Kit theme using Tailwind CSS

#### Breadcrumb Navigation
- **Purpose**: Show current location hierarchy (e.g., Bills > Bill #123 > Container ABC)
- **Behavior**: Clickable navigation path with proper routing
- **Integration**: Automatically updates based on current route

### Data Display Components

#### Bills Section
- **List View**: Paginated table with bill_number, seller, buyer, container count, outurn
- **Detail View**: Complete bill information with expandable containers table
- **Search**: Filter by bill_number, seller, buyer
- **Actions**: Create new bill, edit existing, add containers/final samples

#### Containers Section
- **List View**: Paginated table with container_number, truck, bill info, weights, outurn
- **Detail View**: Complete container information with cutting tests
- **Search**: Filter by container_number, truck, bill info, date range
- **Actions**: View details, add cutting tests

#### Cutting Tests Section
- **List View**: Paginated table with test type, bill/container context, results
- **Filtering**: By test type (final samples vs container tests), bill number
- **Display**: Formatted moisture (xx.x%), defective ratios (xxx/yyy.y)

### Dashboard Enhancements

#### KPI Cards
- **Recent Bills**: Last 5 bills with quick access links
- **Pending Final Tests**: Bills missing final sample cutting tests
- **Container Summary**: Total containers processed, average weights
- **Quality Metrics**: Average moisture, outurn rates

#### Alerts Panel
- **Missing Final Samples**: Bills without complete final sample tests (types 1-3)
- **High Moisture Alerts**: Containers with moisture > 11%
- **Direct Actions**: Quick links to resolve identified issues

## Data Models

### Frontend Interfaces

```typescript
interface Bill {
  id: number;
  bill_number: string | null;
  seller: string | null;
  buyer: string | null;
  containers_count: number;
  final_samples_count: number;
  average_outurn?: number;
  note: string | null;
  created_at: string;
  updated_at: string;
}

interface Container {
  id: number;
  bill_id: number;
  bill: Bill;
  truck: string | null;
  container_number: string | null;
  quantity_of_bags: number | null;
  w_jute_bag: number;
  w_total: number | null;
  w_truck: number | null;
  w_container: number | null;
  w_gross: number | null;
  w_dunnage_dribag: number | null;
  w_tare: number | null;
  w_net: number | null;
  cutting_tests_count: number;
  average_moisture?: number;
  outurn_rate?: number;
  note: string | null;
  created_at: string;
  updated_at: string;
}

interface CuttingTest {
  id: number;
  bill_id: number;
  container_id: number | null;
  bill: Bill;
  container?: Container;
  type: 1 | 2 | 3 | 4;
  moisture: number | null;
  sample_weight: number;
  nut_count: number | null;
  w_reject_nut: number | null;
  w_defective_nut: number | null;
  w_defective_kernel: number | null;
  w_good_kernel: number | null;
  w_sample_after_cut: number | null;
  outturn_rate: number | null;
  note: string | null;
  created_at: string;
  updated_at: string;
}
```

### Navigation State Management

```typescript
interface NavigationState {
  currentSection: 'dashboard' | 'bills' | 'containers' | 'cutting-tests';
  breadcrumbs: BreadcrumbItem[];
  sidebarCollapsed: boolean;
}

interface BreadcrumbItem {
  label: string;
  route?: string;
  active: boolean;
}
```

## Error Handling

### Frontend Error Handling
- **Network Errors**: Toast notifications for failed requests
- **Validation Errors**: Inline form validation with clear error messages
- **Loading States**: Skeleton loaders and progress indicators
- **Empty States**: Informative messages when no data is available

### Backend Error Handling
- **Validation**: Laravel Form Requests with detailed error responses
- **Database Errors**: Graceful handling with user-friendly messages
- **Authorization**: Proper error responses for unauthorized access
- **Rate Limiting**: Protection against excessive requests

## Testing Strategy

### Frontend Testing
- **Unit Tests**: Vue component testing using Vitest
- **Integration Tests**: Navigation flow testing
- **E2E Tests**: Critical user journeys using Playwright
- **Accessibility Tests**: WCAG compliance verification

### Backend Testing
- **Feature Tests**: API endpoint testing using Pest PHP
- **Unit Tests**: Model and service layer testing
- **Database Tests**: Migration and relationship testing
- **Performance Tests**: Query optimization verification

### Test Coverage Areas
- Navigation between sections
- Data filtering and pagination
- Form validation and submission
- Dashboard widget functionality
- Responsive design behavior
- Error handling scenarios

## Performance Considerations

### Frontend Optimization
- **Lazy Loading**: Route-based code splitting
- **Virtual Scrolling**: For large data tables
- **Debounced Search**: Prevent excessive API calls
- **Caching**: Browser caching for static assets
- **Bundle Optimization**: Tree shaking and minification

### Backend Optimization
- **Database Indexing**: Optimized queries for filtering and sorting
- **Eager Loading**: Prevent N+1 query problems
- **Pagination**: Efficient data loading with cursor-based pagination
- **Caching**: Redis caching for dashboard widgets
- **Query Optimization**: Database query performance monitoring

## Security Considerations

### Authentication & Authorization
- **Laravel Fortify**: Existing authentication system integration
- **Route Protection**: Middleware for authenticated routes
- **CSRF Protection**: Form submission security
- **Input Validation**: Server-side validation for all inputs

### Data Security
- **SQL Injection Prevention**: Eloquent ORM usage
- **XSS Protection**: Output escaping and sanitization
- **File Upload Security**: Validation and storage restrictions
- **Rate Limiting**: API endpoint protection

## Responsive Design Strategy

### Desktop-First Approach
- **Primary Target**: Desktop screens (1024px+)
- **Navigation**: Fixed sidebar with full menu visibility
- **Data Tables**: Full column display with horizontal scrolling if needed
- **Forms**: Multi-column layouts for efficient data entry

### Tablet Adaptation (768px - 1023px)
- **Navigation**: Collapsible sidebar with overlay
- **Tables**: Responsive column hiding/stacking
- **Forms**: Single-column layouts with optimized spacing

### Mobile Considerations (< 768px)
- **Navigation**: Bottom navigation or hamburger menu
- **Tables**: Card-based and Accordion layouts for better mobile experience
- **Forms**: Touch-optimized inputs and buttons

## Integration Points

### Dashboard Integration
- **Existing Widgets**: Preserve current dashboard functionality
- **Enhanced KPIs**: Add inspection-specific metrics
- **Quick Actions**: Direct navigation to common tasks
- **Alert System**: Proactive issue identification

### Laravel Starter Kit Integration
- **Authentication**: Use existing Fortify authentication
- **Styling**: Maintain Tailwind CSS theme consistency
- **Layout**: Extend existing application layout
- **Components**: Utilize shadcn-vue component library

### Database Integration
- **Existing Schema**: Work with current database structure
- **Relationships**: Leverage Eloquent relationships
- **Migrations**: Minimal schema changes if needed
- **Seeding**: Test data for development and testing

## Design Rationale

### Sidebar Navigation Choice
- **Rationale**: Provides persistent access to all sections while maximizing content area
- **Alternative Considered**: Top navigation - rejected due to limited horizontal space for section names
- **Benefits**: Clear section hierarchy, easy navigation state management

### Desktop-First Responsive Design
- **Rationale**: Primary users are inspectors working on desktop/laptop computers
- **Alternative Considered**: Mobile-first - rejected as mobile usage is secondary
- **Benefits**: Optimized for primary use case, better data table experience

### Inertia.js SPA Experience
- **Rationale**: Maintains fast navigation while leveraging Laravel backend
- **Alternative Considered**: Traditional page reloads - rejected for poor UX
- **Benefits**: Fast transitions, maintained state, better user experience

### Integrated Dashboard Approach
- **Rationale**: Preserves existing workflow while adding navigation capabilities
- **Alternative Considered**: Separate dashboard page - rejected for workflow disruption
- **Benefits**: Familiar interface, enhanced functionality, seamless transition