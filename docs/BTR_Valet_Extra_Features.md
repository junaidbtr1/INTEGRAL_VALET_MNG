# BTR Valet — Extra Features Built Beyond Requirements
**Date:** April 27, 2026

All features listed below were **not mentioned in the client's requirements document** but have been fully built and delivered as part of the system.

---

## 1. Multi-Tenant SaaS Architecture

### Subscription Plans
- Super admin creates plans with quotas: max parking slots, max staff users, max tickets per day
- Quotas enforced at runtime — system rejects check-ins or staff creation when limits are hit
- Plans can be activated/deactivated

### Tenant Lifecycle Management
- 5 tenant states: **Trial → Active → Suspended → Cancelled → Deleted**
- Trial period configurable per tenant (1–90 days)
- Suspended or cancelled tenants are blocked from logging in

### Tenant Feature Flags
Each garage can independently toggle on/off:
- Coupon/discount system
- QR code tickets
- Barcode tickets
- SMS notifications
- Email notifications
- Vehicle photo capture
- Multi-floor parking
- Reserved parking slots
- Valet GPS tracking
- Customer feedback

### Building Owner User Type
- A single user can own and manage **multiple garages**
- Dedicated dashboard showing all buildings with live usage bars (slots used, staff count, tickets today)
- One-click switch between buildings without logging out

---

## 2. Super Admin Portal

Separate portal for the platform operator — completely invisible to garage clients:

| Feature | Description |
|---|---|
| Super Admin Dashboard | Total tenants, active / trial / suspended counts, new tenants this month |
| Tenant Management | Create, view, edit, suspend, delete tenants |
| Plan Management | Create and manage subscription plans with feature sets and quotas |
| Global User Management | View and manage users across all tenants |
| Building Owner Assignment | Assign building owners to specific garages |

---

## 3. Coupon & Discount System

4 coupon types supported:

| Type | Behavior | Example |
|---|---|---|
| Percentage | Discount by % of total | 20% off |
| Fixed Amount | Flat dollar discount | $5.00 off |
| Free Hours | Deduct hours from duration | First 2 hours free |
| Full Waiver | 100% free parking | VIP complimentary |

Additional coupon controls:
- Activate / deactivate coupons
- Valid date range (start and end dates)
- Total usage limit across all customers
- Per-customer usage limit
- Minimum charge threshold (e.g. only valid on charges above $10)
- Maximum discount cap (e.g. 20% off but never more than $20)
- Coupon usage log (per ticket, per user, discount amount stored)
- Validate coupon before payment to catch errors before charging

---

## 4. Vehicle Registry (Persistent Database)

Vehicles are tracked across all visits — not just for the current ticket:

| Feature | Description |
|---|---|
| Persistent records | Plate, type, color, make, model, owner name, owner phone stored permanently |
| VIP flagging | VIP vehicles automatically receive free or discounted parking |
| Blacklist system | Block check-in for specific plates; reason stored; alert shown to valet at check-in |
| Visit count | Auto-incremented on every check-in; last visit date tracked |
| Plate autocomplete | Suggests returning vehicle details when valet starts typing a plate number |
| Ticket history | Last 10 tickets shown per vehicle |

---

## 5. Parking Slot Management

| Feature | Description |
|---|---|
| Slot types | Standard, Compact, Large, Disabled, EV Charging, VIP, Reserved |
| Visual slot grid | Interactive color-coded map of all slots (green = available, red = occupied, amber = reserved, grey = maintenance) |
| Slot status toggle | Click any slot to toggle between available and maintenance |
| Bulk slot creation | Create up to 200 slots at once by specifying floor, zone, prefix, and number range |
| Occupancy summary | Live count of total / available / occupied / maintenance + occupancy percentage |
| Floor & zone organization | Multi-floor support (B1, G, 1F, 2F) with zone labels |
| Capacity enforcement | System rejects new check-ins when parking lot is 100% full |
| Auto slot assignment | If no slot specified at check-in, system assigns the nearest available slot automatically |

---

## 6. Ticket Status State Machine

8 ticket states with enforced transitions (invalid transitions are blocked with an error):

| From | To | Trigger |
|---|---|---|
| Created | Active | Vehicle enters |
| Active | Completed | Payment taken |
| Active | Lost Ticket | Customer lost their ticket (penalty fee applies) |
| Active | Overstay | Exceeded maximum parking duration (surcharge applies) |
| Active | Disputed | Payment disputed |
| Active | Cancelled | Ticket voided |
| Completed | Closed | Vehicle exits after payment |

---

## 7. Advanced Pricing Engine

| Feature | Description |
|---|---|
| Flat hourly rate | Simple rate per hour |
| Tiered pricing | Different rates per duration band (e.g. 0–1h: $5, 1–3h: $8, 3–6h: $12) |
| Grace period | Configurable free window at the start of every visit (e.g. first 15 minutes free) |
| Tax calculation | Configurable tax rate; stored separately from base amount on every ticket |
| Overstay surcharge | Extra charge after configurable maximum duration |
| Lost ticket penalty | Flat fee when customer cannot produce their ticket |
| Per-vehicle-type rates | Different pricing for motorcycle / car / SUV / van / truck |
| Live pricing preview | Settings page shows what a customer would pay at 1h, 3h, and 6h with all rules applied |
| Operating hours | Configurable open and close times per garage |
| Auto-close tickets | Tickets automatically closed after a configurable number of hours |

---

## 8. Payment Refunds

- Refund any completed payment with a mandatory reason
- Creates a separate refund record (negative amount, REF prefix on receipt number)
- Original payment marked as REFUNDED
- Full refund history visible in the payments list

---

## 9. Custom Role & Permission System

| Feature | Description |
|---|---|
| 50+ granular permissions | e.g. tickets.create, payments.refund, reports.view, settings.update, slots.manage |
| Built-in roles | Supervisor, Valet Staff, Cashier, Viewer — predefined permission sets |
| Custom role builder | Tenant admin creates their own roles by selecting from a grouped permission checklist |
| Tenant-scoped roles | Same user can have Supervisor access in Garage A and Viewer access in Garage B |
| Route-level enforcement | Unauthorized users cannot access restricted pages |
| UI-level enforcement | Buttons and actions are hidden based on the logged-in user's permissions |

---

## 10. QR Code Generation & Thermal Receipt Printing

| Feature | Description |
|---|---|
| QR code per ticket | Generated and displayed in a modal immediately on ticket creation |
| Print ticket | 80mm thermal receipt format, compatible with standard receipt printers |
| Print contents | Ticket number, plate, vehicle type, slot, entry time, garage name |
| No special drivers | Works via the browser's native print dialog — no extra software needed |

---

## 11. Analytics & Reporting

Reports beyond the daily shift totals:

| Report | Description |
|---|---|
| Revenue by day | Bar chart with hover showing amount + ticket count per day |
| Peak hours chart | Ticket volume by hour (0–23) — identifies busiest periods of the day |
| Payment method breakdown | Cash / card / digital with counts, amounts, and percentages |
| Vehicle type breakdown | Motorcycle / car / SUV / etc. with counts and percentages |
| Top 10 vehicles | Most frequent visitors ranked by visit count |
| Summary metrics | Total revenue, total tickets, completion rate %, average duration, average ticket value |
| Date range presets | Today, This Week, This Month, Last Month, Custom range |
| CSV export | Download any report as a spreadsheet |

---

## 12. Staff Management Module

| Feature | Description |
|---|---|
| Full staff CRUD | Create, edit, deactivate, delete staff members |
| Role assignment | Assign built-in or custom roles at creation |
| Shift assignment | Assign staff to a shift at creation or update |
| Plan quota enforcement | Rejects staff creation when the plan's max staff limit is reached |
| Deactivation cascade | Deactivating a staff member immediately revokes all their active login sessions |
| Last login tracking | Last login date and IP address visible per staff member |

---

## 13. Authentication Security Features

| Feature | Description |
|---|---|
| Account lockout | Account locked after multiple failed login attempts |
| Multi-device sessions | Each login on a new device creates a separate session token |
| Logout single device | Revoke current session only |
| Logout all devices | Revoke all sessions simultaneously (one click) |
| Token refresh | Re-authenticate without re-entering credentials |
| Force password change | Flag staff to change their password on first login |
| Login tracking | Last login timestamp and IP address stored per user |
| Tenant status check at login | Suspended or cancelled tenants are blocked at the login screen |

---

## 14. Digital Wallet as a Third Payment Method

In addition to cash and credit card, the system supports:
- **Digital wallets** (JazzCash, EasyPaisa, or any configured digital payment provider)

---

## 15. System Health Check Endpoint

- `GET /health` — public endpoint returning real-time status of database, Redis cache, queue workers, and file storage
- Used by load balancers, uptime monitors, and DevOps dashboards
- Responds in under 200ms

---

*BTR Valet Development Team — April 2026*
