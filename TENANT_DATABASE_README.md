# TCMS Tenant Database Schema

This document provides a comprehensive overview of the **tenant application database** for the **TCMS (Tenant Course Management System)**. Each tenant has an isolated database containing all tenant-specific data including users, courses, enrollments, assessments, and certificates.

---

## Table of Contents

1. [Overview](#overview)
2. [User Management](#user-management)
3. [Training Content](#training-content)
4. [Enrollments & Tracking](#enrollments--tracking)
5. [Assessment & Certification](#assessment--certification)
6. [Trainer Management](#trainer-management)
7. [Notifications](#notifications)
8. [Relationships Diagram](#relationships-diagram)
9. [User Roles & Permissions](#user-roles--permissions)
10. [Common Queries](#common-queries)

---

## Overview

**Database Naming Convention**: `tenant{tenant_uuid}`  
**Example**: `tenantabc123def456`

Each tenant database is **completely isolated** and contains:
- All tenant users (admin, trainers, trainees)
- Courses and training schedules
- Enrollments and attendance tracking
- Assessments and results
- Certificates and completion records
- Tenant-specific notifications

This isolation ensures:
- **Data Privacy**: No cross-tenant data leakage
- **Performance**: Tenant loads don't affect others
- **Customization**: Tenants can have custom fields/workflows
- **Backup/Recovery**: Tenant data can be managed independently

---

## User Management

### `users`
All users within a tenant organization (admin, trainers, trainees).

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary key |
| name | string | User's full name |
| email | string | Unique email within tenant |
| google_id | string | Google OAuth ID (nullable) |
| email_verified_at | timestamp | Email verification status |
| password | string | Hashed password (nullable for OAuth) |
| role | string | `admin`, `trainer`, `trainee` |
| remember_token | string | Session token |
| created_at | timestamp | Record creation |
| updated_at | timestamp | Last update |

**Note**: Unlike central users, tenant users have roles: `admin`, `trainer`, `trainee`.

### `password_reset_tokens`
Password reset functionality for tenant users.

| Column | Type | Notes |
|--------|------|-------|
| email | string | Primary key |
| token | string | Reset token |
| created_at | timestamp | Token creation time |

### `sessions`
Laravel session table for user authentication within tenant context.

| Column | Type | Notes |
|--------|------|-------|
| id | string | Primary key (session ID) |
| user_id | bigint | Foreign key to users |
| ip_address | string (45) | IPv4/IPv6 address |
| user_agent | text | Browser/client info |
| payload | longtext | Serialized session data |
| last_activity | integer | Unix timestamp of last activity |

### `cache`
Tenant-specific cache storage.

| Column | Type | Notes |
|--------|------|-------|
| key | string | Cache key |
| value | text | Cached value |
| expiration | integer | Unix expiration timestamp |

### `cache_locks`
Distributed cache lock table.

| Column | Type | Notes |
|--------|------|-------|
| key | string | Primary key |
| owner | string | Lock owner ID |
| expiration | integer | Expiration timestamp |

### `jobs`
Queue system for tenant-specific background jobs (reports, exports, etc.).

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary key |
| queue | string | Queue name |
| payload | longtext | Job data |
| attempts | bigint | Number of attempts |
| reserved_at | timestamp | Reserved timestamp |
| available_at | timestamp | When job becomes available |
| created_at | timestamp | Job creation |

### `job_batches`
Batch job tracking for grouped operations.

| Column | Type | Notes |
|--------|------|-------|
| id | string | Primary key |
| name | string | Batch name |
| total_jobs | integer | Total jobs in batch |
| pending_jobs | integer | Pending job count |
| failed_jobs | integer | Failed job count |
| failed_job_ids | longtext | JSON array of failed job IDs |
| options | json | Batch options |
| cancelled_at | timestamp | Cancellation time (nullable) |
| created_at | timestamp | Batch creation |
| finished_at | timestamp | Completion time (nullable) |

### `failed_jobs`
Failed job logs for debugging and retrying.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary key |
| uuid | string | Unique job UUID |
| connection | string | Queue connection |
| queue | string | Queue name |
| payload | longtext | Job data |
| exception | longtext | Exception/error message |
| failed_at | timestamp | Failure timestamp |

---

## Training Content

### `courses`
Training courses offered by the tenant organization.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary key |
| code | string | Unique course code (e.g., `NC-2-ELE`) |
| name | string | Course title |
| description | text | Course details/syllabus (nullable) |
| duration_hours | integer | Total course hours |
| level | enum | `NC I`, `NC II`, `NC III`, `NC IV`, `COC` (nullable) |
| status | enum | `active`, `inactive` (default: active) |
| created_at | timestamp | Record creation |
| updated_at | timestamp | Last update |

**NC Levels**: National Competency levels (Philippines TESDA standard)

### `training_schedules`
Scheduled training sessions for courses.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary key |
| course_id | bigint | Foreign key to courses |
| trainer_id | bigint | Foreign key to users (trainer) |
| start_date | date | Training start date |
| end_date | date | Training end date |
| time_start | time | Daily start time |
| time_end | time | Daily end time |
| location | string | Training location/venue (nullable) |
| status | enum | `upcoming`, `ongoing`, `completed`, `cancelled` |
| created_at | timestamp | Record creation |
| updated_at | timestamp | Last update |

**Note**: One course can have multiple training_schedules (different batches/trainers).

---

## Enrollments & Tracking

### `enrollments`
Trainee enrollment in courses.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary key |
| trainee_id | bigint | Foreign key to users (trainee) |
| course_id | bigint | Foreign key to courses |
| status | enum | `pending`, `approved`, `completed`, `dropped` |
| enrolled_at | timestamp | Enrollment timestamp (nullable) |
| created_at | timestamp | Record creation |
| updated_at | timestamp | Last update |

**Workflow**:
1. Trainee enrolls → `pending`
2. Admin approves → `approved`
3. Course completes → `completed` (if attended/passed)
4. Trainee drops → `dropped`

### `attendances`
Daily attendance tracking for enrolled trainees.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary key |
| enrollment_id | bigint | Foreign key to enrollments |
| date | date | Attendance date |
| status | enum | `present`, `absent`, `late` |
| created_at | timestamp | Record creation |
| updated_at | timestamp | Last update |

**Note**: One attendance record per trainee per training day.

---

## Assessment & Certification

### `assessments`
Assessment results for trainee competency evaluation.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary key |
| enrollment_id | bigint | Foreign key to enrollments |
| trainer_id | bigint | Foreign key to users (trainer) |
| score | decimal (5,2) | Score out of 100 (nullable) |
| remarks | text | Trainer comments/feedback (nullable) |
| result | enum | `competent`, `not_yet_competent` |
| assessed_at | timestamp | Assessment timestamp (nullable) |
| created_at | timestamp | Record creation |
| updated_at | timestamp | Last update |

**Competency-Based Assessment**: Results are either `competent` or `not_yet_competent` (pass/fail model common in TESDA).

### `certificates`
Certificates issued upon successful course completion.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary key |
| enrollment_id | bigint | Foreign key to enrollments |
| certificate_number | string | Unique cert number (e.g., `CERT-2026-001`) |
| issued_at | date | Issue date |
| expires_at | date | Expiration date (nullable) |
| trainer_id | bigint | Foreign key to users (trainer who signed) (nullable) |
| created_at | timestamp | Record creation |
| updated_at | timestamp | Last update |

**Certificate Lifecycle**:
1. Trainee completes course
2. Trainer assesses as `competent`
3. Certificate issued with unique number
4. Optional expiration date for renewal

---

## Trainer Management

### `trainers`
Extended profile information for trainer users.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key to users (unique) |
| specialization | string | Training specialization (nullable) |
| certification_number | string | Official certification ID (nullable) |
| experience_years | integer | Years of training experience (nullable) |
| department | string | Department/unit (nullable) |
| created_at | timestamp | Record creation |
| updated_at | timestamp | Last update |

**Note**: One-to-one relationship with users. Only users with `role = trainer` have trainer records.

---

## Notifications

### `notifications`
In-app notifications for tenant users.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key to users |
| title | string | Notification title |
| message | text | Notification message |
| is_read | boolean | Read status (default: false) |
| link | string | Optional action link (nullable) |
| created_at | timestamp | Record creation |
| updated_at | timestamp | Last update |

**Use Cases**:
- Enrollment approved
- Assessment results available
- Certificate issued
- Course starting
- Trainer announcements

---

## Relationships Diagram

```
users (1:N) ← → sessions (many)
users (1:N) ← → password_reset_tokens
users (1:1) ← → trainers (only for trainers)
users (1:N) ← → notifications
users (1:N) ← → training_schedules (as trainer)
users (1:N) ← → enrollments (as trainee)
users (1:N) ← → assessments (as trainer)
users (1:N) ← → certificates (as trainer)

courses (1:N) ← → training_schedules
courses (1:N) ← → enrollments

training_schedules (1:N) ← → enrollments
training_schedules → trainer_id (users)

enrollments (1:1) ← → assessments
enrollments (1:1) ← → certificates
enrollments (1:N) ← → attendances

assessments → trainer_id (users)
certificates → trainer_id (users)
certificates → enrollment_id (enrollments)

attendances → enrollment_id (enrollments)
```

---

## User Roles & Permissions

### Admin Role
- Manage users (trainers, trainees)
- Create and manage courses
- Schedule training sessions
- Approve/reject enrollments
- View reports and analytics
- Issue certificates (or delegate to trainers)
- System configuration

### Trainer Role
- View assigned courses and schedules
- Record attendance for trainees
- Conduct assessments
- Provide feedback
- View trainee progress
- Generate training reports

### Trainee Role
- Enroll in courses
- View personal dashboard
- Check attendance/progress
- View assessments and feedback
- Download certificates
- Receive notifications

---

## Common Queries

### Get All Active Courses
```sql
SELECT * FROM courses 
WHERE status = 'active' 
ORDER BY name ASC;
```

### Get Upcoming Training Schedules
```sql
SELECT ts.*, c.name as course_name, u.name as trainer_name
FROM training_schedules ts
JOIN courses c ON ts.course_id = c.id
JOIN users u ON ts.trainer_id = u.id
WHERE ts.start_date >= CURDATE() AND ts.status = 'upcoming'
ORDER BY ts.start_date ASC;
```

### Get Trainees Enrolled in a Course
```sql
SELECT u.*, e.status, e.enrolled_at
FROM enrollments e
JOIN users u ON e.trainee_id = u.id
WHERE e.course_id = ? AND u.role = 'trainee'
ORDER BY u.name ASC;
```

### Get Trainee Attendance Summary
```sql
SELECT 
    u.name,
    COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present_count,
    COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as absent_count,
    COUNT(CASE WHEN a.status = 'late' THEN 1 END) as late_count,
    COUNT(a.id) as total_sessions
FROM enrollments e
JOIN users u ON e.trainee_id = u.id
JOIN attendances a ON e.id = a.enrollment_id
WHERE e.course_id = ?
GROUP BY u.id
ORDER BY present_count DESC;
```

### Get Assessment Results for a Course
```sql
SELECT 
    u.name as trainee_name,
    a.score,
    a.result,
    a.remarks,
    t.name as trainer_name,
    a.assessed_at
FROM assessments a
JOIN enrollments e ON a.enrollment_id = e.id
JOIN users u ON e.trainee_id = u.id
JOIN users t ON a.trainer_id = t.id
WHERE e.course_id = ?
ORDER BY a.assessed_at DESC;
```

### Get Competent Trainees (Eligible for Certification)
```sql
SELECT 
    u.name,
    c.name as course_name,
    a.score,
    a.result,
    a.assessed_at
FROM assessments a
JOIN enrollments e ON a.enrollment_id = e.id
JOIN users u ON e.trainee_id = u.id
JOIN courses c ON e.course_id = c.id
WHERE a.result = 'competent' AND e.status = 'approved'
ORDER BY a.assessed_at DESC;
```

### Get Certificates Issued by Month
```sql
SELECT 
    DATE_TRUNC('month', cert.issued_at)::date as month,
    COUNT(cert.id) as certificates_issued,
    COUNT(DISTINCT e.trainee_id) as trainees_certified
FROM certificates cert
JOIN enrollments e ON cert.enrollment_id = e.id
GROUP BY DATE_TRUNC('month', cert.issued_at)
ORDER BY month DESC;
```

### Get Trainer Activity (Scheduled Classes)
```sql
SELECT 
    u.name as trainer_name,
    COUNT(ts.id) as total_schedules,
    COUNT(CASE WHEN ts.status = 'upcoming' THEN 1 END) as upcoming_classes,
    COUNT(CASE WHEN ts.status = 'ongoing' THEN 1 END) as ongoing_classes,
    COUNT(CASE WHEN ts.status = 'completed' THEN 1 END) as completed_classes
FROM users u
JOIN training_schedules ts ON u.id = ts.trainer_id
WHERE u.role = 'trainer'
GROUP BY u.id
ORDER BY total_schedules DESC;
```

### Get Enrollment Funnel (Application → Completion)
```sql
SELECT 
    c.name as course_name,
    COUNT(CASE WHEN e.status = 'pending' THEN 1 END) as pending,
    COUNT(CASE WHEN e.status = 'approved' THEN 1 END) as approved,
    COUNT(CASE WHEN e.status = 'completed' THEN 1 END) as completed,
    COUNT(CASE WHEN e.status = 'dropped' THEN 1 END) as dropped,
    COUNT(e.id) as total_enrollments
FROM enrollments e
JOIN courses c ON e.course_id = c.id
GROUP BY c.id
ORDER BY total_enrollments DESC;
```

### Get Unread Notifications by User
```sql
SELECT 
    u.name,
    COUNT(n.id) as unread_count
FROM notifications n
JOIN users u ON n.user_id = u.id
WHERE n.is_read = false
GROUP BY u.id
ORDER BY unread_count DESC;
```

---

## Data Isolation & Multi-Tenancy

### Key Points

1. **Complete Database Isolation**
   - Each tenant has a separate database instance
   - No shared tables or data across tenants
   - Queries automatically scoped to active tenant

2. **Tenant Context Management**
   - Laravel tenancy middleware enforces tenant context
   - All queries within request use tenant's database
   - Storage and cache are tenant-scoped

3. **Backup & Recovery**
   - Each tenant database can be backed up independently
   - Restore operations don't affect other tenants
   - Point-in-time recovery available per tenant

4. **Scaling**
   - Can migrate tenants to separate database servers
   - Read replicas for reporting/analytics per tenant
   - Database size managed independently

---

## Performance Optimization

### Recommended Indices
```sql
CREATE INDEX idx_courses_status ON courses(status);
CREATE INDEX idx_training_schedules_course_date ON training_schedules(course_id, start_date);
CREATE INDEX idx_training_schedules_trainer_date ON training_schedules(trainer_id, start_date);
CREATE INDEX idx_enrollments_trainee_status ON enrollments(trainee_id, status);
CREATE INDEX idx_enrollments_course_status ON enrollments(course_id, status);
CREATE INDEX idx_enrollments_created_at ON enrollments(created_at);
CREATE INDEX idx_attendances_enrollment_date ON attendances(enrollment_id, date);
CREATE INDEX idx_assessments_enrollment ON assessments(enrollment_id);
CREATE INDEX idx_assessments_result ON assessments(result);
CREATE INDEX idx_certificates_enrollment ON certificates(enrollment_id);
CREATE INDEX idx_trainers_user_id ON trainers(user_id);
CREATE INDEX idx_notifications_user_read ON notifications(user_id, is_read);
```

### Query Optimization
- Use eager loading for relationships
- Paginate large result sets
- Index frequently searched columns
- Archive old records regularly
- Use aggregation for reports

---

## Migration Path

When onboarding a new tenant:

1. **Central Database Updates**
   - Create tenant record in central `tenants` table
   - Create domain mapping in central `domains` table
   - Create subscription in central `tenant_subscriptions`
   - Update `tenant_version_statuses` and `tenant_usage_stats`

2. **Tenant Database Creation**
   - Create new database: `tenant{uuid}`
   - Run migrations: `php artisan migrate --tenancy`
   - Seed admin user and default data
   - Configure tenant branding

3. **Activation**
   - Set `tenants.status = 'approved'`
   - Set `tenants.is_active = true`
   - Configure custom domain (if applicable)
   - Deploy tenant application

---

## References

- **Tenant Migrations**: [database/migrations/tenant/](database/migrations/tenant/)
- **Models**: [app/Models/](app/Models/)
- **Multi-Tenancy Config**: [config/tenancy.php](config/tenancy.php)
- **Stancl Tenancy Docs**: [tenancyforlaravel.com](https://tenancyforlaravel.com/)

