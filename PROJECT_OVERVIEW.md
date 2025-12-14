# HiveIT Portal - Comprehensive Project Overview

## Project Identity
**Name:** HiveIT Portal (formerly ProjectTracker)  
**Type:** Web-based IT Service Management & CRM Platform  
**Tech Stack:** PHP 8.x, Microsoft SQL Server, Bootstrap 5.3.2, JavaScript  
**Server Environment:** Apache/Nginx on Linux (Proxmox VE 6.8.12-8)  
**Primary URL:** https://itcrm.merakitechnologies.co.uk  
**Development Server:** 192.168.2.12  
**Web Root:** `/var/www/ProjectTracker/`  
**Worktree:** `/root/.cursor/worktrees/ProjectTracker__SSH__192.168.2.12_/bjk/`

---

## Architecture Overview

### MVC Pattern
The application follows a custom MVC (Model-View-Controller) architecture:

```
/app
  /controllers     - Business logic and request handling
  /models          - Database interactions and data models
  /views           - HTML templates (PHP-based)
  /core            - Framework components (App, Controller, Database)
  /helpers         - Utility functions (flash, permissions, format)
  /services        - Third-party integrations (Email, Level.io API)
  /scripts         - Background tasks (email processing)
```

### Core Framework Components

#### 1. **App.php** - Application Router
- Parses URLs and routes to appropriate controllers
- Handles authentication checks and redirects
- Supports customer portal routing (`/customer/*`)
- Default controller: `Home` → redirects to `Dashboard`
- Session management and error reporting

#### 2. **EasySQL.php** - Database Abstraction Layer
- MS SQL Server connection via ODBC Driver 18
- Methods: `select()`, `insert()`, `update()`, `remove()`, `query()`
- Parameter binding for SQL injection prevention
- Connection pooling via `DB1` constant

#### 3. **PermissionHelper.php** - Access Control
- Role-based permissions (RBAC)
- Functions: `hasPermission()`, `requirePermission()`, `getAccessibleMenuItems()`
- Permissions format: `{resource}.{action}` (e.g., `clients.read`, `tasks.update`)
- Integrates with `Roles` and `EnhancedPermissions` tables

#### 4. **Functions.php** - Global Helpers
- `sanitize_input()` - XSS prevention
- `isLoggedIn()`, `isAdmin()` - Authentication checks
- `redirect()` - Header-based redirects
- `flash()` - Session-based flash messages

---

## Database Schema (MS SQL Server)

### Core Tables

#### Users & Authentication
- **Users** - User accounts (id, username, password, email, full_name, role_id, created_at)
- **Roles** - User roles (id, name, display_name, description)
- **EnhancedPermissions** - Granular permissions (id, role_id, permission_key, allowed)
- **UserLoginAudit** - Login tracking (id, user_id, username, ip_address, user_agent, success, created_at)
- **UserSettings** - Per-user preferences (user_id, setting_key, setting_value)

#### Client Management
- **Clients** - Customer records (id, name, contact_person, email, phone, address, industry, status, is_restricted, allowed_role_ids, created_at, updated_at)
- **ClientServices** - Services provided to clients (id, client_id, service_name, service_type, quantity, start_date, end_date, notes)
- **ClientStatusHistory** - Tracks status changes (id, client_id, old_status, new_status, changed_by, changed_at)
- **ClientContracts** - Uploaded contracts (id, client_id, file_name, file_path, file_size, uploaded_at)
- **ClientMeetings** - Meeting records (id, client_id, meeting_at, title, notes)
- **ClientDocuments** - Document storage (id, client_id, file_name, file_path, file_size, uploaded_at)
- **ClientDomains** - Email domains for auto-linking (id, client_id, domain, is_primary)

#### Project & Task Management
- **projects** - Project records (id, title, description, start_date, end_date, status, user_id, department_id, budget, client_id)
- **project_users** - Project team members (id, project_id, user_id, role)
- **project_sites** - Projects linked to sites (id, project_id, site_id)
- **tasks** - Task records (id, project_id, title, description, status, priority, start_date, due_date, assigned_to, created_by, parent_task_id, progress_percent, tags, estimated_hours)
- **task_users** - Multi-user task assignments (id, task_id, user_id)
- **task_sites** - Tasks linked to sites (id, task_id, site_id)

#### Site & Location Management
- **Sites** - Physical locations (id, name, site_code, location, type, status, created_at)
- **SiteClients** - Many-to-many client-site relationships (id, site_id, client_id, relationship_type)
- **SiteServices** - Services per site (id, site_id, service_name, service_type, start_date, end_date, notes)
- **SiteVisits** - Technician site visits (id, site_id, user_id, visit_date, title, summary, created_at)
- **SiteVisitAttachments** - Photos/docs from visits

#### Ticketing System
- **Tickets** - Support tickets (id, subject, description, status, priority, client_id, assigned_to, created_by, created_at)
- **EmailInbox** - Parsed email tickets (id, from_email, subject, body_html, body_plain, received_at, status)
- **EmailAttachments** - Email attachments storage

#### Time Tracking
- **TimeEntries** - Time logs (id, user_id, project_id, site_id, start_time, end_time, duration_minutes, description)
- **DailyActivities** - Daily summaries

#### Universal Systems
- **Reminders** - Cross-entity follow-ups (id, entity_type, entity_id, title, notes, remind_at, created_by, recipient_user_id, status, notify_all)
  - Supports: 'client', 'project', 'task' entities
  - Replaces legacy ClientCallbacks, ProjectCallbacks
- **Notes** - Cross-entity notes (id, reference_type, reference_id, title, content, tags, created_by, created_at)
- **ActivityLog** - System activity tracking
- **settings** - System configuration (setting_key, setting_value)

#### Organization
- **departments** - Organizational units (id, name, budget, currency)
- **Employees** - Employee records (id, user_id, department_id, hire_date, salary, performance_rating)
- **Suppliers** - Vendor management

#### Network & Infrastructure
- **Networkaudits** - Network discovery forms (id, client_id, audit_date, findings)
- **Devices** - IT equipment tracking
- **WeeklyRouters** - Router scheduling

---

## Key Features & Modules

### 1. Dashboard System
**Routes:** `/dashboard`, `/home` (redirects to dashboard)  
**Controller:** `Dashboard.php`, `Home.php`  
**Views:** `dashboard/index.php`, `home/dashboard.php`

**Displays:**
- KPI cards: Active Clients, Total Users, Technicians, Active Sites, Open Tickets, Open Tasks, Currently Working
- Assigned Tasks table (filtered by client visibility)
- Recent Activity feed
- Top Clients by ticket volume (today/week/month filter)
- Budget usage by department (via `Home` controller)
- All data respects client visibility restrictions

**User-Specific Dashboard:**
- Shows tasks assigned to logged-in user
- Project/task stats filtered by role-based client access
- Recent activity limited to accessible clients

### 2. Client Management
**Routes:** `/clients/*`  
**Controller:** `Clients.php`  
**Model:** `Client.php`, `ClientService.php`, `ClientStatusHistory.php`  
**Views:** `clients/index.php`, `clients/view.php`, `clients/create.php`, `clients/edit.php`

**Core Features:**
- **CRUD Operations:** Create, read, update, delete clients
- **Visibility Controls:** Per-client restrictions by role (admin configurable)
  - `is_restricted` flag (BIT)
  - `allowed_role_ids` (comma-separated role IDs)
  - Filters cascade to projects/tasks
- **Client Services:** Track services provided (name, type, quantity, dates, notes)
- **Status History:** Automatic logging of status changes (Prospect → Active, etc.)
- **Profile Age:** Calculates and displays client age (e.g., "2y 3m", "5m 12d")
- **Site Assignments:** Link clients to multiple sites via `SiteClients`
- **Contracts:** File upload/download/delete for PDFs, DOCs, images
- **Domains:** Email domain management for auto-linking tickets
- **Follow-ups/Reminders:** Using universal `Reminders` table
- **Meetings:** Schedule and track client meetings
- **Documents:** Additional file storage
- **Network Audits:** Link discovery forms to clients
- **Level.io Integration:** Device monitoring via API

**Visibility Enforcement:**
- Client list filtered by `filterClientsForRole()`
- View access guarded by `canAccessClientId()`
- Related projects/tasks filtered by `getBlockedClientIdsForRole()`
- Cascades to search, dashboards, reports

### 3. Project Management
**Routes:** `/projects/*`  
**Controller:** `Projects.php`  
**Model:** `Project.php`  
**Views:** `projects/index.php`, `projects/viewProject.php`, `projects/create.php`

**Features:**
- **Project CRUD** with client linking (`client_id` column)
- **Budget Tracking:** Per-project budgets, department rollups
- **Team Assignments:** Multi-user via `project_users` table
- **Site Linking:** Associate projects with sites
- **Task Management:** Nested tasks with status/priority/progress
- **Documents:** File attachments
- **Follow-ups:** Universal reminders
- **Risk Assessment:** `getProjectRisks()` method
- **KPI Metrics:** Total tasks, open tasks, overdue tasks, weighted completion %
- **Visibility:** Inherits from client; filtered by `blockedClientIds()`

**Project Stats:**
- Active/Completed/On Hold counts
- Total budget across all projects
- Department budget usage with percentage bars

### 4. Task Management
**Routes:** `/tasks/*`  
**Controller:** `Tasks.php`  
**Model:** `Task.php`  
**Views:** `tasks/index.php`, `tasks/show.php`, `tasks/create.php`

**Features:**
- **Multi-user Assignments:** `task_users` junction table
- **Subtasks:** `parent_task_id` for hierarchical tasks
- **Progress Tracking:** `progress_percent` (0-100)
- **Site Linking:** Tasks can be site-specific
- **Callbacks:** Follow-ups via `Reminders`
- **Tags & References:** Flexible metadata
- **Estimated Hours:** For weighted progress calculations
- **Filters:** By project, status, priority
- **Visibility:** Filtered via project → client relationship

**Task Statuses:** Pending, In Progress, Testing, Completed, Blocked  
**Priorities:** Low, Normal, High, Critical

### 5. Ticketing System
**Routes:** `/tickets/*`  
**Controller:** `Tickets.php`  
**Model:** `Ticket.php`, `EmailInbox.php`, `EmailInboxModel.php`

**Features:**
- **Email-to-Ticket:** Automated parsing from inbound email (IMAP/POP3/OAuth2)
- **Client Auto-Linking:** Matches sender domain to `ClientDomains`
- **Attachments:** Download/view email attachments
- **Ticket Dashboard:** Visual analytics
- **Customer Portal:** Customers can view their tickets via Azure AD SSO
- **Email Processing Script:** `process_emails.php` (runs via cron)

**Email Configuration:**
- SMTP outbound (settings: `from_email`, `smtp_host`, `smtp_port`, etc.)
- IMAP/POP3 inbound (settings: `inbound_protocol`, `inbound_host`, etc.)
- OAuth2 support (Microsoft/Azure)
- Auto-acknowledgment emails

### 6. Time Tracking
**Routes:** `/time/*`  
**Controller:** `Time.php`  
**Model:** `TimeTracking.php`, `DailyActivity.php`  
**Widget:** Sidebar time status widget

**Features:**
- **Clock In/Out:** Start/stop time entries
- **Project/Site Linking:** Track time per project or site
- **Daily Summaries:** Aggregate hours per user per day
- **Sidebar Widget:** Shows current timer, quick project select, clock in/out
- **Dashboard:** Time tracking overview with charts
- **Reports:** Hours by user, project, period

**Settings:**
- `show_sidebar_time_status` - Toggle sidebar widget visibility

### 7. Employee Management
**Routes:** `/employees/*`  
**Controller:** `Employees.php`  
**Model:** `Employee.php`  
**Views:** `employees/index.php`, `employees/performance_dashboard.php`

**Features:**
- **HR Records:** Hire date, salary, department assignment
- **Performance Tracking:** Ratings (1-5), notes, history
- **Login Analytics:** Last login stats from `UserLoginAudit`
- **Department Assignment:** Links to departments table

### 8. Settings & Configuration

#### Admin Settings
**Route:** `/admin/settings`  
**Controller:** `Admin.php`  
**View:** `admin/settings_clean.php`  
**Model:** `Setting.php`

**Categories:**

**Application Tab:**
- Maintenance Mode (admin-only access)
- User Registration (enable/disable)
- API Access (enable/disable)
- Sidebar Time Status Widget (show/hide)
- Max Upload Size (MB)
- Projects Per User Limit
- Prospect Follow-up Automation:
  - Enable/disable (`prospect_followup_enabled`)
  - Interval in days (`prospect_followup_interval_days`)
  - Auto-creates reminders for Prospect clients

**Display Settings:**
- Date Format (Y-m-d, m/d/Y, d/m/Y, M j, Y)
- Display Timezone (default: America/Los_Angeles)
- Database Timezone (default: America/Toronto)
- Handles timezone conversion in login audit and other date displays

**Currency Tab:**
- Currency Code (USD, EUR, GBP, etc.)
- Symbol ($, €, £)
- Position (before/after)
- Decimals (0-4)
- Thousands/Decimal Separators

**Email Tab:**
- SMTP Configuration (host, port, username, password, encryption)
- Inbound Protocol (IMAP/POP3)
- OAuth2 Settings (Microsoft/Azure)
- Auto-process Emails
- Auto-acknowledge Tickets
- Attachment Limits

**Authentication Tab:**
- Customer Auth (Azure AD SSO for customer portal)
- Domain Restrictions
- Ticket Visibility Rules

**Integrations:**
- Level.io API (device monitoring)
- Microsoft Graph API (email)

#### User Settings
**Route:** `/settings`  
**Controller:** `Settings.php`  
**View:** `settings/index.php`

- Profile Information
- Preferences (theme, items per page, date format)
- Notification Settings
- Security (password change via `/profile/changePassword`)

---

## Authentication & Authorization

### User Authentication
**Login:** `/auth` or `/users/login`  
**Controller:** `Auth.php`  
**Model:** `User.php`

**Password Support:**
- Hashed passwords (bcrypt/argon2 via `password_hash()`)
- Legacy plain text (fallback for migration)
- MD5/SHA-256 hex (detected and verified)
- Password trimming for legacy whitespace

**Session Variables:**
- `$_SESSION['is_logged_in']` - Boolean
- `$_SESSION['user_id']` - Integer
- `$_SESSION['username']` - String
- `$_SESSION['user_name']` - Display name
- `$_SESSION['role']` - Role name (admin, user, technician, etc.)
- `$_SESSION['role_id']` - Integer FK to Roles table

### Customer Portal Authentication
**Login:** `/customer/auth`  
**Controller:** `CustomerAuth.php`

**Azure AD Integration:**
- OAuth2 flow via Microsoft identity platform
- Domain-restricted access (`customer_domain_restriction` setting)
- Ticket visibility: `email_match` or `domain_match`
- Separate session: `$_SESSION['customer_logged_in']`

### Permission System

#### Role-Based Access Control (RBAC)
**Tables:** `Roles`, `EnhancedPermissions`

**Permission Keys:** (stored in `EnhancedPermissions.permission_key`)
```
clients.read, clients.create, clients.update, clients.delete, clients.assign_sites
projects.read, projects.create, projects.update, projects.delete
tasks.read, tasks.create, tasks.update, tasks.delete
tickets.read, tickets.create, tickets.update, tickets.delete
users.manage
admin.access, admin.system_settings
reports.read
time.manage
```

**Enforcement:**
- Controller `__construct()` checks via `hasPermission()`
- Menu items filtered by `PermissionHelper::getAccessibleMenuItems()`
- UI elements conditionally rendered: `<?php if (hasPermission('resource.action')): ?>`

#### Client Visibility System (NEW)
**Purpose:** Hide clients and their projects/tasks from users without proper role access.

**Implementation:**
1. **Client Model (`Client.php`):**
   - `ensureVisibilityColumns()` - Adds `is_restricted` (BIT), `allowed_role_ids` (NVARCHAR)
   - `getBlockedClientIdsForRole($roleId, $isAdmin)` - Returns IDs user can't see
   - `filterClientsForRole($clients, $roleId, $isAdmin)` - Filters client arrays
   - `canAccessClientId($clientId, $roleId, $isAdmin)` - Single client check

2. **Controller Integration:**
   - `Clients::index()` - Filters client list
   - `Clients::viewClient($id)` - Blocks access if restricted
   - `Projects::index()` - Filters projects by `blockedClientIds()`
   - `Tasks::index()` - Filters tasks via `projectClientMap`
   - `Dashboard::index()` - Filters all stats/lists
   - `Search::index()` - Excludes restricted items from search

3. **Data Flow:**
   - Admin edits client → sets `is_restricted=1`, selects allowed roles
   - On save → stores as comma-separated IDs in `allowed_role_ids`
   - On query → model checks current user's role against allowed list
   - If blocked → item excluded from results, direct access returns 403

4. **UI Elements:**
   - Client create/edit forms: "Restrict visibility" toggle + role multi-select
   - Client view page: "Visibility" card shows Public/Restricted status + allowed roles
   - Restricted clients hidden from dropdowns, lists, search, dashboards

---

## Recent Enhancements (Dec 2024)

### Dashboard Improvements
1. **User Tasks Panel:** Shows tasks assigned to logged-in user on both dashboards
2. **My Tasks Section:** Displays due dates, priority badges, urgency highlighting
3. **Client Visibility Filtering:** All dashboard stats respect restrictions

### Client Module Enhancements
1. **Services Tracking:** New `ClientServices` table with quantity support
2. **Status History:** Auto-logs status changes (e.g., Prospect → Active)
3. **Profile Age:** Displays client age in human-readable format
4. **Collapsible Forms:** Services, Follow-ups, Contracts now collapsible
5. **Header Button Cleanup:** Inline flex layout prevents wrapping
6. **Manage Sites Button:** Styled consistently with others

### Prospect Follow-up Automation
**Settings:** `prospect_followup_enabled`, `prospect_followup_interval_days`  
**Scheduler:** Runs on dashboard load (throttled to 12h intervals)

**Logic:**
- Queries all clients with `status = 'Prospect'`
- For each, checks last reminder via `getLatestReminderForEntity()`
- If no reminder or last reminder > interval days → creates new reminder
- Title: "Prospect follow-up - [Client Name]"
- `notify_all = 1` for visibility

### Password Change
**Route:** `/profile/changePassword`  
**Method:** POST form in modal on profile page

**Validation:**
- Current password verification (supports hashed + legacy formats)
- New password ≥ 6 characters
- Confirm password must match
- New ≠ current
- Hashes new password with `password_hash()`

### Timezone Management
**Settings:**
- `display_timezone` - How times are shown (default: America/Los_Angeles)
- `db_timezone` - Database storage timezone (default: America/Toronto)

**Application:**
- `App.php` sets `date_default_timezone_set()` on bootstrap
- Login audit converts DB time → display time explicitly
- Admin can change via System Settings → Display Settings

### Dark Mode
**Implementation:**
- Uses Bootstrap 5.3 `data-bs-theme` attribute
- Auto-detects system preference via `prefers-color-scheme`
- Manual toggle button in navbar (sun/moon icon)
- Preference saved in `localStorage.setItem('theme')`
- Priority: localStorage > system preference

**Styling:**
- Comprehensive dark palette in `app.css`
- Base colors: backgrounds #0d1117, #161b22, #21262d; text #c9d1d9
- Borders: #30363d
- Links/accents: #58a6ff
- All cards, forms, tables, modals, dropdowns, search, sidebar themed
- Notes cards removed hardcoded `bg-white` classes

---

## File Structure Deep Dive

### Controllers (170 files)
**Key Controllers:**
- `Auth.php` - Login/logout, password reset
- `Dashboard.php` - Main dashboard with stats/charts
- `Home.php` - Legacy dashboard (now redirects to `/dashboard`)
- `Clients.php` - Client CRUD, services, contracts, visibility
- `Projects.php` - Project CRUD, team management, documents
- `Tasks.php` - Task CRUD, subtasks, callbacks, progress
- `Tickets.php` - Ticket management, email parsing
- `Time.php` - Time tracking dashboard
- `Profile.php` - User profile, password change, skills
- `Admin.php` - Admin dashboard, settings, user management, login audit
- `Search.php` - Universal search API (JSON responses)
- `Reports.php` - System analytics and reports
- `Settings.php` - User/system settings management
- `Employees.php` - HR module, performance reviews
- `Sites.php`, `Sitevisits.php` - Location management
- `Suppliers.php`, `Invoices.php` - Vendor/billing
- `Customer.php`, `CustomerAuth.php` - Customer portal

**Controller Pattern:**
```php
class Clients extends Controller {
    private $clientModel;
    private $roleModel;
    
    public function __construct() {
        if (!isLoggedIn()) redirect('users/login');
        $this->clientModel = $this->model('Client');
        $this->roleModel = $this->model('Role');
    }
    
    private function currentRoleId(): ?int {
        return isset($_SESSION['role_id']) ? (int)$_SESSION['role_id'] : null;
    }
    
    private function isAdminRole(): bool {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
    
    public function index() {
        if (!hasPermission('clients.read')) {
            flash('error', 'No permission');
            redirect('dashboard');
        }
        $clients = $this->clientModel->getAllClients();
        $clients = $this->clientModel->filterClientsForRole($clients, $this->currentRoleId(), $this->isAdminRole());
        $this->view('clients/index', ['clients' => $clients]);
    }
}
```

### Models
**Pattern:** Each model represents a table, provides CRUD + specialized queries

**Key Models:**
- `User.php` - Authentication, profile, settings, skills
- `Client.php` - Client CRUD, visibility filtering, site relationships
- `Project.php` - Project CRUD, stats, budget calculations, client mapping
- `Task.php` - Task CRUD, stats, filtering, search, user assignments
- `Ticket.php` - Ticket management
- `TimeTracking.php` - Time entry CRUD, summaries, currently working
- `Setting.php` - System configuration (get/set/getSystemSettings)
- `Reminder.php` - Universal follow-up system
- `Note.php` - Universal notes system
- `Role.php` - Role management
- `EnhancedPermission.php` - Permission CRUD
- `Department.php` - Budget tracking
- `Site.php`, `SiteService.php`, `SiteVisit.php` - Location management
- `ClientService.php`, `ClientStatusHistory.php` - Client enhancements
- `LoginAudit.php` - Login tracking

**Model Pattern:**
```php
class Client {
    private $db;
    
    public function __construct() {
        $this->db = new EasySQL(DB1);
        $this->ensureVisibilityColumns(); // Auto-migrate
    }
    
    public function getAllClients() {
        return $this->db->select("SELECT * FROM Clients ORDER BY name");
    }
    
    public function getClientById($id) {
        $result = $this->db->select("SELECT * FROM Clients WHERE id = ?", [$id]);
        return $result ? $result[0] : false;
    }
    
    public function addClient($data) {
        return $this->db->insert("INSERT INTO Clients (...) VALUES (...)", [...]);
    }
}
```

### Views
**Layout System:**
- `layouts/default.php` - Main layout (header, sidebar, content, footer)
- `layouts/login.php` - Login-specific layout
- `partials/sidebar.php` - Navigation menu (permission-filtered)
- `partials/header.php` - Empty (legacy)
- `partials/footer.php` - Scripts, closing tags
- `partials/notes_section.php` - Reusable notes widget
- `partials/time_widget.php` - Time tracking sidebar widget

**View Pattern:**
```php
// In controller:
$this->view('clients/index', ['clients' => $clients, 'title' => 'Clients']);

// In view (clients/index.php):
<?php foreach ($clients as $client): ?>
    <tr>
        <td><?= htmlspecialchars($client['name']) ?></td>
        ...
    </tr>
<?php endforeach; ?>
```

### Services
**Location:** `/app/services/`

- `EmailService.php` - SMTP email sending
- `LevelApiService.php` - Level.io device monitoring API client
- `SimpleMailer.php` - Lightweight mailer wrapper

### Scripts (Background Jobs)
**Location:** `/app/scripts/`

- `process_emails.php` - Fetches emails, creates tickets, saves attachments
  - Run via cron: `php /var/www/ProjectTracker/app/scripts/process_emails.php`
  - Logs to: `logs/email_processing.log`
- `send_missing_acknowledgments.php` - Sends ticket acknowledgment emails
- `fix_duplicate_emails.php` - Data cleanup utility

---

## Frontend Architecture

### CSS Organization
**File:** `/public/css/app.css` (2600+ lines)

**Sections:**
1. **CSS Variables** - Colors, spacing, fonts
2. **Layout** - App container, sidebar, main content
3. **Sidebar** - Navigation, menu items, brand
4. **Search Bar** - Universal search UI
5. **Cards & Stats** - Dashboard widgets
6. **Tables** - Data tables, sorting, filtering
7. **Forms** - Inputs, selects, validation
8. **Buttons** - Primary, secondary, outline variants
9. **Modals** - Popups for forms/confirmations
10. **Notifications** - Toast-style alerts
11. **Activity Feed** - Timeline items
12. **Client/Project Cards** - List item styling
13. **Notes** - Note cards with expand/collapse
14. **Time Widget** - Sidebar timer UI
15. **Print Styles** - Clean print layouts
16. **Responsive** - Mobile breakpoints
17. **Dark Mode** - Comprehensive `[data-bs-theme="dark"]` overrides

**Dark Mode Color Palette:**
```css
Background: #0d1117, #161b22, #21262d (3-tier depth)
Text: #c9d1d9 (primary), #8b949e (muted)
Borders: #30363d
Links/Accent: #58a6ff
Success: #0e7c3a
Warning: #f59e0b
Danger: #d1242f
```

### JavaScript Components

#### Universal Search
**Location:** `layouts/default.php` inline script

**Features:**
- Debounced AJAX search (300ms)
- Multi-entity: projects, tasks, users, clients, notes, tickets
- Keyboard navigation (↑↓ arrows, Enter, Esc)
- Global shortcut: Ctrl+K / Cmd+K
- Permission-aware results
- Real-time type filtering

**API Endpoint:** `/search?q={query}&type={type}&limit={limit}`

#### Theme Toggle
**Location:** `layouts/default.php` inline script

**Features:**
- Toggle button in navbar (sun/moon icon)
- Persists to `localStorage.getItem('theme')`
- Instant visual update via `data-bs-theme` attribute
- Falls back to system preference if not set
- Icons: `bi-sun-fill` (light mode), `bi-moon-fill` (dark mode)

#### Notes Filtering & Sorting
**Location:** `partials/notes_section.php` inline script

- Live filter by title/content
- Sort by newest/oldest
- Expand/collapse long notes
- AJAX add/delete without page reload

---

## Database Connection

### Configuration
**File:** `/config/config.php`

```php
define('DB_SERVER', '192.168.2.12');
define('DB_USERNAME', 'sa');
define('DB_PASSWORD', '...');
define('DB_DATABASE', 'ProjectTracker');
define('DB_DRIVER', '{ODBC Driver 18 for SQL Server}');
define('DB1', 'Driver=' . DB_DRIVER . ';Server=' . DB_SERVER . ';Database=' . DB_DATABASE . ';Encrypt=no;TrustServerCertificate=yes;UID=' . DB_USERNAME . ';PWD=' . DB_PASSWORD);
```

### EasySQL Methods
```php
$db->select($query, $params)      // Returns array of rows
$db->insert($query, $params)      // Returns last inserted ID
$db->update($query, $params)      // Returns affected rows
$db->remove($query, $params)      // Returns affected rows
$db->query($query)                // Execute raw SQL (DDL)
```

### Auto-Migration Pattern
Models create tables/columns automatically on instantiation:

```php
public function __construct() {
    $this->db = new EasySQL(DB1);
    $this->ensureVisibilityColumns(); // Adds columns if missing
}

private function ensureVisibilityColumns(): void {
    $sql = "IF COL_LENGTH('dbo.Clients', 'is_restricted') IS NULL
            BEGIN
                ALTER TABLE Clients ADD is_restricted BIT DEFAULT 0
            END";
    $this->db->query($sql);
}
```

---

## Workflow Examples

### Creating a Client with Restricted Visibility

1. **Admin navigates to:** `/clients/create`
2. **Fills form:**
   - Name: "Confidential Corp"
   - Status: "Prospect"
   - Checks "Restrict visibility"
   - Selects allowed roles: "Manager", "Senior Technician"
3. **Submits →** `Clients::create()` (POST)
4. **Controller:**
   - Sanitizes POST data
   - Validates name/email
   - Converts `allowed_roles[]` array to comma-separated string
   - Calls `Client::addClient($data)`
5. **Model:**
   - Inserts to `Clients` table
   - Sets `is_restricted=1`, `allowed_role_ids='3,5'`
6. **Result:**
   - Users with role_id 3 or 5 can see client
   - Admin always sees client
   - Other users: client excluded from all lists/queries

### Automatic Prospect Follow-up

1. **Admin enables:** System Settings → Prospect Follow-ups → Enable + Set interval (14 days)
2. **Next dashboard load →** `Dashboard::index()` runs scheduler:
   - Checks `prospect_followup_last_run` > 12 hours ago
   - Queries `Clients WHERE status = 'Prospect'`
   - For each client:
     - Gets latest reminder via `getLatestReminderForEntity('client', $clientId)`
     - If none OR last reminder > 14 days old
     - Creates new reminder: title = "Prospect follow-up - {ClientName}", remind_at = now + 14 days, notify_all = 1
   - Updates `prospect_followup_last_run` timestamp
3. **Reminders appear in:**
   - Notification bell dropdown (top navbar)
   - Client view page → Follow-ups section
   - Reminder list for assigned users

### Task Visibility Inheritance

1. **Client "SecretCo" restricted to role_id=5**
2. **Project "Secure Migration" linked to SecretCo (client_id=1010)**
3. **Task "Setup VPN" linked to project**
4. **User with role_id=3 navigates to `/tasks`:**
   - `Tasks::index()` calls `Task::getAllTasks()`
   - Gets `blockedClientIds()` → [1010]
   - Gets `projectClientMap` → [project_id => client_id]
   - Filters tasks: removes any where project maps to blocked client
   - Task "Setup VPN" excluded from list
5. **Direct access `/tasks/show/123`:**
   - `Tasks::show($id)` loads task
   - Gets project, checks `project->client_id`
   - Calls `canAccessClientId(1010, role_id=3, isAdmin=false)` → false
   - Flash error: "You do not have access to this task"
   - Redirects to `/tasks`

---

## Integration Points

### Email Processing
**Flow:**
1. Cron runs `process_emails.php` every 5-15 minutes
2. Script connects via IMAP/POP3/OAuth2
3. Fetches unread emails
4. Parses headers, body, attachments
5. Extracts ticket reference or creates new ticket
6. Matches sender domain to `ClientDomains` → auto-links client
7. Saves attachments to `/public/uploads/email_attachments/`
8. Marks email as processed in `EmailInbox`
9. Optionally sends acknowledgment email
10. Logs to `logs/email_processing.log`

**Settings Used:**
- `inbound_protocol`, `inbound_host`, `inbound_port`, `inbound_username`, `inbound_password`
- `auto_process_emails`, `delete_processed_emails`, `auto_acknowledge_tickets`
- `ticket_email_pattern` - Regex to match ticket refs in subject

### Level.io Device Monitoring
**Service:** `LevelApiService.php`  
**Endpoints:**
- List groups
- List devices per group
- Device details

**UI:**
- Client view page: "Level.io Devices" button (if groups linked)
- Route: `/clients/levelDevices/{clientId}`
- Shows devices for client's linked Level.io groups

**Settings:**
- `level_io_enabled` (boolean)
- `level_io_api_key` (string)
- Client-level: `level_io_group_id`, `level_io_group_name`

### Customer Portal (Azure AD SSO)
**Routes:** `/customer/*`  
**Purpose:** Allow clients to view their tickets without staff accounts

**Flow:**
1. Customer visits `/customer`
2. Redirected to `/customer/auth`
3. "Sign in with Microsoft" button
4. OAuth2 redirect to Microsoft
5. Callback validates token, checks domain
6. If domain matches `customer_domain_restriction` → session created
7. Customer sees tickets filtered by `ticket_visibility` rule:
   - `email_match`: Tickets where customer email = ticket.from_email
   - `domain_match`: Tickets where domain in `ClientDomains`
8. Customer can view ticket details, attachments
9. Optional: Create new tickets if `allow_ticket_creation=1`

---

## Security Considerations

### Input Validation
- All POST data sanitized via `htmlspecialchars()`, `trim()`
- Email validation: `filter_var($email, FILTER_VALIDATE_EMAIL)`
- File upload checks: type, size, extension whitelist
- SQL injection prevention: parameterized queries via `EasySQL`

### XSS Prevention
- All output escaped: `<?= htmlspecialchars($var) ?>`
- URL encoding: `urlencode()` for query strings
- JavaScript escaping: `escapeHtml()` function in search

### Authentication
- Session-based with `is_logged_in` flag
- Password hashing with bcrypt (PHP `password_hash()`)
- Legacy password migration on login
- Login audit trail (`UserLoginAudit`)

### Authorization
- Permission checks on every controller action
- Menu items dynamically filtered
- Client visibility enforcement at data layer
- Admin actions require `admin.access` permission

### File Uploads
- Validated extensions: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG
- Size limits: Configurable via `max_upload_size` setting
- Stored outside web root or with unique names
- Download via controller (forced headers, no direct access)

---

## Performance Optimizations

### CSS
- Will-change properties on animated elements
- Transform GPU acceleration
- Reduced motion media query support

### Database
- Indexes on frequently queried columns (created_at, user_id, status)
- TOP N queries for limits (SQL Server syntax)
- Cached settings in memory where possible
- Project client map for batch filtering

### JavaScript
- Debounced search (300ms)
- Event delegation for dynamic elements
- Lazy loading of models in sidebar notifications

---

## Deployment Workflow

### Development → Production
**Current Setup:**
- Development: Git worktree at `/root/.cursor/worktrees/ProjectTracker__SSH__192.168.2.12_/bjk/`
- Production: Web root at `/var/www/ProjectTracker/`
- Manual sync: `cp` commands from worktree to web root
- No CI/CD pipeline currently

**Sync Pattern:**
```bash
cp /root/.cursor/worktrees/.../file.php /var/www/ProjectTracker/file.php
service apache2 reload  # or nginx reload
```

### Git Repository
**Location:** `/root/.cursor/worktrees/ProjectTracker__SSH__192.168.2.12_/bjk/.git`  
**Branch:** Detached HEAD (worktree)  
**Modified:** `logs/email_processing.log` (not committed)

---

## Configuration Files

### `/config/config.php`
- Database credentials
- URL constants: `URLROOT`, `SITENAME`
- Path constants: `APPROOT`, `VIEWSPATH`
- Error reporting settings

### `/config/email_config.php`
- SMTP configuration
- Email templates
- Sender defaults

---

## Error Handling & Logging

### PHP Errors
- Display errors enabled in development
- Error reporting: E_ALL
- Logs to Apache/PHP error logs

### Application Logs
- Email processing: `/logs/email_processing.log`
- Model errors: `error_log()` to system log
- Controller errors: Flash messages to user

### User-Facing Errors
- Flash messages: `flash($name, $message, $class)`
- Alert types: `alert-success`, `alert-danger`, `alert-warning`, `alert-info`
- Auto-dismiss after 5 seconds
- 404 page: `errors/404.php`

---

## Testing & Verification

### Client Visibility Testing
**Manual Steps:**
1. Create client "TestRestricted" with `is_restricted=1`, allowed_role_ids='5'
2. Create project "TestProject" linked to client
3. Create task "TestTask" linked to project
4. Log in as user with role_id=3:
   - Client absent from `/clients` list
   - Project absent from `/projects` list
   - Task absent from `/tasks` list
   - Dashboard stats exclude client's data
   - Search returns no results for client/project/task
   - Direct URL access → flash error + redirect
5. Log in as user with role_id=5:
   - All items visible
   - Can access, edit, view
6. Log in as admin:
   - All items visible regardless

### Prospect Follow-up Testing
1. Set `prospect_followup_enabled=1`, `prospect_followup_interval_days=7`
2. Create client "TestProspect" with status="Prospect"
3. Load `/dashboard`
4. Check `Reminders` table for new entry:
   - `entity_type='client'`, `entity_id={client_id}`
   - `title='Prospect follow-up - TestProspect'`
   - `remind_at` = current date + 7 days
   - `notify_all=1`
5. Notification appears in bell dropdown
6. Wait 7 days (or change client status to "Active")
7. Verify no new reminders created for Active clients

---

## Common Pitfall Solutions

### SQL Ambiguous Columns
**Problem:** Joining `tasks` + `projects` creates ambiguous `status` column

**Solution:**
```sql
-- Bad:
SELECT status FROM tasks t JOIN projects p ON t.project_id = p.id

-- Good:
SELECT t.status FROM tasks t JOIN projects p ON t.project_id = p.id
```

### Password Verification Failures
**Problem:** Multiple password formats (hashed, plain, MD5, SHA-256)

**Solution:** Unified verification logic in `User::authenticate()`:
```php
$stored = trim($user['password']);
if (password_get_info($stored)['algo'] !== 0) {
    // Hashed (bcrypt/argon2)
    $isValid = password_verify($password, $stored);
} elseif (strlen($stored) === 32 && ctype_xdigit($stored)) {
    // MD5 hex
    $isValid = hash_equals($stored, md5($password));
} elseif (strlen($stored) === 64 && ctype_xdigit($stored)) {
    // SHA-256 hex
    $isValid = hash_equals($stored, hash('sha256', $password));
} else {
    // Plain text
    $isValid = hash_equals($stored, $password);
}
```

### Dynamic Property Deprecation (PHP 8.2+)
**Problem:** `Deprecated: Creation of dynamic property`

**Solution:** Declare all properties:
```php
class Clients extends Controller {
    private $clientModel;
    private $roleModel;
    private $clientServiceModel;
    private $clientStatusHistoryModel; // Must be declared
}
```

### Dark Mode CSS Not Applying
**Problem:** Browser caching old CSS

**Solutions:**
1. Cache buster in URL: `<link href="/css/app.css?v=<?= time() ?>">`
2. Hard refresh: Ctrl+Shift+R
3. Clear browser cache via DevTools

### Timezone Display Issues
**Problem:** Database stores in Canada time, display needs LA/UTC

**Solution:**
- Set `db_timezone` and `display_timezone` in settings
- Convert on display: `DateTime::setTimezone()`
- Login audit explicitly converts timestamps

---

## Key Business Rules

### Client Status Workflow
**Statuses:** Prospect → Active → Inactive

**Automation:**
- Prospect clients auto-generate follow-up reminders every X days
- Status changes logged to `ClientStatusHistory`
- Change visible on client view page

### Department Budgets
- Each department has `budget` field
- Projects assigned to departments consume budget
- Dashboard shows usage percentage with color coding:
  - <70%: Green
  - 70-90%: Orange
  - >90%: Red

### Task Completion
- Tasks with `status='Completed'` counted separately
- Progress percent (0-100) for granular tracking
- Weighted completion: `(∑ progress * estimated_hours) / ∑ estimated_hours`
- Overdue if `due_date < now AND status != 'Completed'`

### Email-to-Ticket Matching
1. Parse email subject for pattern: `[TKT-YYYY-NNNNNN]`
2. If match: update existing ticket
3. If no match: create new ticket
4. Link to client via sender domain match in `ClientDomains`
5. Send auto-acknowledgment if enabled

---

## API Endpoints (AJAX/JSON)

### Search API
**Endpoint:** `GET /search`  
**Params:** `q` (query), `type` (all/projects/tasks/users/clients/notes), `limit` (max 50)  
**Response:**
```json
{
  "success": true,
  "results": [
    {
      "type": "client",
      "id": 1010,
      "title": "Meraki Group",
      "description": "Technology company...",
      "url": "/clients/viewClient/1010",
      "icon": "bi bi-building",
      "status": "Active",
      "meta": {
        "Industry": "Technology",
        "Created": "Jan 15, 2024"
      }
    }
  ],
  "total": 1,
  "permissions": {...}
}
```

**Visibility:** Respects client restrictions, filters results by role

### Notes API (Internal)
Used by notes section for AJAX add/delete

---

## Dependencies

### Server-Side
- PHP 8.x with extensions: pdo, odbc, mbstring, openssl
- Microsoft ODBC Driver 18 for SQL Server
- Apache/Nginx web server
- SQL Server database (remote: 192.168.2.12)

### Frontend (CDN-based)
- Bootstrap 5.3.2 (CSS + JS)
- Bootstrap Icons 1.11.0
- Font Awesome 6.4.0
- Google Fonts: Inter family

### PHP Libraries (included)
- FPDF (PDF generation) - `/helpers/fpdf/`
- PHPMailer - `/libraries/phpmailer/`

---

## Environment & Deployment

### Production Environment
- **OS:** Linux 6.8.12-8-pve (Proxmox VE)
- **Web Server:** Apache or Nginx
- **PHP:** 8.x with FPM
- **Database:** MS SQL Server (separate server)
- **Timezone:** America/Los_Angeles (configurable)

### File Permissions
- Web root: `www-data:www-data` for uploads
- Scripts: `root` for cron jobs
- Logs: Writable by PHP process

### Cron Jobs
```bash
# Email processing every 5 minutes
*/5 * * * * php /var/www/ProjectTracker/app/scripts/process_emails.php >> /var/www/ProjectTracker/logs/email_processing.log 2>&1
```

---

## Maintenance Tasks

### Regular Maintenance
1. **Monitor email processing log** for errors
2. **Check login audit** for security anomalies
3. **Review client status changes** via history
4. **Update prospect follow-up intervals** based on sales cycle
5. **Backup database** regularly
6. **Clear old reminders** (completed/expired)

### Schema Migrations
- Auto-handled via model `ensureTable()` / `ensureColumn()` methods
- Manual migrations in `/database/*.sql` for complex changes

---

## Future Considerations

### Recommended Enhancements
1. **Automated Testing:** PHPUnit for models/controllers
2. **CI/CD Pipeline:** Git push → auto-deploy to production
3. **API Documentation:** OpenAPI/Swagger for search and future APIs
4. **Rate Limiting:** Prevent abuse of search/email endpoints
5. **Audit Trail:** Comprehensive activity logging (who changed what when)
6. **Advanced Reporting:** Charts, exports (PDF/Excel)
7. **Mobile App:** React Native or PWA
8. **Webhooks:** Notify external systems on ticket/task changes
9. **Calendar Integration:** Sync meetings/tasks to Outlook/Google Calendar
10. **Two-Factor Authentication:** TOTP for admin accounts

### Known Limitations
1. No real-time updates (requires WebSocket/SSE)
2. Large datasets (>10k records) may need pagination optimization
3. File storage grows unbounded (implement cleanup/archival)
4. Email processing single-threaded (consider queue system)
5. No disaster recovery plan documented
6. Hardcoded timezone conversion (should use PHP DateTimeZone fully)

---

## Support & Troubleshooting

### Common Issues

#### "SQL Execution Failed: Ambiguous column name"
**Cause:** Column exists in multiple joined tables  
**Fix:** Prefix with table alias: `t.status` instead of `status`

#### "Current password is incorrect"
**Cause:** Password format mismatch  
**Fix:** Verify `User::updatePassword()` uses same logic as `authenticate()`

#### "You do not have access to this client"
**Cause:** Client is restricted and user's role not in allowed list  
**Fix:** Admin edits client → add user's role to allowed roles OR remove restriction

#### Dark mode partially applies
**Cause:** CSS cache or missing `data-bs-theme` attribute  
**Fix:** Hard refresh (Ctrl+Shift+R), ensure `<html data-bs-theme="dark">`

#### Timezone showing wrong time
**Cause:** Mismatch between `db_timezone` and `display_timezone`  
**Fix:** Admin Settings → Display Settings → Set both timezones correctly

---

## Code Style & Conventions

### PHP
- **Classes:** PascalCase (e.g., `ClientService`)
- **Methods:** camelCase (e.g., `getClientById`)
- **Variables:** snake_case (e.g., `$user_id`) or camelCase
- **Constants:** UPPER_CASE (e.g., `APPROOT`, `DB1`)
- **Indentation:** Tabs or 4 spaces
- **Braces:** K&R style (opening brace on same line)

### SQL
- **Tables:** PascalCase (e.g., `Clients`, `ClientServices`)
- **Columns:** snake_case (e.g., `created_at`, `user_id`)
- **MS SQL Server Syntax:**
  - TOP N instead of LIMIT
  - GETDATE() instead of NOW()
  - IDENTITY(1,1) instead of AUTO_INCREMENT
  - NVARCHAR instead of VARCHAR (Unicode support)
  - BIT instead of BOOLEAN

### JavaScript
- **Variables:** camelCase (e.g., `searchInput`)
- **Functions:** camelCase (e.g., `performSearch`)
- **Event Listeners:** Arrow functions preferred
- **Modern ES6+:** const/let, template literals, fetch API

### CSS
- **BEM-inspired:** `.sidebar-header`, `.nav-link`, `.stats-card`
- **Variables:** `--primary-color`, `--sidebar-width`
- **Organization:** Sectioned with comment headers
- **Dark Mode:** `[data-bs-theme="dark"]` selector

---

## Data Model Relationships

```
Users (1) ─── (N) Projects (created_by)
Users (1) ─── (N) Tasks (assigned_to, created_by)
Users (N) ───< project_users >─── (N) Projects
Users (N) ───< task_users >─── (N) Tasks

Clients (1) ─── (N) Projects
Clients (1) ─── (N) ClientServices
Clients (1) ─── (N) ClientStatusHistory
Clients (N) ───< SiteClients >─── (N) Sites
Clients (1) ─── (N) Tickets
Clients (1) ─── (N) ClientContracts

Projects (1) ─── (N) Tasks
Projects (N) ───< project_sites >─── (N) Sites
Projects (1) ─── (N) Reminders (entity_type='project')

Tasks (1) ─── (N) Tasks (parent_task_id, subtasks)
Tasks (N) ───< task_sites >─── (N) Sites

Sites (1) ─── (N) SiteServices
Sites (1) ─── (N) SiteVisits

Departments (1) ─── (N) Projects
Departments (1) ─── (N) Employees

Roles (1) ─── (N) Users
Roles (1) ─── (N) EnhancedPermissions
Roles (N) ─── (N) Clients (via allowed_role_ids CSV)

Reminders (polymorphic) → Clients, Projects, Tasks (entity_type + entity_id)
Notes (polymorphic) → Any entity (reference_type + reference_id)
```

---

## Critical File Paths

### Configuration
- `/config/config.php` - Database and app constants
- `/config/email_config.php` - Email settings

### Core Framework
- `/app/init.php` - Bootstrap file
- `/app/core/App.php` - Router
- `/app/core/Controller.php` - Base controller
- `/app/core/EasySQL.php` - Database layer
- `/app/core/Functions.php` - Global helpers
- `/app/core/PermissionHelper.php` - Permission system

### Entry Point
- `/public/index.php` - All requests route here

### Layouts
- `/app/views/layouts/default.php` - Main layout with sidebar, search, notifications
- `/app/views/layouts/login.php` - Login page layout

### Assets
- `/public/css/app.css` - Main stylesheet (2600+ lines)
- `/public/css/bootstrap.css` - Bootstrap framework
- `/public/css/bootstrap-icons.css` - Icon font
- `/public/uploads/` - File storage (contracts, attachments, profile pics)

### Documentation
- `/docs/Enhanced_Permissions_Setup_Guide.md`
- `/docs/User_Role_Assignment_Guide.md`
- `/docs/MS_SQL_Server_Migration_Notes.md`
- `/SETTINGS_IMPROVEMENTS.md` - Feature notes

---

## System Settings Reference

### Database Settings Table
**Schema:** `setting_key` (PK), `setting_value` (TEXT/JSON)

**Key Settings:**
- `maintenance_mode` - Boolean
- `enable_registration` - Boolean
- `enable_api` - Boolean
- `show_sidebar_time_status` - Boolean
- `default_date_format` - String (PHP date format)
- `max_upload_size` - Integer (MB)
- `display_timezone` - String (PHP timezone identifier)
- `db_timezone` - String (PHP timezone identifier)
- `prospect_followup_enabled` - Boolean
- `prospect_followup_interval_days` - Integer
- `prospect_followup_last_run` - Timestamp
- `currency` - JSON object (code, symbol, position, decimals, separators)
- `smtp_host`, `smtp_port`, `smtp_username`, `smtp_password`, `smtp_encryption` - Email config
- `inbound_protocol`, `inbound_host`, `inbound_port`, etc. - Inbound email
- `level_io_enabled`, `level_io_api_key` - Integration toggle

---

## Session Variables Reference

### Standard User Session
```php
$_SESSION['is_logged_in']       // Boolean
$_SESSION['user_id']            // Integer
$_SESSION['username']           // String (login name)
$_SESSION['user_name']          // String (display name / full_name)
$_SESSION['email']              // String
$_SESSION['role']               // String (e.g., 'admin', 'user', 'technician')
$_SESSION['role_id']            // Integer (FK to Roles.id)
$_SESSION['page']               // String (current section for sidebar highlighting)
```

### Customer Portal Session
```php
$_SESSION['customer_logged_in'] // Boolean
$_SESSION['customer_email']     // String
$_SESSION['customer_name']      // String
$_SESSION['customer_id']        // Integer (from Azure AD)
```

### Flash Messages
```php
$_SESSION['flash_messages'][$name] = ['message' => '...', 'class' => 'alert-success']
```

---

## SQL Server Specifics

### Syntax Differences
- **LIMIT:** Use `SELECT TOP N` instead
- **AUTO_INCREMENT:** Use `IDENTITY(1,1)`
- **BOOLEAN:** Use `BIT` (0/1)
- **DATE/TIME:** Use `DATETIME` or `DATETIME2`
- **NOW():** Use `GETDATE()`
- **CONCAT:** Use `+` operator or `CONCAT()`
- **IF EXISTS:** Use `IF EXISTS (SELECT ...)` or `IF COL_LENGTH(...) IS NULL`

### Migration Pattern
```sql
-- Check if table exists
IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='TableName' AND xtype='U')
BEGIN
    CREATE TABLE TableName (...)
END

-- Check if column exists
IF COL_LENGTH('dbo.TableName', 'column_name') IS NULL
BEGIN
    ALTER TABLE TableName ADD column_name DATATYPE
END
```

---

## Conclusion

This is a mature, feature-rich IT service management platform with:
- **250+ database tables/views** (via auto-migration)
- **170+ PHP controller files**
- **42+ view templates**
- **30+ models**
- **Comprehensive RBAC** with granular permissions
- **Client visibility system** for data segregation
- **Multi-tenant support** via client restrictions
- **Dark mode** with manual toggle
- **Email-to-ticket automation**
- **Time tracking** with sidebar widget
- **Universal search** across all entities
- **Prospect automation** for sales workflows
- **Timezone management** for global teams
- **File storage** for contracts, documents, attachments
- **Audit trails** for login, status changes

The codebase is actively maintained, with recent additions focusing on UX improvements (dark mode, collapsible forms, client services, status tracking) and data security (client visibility, permission refinements).

**For AI Assistants:** When modifying this project, always:
1. Check for existing methods/tables before creating duplicates
2. Use parameterized queries for SQL injection prevention
3. Respect permission checks in controllers
4. Maintain visibility filtering for client-related data
5. Use `flash()` for user feedback
6. Sync changes from worktree to web root: `cp /root/.cursor/worktrees/.../file /var/www/ProjectTracker/file`
7. Handle both array and object return types from models (some return arrays, some objects)
8. Prefix columns in JOINs to avoid ambiguity
9. Support legacy password formats during migration period
10. Add `!important` to dark mode CSS for override certainty
