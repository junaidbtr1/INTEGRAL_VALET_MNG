# BTR Valet — Parking & Valet Management System
### Software Requirements Document (SRD)

**Version:** 1.0
**Date:** March 2026
**Prepared for:** Client Review & Approval
**Status:** Draft — Pending Approval

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [Key Features](#2-key-features)
3. [User Roles & Permissions](#3-user-roles--permissions)
4. [System Workflow](#4-system-workflow)
5. [Module 1 — Authentication & Access Control](#5-module-1--authentication--access-control)
6. [Module 2 — Super Admin Panel](#6-module-2--super-admin-panel)
7. [Module 3 — Tenant Management](#7-module-3--tenant-management)
8. [Module 4 — Role & Permission Management](#8-module-4--role--permission-management)
9. [Module 5 — Staff Management](#9-module-5--staff-management)
10. [Module 6 — Parking Slot Management](#10-module-6--parking-slot-management)
11. [Module 7 — Ticket Management](#11-module-7--ticket-management)
12. [Module 8 — Vehicle Management](#12-module-8--vehicle-management)
13. [Module 9 — Payment & Pricing](#13-module-9--payment--pricing)
14. [Module 10 — Coupon & Discount System](#14-module-10--coupon--discount-system)
15. [Module 11 — Reports & Analytics](#15-module-11--reports--analytics)
16. [Module 12 — Notification System](#16-module-12--notification-system)
17. [Module 13 — Branding & Customization](#17-module-13--branding--customization)
18. [Module 14 — Subscription & Billing](#18-module-14--subscription--billing)
19. [Module 15 — QR Code & Barcode](#19-module-15--qr-code--barcode)
20. [Module 16 — Settings](#20-module-16--settings)
21. [Module 17 — Security & Audit](#21-module-17--security--audit)
22. [Screen List](#22-screen-list)
23. [Subscription Plans](#23-subscription-plans)
24. [Deliverables](#24-deliverables)
25. [Tech Stack](#25-tech-stack)
26. [Approval Checklist](#26-approval-checklist)

---

## 1. Executive Summary

BTR Valet is a modern, **white-label parking and valet management system** designed for:

- Shopping Malls
- Residential Societies
- Hotels & Resorts
- Commercial Buildings
- Hospitals
- Event Venues

It is a **multi-tenant SaaS platform** — one system serves multiple clients. Each client (tenant) operates independently with their own:

- Branding (logo, colors, name)
- Staff & users
- Pricing configuration
- Data (tickets, payments, vehicles)
- Feature toggles

**The system improves efficiency, tracking, and revenue management for parking operations.**

### How It Works
```
┌─────────────────────────────────────────────────────────┐
│                    BTR VALET SYSTEM                      │
│                                                         │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐              │
│  │  Mall A   │  │ Hotel B  │  │Society C │  ← Tenants   │
│  │ (Green)   │  │ (Blue)   │  │ (Orange) │    (Clients) │
│  │           │  │          │  │          │              │
│  │ Own Staff │  │Own Staff │  │Own Staff │              │
│  │ Own Data  │  │Own Data  │  │Own Data  │              │
│  │ Own Brand │  │Own Brand │  │Own Brand │              │
│  └──────────┘  └──────────┘  └──────────┘              │
│                                                         │
│  Managed by: SUPER ADMIN (System Owner — BTR)           │
└─────────────────────────────────────────────────────────┘
```

---

## 2. Key Features

| # | Feature | Description |
|---|---------|-------------|
| 1 | Multi-Tenant System | Multiple clients in one platform, completely isolated data |
| 2 | White-Label Branding | Each client gets their own logo, colors, name |
| 3 | Custom Roles & Permissions | Tenant admin creates roles, assigns 50+ permissions |
| 4 | Ticket Management | Full lifecycle: entry → parking → payment → exit |
| 5 | Vehicle Tracking | Plate lookup, VIP/blacklist, visit history |
| 6 | Parking Slot Management | Floor/zone/slot structure, real-time occupancy map |
| 7 | Multiple Pricing Models | Flat, hourly, tiered, vehicle-based, time-of-day |
| 8 | QR Code / Barcode | Generate on ticket, scan at exit |
| 9 | Coupon & Discount System | Percentage, fixed, free hours, full waiver |
| 10 | Payment Processing | Cash, card, digital (JazzCash, EasyPaisa) |
| 11 | Receipt Generation | Thermal printer + PDF receipt |
| 12 | Reports & Analytics | Revenue, occupancy, staff performance, exportable |
| 13 | Real-Time Dashboard | Live stats: tickets, revenue, occupancy |
| 14 | Notification System | In-app, email, SMS notifications |
| 15 | Subscription Billing | Starter / Professional / Enterprise plans |
| 16 | Security & Audit | Login security, data isolation, full audit trail |
| 17 | Offline Support | Valet app works without internet, syncs when online |
| 18 | Mobile Responsive | Works on desktop, tablet, and mobile |

---

## 3. User Roles & Permissions

### Role Hierarchy
```
SUPER ADMIN (System Owner — BTR)
  │
  └── TENANT ADMIN (Client's Manager)
        │
        ├── Custom Role (e.g., Supervisor)
        ├── Custom Role (e.g., Valet Staff)
        ├── Custom Role (e.g., Cashier)
        └── Custom Role (e.g., Viewer)
```

### Default Roles

| Role | Who | What They Can Do |
|------|-----|-----------------|
| **Super Admin** | System owner (BTR) | Create tenants, manage plans, see all data, manage all users |
| **Tenant Admin** | Client's manager | Full control within their tenant: staff, pricing, settings, reports |
| **Supervisor** | On-site manager | View/create/close tickets, view reports, manage vehicles |
| **Valet Staff** | Parks vehicles | Create tickets, close own tickets, search vehicles |
| **Cashier** | Handles payments | View tickets, process payments, apply coupons |
| **Viewer** | Read-only access | View dashboard, view tickets, view reports |

### Custom Roles
Tenant Admin can create **unlimited custom roles** with any combination of 50+ permissions.

Example custom roles a tenant might create:
- **Floor Manager** — tickets + slots + vehicles
- **Gate Operator** — create tickets + scan QR only
- **Accounts Manager** — payments + reports + export
- **Night Shift Lead** — tickets + slots (no settings)

### Permission Categories (50+)

| Module | Available Permissions |
|--------|---------------------|
| Tickets | view, create, update, delete, close, cancel, export, bulk_create, reassign |
| Payments | view, create, refund, export, reconcile, void |
| Users/Staff | view, create, update, delete, activate, deactivate, reset_password, assign_role |
| Vehicles | view, create, update, delete, search |
| Parking Slots | view, create, update, delete, reserve, release |
| Coupons | view, create, update, delete, activate, deactivate |
| Reports | view, export, schedule, revenue, occupancy, staff |
| Settings | view, update, branding, features, billing, notifications |
| Audit Logs | view, export |
| Webhooks | view, create, update, delete |

### Permission Matrix (Default Roles)

| Permission | Super Admin | Tenant Admin | Supervisor | Valet Staff | Cashier | Viewer |
|:-----------|:---:|:---:|:---:|:---:|:---:|:---:|
| View tickets | Yes | Yes | Yes | Own only | Yes | Yes |
| Create tickets | Yes | Yes | Yes | Yes | No | No |
| Close tickets | Yes | Yes | Yes | Own only | Yes | No |
| Delete tickets | Yes | Yes | No | No | No | No |
| Export tickets | Yes | Yes | Yes | No | No | No |
| View payments | Yes | Yes | Yes | No | Yes | Yes |
| Process payments | Yes | Yes | No | No | Yes | No |
| Refund payments | Yes | Yes | No | No | No | No |
| Manage staff | Yes | Yes | No | No | No | No |
| Manage roles | Yes | Yes | No | No | No | No |
| View reports | Yes | Yes | Yes | No | No | Yes |
| Export reports | Yes | Yes | No | No | No | No |
| Manage settings | Yes | Yes | No | No | No | No |
| View audit logs | Yes | Yes | No | No | No | No |

---

## 4. System Workflow

### Overall Flow
```
🚗 VEHICLE ENTRY
  → Staff creates ticket
  → System checks: blacklisted? duplicate? lot full?
  → System assigns parking slot
  → Ticket generated with QR code
  → Ticket printed or shown on screen

🅿️ PARKING
  → Vehicle parked in assigned slot
  → System tracks occupancy in real-time
  → Dashboard updates live

🚙 VEHICLE EXIT
  → Customer shows ticket / scans QR code
  → System calculates duration & amount
  → Coupon applied (if any)

💰 PAYMENT
  → Cash / Card / Digital payment processed
  → Receipt generated & printed
  → Parking slot released (available again)
  → Ticket closed

📊 REPORTING
  → All data feeds into dashboards
  → Revenue, occupancy, staff performance tracked
  → Reports exportable (CSV, PDF)
```

---

## 5. Module 1 — Authentication & Access Control

### Features
| # | Feature | Description |
|---|---------|-------------|
| 1 | Single login page | All users (super admin, tenant admin, staff) login at same URL |
| 2 | Email + password authentication | Secure credential-based login |
| 3 | Token-based API auth | Sanctum tokens for session management |
| 4 | Role-based dashboard | After login, sidebar shows only permitted menu items |
| 5 | Account lockout | 5 failed attempts → 15-minute lock |
| 6 | Session management | View & revoke active sessions |
| 7 | Password change | Users can change their own password |
| 8 | Force password change | Admin can force first-login password change |
| 9 | Token expiry | Auto-expire after 7 days (configurable) |
| 10 | Two-factor auth (future) | TOTP + SMS OTP (optional per tenant) |

### Login Flow
```
User opens app → Login page
  → Enters email & password
  → System validates:
      ✓ Credentials correct?
      ✓ Account active?
      ✓ Account not locked?
      ✓ Tenant not suspended? (for tenant users)
  → Generates auth token
  → Loads user roles & permissions
  → Redirects to dashboard
  → Sidebar shows menu items based on permissions
```

---

## 6. Module 2 — Super Admin Panel

### Purpose
Super Admin (BTR system owner) manages all tenants, plans, and users across the entire system.

### Features
| # | Feature | Description |
|---|---------|-------------|
| 1 | System dashboard | Total tenants, users, tickets, revenue (across all tenants) |
| 2 | Create tenant | Register new client with name, email, plan, trial period |
| 3 | Manage tenants | View, edit, suspend, reactivate, delete tenants |
| 4 | Manage plans | Create/edit subscription plans (Starter, Pro, Enterprise) |
| 5 | Manage all users | View all users across tenants, create tenant admin users |
| 6 | Tenant detail view | See tenant's stats, branding, features, users count |
| 7 | Tenant status control | Change status: trial → active → suspended → cancelled |

### Dashboard Widgets
| Widget | Data |
|--------|------|
| Total Tenants | Count (active, trial, suspended breakdown) |
| Total Users | All non-admin users across tenants |
| Tickets Today | Total tickets created today (all tenants) |
| Revenue Today | Total revenue today (all tenants) |
| Tickets This Month | Monthly ticket count |
| Revenue This Month | Monthly revenue total |
| Recent Tenants | Last 5 tenants created |

---

## 7. Module 3 — Tenant Management

### Tenant Lifecycle
```
TRIAL (14 days free)
  ↓
ACTIVE (subscription paid)
  ↓
SUSPENDED (payment failed / manual by super admin)
  ↓
CANCELLED (client leaves — 30-day data retention)
  ↓
DELETED (data permanently removed)
```

### Create Tenant
| Field | Required | Description |
|-------|----------|-------------|
| Name | Yes | Organization name (e.g., "DHA Parking") |
| Email | Yes | Primary contact email (unique) |
| Phone | No | Contact phone number |
| Address | No | Physical address |
| Plan | No | Subscription plan (Starter/Pro/Enterprise) |
| Trial Days | No | Trial period (default: 14 days) |

### Auto-Generated on Creation
- Unique slug (URL-friendly name)
- Default feature toggles
- Default parking settings (rate, currency, timezone)
- Trial expiry date

### Tenant Detail View
- Basic info (name, email, phone, status)
- Plan info
- User count, ticket count, slot count
- Branding colors preview
- Feature toggles list
- Created date

---

## 8. Module 4 — Role & Permission Management

### Purpose
Tenant Admin creates **custom roles** for their organization and assigns specific permissions to each role. This controls what each staff member can see and do.

### Features
| # | Feature | Description |
|---|---------|-------------|
| 1 | View all roles | List roles with name, description, permission count, user count |
| 2 | Create custom role | Enter name + description, pick permissions via checkboxes |
| 3 | Edit role | Change description, add/remove permissions |
| 4 | Delete role | Only if no users assigned to it |
| 5 | Permission groups | Permissions organized by module (Tickets, Payments, etc.) |
| 6 | Select all / group | Toggle entire group of permissions at once |
| 7 | Role cards view | Visual cards showing role name, user count, permission count |

### Create Role Flow
```
Tenant Admin → Roles → "New Role"
  → Enters: Role name (e.g., "floor_manager")
  → Enters: Description (e.g., "Manages parking floor operations")
  → Selects permissions by group:
      TICKETS:
        ☑ view  ☑ create  ☑ update  ☑ close  ☐ delete  ☐ cancel
      VEHICLES:
        ☑ view  ☑ search  ☐ create  ☐ update
      PARKING SLOTS:
        ☑ view  ☐ create  ☐ update
      PAYMENTS:
        ☐ (none selected)
      REPORTS:
        ☑ view  ☐ export
  → Saves role
  → Role appears in list with permission count
```

---

## 9. Module 5 — Staff Management

### Purpose
Tenant Admin creates and manages staff accounts for their organization.

### Features
| # | Feature | Description |
|---|---------|-------------|
| 1 | View all staff | List with name, email, role, status, last login |
| 2 | Add staff member | Name, email, phone, password, role selection |
| 3 | Edit staff | Update info, change role, change password, toggle active |
| 4 | Deactivate staff | Disable login without deleting |
| 5 | Delete staff | Soft delete + revoke all tokens |
| 6 | Filter by role | Show only specific role members |
| 7 | Search | Search by name or email |
| 8 | Role badge | Color-coded role badge per user |

### Add Staff Flow
```
Tenant Admin → Staff → "Add Staff"
  → Fills: Name, Email, Phone, Password
  → Selects: Role (from custom roles created in Module 4)
  → System creates user account
  → Staff member can login immediately
  → They see sidebar items matching their role's permissions
```

---

## 10. Module 6 — Parking Slot Management

### Purpose
Define and manage physical parking spaces. Track occupancy in real-time.

### Slot Structure
```
Tenant
  └── Floor (G, B1, B2, 1F, 2F...)
       └── Zone (A, B, C, VIP, Disabled)
            └── Slot (A-001, A-002, A-003...)
```

### Features
| # | Feature | Description |
|---|---------|-------------|
| 1 | Add slots | Create individual or bulk slots with floor/zone/number |
| 2 | Slot types | Standard, Compact, Large, Disabled, EV Charging, VIP, Reserved |
| 3 | Slot status | Available, Occupied, Reserved, Maintenance, Out of Service |
| 4 | Occupancy map | Visual grid showing real-time slot availability |
| 5 | Auto-assignment | System picks best available slot when creating ticket |
| 6 | Manual assignment | Staff can override and pick specific slot |
| 7 | Capacity alerts | Notifications at 80%, 90%, 100% capacity |
| 8 | Vehicle type filter | Only allow matching vehicles in specific slot types |
| 9 | Maintenance mode | Mark slots as under maintenance |
| 10 | Bulk operations | Add/update multiple slots at once |

### Slot States
```
AVAILABLE → OCCUPIED (ticket created)
AVAILABLE → RESERVED (reservation made)
AVAILABLE → MAINTENANCE (admin action)
OCCUPIED → AVAILABLE (ticket closed)
RESERVED → OCCUPIED (reservation checked in)
MAINTENANCE → AVAILABLE (admin action)
Any → OUT_OF_SERVICE (admin action)
```

### Occupancy Map (Visual)
```
┌──────────────────────────────────┐
│  Floor G — Zone A                │
│                                  │
│  [A-01 ✓] [A-02 ✓] [A-03 ■]    │  ✓ = Available (green)
│  [A-04 ✓] [A-05 ■] [A-06 ✓]    │  ■ = Occupied (red)
│  [A-07 ✓] [A-08 ✓] [A-09 ⚠]    │  ⚠ = Maintenance (yellow)
│                                  │
│  Capacity: 6/9 (67%)            │
└──────────────────────────────────┘
```

---

## 11. Module 7 — Ticket Management

### Purpose
Core module — manages the entire parking session from vehicle entry to exit.

### Features
| # | Feature | Description |
|---|---------|-------------|
| 1 | Create ticket | Enter plate number, select vehicle type, auto-assign slot |
| 2 | Ticket number | Auto-generated: {PREFIX}-{YYMMDD}-{SEQUENCE} (e.g., DHA-260329-00042) |
| 3 | QR code / barcode | Generated on each ticket for scanning |
| 4 | Print ticket | Thermal printer or browser print |
| 5 | Search ticket | By plate number, ticket number, or QR scan |
| 6 | Close ticket | Calculate amount, process payment, release slot |
| 7 | Cancel ticket | Cancel with reason (supervisor+ only) |
| 8 | Ticket list | Filterable by status, date, plate, with pagination |
| 9 | Ticket detail | Full info: vehicle, slot, timing, payment, QR code |
| 10 | Lost ticket handling | Apply penalty fee, process with verification |
| 11 | Overstay detection | Auto-flag tickets exceeding time limit |
| 12 | Export tickets | CSV/PDF export with date range filter |
| 13 | Vehicle validation | Check blacklist, check duplicate, check lot capacity |
| 14 | Duration tracking | Real-time duration calculation on active tickets |
| 15 | Bulk operations | Bulk close/cancel for end-of-day processing |

### Ticket Lifecycle (State Machine)
```
                  ┌─────────────┐
                  │   CREATED   │  ← Vehicle enters, ticket generated
                  └──────┬──────┘
                         │ vehicle parked
                         ▼
                  ┌─────────────┐
            ┌─────│   ACTIVE    │─────┐
            │     └──────┬──────┘     │
            │            │            │
     lost ticket    customer      overstay
            │       returns          │
            ▼            │            ▼
    ┌──────────────┐     │   ┌──────────────┐
    │  LOST_TICKET │     │   │   OVERSTAY   │
    └──────┬───────┘     │   └──────┬───────┘
            │            │            │
            └─────┬──────┘────────────┘
                  │ payment processed
                  ▼
           ┌─────────────┐
           │   COMPLETED │  ← Payment done
           └──────┬──────┘
                  │ vehicle exits
                  ▼
           ┌─────────────┐
           │   CLOSED    │  ← Vehicle left, slot freed
           └─────────────┘

    Any State ──── cancel ───→ CANCELLED
    Any State ──── dispute ──→ DISPUTED
```

### Create Ticket Flow
```
Vehicle arrives at gate

Valet Staff → Tickets → "New Ticket"
  → Enters: Vehicle plate number (ABC-1234)
  → System auto-checks:
      ✓ Is vehicle blacklisted? → Show alert / block
      ✓ Does vehicle have active ticket? → Block duplicate
      ✓ Is parking lot full? → Block / show warning
      ✓ Is vehicle returning? → Auto-fill owner info
  → Selects: Vehicle type (Car, SUV, Motorcycle...)
  → Enters: Vehicle color (optional)
  → System auto-assigns parking slot (or staff picks manually)
  → System generates:
      - Unique ticket number: DHA-260329-00042
      - QR code / barcode
      - Entry timestamp
  → Ticket printed or shown on screen
```

### Printed Ticket
```
╔════════════════════════════╗
║    DHA PARKING             ║
╠════════════════════════════╣
║  PARKING TICKET            ║
║  Ticket: DHA-260329-00042  ║
║  Date: 29-Mar-2026 10:30   ║
║  Vehicle: ABC-1234         ║
║  Type: Car                 ║
║  Slot: A-15                ║
║                            ║
║  [████ QR CODE ████]       ║
║                            ║
║  Rate: Rs 50/hour          ║
║  * Keep this ticket safe   ║
║  * Lost ticket fee: Rs 500 ║
╚════════════════════════════╝
```

### Ticket Number Format
```
{TENANT_PREFIX}-{YYMMDD}-{SEQUENCE}

Example: DHA-260329-00042

DHA     → Tenant prefix (configurable, max 5 chars)
260329  → Date (29 March 2026)
00042   → Daily sequence number (resets each day)
```

---

## 12. Module 8 — Vehicle Management

### Purpose
Track vehicles, identify returning visitors, manage VIP and blacklisted vehicles.

### Features
| # | Feature | Description |
|---|---------|-------------|
| 1 | Auto-register | System creates vehicle record on first ticket |
| 2 | Plate lookup | Search by partial/full plate number |
| 3 | Returning vehicle detection | Auto-fill owner info from history |
| 4 | VIP flag | Auto-apply free parking or special rate |
| 5 | Blacklist | Block ticket creation with reason |
| 6 | Visit history | All past tickets for a vehicle |
| 7 | Vehicle photo | Optional photo capture at entry |
| 8 | Owner info | Name, phone (optional, builds over time) |
| 9 | Visit count | Track how many times vehicle has parked |
| 10 | Frequent visitor | Auto-suggest subscription after N visits |

### Vehicle Data
| Field | Type | Description |
|-------|------|-------------|
| Plate Number | Required | Normalized: uppercase, no spaces |
| Vehicle Type | Required | Motorcycle, Car, SUV, Van, Truck, Bus |
| Color | Optional | Vehicle color |
| Make | Optional | e.g., Toyota |
| Model | Optional | e.g., Corolla |
| Owner Name | Optional | Customer name |
| Owner Phone | Optional | Contact number |
| VIP | Toggle | Free/discounted parking |
| Blacklisted | Toggle | Block with reason |
| Photo | Optional | Vehicle photo URL |
| Visit Count | Auto | Incremented per ticket |

---

## 13. Module 9 — Payment & Pricing

### Purpose
Calculate parking fees, process payments, generate receipts.

### Pricing Strategies

**Each tenant selects ONE pricing model:**

| # | Strategy | Description | Example |
|---|----------|-------------|---------|
| 1 | **Flat Rate** | Fixed price per entry | Rs 100 per entry |
| 2 | **Hourly Rate** | Per hour with minimum | Rs 50/hr, min Rs 50, 15min grace |
| 3 | **Tiered Rate** | Different rate per duration | 0-1hr: Rs 50, 1-3hr: Rs 100, 3-6hr: Rs 200 |
| 4 | **Vehicle-Based** | Different rate by vehicle | Motorcycle: Rs 30/hr, Car: Rs 50/hr, SUV: Rs 70/hr |
| 5 | **Time-of-Day** | Peak/off-peak pricing | Peak: Rs 80/hr, Normal: Rs 50/hr, Night: Rs 30/hr |

### Payment Calculation
```
1. Get ticket duration
2. Apply pricing strategy → Base Amount
3. Check overstay → Add Surcharge (if applicable)
4. Check lost ticket → Add Penalty (if applicable)
5. Calculate Tax → Tax Amount
6. Apply coupon → Discount Amount (if any)
7. TOTAL = Base + Surcharge + Tax - Discount
```

### Payment Methods
| Method | Description |
|--------|-------------|
| Cash | Cashier collects cash |
| Card | Swipe/tap card terminal |
| Digital | JazzCash, EasyPaisa, etc. |

### Payment Features
| # | Feature | Description |
|---|---------|-------------|
| 1 | Process payment | Select method, confirm amount, record payment |
| 2 | Generate receipt | Auto-generated receipt with breakdown |
| 3 | Print receipt | Thermal printer or PDF |
| 4 | Refund | Create negative payment, record reason |
| 5 | Payment history | All payments with filters |
| 6 | Export | CSV/PDF export |
| 7 | Amount validation | Server-side calculation, never trust client |
| 8 | Receipt number | Auto-generated unique number |

### Payment Receipt
```
╔════════════════════════════╗
║    DHA PARKING             ║
║    PAYMENT RECEIPT         ║
╠════════════════════════════╣
║  Receipt: RCP-260329-0018  ║
║  Date: 29-Mar-2026 14:45   ║
║                            ║
║  Ticket: DHA-260329-00042  ║
║  Vehicle: ABC-1234         ║
║  Entry: 10:30  Exit: 14:45 ║
║  Duration: 4h 15m          ║
║                            ║
║  Base:        Rs 250.00    ║
║  Tax (5%):    Rs  12.50    ║
║  Coupon:     -Rs  50.00    ║
║  ─────────────────────     ║
║  TOTAL:       Rs 212.50    ║
║  Paid: Cash                ║
║                            ║
║  Thank you for parking!    ║
╚════════════════════════════╝
```

### Money Storage
- All amounts stored as **integers** (smallest currency unit)
- Rs 150.50 → stored as `15050` (paisa)
- Prevents floating-point calculation errors
- Converted to display format only in UI

---

## 14. Module 10 — Coupon & Discount System

### Purpose
Create and manage discount coupons for parking fees.

### Coupon Types
| Type | Example | Behavior |
|------|---------|----------|
| **Percentage** | `20OFF` → 20% off | Percentage discount |
| **Fixed Amount** | `RS100OFF` → Rs 100 off | Flat discount |
| **Free Hours** | `2HRFREE` → 2 hours free | Deduct hours from duration |
| **Full Waiver** | `VIPFREE` → 100% free | Completely free parking |

### Coupon Properties
| Property | Description |
|----------|-------------|
| Code | Uppercase alphanumeric (4-20 chars) |
| Type | Percentage / Fixed / Free Hours / Full Waiver |
| Value | Amount or percentage |
| Min Amount | Minimum ticket amount to apply |
| Max Discount | Cap on discount amount |
| Usage Limit | Total times coupon can be used |
| Per-User Limit | Max uses per user |
| Valid From/Until | Date range |
| Vehicle Types | Applicable vehicle types |
| Active/Inactive | Toggle |

### Coupon Validation
```
Cashier enters coupon code → System checks:
  1. ✓ Code exists?
  2. ✓ Code is active?
  3. ✓ Within valid date range?
  4. ✓ Total usage limit not reached?
  5. ✓ Per-user limit not reached?
  6. ✓ Minimum ticket amount met?
  7. ✓ Vehicle type applicable?

  All pass → Apply discount → Show new total
  Any fail → Show specific error message under field
```

---

## 15. Module 11 — Reports & Analytics

### Real-Time Dashboard Widgets
| Widget | Data |
|--------|------|
| Tickets Today | Count + trend vs yesterday |
| Active Vehicles | Currently parked count |
| Revenue Today | Total + comparison with yesterday |
| Revenue This Month | Monthly total |
| Occupancy | Percentage + visual bar |
| Available Slots | Count |
| Peak Hours | Heatmap chart |
| Recent Tickets | Live feed (last 10) |
| Staff Activity | Recent actions by staff |

### Reports
| Report | Frequency | Export Formats |
|--------|-----------|---------------|
| Daily Summary | Daily | Email + Dashboard |
| Revenue Report | Daily / Weekly / Monthly | Dashboard + CSV + PDF |
| Occupancy Report | Real-time + Daily | Dashboard |
| Staff Performance | Weekly | Dashboard + PDF |
| Ticket Analytics | Real-time | Dashboard |
| Financial Reconciliation | Monthly | PDF + CSV |
| Vehicle Analytics | Monthly | Dashboard |
| Coupon Usage | Monthly | Dashboard + CSV |

### Report Features
- Date range filter
- Compare periods (this month vs last month)
- Export to CSV, XLSX, PDF
- Large reports generated in background
- PDF reports include tenant branding

---

## 16. Module 12 — Notification System

### Channels
| Channel | Description |
|---------|-------------|
| In-App | Notification bell in dashboard (always enabled) |
| Email | Via SMTP (configurable per tenant) |
| SMS | Via Twilio or local provider (configurable per tenant) |

### Notification Events
| Event | In-App | Email | SMS |
|-------|:------:|:-----:|:---:|
| Vehicle ready for pickup | Yes | No | Yes |
| Payment received | Yes | Yes | Optional |
| Daily summary report | No | Yes | No |
| Account locked | Yes | Yes | No |
| Subscription expiring | Yes | Yes | Yes |
| New staff member added | Yes | Yes | No |
| Parking lot almost full (90%) | Yes | No | No |
| Parking lot full (100%) | Yes | No | Yes |
| System maintenance | Yes | Yes | No |

### Notification Rules
- All notifications queued (non-blocking)
- Respect tenant ON/OFF preferences
- Respect user notification preferences
- Failed notifications retry 3 times
- All emails include tenant branding
- SMS limited to 100/hour per tenant

---

## 17. Module 13 — Branding & Customization

### Visual Branding
| Setting | Description |
|---------|-------------|
| Logo | Upload company logo |
| Primary Color | Sidebar and button color |
| Secondary Color | Accent elements |
| Accent Color | Highlights |

### Ticket Customization
| Setting | Description |
|---------|-------------|
| Ticket Prefix | e.g., "DHA" (max 5 chars) |
| Header Text | Custom text on ticket top |
| Footer Text | Custom text on ticket bottom |
| QR vs Barcode | Choose preference |

### Receipt Customization
| Setting | Description |
|---------|-------------|
| Header Text | Receipt title area |
| Footer Text | "Thank you" message |
| Show/Hide Fields | Toggle which fields appear |

---

## 18. Module 14 — Subscription & Billing

### Plan Tiers

| Feature | Starter | Professional | Enterprise |
|---------|:-------:|:------------:|:----------:|
| **Monthly Price** | Rs 4,999 | Rs 9,999 | Rs 24,999 |
| **Yearly Price** | Rs 49,990 | Rs 99,990 | Rs 249,990 |
| Parking Slots | 50 | 500 | Unlimited |
| Staff Users | 5 | 25 | Unlimited |
| Tickets/Day | 200 | 1,000 | Unlimited |
| SMS Notifications | No | Yes | Yes |
| Custom Branding | No | Yes | Yes |
| Advanced Reports | No | Yes | Yes |
| API Access | No | No | Yes |
| Multi-Floor | No | No | Yes |
| Valet Tracking | No | No | Yes |
| Priority Support | No | No | Yes |
| SLA Guarantee | No | No | 99.9% |

### Tenant Status Flow
```
TRIAL (14 days) → ACTIVE (paid) → SUSPENDED (payment fail) → CANCELLED → DELETED
```

---

## 19. Module 15 — QR Code & Barcode

### Features
| # | Feature | Description |
|---|---------|-------------|
| 1 | QR generation | Auto-generated on ticket creation |
| 2 | Barcode generation | Code128 format with ticket number |
| 3 | Camera scanning | Scan QR/barcode with phone camera |
| 4 | Instant lookup | Scan → immediately shows ticket details |
| 5 | Tenant logo in QR | Optional logo overlay in QR center |
| 6 | Offline scan queue | Scans queued locally when offline |
| 7 | Print integration | QR/barcode on printed tickets |

---

## 20. Module 16 — Settings

### Parking Settings
| Setting | Description |
|---------|-------------|
| Pricing Model | Select: Flat / Hourly / Tiered / Vehicle-Based / Time-of-Day |
| Default Rate | Rate per hour (in paisa) |
| Currency | PKR, USD, AED, etc. |
| Timezone | Asia/Karachi, etc. |
| Operating Hours | Start and end time |
| Grace Period | Minutes of free parking |
| Lost Ticket Penalty | Flat fee for lost tickets |
| Overstay Surcharge | Extra charge after threshold |
| Tax Rate | Percentage |
| Auto-Close | Auto-close tickets after N hours |

### Feature Toggles
| Feature | ON/OFF |
|---------|--------|
| Coupon System | Toggle |
| Chargeback | Toggle |
| Ticket Printing | Toggle |
| QR Code | Toggle |
| Barcode | Toggle |
| Email Notifications | Toggle |
| SMS Notifications | Toggle |
| Valet GPS Tracking | Toggle |
| Customer Feedback | Toggle |
| Vehicle Photo Capture | Toggle |
| Multi-Floor Parking | Toggle |
| Reserved Parking Slots | Toggle |

### Notification Preferences
| Setting | Description |
|---------|-------------|
| Email enabled | ON/OFF |
| SMS enabled | ON/OFF |
| SMS provider | Twilio / local |
| Email sender name | Custom sender |

---

## 21. Module 17 — Security & Audit

### Authentication Security
| Feature | Description |
|---------|-------------|
| Password hashing | bcrypt with cost factor 12 |
| Account lockout | 5 failed attempts → 15 min lock |
| Progressive lockout | Repeated lockouts increase duration |
| Token expiry | 7 days (configurable) |
| HTTPS enforced | All traffic encrypted in production |
| Session management | View & revoke active sessions |
| 2FA (future) | TOTP + SMS |

### Data Isolation
| Layer | Protection |
|-------|-----------|
| Database | `tenant_id` on every row |
| Middleware | Auto-filter queries by tenant |
| Application | Policy checks on every action |
| API | Tenant-scoped endpoints |

### Audit Trail
Every action logged:
| Field | Description |
|-------|-------------|
| User | Who performed the action |
| Action | What was done (create, update, delete) |
| Model | What was affected (Ticket, Payment, etc.) |
| Old Values | Previous data (for updates) |
| New Values | New data (for updates) |
| IP Address | Where from |
| User Agent | Device/browser info |
| Timestamp | When |

Audit logs are:
- Append-only (cannot be modified or deleted)
- Retained for 1 year
- Searchable and filterable
- Exportable to CSV
- Accessible to Tenant Admin + Super Admin

### API Security
| Feature | Description |
|---------|-------------|
| CORS | Restricted to frontend domain |
| Rate Limiting | Auth: 5/min, API: 60/min, Reports: 10/min |
| Input Validation | All inputs validated on server (Form Requests) |
| SQL Injection | Prevented via parameterized queries |
| XSS Prevention | Input sanitization |
| No sensitive data in logs | Passwords, tokens masked |

---

## 22. Screen List

### Public Screens
| Screen | URL | Access |
|--------|-----|--------|
| Login | `/login` | Everyone |
| Forgot Password | `/forgot-password` | Everyone |
| Reset Password | `/reset-password` | Everyone |

### Dashboard (All Authenticated Users)
| Screen | URL | Access |
|--------|-----|--------|
| Dashboard | `/` | All logged-in users (content varies by role) |

### Super Admin Screens
| Screen | URL | Accessible By |
|--------|-----|---------------|
| Tenants List | `/tenants` | Super Admin |
| Tenant Detail | `/tenants/:id` | Super Admin |
| Plans | `/plans` | Super Admin |
| All Users | `/users` | Super Admin |

### Tenant Screens (Permission-Based)
| Screen | URL | Permission Required |
|--------|-----|-----------|
| Tickets | `/tickets` | tickets.view |
| Create Ticket | `/tickets/new` | tickets.create |
| Ticket Detail | `/tickets/:id` | tickets.view |
| Parking Slots | `/slots` | slots.view |
| Slot Map | `/slots/map` | slots.view |
| Vehicles | `/vehicles` | vehicles.view |
| Vehicle Detail | `/vehicles/:id` | vehicles.view |
| Payments | `/payments` | payments.view |
| Process Payment | `/payments/new` | payments.create |
| Coupons | `/coupons` | coupons.view |
| Staff | `/staff` | users.view |
| Roles & Permissions | `/roles` | users.assign_role |
| Reports | `/reports` | reports.view |
| Settings | `/settings` | settings.view |
| Audit Logs | `/audit-logs` | audit_logs.view |

---

## 23. Subscription Plans

| Feature | Starter | Professional | Enterprise |
|---------|:-------:|:------------:|:----------:|
| Monthly Price | Rs 4,999 | Rs 9,999 | Rs 24,999 |
| Parking Slots | 50 | 500 | Unlimited |
| Staff Users | 5 | 25 | Unlimited |
| Tickets/Day | 200 | 1,000 | Unlimited |
| SMS Notifications | No | Yes | Yes |
| Custom Branding | No | Yes | Yes |
| Advanced Reports | No | Yes | Yes |
| API Access | No | No | Yes |
| Multi-Floor Support | No | No | Yes |
| Priority Support | No | No | Yes |

---

## 24. Deliverables

| # | Deliverable | Description |
|---|------------|-------------|
| 1 | **Web Admin Panel** | Super Admin + Tenant Admin dashboard (Vue.js SPA) |
| 2 | **Staff Interface** | Mobile-responsive web interface for valet staff & cashiers |
| 3 | **Backend API** | Complete REST API (Laravel) with all endpoints |
| 4 | **Ticket System** | Full ticket lifecycle with QR/barcode |
| 5 | **Payment System** | Multi-method payment processing with receipts |
| 6 | **Report System** | Dashboard + exportable reports (CSV/PDF) |
| 7 | **Role & Permission System** | Custom role creation with 50+ permissions |
| 8 | **Notification System** | In-app, email, SMS notifications |
| 9 | **Tenant Branding** | White-label customization per client |
| 10 | **Database** | MySQL with all migrations, seeders, factories |
| 11 | **API Documentation** | Auto-generated Swagger/OpenAPI docs |
| 12 | **Source Code** | Complete source code with documentation |

---

## 25. Tech Stack

| Component | Technology |
|-----------|-----------|
| **Backend** | Laravel 11 (PHP 8.4) |
| **Frontend** | Vue 3 + TypeScript + Vite |
| **Database** | MySQL |
| **Cache/Queue** | Database (upgradable to Redis) |
| **Authentication** | Laravel Sanctum (token-based) |
| **Roles & Permissions** | Spatie Laravel Permission v6 |
| **UI Framework** | PrimeVue + Tailwind CSS |
| **Form Validation** | VeeValidate + Yup (frontend), Form Requests (backend) |
| **State Management** | Pinia |
| **API Format** | RESTful JSON |
| **PDF Generation** | DomPDF |
| **QR Code** | simplesoftwareio/simple-qrcode |

---

## 26. Approval Checklist

Please review each module and mark as approved:

| # | Module | Status |
|---|--------|--------|
| 1 | Authentication & Access Control | ☐ Approved |
| 2 | Super Admin Panel | ☐ Approved |
| 3 | Tenant Management | ☐ Approved |
| 4 | Role & Permission Management | ☐ Approved |
| 5 | Staff Management | ☐ Approved |
| 6 | Parking Slot Management | ☐ Approved |
| 7 | Ticket Management | ☐ Approved |
| 8 | Vehicle Management | ☐ Approved |
| 9 | Payment & Pricing | ☐ Approved |
| 10 | Coupon & Discount System | ☐ Approved |
| 11 | Reports & Analytics | ☐ Approved |
| 12 | Notification System | ☐ Approved |
| 13 | Branding & Customization | ☐ Approved |
| 14 | Subscription & Billing | ☐ Approved |
| 15 | QR Code & Barcode | ☐ Approved |
| 16 | Settings | ☐ Approved |
| 17 | Security & Audit | ☐ Approved |
| 18 | Screen List | ☐ Approved |
| 19 | Subscription Plans | ☐ Approved |
| 20 | Deliverables | ☐ Approved |

### Additional Notes / Changes Requested:
```
_____________________________________________
_____________________________________________
_____________________________________________
_____________________________________________
```

**Client Name:** _________________________

**Client Signature:** _________________________

**Date:** _________________________

---

*BTR Valet — White-Label Parking & Valet Management SaaS*
*Prepared by BTR Development Team*
