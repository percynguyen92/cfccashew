# Implementation Plan

- [x] 1. Update backend models to reflect complete database schema





  - Update Bill model fillable array and casts to include all database fields
  - Update Container model fillable array and casts for missing fields
  - Fix weight calculation methods to use correct field sources
  - Verify CuttingTest model completeness
  - _Requirements: 1.1, 1.2, 1.3, 1.4_

- [x] 2. Refactor service layer business logic





- [x] 2.1 Update BillService for complete field handling


  - Implement CRUD operations for all Bill fields including inspection dates, origin, sampling ratio
  - Add validation methods for Bill-specific business rules
  - Update weight calculation coordination methods
  - _Requirements: 2.1, 2.2_

- [x] 2.2 Enhance ContainerService weight calculations


  - Fix weight calculation methods to reference Bill model for w_dunnage_dribag and w_jute_bag
  - Implement container and seal condition validation
  - Add weight discrepancy validation alerts
  - _Requirements: 2.2, 5.2_


- [x] 2.3 Improve CuttingTestService validation

  - Implement all validation alerts as per business rules
  - Add moisture validation and alerts (>11%)
  - Ensure proper outturn rate calculations with complete data
  - _Requirements: 2.3, 5.3_

- [x] 2.4 Write comprehensive service tests






  - Create unit tests for all service methods with complete field sets
  - Test weight calculation accuracy with edge cases
  - Test validation alert functionality
  - _Requirements: 2.1, 2.2, 2.3_

- [x] 3. Enhance repository layer data access





- [x] 3.1 Expand BillRepository query capabilities


  - Add query methods for inspection date ranges
  - Implement filtering by origin and sampling ratio
  - Optimize queries to include related data efficiently
  - _Requirements: 4.1, 4.4_

- [x] 3.2 Improve ContainerRepository functionality


  - Add queries for container/seal condition filtering
  - Implement weight range query methods
  - Add moisture alert queries for containers with >11% moisture
  - _Requirements: 4.2, 4.4_

- [x] 3.3 Verify CuttingTestRepository completeness


  - Add queries for validation alert detection
  - Implement improved type-specific filtering
  - Ensure efficient joins with Bills and Containers
  - _Requirements: 4.3, 4.4_

- [x] 3.4 Write repository tests

  **Implementation Summary:**
  - **BillRepository Tests:** 24 comprehensive tests covering all new query methods including `findByInspectionDateRange`, `findByOrigin`, `findBySamplingRatioRange`, `findWithCompleteRelations`, `getBillsWithHighMoistureContainers`, and advanced filtering with `findWithFilters`
  - **ContainerRepository Tests:** 30 comprehensive tests covering condition filtering, weight range queries, moisture alerts, weight discrepancy detection, and advanced filtering capabilities
  - **CuttingTestRepository Tests:** 26 comprehensive tests covering type-specific queries, validation alert detection, efficient relationship loading, and reporting queries with joins
  - **Performance Testing:** All repositories tested with large datasets (100-200+ records) with performance benchmarks ensuring queries complete within acceptable time limits
  - **Relationship Accuracy:** Comprehensive testing of many-to-many relationships, eager loading, and relationship counting accuracy
  - **Edge Cases:** Thorough testing of boundary conditions, validation alerts, and complex filtering scenarios

  - Create unit tests for all new query methods ✅
  - Test query performance with large datasets ✅
  - Verify relationship handling accuracy ✅
  - _Requirements: 4.1, 4.2, 4.3_

- [x] 4. Update API layer request handling and validation









- [x] 4.1 Update Bill request validation classes

  - Add validation rules for all Bill fields including dates, origin, sampling ratio
  - Implement custom validation for inspection date logic
  - Update form request classes to handle complete field sets


  - _Requirements: 5.4, 6.3_

- [x] 4.2 Enhance Container request validation

  - Add validation for container_number ISO format (4 letters + 7 digits)


  - Implement container and seal condition validation
  - Add weight field validation with business rule checks
  - _Requirements: 5.1, 5.4, 6.3_

- [x] 4.3 Update API controllers for complete data handling

  - Modify BillController to handle all database fields
  - Update ContainerController for condition fields and weight calculations
  - Ensure CuttingTestController supports all validation features
  - _Requirements: 1.1, 1.2, 2.1, 2.2_

- [x] 5. Create comprehensive TypeScript interfaces

- [x] 5.1 Define complete Bill interface

  - Create TypeScript interface matching all Bill database fields
  - Include proper typing for dates, decimals, and nullable fields
  - Add relationship type definitions
  - _Requirements: 6.1, 6.2_

- [x] 5.2 Define complete Container interface

  - Create TypeScript interface for all Container fields including conditions
  - Add proper typing for weight calculations and nullable fields
  - Include relationship and computed property types
  - _Requirements: 6.1, 6.2_

- [x] 5.3 Update CuttingTest interface

  - Verify and update CuttingTest interface completeness
  - Add validation alert types and computed properties
  - Ensure proper relationship typing
  - _Requirements: 6.1, 6.2_

- [-] 6. Refactor Vue.js components for complete data handling



- [x] 6.1 Update Bill form components

  - Add form fields for inspection dates with date pickers
  - Implement origin and sampling ratio input fields
  - Add validation feedback for all new fields
  - Update form submission to handle complete data
  - _Requirements: 3.1, 3.2, 6.3_

- [x] 6.2 Enhance Container form components

  - Add container and seal condition selection dropdowns
  - Update weight calculation displays with real-time updates
  - Implement weight discrepancy alert displays
  - Add validation feedback for container number format
  - _Requirements: 3.3, 3.4, 5.1, 5.2_

- [x] 6.3 Update display components for complete data



  - Modify Bill detail views to show inspection dates, origin, sampling ratio, and weight fields
  - Add container condition indicators and displays in container views
  - Implement weight discrepancy alerts in container views
  - Update cutting test displays with validation alerts
  - _Requirements: 3.2, 3.4, 5.2_

- [x] 7. Implement data validation and business rule alerts


- [x] 7.1 Add real-time weight calculation updates





  - Implement automatic weight recalculation when related fields change
  - Add visual indicators for calculated vs manual weight values
  - Display weight discrepancy warnings in real-time
  - _Requirements: 2.2, 5.2, 6.4_

- [x] 7.2 Implement cutting test validation alerts





  - Add visual alerts for all cutting test validation rules
  - Display moisture alerts for values >11%
  - Implement sample weight discrepancy warnings
  - Add defective nut/kernel ratio alerts
  - _Requirements: 2.3, 5.3, 6.4_

- [x] 8. Update database factories and seeders

- [x] 8.1 Update model factories for complete field coverage

  - Modify BillFactory to generate all database fields
  - Update ContainerFactory for condition fields and complete weights
  - Ensure CuttingTestFactory covers all validation scenarios
  - _Requirements: 1.1, 1.2, 1.3_

- [x] 8.2 Update database seeders

  - Modify DatabaseSeeder to use updated factories with complete field coverage
  - Ensure seeders populate all database fields including inspection dates, origins, conditions
  - Create edge case data for validation testing scenarios
  - _Requirements: 1.1, 1.2, 1.3_

- [x] 8.3 Write integration tests






  - Create end-to-end tests for complete workflows
  - Test API endpoints with full data structures
  - Verify frontend-backend data consistency
  - Test validation alerts and business rules
  - _Requirements: 2.1, 2.2, 2.3, 6.1, 6.2_