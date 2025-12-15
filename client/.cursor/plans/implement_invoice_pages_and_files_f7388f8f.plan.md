---
name: Implement Invoice Pages and Files
overview: "Create invoice pages, components, services, and composables following the customers pattern, using the actual database structure from the migration: customer_id, invoice_number, date, due_days, date_due, status, subtotal, total_vat_0/9/21, total, notes."
todos: []
---

# Implement Invoice Pages and Files

## Overview

Implement invoice management pages and files following the customers pattern, using the actual database structure from the migration. This includes:

- Service layer with API calls (`invoices.service.ts`)
- Composable for state management (`use-invoices.ts`)
- Pages: list (`index.vue`) and detail (`[id].vue`)
- Components: forms, data tables, filters, CRUD dialogs
- Schema validation (`data/schema.ts`)

## Database Structure (from Migration)

Based on the migration file, invoices have the following fields:

- `id` - Primary key
- `customer_id` - Foreign key to customers (required, cascade delete)
- `invoice_number` - Unique string, nullable
- `date` - Date field (required)
- `due_days` - Integer, default 30
- `date_due` - Date field, default now + 30 days
- `status` - Enum from InvoiceStatus, default DRAFT
- `subtotal` - Money field, default 0
- `total_vat_0` - Money field, default 0
- `total_vat_9` - Money field, default 0
- `total_vat_21` - Money field, default 0
- `total` - Money field, default 0
- `notes` - Text, nullable
- `created_at`, `updated_at` - Timestamps

**InvoiceStatus Enum**: Assumed values (to be confirmed): `draft`, `sent`, `paid`, `overdue`, `cancelled`

## Architecture

The implementation follows the same structure as customers:

```
src/
├── services/
│   └── invoices.service.ts          # API service with queries/mutations
├── composables/
│   └── use-invoices.ts              # State management composable
└── pages/
    └── invoices/
        ├── index.vue                # List page with table/card views
        ├── [id].vue                 # Detail page
        ├── data/
        │   └── schema.ts            # Zod schemas for validation
        └── components/
            ├── columns.ts           # Table column definitions
            ├── invoice-form.vue    # Create/Edit form
            ├── invoice-create.vue   # Create dialog trigger
            ├── invoice-delete.vue   # Delete confirmation
            ├── invoice-delete-batch.vue # Batch delete
            ├── invoice-resource-dialog.vue # Edit dialog wrapper
            ├── invoice-resource.vue # Form wrapper for drawer
            ├── invoice-card.vue    # Card view component
            ├── invoices-card-grid.vue # Grid of cards
            ├── invoices-filter.vue  # Filter component
            ├── data-table.vue       # Main data table wrapper
            ├── data-table-toolbar.vue # Table toolbar with filters
            └── data-table-row-actions.vue # Row action menu
```

## Implementation Details

### 1. Service Layer (`src/services/invoices.service.ts`)

Create service following `customers.service.ts` pattern:

**Interfaces:**

- **Money interface**: Reuse from `items.service.ts` (amount, currency, formatted)
- **Invoice interface**: 
  ```typescript
  {
    id: number
    customer_id: number
    customer?: Customer // When loaded via include
    invoice_number: string | null
    date: string // Date format from backend
    due_days: number
    date_due: string // Date format from backend
    status: InvoiceStatus // 'draft' | 'sent' | 'paid' | 'overdue' | 'cancelled'
    subtotal: Money | number
    total_vat_0: Money | number
    total_vat_9: Money | number
    total_vat_21: Money | number
    total: Money | number
    notes: string | null
    created_at: string // Format: "d-m-Y H:i:s"
    updated_at: string // Format: "d-m-Y H:i:s"
  }
  ```

- **PaginatedInvoicesResponse**: Laravel pagination structure
- **InvoiceFilters**: Filter interface (status, customer_id, date, date_due, invoice_number, search)
- **CreateInvoiceRequest**: Fields for creation (customer_id, invoice_number, date, due_days, notes)
- **UpdateInvoiceRequest**: Partial fields for update

**Query/Mutation Functions:**

- `useGetInvoicesQuery`: List query with pagination, sorting, filtering
- `useGetInvoiceQuery`: Single invoice query (with customer relationship)
- `useCreateInvoiceMutation`: Create mutation
- `useUpdateInvoiceMutation`: Update mutation
- `useDeleteInvoiceMutation`: Delete mutation
- `convertSortingToQueryString`: Helper for TanStack Table sorting

**API Endpoints** (standard REST):

- `GET /api/invoices` - List with pagination/filters
- `GET /api/invoices/:id` - Get single invoice
- `POST /api/invoices` - Create invoice
- `PUT /api/invoices/:id` - Update invoice
- `DELETE /api/invoices/:id` - Delete invoice

### 2. Composable (`src/composables/use-invoices.ts`)

Create composable following `use-customers.ts` pattern:

- Pagination state (page, pageSize)
- Sorting state (TanStack Table format)
- Filters state
- Include relationships state (e.g., 'customer')
- Query hooks integration
- CRUD operations with error handling
- Server pagination computed property
- Handlers: onSortingChange, onFiltersChange, clearFilters, onPageChange, onPageSizeChange

### 3. Schema (`src/pages/invoices/data/schema.ts`)

Create Zod schemas following `customers/data/schema.ts`:

- **invoiceStatusSchema**: Enum schema matching InvoiceStatus
- **moneySchema**: Schema for Money objects (or number for compatibility)
- **invoiceSchema**: Main invoice schema with all fields matching backend resource
- **Invoice type**: Inferred from schema
- Match backend resource structure (snake_case fields)

### 4. List Page (`src/pages/invoices/index.vue`)

Create following `customers/index.vue`:

- View mode toggle (table/card)
- Uses `useInvoices` composable
- Renders DataTable or InvoicesCardGrid based on view mode
- Includes InvoiceCreate button in actions
- Sticky header with Page component

### 5. Detail Page (`src/pages/invoices/[id].vue`)

Create following `customers/[id].vue`:

- Route meta with auth: true
- Uses `useGetInvoiceQuery` for single invoice (with customer include)
- Displays invoice details in cards:
  - Main invoice info (number, customer link, dates, status badge)
  - Financial breakdown (subtotal, VAT breakdown by rate, total)
  - Notes section
  - Timestamps
- Edit/Delete actions in header
- Error handling with Error component
- Loading state with Loading component
- Customer name links to customer detail page

### 6. Components

#### `invoice-form.vue`

- Form with vee-validate and Zod schema
- Fields:
  - Customer selection (dropdown using customers service)
  - Invoice number (text, optional)
  - Date (date picker)
  - Due days (number input, default 30)
  - Date due (date picker, calculated from date + due_days)
  - Status (select dropdown)
  - Notes (textarea)
- Auto-calculate date_due when date or due_days changes
- Submit handler calls create/update from composable
- Validation: customer_id required, date required, status required

#### `columns.ts`

- Table column definitions using TanStack Table
- Columns:
  - Select column
  - Invoice number (sortable, searchable)
  - Customer (with link to customer detail, sortable)
  - Date (sortable)
  - Date due (sortable)
  - Status (badge with color coding, sortable)
  - Total amount (formatted money, sortable)
  - Created at (formatted date, sortable)
  - Actions (view/edit/delete)
- Status column shows colored badge (draft=gray, sent=blue, paid=green, overdue=red, cancelled=gray)
- Total column formats Money object or number

#### `data-table.vue`

- Wraps DataTable component
- Includes BulkActions with batch delete
- Passes filters and handlers to toolbar
- Uses generateVueTable helper

#### `data-table-toolbar.vue`

- Search input for invoice number/customer
- InvoicesFilter component
- Clear filters button
- DataTableViewOptions for column visibility

#### `invoices-filter.vue`

- Popover with filter options
- Filters:
  - Status (select dropdown)
  - Customer (select dropdown with customer list)
  - Date range (from/to date pickers)
  - Date due range (from/to date pickers)
  - Invoice number (text input)
- Active filter count badge
- Clear filters functionality

#### `invoice-card.vue`

- Card component for grid view
- Displays key invoice info:
  - Invoice number
  - Customer name (with link)
  - Status badge
  - Total amount
  - Date and due date
- Dropdown menu for actions (view/edit/delete)
- Click to navigate to detail page

#### `invoices-card-grid.vue`

- Grid layout of invoice cards
- Loading state handling
- Responsive grid (1-3 columns)

#### `invoice-create.vue`

- Drawer trigger button
- Opens drawer with InvoiceResource component

#### `invoice-resource.vue`

- Drawer header with title/description
- Wraps InvoiceForm component

#### `invoice-resource-dialog.vue`

- Dialog wrapper for edit mode
- Header with title/description
- Wraps InvoiceForm component

#### `invoice-delete.vue`

- Delete confirmation dialog
- Shows invoice number
- Calls delete from composable
- Emits close event

#### `invoice-delete-batch.vue`

- Batch delete confirmation
- Shows count of selected invoices
- Deletes multiple invoices in parallel
- Resets table selection on success

#### `data-table-row-actions.vue`

- Dropdown menu for row actions
- Actions: view (navigate), edit (dialog), delete (dialog)
- Uses dynamic component rendering

## File Structure

All files will be created in:

- `src/services/invoices.service.ts`
- `src/composables/use-invoices.ts`
- `src/pages/invoices/index.vue`
- `src/pages/invoices/[id].vue`
- `src/pages/invoices/data/schema.ts`
- `src/pages/invoices/components/*.vue` (13 component files)
- `src/pages/invoices/components/columns.ts`

## Key Differences from Customers

1. **Money Fields**: All amount fields (subtotal, total_vat_*, total) use Money type from items service pattern
2. **VAT Breakdown**: Three separate VAT totals (0%, 9%, 21%) instead of single tax field
3. **Due Days**: Separate `due_days` field in addition to `date_due`
4. **Customer Relationship**: Invoices always have a customer_id (required foreign key)
5. **Status Enum**: Uses InvoiceStatus enum (likely: draft, sent, paid, overdue, cancelled)
6. **No Line Items in DB**: Migration doesn't show line_items table, so form may need to handle line items differently (or they're stored as JSON, or separate table)

## Notes

- **Money Formatting**: All money fields will handle both Money objects and numbers for backward compatibility
- **Customer Selection**: Form will use existing customer service/composable to fetch customer list
- **Date Calculation**: Form will auto-calculate `date_due` from `date` + `due_days`
- **Status Colors**: Status badges will use appropriate colors (draft=gray, sent=blue, paid=green, overdue=red, cancelled=gray)
- **Invoice Number**: Auto-generation may be handled by backend, but frontend allows manual entry
- **Line Items**: If line items exist, they may be in a separate table or stored as JSON. This can be added later if needed.

## Testing Considerations

After implementation, consider:

- E2E tests for invoice CRUD flows
- Unit tests for composable logic
- Component tests for form validation
- API tests for service layer
- Money formatting tests
- Date calculation tests