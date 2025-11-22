# Grievance Types Implementation - Feature Request

## Problem Description

The current Grievance model in the FUNAE system is not properly structured to handle the three required grievance types: **RECLAMACAO** (Complaint), **QUEIXA** (Grievance), and **SUGESTAO** (Suggestion).

### Current Issues Identified:

1. **Missing Type Field**: The Grievance model only has a generic `category` string field instead of a proper enum field for grievance types
2. **Inconsistent Categorization**: Current categories are generic service-based categories (e.g., "Serviços Públicos", "Infraestrutura") rather than the specific types required
3. **No Type Validation**: There's no database-level validation to ensure only the three valid types are used
4. **Missing Type-Specific Logic**: No business logic or UI components to handle different processing workflows based on grievance type

## Current Database Structure

**File**: `database/migrations/2025_11_14_121054_create_grievances_table.php`

```php
$table->string('category'); // ❌ Currently just a string
```

**Expected**: 
```php
$table->enum('type', ['RECLAMACAO', 'QUEIXA', 'SUGESTAO']); // ✅ Should be enum
```

## Impact Assessment

- **Data Integrity**: No guarantee that grievances are properly categorized by type
- **Business Logic**: Cannot implement type-specific workflows or routing
- **User Experience**: Cannot provide type-appropriate interfaces or processes
- **Reporting**: Cannot generate accurate statistics by grievance type
- **System Integration**: External systems cannot reliably identify grievance types

## Proposed Solution

### 1. Database Migration
Create a new migration to add the `type` enum field:

```php
// New migration: add_grievance_types_enum.php
Schema::table('grievances', function (Blueprint $table) {
    $table->enum('type', ['RECLAMACAO', 'QUEIXA', 'SUGESTAO'])->default('RECLAMACAO')->after('category');
});
```

### 2. Model Updates
**File**: `app/Models/Grievance.php`

- Add `type` to fillable array
- Add casting for the enum
- Add validation constants
- Add type-specific methods and scopes

### 3. Factory Updates
**File**: `database/factories/GrievanceFactory.php`

- Update to use the new enum values instead of generic categories
- Add type-specific factory states

### 4. Frontend Updates
Update Vue components to:
- Display appropriate form fields based on type
- Show type-specific icons and labels
- Implement type-specific validation rules

### 5. Business Logic
Implement type-specific:
- Routing and assignment logic
- Priority determination
- Processing workflows
- Notification templates

## Implementation Priority

**HIGH PRIORITY** - This is a core system functionality that affects:
- Data consistency
- User workflow
- System reliability
- Business requirements compliance

## Acceptance Criteria

- [ ] Database migration adds `type` enum field with three values
- [ ] Model validation ensures only valid types are accepted
- [ ] Factory generates grievances with proper type distribution
- [ ] Frontend forms support all three types
- [ ] Type-specific processing workflows are implemented
- [ ] Existing grievances can be migrated/classified appropriately

## Technical Notes

- The `category` field should be kept for backward compatibility and detailed classification
- Consider adding migration path for existing data
- Type should be required field for new submissions
- Implement proper database constraints and indexes

## Related Files to Update

- `database/migrations/2025_11_14_121054_create_grievances_table.php`
- `app/Models/Grievance.php`
- `database/factories/GrievanceFactory.php`
- Frontend Vue components for grievance forms
- API controllers and validation rules
- Notification and email templates

## Timeline Recommendation

This feature should be implemented within the next sprint to maintain data integrity and system consistency.