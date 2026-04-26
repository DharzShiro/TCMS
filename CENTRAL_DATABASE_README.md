# TCMS Central/Superadmin Database Schema

This document provides a comprehensive overview of the central application database for the **TCMS (Tenant Course Management System)**. The central database manages multi-tenancy operations, billing, support, and system administration, while tenant-specific data resides in isolated tenant databases.

---

## Table of Contents

1. [Overview](#overview)
2. [Core Tables](#core-tables)
3. [Tenant Management](#tenant-management)
4. [Billing & Subscriptions](#billing--subscriptions)
5. [Support & Communication](#support--communication)
6. [System & Monitoring](#system--monitoring)
7. [Architecture Notes](#architecture-notes)

---

## Overview

**Database Connection:** `central` (as defined in `config/tenancy.php`)

The TCMS uses a **multi-tenant architecture** where:
- **Central Database**: Manages superadmin functions, tenant metadata, billing, and support
- **Tenant Databases**: Each tenant has an isolated database named `tenant{tenant_id}` containing courses, trainees, assessments, etc.

All timestamps use UTC and are automatically managed by Laravel's `timestamps()` migration helper.

---

## Core Tables

### `users`
Central system users with superadmin and admin roles.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary key |
| name | string | User's full name |
| email | string | Unique email address |
| email_verified_at | timestamp | NULL if unverified |
| password | string | Hashed password |
| role | string | `super_admin`, `admin`, `trainer`, `trainee` |
| remember_token | string | Session token |
| created_at | timestamp | Record creation |
| updated_at | timestamp | Last update |

### `sessions`
Laravel session table for user authentication state.

| Column | Type | Notes |
|--------|------|-------|
| id | string | Primary key (session ID) |
| user_id | bigint | Foreign key to users |
| ip_address | string (45) | IPv4/IPv6 address |
| user_agent | text | Browser/client info |
| payload | longtext | Serialized session data |
| last_activity | integer | Unix timestamp of last activity |

### `password_reset_tokens`
Password reset functionality and tokens.

| Column | Type | Notes |
|--------|------|-------|
| email | string | Primary key |
| token | string | Reset token |
| created_at | timestamp | Token creation time |

### `cache`
Laravel cache storage table.

| Column | Type | Notes |
|--------|------|-------|
| key | string | Cache key |
| value | text | Cached value |
| expiration | integer | Unix expiration timestamp |

### `jobs`
Queue system for background jobs.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary key |
| queue | string | Queue name |
| payload | longtext | Job data |
| attempts | bigint | Number of attempts |
| reserved_at | timestamp | Reserved timestamp |
| available_at | timestamp | When job becomes available |
| created_at | timestamp | Job creation |

---

## Tenant Management

### `tenants`
Core tenant organization records. Each tenant is a separate client/organization.

| Column | Type | Notes |
|--------|------|-------|
| id | string | Primary key (UUID) |
| name | string | Organization name |
| admin_email | string | Unique admin email |
| subdomain | string | Unique subdomain (e.g., `acme.tcm.com`) |
| subscription | string | Current plan: `basic`, `standard`, `premium` |
| status | enum | `pending`, `approved`, `rejected` |
| is_active | boolean | Account active flag |
| brand_name | string | Custom application name |
| brand_logo | string | Path to uploaded logo |
| brand_color_primary | string | Hex color (e.g., `#003087`) |
| brand_color_accent | string | Hex color (e.g., `#CE1126`) |
| brand_tagline | string | Custom tagline |
| expires_at | timestamp | Subscription expiration |
| created_at | timestamp | Account creation |
| updated_at | timestamp | Last update |
| data | json | Additional metadata |

### `domains`
Custom domain mappings for tenants (alternative to subdomain routing).

| Column | Type | Notes |
|--------|------|-------|
| id | integer | Primary key |
| domain | string | Fully qualified domain (unique) |
| tenant_id | string | Foreign key to tenants |
| created_at | timestamp | Record creation |
| updated_at | timestamp | Last update |

**Relationship**: Each tenant can have multiple domains.

### `tenant_subscriptions`
Subscription transaction history per tenant. Tracks all subscription activations and renewals.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary key |
| tenant_id | string | Foreign key to tenants |
| plan_slug | string | `basic`, `standard`, or `premium` |
| discount_usage_id | bigint | Foreign key to discount_usages (nullable) |
| amount_paid | decimal (10,2) | Amount paid in PHP |
| action | string | `approve`, `upgrade_superadmin`, `renewal` |
| starts_at | timestamp | Subscription start date |
| expires_at | timestamp | Subscription expiry (nullable) |
| applied_by | bigint | User ID who processed (nullable) |
| created_at | timestamp | Record creation |
| updated_at | timestamp | Last update |

**Indices**: `[tenant_id, starts_at]` for efficient querying.

### `tenant_version_statuses`
Tracks current software version and update status per tenant.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary key |
| tenant_id | string | Foreign key to tenants (unique) |
| current_version | string | Version tenant is running |
| latest_version | string | Latest available version |
| update_status | enum | `up_to_date`, `update_available`, `queued`, `running`, `completed`, `failed`, `skipped` |
| failure_reason | string | Error message if failed |
| last_checked_at | timestamp | Last version check |
| last_updated_at | timestamp | Last successful update |
| applied_releases | json | Array of applied release IDs |
| created_at | timestamp | Record creation |
| updated_at | timestamp | Last update |

### `tenant_update_logs`
Detailed logs of system updates applied to each tenant.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary key |
| tenant_id | string | Foreign key to tenants |
| release_id | bigint | Foreign key to system_releases (nullable) |
| from_version | string | Version upgraded from |
| to_version | string | Version upgraded to |
| status | enum | `queued`, `running`, `completed`, `failed`, `rolled_back` |
| triggered_by | enum | `superadmin`, `tenant`, `auto` |
| output | text | Artisan migration output |
| failure_reason | text | Failure details |
| started_at | timestamp | Update start time |
| completed_at | timestamp | Update completion time |
| created_at | timestamp | Log creation |
| updated_at | timestamp | Last update |

**Indices**: `[tenant_id, status]`, `created_at`.

### `tenant_usage_stats`
Resource usage metrics per tenant (storage, bandwidth, database size).

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary key |
| tenant_id | string | Foreign key to tenants (unique) |
| db_size_bytes | bigint | Database size in bytes |
| file_size_bytes | bigint | File storage size in bytes |
| bandwidth_bytes_today | bigint | Bandwidth used today |
| bandwidth_bytes_total | bigint | Total bandwidth used |
| bandwidth_date | date | Date of bandwidth measurement |
| last_calculated_at | timestamp | Last calculation time |
| created_at | timestamp | Record creation |
| updated_at | timestamp | Last update |

---

## Billing & Subscriptions

### `subscription_plans`
Master list of available subscription tiers. Pre-seeded with Basic, Standard, Premium plans.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary key |
| slug | string | Unique plan identifier (`basic`, `standard`, `premium`) |
| name | string | Display name (e.g., "Basic Plan") |
| icon | string | Emoji icon (default: 📦) |
| description | text | Plan description |
| price | decimal (10,2) | Monthly price in PHP |
| currency | string | Currency code (default: `PHP`) |
| duration_days | integer | Days granted per subscription (30, 180, 365) |
| max_trainees | integer | User limit (NULL = unlimited) |
| max_trainers | integer | Trainer limit |
| max_users | integer | Total user limit |
| max_courses | integer | Course limit |
| max_exports_monthly | integer | Monthly export limit |
| allowed_export_formats | json | Array of allowed formats: `["csv", "excel", "pdf"]` |
| has_assessments | boolean | Feature flag |
| has_certificates | boolean | Feature flag |
| has_custom_reports | boolean | Feature flag |
| has_branding | boolean | Feature flag |
| has_trainers | boolean | Feature flag |
| is_active | boolean | Plan availability |
| sort_order | tinyint | Display order |
| available_from | date | Plan availability start (nullable) |
| available_until | date | Plan availability end (nullable) |
| created_at | timestamp | Record creation |
| updated_at | timestamp | Last update |

### `discounts`
Promotional discount codes and rules.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary key |
| code | string | Unique promo code (e.g., `SAVE20`) |
| label | string | Human-readable name |
| type | enum | `percentage` or `fixed` |
| value | decimal (10,2) | Discount value (20.00 or 500.00) |
| plan_slugs | json | Applicable plans (NULL = all plans) |
| tenant_ids | json | Restricted tenants (NULL = all tenants) |
| valid_from | date | Valid start date (nullable) |
| valid_until | date | Valid end date (nullable) |
| is_active | boolean | Discount active flag |
| is_automatic | boolean | Automatically applied flag |
| created_at | timestamp | Record creation |
| updated_at | timestamp | Last update |

### `discount_usages`
Audit trail of discount applications.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary key |
| discount_id | bigint | Foreign key to discounts |
| tenant_id | string | Foreign key to tenants |
| action | string | `approve`, `upgrade_superadmin`, `renewal` |
| plan_slug | string | Plan purchased |
| original_price | decimal (10,2) | Price before discount |
| discount_amount | decimal (10,2) | Discount applied |
| final_price | decimal (10,2) | Price after discount |
| applied_by | bigint | Superadmin user ID (nullable) |
| created_at | timestamp | Record creation |
| updated_at | timestamp | Last update |

**Indices**: `[discount_id, tenant_id]` for efficient lookups.

### `renewal_requests`
Tenant subscription renewal requests awaiting superadmin approval.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary key |
| tenant_id | string | Foreign key to tenants |
| plan_slug | string | Plan requested for renewal |
| duration_days | integer | Subscription duration |
| discount_code | string | Applied promo code (nullable) |
| original_price | decimal (10,2) | Base price |
| discount_amount | decimal (10,2) | Discount applied |
| final_price | decimal (10,2) | Price after discount |
| status | string | `pending`, `approved`, `rejected`, `expired` |
| reviewed_by | bigint | Superadmin user ID (nullable) |
| reviewed_at | timestamp | Review timestamp (nullable) |
| notes | text | Superadmin review notes (nullable) |
| created_at | timestamp | Record creation |
| updated_at | timestamp | Last update |

**Indices**: `[tenant_id, status]`, `[status, created_at]`.

---

## Support & Communication

### `support_tickets`
Customer support tickets from tenants.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary key |
| ticket_number | string | Unique reference (e.g., `TKT-0001`) |
| tenant_id | string | Foreign key to tenants |
| requester_name | string | Name of person submitting ticket |
| requester_email | string | Email of requester |
| tenant_user_id | bigint | Reference to tenant's user (nullable) |
| subject | string | Ticket subject |
| category | enum | `bug_report`, `technical_issue`, `account_concern`, `billing_concern`, `feature_request`, `general_inquiry` |
| status | enum | `open`, `in_progress`, `resolved`, `closed` |
| priority | enum | `low`, `medium`, `high`, `urgent` |
| assignee_id | bigint | Superadmin assigned (nullable) |
| last_reply_at | timestamp | Last message timestamp (nullable) |
| last_reply_by | enum | `admin` or `tenant` (nullable) |
| unread_admin | integer | Unread count for admin |
| unread_tenant | integer | Unread count for tenant |
| created_at | timestamp | Ticket creation |
| updated_at | timestamp | Last update |

**Indices**: `[tenant_id, status]`, `[status, priority]`, `ticket_number`.

### `support_messages`
Individual messages within support tickets.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary key |
| ticket_id | bigint | Foreign key to support_tickets |
| sender_type | enum | `admin` or `tenant` |
| sender_name | string | Name of message sender |
| sender_email | string | Email of sender (nullable) |
| body | text | Message content |
| is_internal | boolean | Internal note (admin-only) |
| created_at | timestamp | Message creation |
| updated_at | timestamp | Last update |

**Indices**: `[ticket_id, created_at]`.

### `support_attachments`
File attachments to support messages.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary key |
| message_id | bigint | Foreign key to support_messages |
| original_name | string | Original filename |
| stored_path | string | Path relative to `storage/app/` |
| mime_type | string | File MIME type |
| file_size | bigint | File size in bytes |
| created_at | timestamp | Record creation |
| updated_at | timestamp | Last update |

**Indices**: `message_id`.

---

## System & Monitoring

### `system_releases`
Software version releases synced from GitHub.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary key |
| github_id | string | GitHub release ID (unique, nullable) |
| tag_name | string | Git tag (e.g., `v1.2.0`) |
| version | string | Semantic version (e.g., `1.2.0`) |
| name | string | Release title from GitHub (nullable) |
| body | longtext | Markdown release notes |
| is_prerelease | boolean | Pre-release flag |
| is_active | boolean | Active/live status (superadmin controlled) |
| is_deployed | boolean | Code deployed to server flag |
| github_url | string | Link to GitHub release (nullable) |
| download_url | string | Download URL (nullable) |
| manifest | json | Structured metadata (nullable) |
| published_at | timestamp | GitHub publication timestamp (nullable) |
| created_at | timestamp | Record creation |
| updated_at | timestamp | Last update |

**Indices**: `version`, `is_active`, `published_at`.

### `activity_logs`
Audit trail of user actions and login attempts.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary key |
| tenant_id | string | Tenant (if applicable, nullable) |
| tenant_name | string | Tenant name snapshot (nullable) |
| user_id | bigint | User ID (nullable) |
| user_name | string | User name snapshot (nullable) |
| user_email | string | User email snapshot (nullable) |
| role | string | User role snapshot (nullable) |
| action | string | `login_success`, `login_failed`, `logout`, etc. |
| ip_address | string | Client IP address (nullable) |
| user_agent | string | Browser/client info (nullable) |
| success | boolean | Action success flag |
| failure_reason | string | Reason for failure (nullable) |
| created_at | timestamp | Record creation |
| updated_at | timestamp | Last update |

**Indices**: `[tenant_id, created_at]`, `[user_email, created_at]`, `action`.

### `notifications`
In-app notifications for system users.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key to users |
| title | string | Notification title |
| message | text | Notification message |
| is_read | boolean | Read status |
| link | string | Optional action link (nullable) |
| created_at | timestamp | Record creation |
| updated_at | timestamp | Last update |

---

## Architecture Notes

### Multi-Tenant Isolation
- **Central Database**: Single database containing all superadmin, billing, and support data
- **Tenant Databases**: Each tenant has an isolated database (e.g., `tenantabc123`) containing:
  - Courses, Assessments, Certificates
  - Trainees, Trainers, Users (tenant staff)
  - Enrollments, Attendance, Grades
  - Custom tenant-specific data

### Connection Configuration
Defined in `config/tenancy.php`:
- Central connection: `central` (MySQL/PostgreSQL)
- Tenant connection: Dynamically created per tenant
- Database naming: `prefix + tenant_id + suffix` → `tenant{uuid}`

### Key Relationships
```
tenants ← → domains (1:N)
tenants ← → tenant_subscriptions (1:N)
tenants ← → tenant_version_statuses (1:1)
tenants ← → tenant_update_logs (1:N)
tenants ← → tenant_usage_stats (1:1)
tenants ← → renewal_requests (1:N)
tenants ← → support_tickets (1:N)

subscription_plans ← → tenant_subscriptions (1:N)
subscription_plans ← → renewal_requests (0:N)

discounts ← → discount_usages (1:N)
discounts ← → renewal_requests (0:N)

support_tickets ← → support_messages (1:N)
support_messages ← → support_attachments (1:N)

system_releases ← → tenant_update_logs (1:N)

users ← → notifications (1:N)
users ← → activity_logs (0:N)
```

### Subscription Lifecycle
1. Tenant requests subscription renewal → `renewal_requests`
2. Superadmin reviews and approves
3. Discount applied (if applicable) → `discount_usages`
4. Transaction recorded → `tenant_subscriptions`
5. Tenant `expires_at` updated in `tenants` table
6. `tenant_subscriptions.expires_at` tracks exact expiry

### Support Workflow
1. Tenant submits ticket → `support_tickets`
2. Superadmin receives notification
3. Superadmin assigns to self/other admin
4. Messages exchanged → `support_messages`
5. Files attached → `support_attachments`
6. Status transitions: `open` → `in_progress` → `resolved` → `closed`

### Version Management
1. GitHub releases synced to `system_releases`
2. Superadmin marks as "active" (live)
3. Tenants checked for updates → `tenant_version_statuses`
4. Update jobs queued → `tenant_update_logs`
5. Migrations run in tenant databases
6. Status tracked (queued, running, completed, failed)

---

## Common Queries

### Get all active tenants with current subscriptions
```sql
SELECT t.*, tp.name as plan_name, ts.starts_at, ts.expires_at
FROM tenants t
LEFT JOIN subscription_plans tp ON t.subscription = tp.slug
LEFT JOIN tenant_subscriptions ts ON t.id = ts.tenant_id
WHERE t.is_active = true AND ts.expires_at > NOW()
ORDER BY t.created_at DESC;
```

### Get pending renewal requests
```sql
SELECT rr.*, t.name as tenant_name, sp.name as plan_name
FROM renewal_requests rr
JOIN tenants t ON rr.tenant_id = t.id
JOIN subscription_plans sp ON rr.plan_slug = sp.slug
WHERE rr.status = 'pending'
ORDER BY rr.created_at ASC;
```

### Get open support tickets assigned to admin
```sql
SELECT st.*, t.name as tenant_name
FROM support_tickets st
JOIN tenants t ON st.tenant_id = t.id
WHERE st.status IN ('open', 'in_progress') AND st.assignee_id = ?
ORDER BY st.priority DESC, st.created_at ASC;
```

### Get tenants needing update
```sql
SELECT t.*, tvs.current_version, tvs.latest_version
FROM tenants t
JOIN tenant_version_statuses tvs ON t.id = tvs.tenant_id
WHERE tvs.update_status = 'update_available' AND t.is_active = true;
```

---

## References

- **Configuration**: [config/tenancy.php](config/tenancy.php)
- **Models**: [app/Models/](app/Models/)
- **Migrations**: [database/migrations/](database/migrations/)
- **Multi-Tenancy Package**: [Stancl Tenancy for Laravel](https://tenancyforlaravel.com/)

