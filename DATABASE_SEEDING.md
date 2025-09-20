# Database Seeding with Real Data

This project uses real CFCCashew inspection data for development and testing.

## Real Data Location
The real data is stored in `/database/seeders/import_data.sql` and contains:
- **14 Bills** from BK AGRO to KIỀU LOAN
- **76 Containers** with actual weights and measurements
- **122 Cutting Tests** with real moisture and quality data

## How to Import Real Data

### Method 1: Using Composer Scripts (Recommended)
```bash
# Fresh migration + import real data
composer fresh

# Or specifically import data
composer import-data
```

### Method 2: Using Artisan Commands
```bash
# Fresh migration + seed with real data
php artisan migrate:fresh --seed

# Or just import real data (clears existing data first)
php artisan db:seed --class=ImportDataSeeder

# Or use the custom command
php artisan data:import --fresh
```

### Method 3: Using the Custom Command
```bash
# Import data only
php artisan data:import

# Fresh migration + import data
php artisan data:import --fresh
```

## Data Overview

### Bills (14 records)
- Bill numbers like: ONEYLOSF01332500, GOSULOS25005163
- Seller: BK AGRO
- Buyer: KIỀU LOAN

### Containers (76 records)
- Container numbers in ISO format (e.g., ONEU5619273, TRHU4279171)
- Quantity of bags: 320-396 bags
- Net weights: ~26-28 tons per container

### Cutting Tests (122 records)
- Types: 1-3 (final samples), 4 (container tests)
- Moisture levels: 8.7% - 11.3%
- Outturn rates: 41-50 lbs/80kg

## Testing with Real Data

When running tests or developing features, the system will automatically use this real data, providing:
- Realistic data volumes
- Actual business scenarios
- Real moisture and quality variations
- Proper relationships between bills, containers, and tests

## Notes

- The `ImportDataSeeder` automatically clears existing data before importing
- All foreign key relationships are properly maintained
- Data includes both container-specific tests and final sample tests
- Timestamps are set to NULL in the import data (will use current time when created via app)