# Requirements Document

## Introduction

The CFCCashew Inspection System needs to be refactored to properly reflect the current database schema. There are discrepancies between the database migrations and the current backend models, services, and frontend components. The database contains additional fields and relationships that are not being utilized in the application layer, leading to incomplete functionality and potential data integrity issues.

## Requirements

### Requirement 1

**User Story:** As a system administrator, I want the backend models to accurately reflect the database schema, so that all database fields are properly accessible and manageable through the application.

#### Acceptance Criteria

1. WHEN the Bill model is accessed THEN the system SHALL include all database fields in the fillable array including w_dunnage_dribag, w_jute_bag, net_on_bl, quantity_of_bags_on_bl, origin, inspection_start_date, inspection_end_date, inspection_location, and sampling_ratio
2. WHEN the Container model is accessed THEN the system SHALL include all database fields in the fillable array including container_condition, seal_condition, and all weight-related fields
3. WHEN models are cast THEN the system SHALL properly cast all database fields to their appropriate types according to the migration schema
4. WHEN weight calculations are performed THEN the system SHALL use the correct fields from the database schema

### Requirement 2

**User Story:** As a developer, I want the services to handle all business logic correctly using the complete database schema, so that weight calculations and validations work as intended.

#### Acceptance Criteria

1. WHEN BillService performs operations THEN the system SHALL handle all Bill fields including inspection dates, origin, and sampling ratio
2. WHEN ContainerService calculates weights THEN the system SHALL use w_dunnage_dribag from the Bill model and w_jute_bag from the Bill model as specified in the business rules
3. WHEN CuttingTestService validates data THEN the system SHALL implement all validation alerts as specified in the business rules
4. WHEN services perform calculations THEN the system SHALL use the correct formulas with all required database fields

### Requirement 3

**User Story:** As a user, I want the frontend forms and displays to show all relevant data fields, so that I can input and view complete information about bills, containers, and cutting tests.

#### Acceptance Criteria

1. WHEN creating or editing a Bill THEN the system SHALL provide form fields for all database columns including inspection details, origin, and sampling ratio
2. WHEN viewing a Bill THEN the system SHALL display all relevant information from the database
3. WHEN creating or editing a Container THEN the system SHALL provide form fields for container condition, seal condition, and all weight fields
4. WHEN viewing Container data THEN the system SHALL display calculated weights using the correct database fields

### Requirement 4

**User Story:** As a data analyst, I want the repositories to provide efficient queries for all database fields, so that I can retrieve complete and accurate data for reporting and analysis.

#### Acceptance Criteria

1. WHEN BillRepository queries data THEN the system SHALL include methods to filter and retrieve by all database fields
2. WHEN ContainerRepository queries data THEN the system SHALL provide methods to search by container condition, seal condition, and weight ranges
3. WHEN CuttingTestRepository queries data THEN the system SHALL maintain existing functionality while supporting all database fields
4. WHEN repositories perform joins THEN the system SHALL correctly handle the many-to-many relationship between Bills and Containers

### Requirement 5

**User Story:** As a quality control inspector, I want the system to properly validate data entry and calculations, so that I can trust the accuracy of inspection results.

#### Acceptance Criteria

1. WHEN entering container data THEN the system SHALL validate container_number format (4 letters + 7 digits)
2. WHEN calculating weights THEN the system SHALL alert users to discrepancies as defined in the business rules
3. WHEN entering cutting test data THEN the system SHALL implement all validation alerts for weight discrepancies
4. WHEN saving data THEN the system SHALL ensure data integrity across all related models

### Requirement 6

**User Story:** As a system user, I want the frontend TypeScript interfaces to match the backend data structures, so that the application provides type safety and prevents runtime errors.

#### Acceptance Criteria

1. WHEN frontend components access data THEN the system SHALL provide TypeScript interfaces that match all database fields
2. WHEN API responses are received THEN the system SHALL have proper typing for all model attributes
3. WHEN forms are submitted THEN the system SHALL validate data types match the expected backend schema
4. WHEN displaying data THEN the system SHALL handle null values appropriately for optional database fields