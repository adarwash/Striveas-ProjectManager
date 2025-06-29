# Time Tracking System

A comprehensive time tracking system for the HiveITPortal application that allows users to clock in/out, take breaks, and track their work hours. The system persists time tracking even when users sign out and provides detailed reporting capabilities.

## Features

### Core Functionality
- **Clock In/Out**: Users can start and end their work sessions
- **Break Management**: Record different types of breaks (regular, lunch, meeting, personal, restroom)
- **Persistent Tracking**: Time tracking continues even when users sign out and log back in
- **Automatic Calculations**: Automatically calculates total hours, break time, and net work time
- **Real-time Updates**: Live timers and status updates

### Reporting & Analytics
- **Daily Summary**: View today's work summary and hours
- **Time History**: Browse historical time entries with filtering
- **Team View**: Managers can view team time tracking (permission-based)
- **Time Reports**: Generate detailed reports with export to CSV
- **Break Analysis**: Detailed break tracking and reporting

### User Interface
- **Intuitive Dashboard**: Easy-to-use time control panel
- **Time Widget**: Persistent widget showing current status
- **Mobile Responsive**: Works on all device sizes
- **Real-time Feedback**: Instant status updates and notifications

## Installation

### 1. Database Setup
Run the SQL script to create the necessary tables:

```sql
-- Execute this file in SQL Server Management Studio
exec('sql/create_time_tracking_tables.sql')
```

### 2. Include Helper Functions
Make sure to include the permissions helper in your application bootstrap:

```php
require_once 'app/helpers/permissions_helper.php';
```

### 3. Add Navigation
The system automatically adds time tracking navigation to the sidebar. Ensure your routes support:
- `/time` - Main dashboard
- `/time/history` - Time history
- `/time/team` - Team view (managers/admins)
- `/time/reports` - Time reports (managers/admins)

## Database Schema

### Tables Created
1. **TimeEntries** - Main time tracking records
2. **TimeBreaks** - Break records linked to time entries
3. **BreakTypes** - Configurable break types with settings
4. **DailyTimeSummary** - View for daily time summaries
5. **BreakSummary** - View for break analysis

### Key Fields
- **TimeEntries**: clock_in_time, clock_out_time, total_hours, total_break_minutes, status
- **TimeBreaks**: break_start, break_end, break_duration_minutes, break_type
- **BreakTypes**: name, max_duration_minutes, is_paid, color_code

## Usage

### Basic Time Tracking

#### Clock In
```php
// Manual clock in via controller
$timeModel = new TimeTracking();
$result = $timeModel->clockIn($userId, $notes);
```

#### Clock Out
```php
// Clock out with automatic hour calculation
$result = $timeModel->clockOut($userId, $notes);
```

#### Break Management
```php
// Start a break
$result = $timeModel->startBreak($userId, 'lunch', $notes);

// End break
$result = $timeModel->endBreak($userId, $notes);
```

### Status Checking
```php
// Get user's current status
$status = $timeModel->getUserStatus($userId);
// Returns: 'clocked_out', 'clocked_in', or 'on_break'
```

### Reporting
```php
// Get user's time entries for date range
$entries = $timeModel->getUserTimeEntries($userId, $startDate, $endDate);

// Get daily summary
$summary = $timeModel->getDailySummary($userId, $date);

// Generate team report
$teamData = $timeModel->getTeamSummary($date);
```

## User Interface Components

### Main Dashboard (`/time`)
- Current status display
- Clock in/out buttons
- Break control panel
- Today's hours summary
- Recent time entries table

### Time History (`/time/history`)
- Filterable time entry list
- Date range selection
- Export functionality
- Break details modal

### Team View (`/time/team`)
- Team member status overview
- Daily hours summary
- Manager dashboard

### Time Reports (`/time/reports`)
- Comprehensive reporting
- User filtering
- Date range selection
- CSV export

## Time Widget

Include the time widget in any view to show persistent time tracking:

```php
<?php include 'app/views/partials/time_widget.php'; ?>
```

The widget provides:
- Current time tracking status
- Quick clock in/out actions
- Session timer
- Today's hours display

## API Endpoints

### AJAX Endpoints
- `POST /time/clockIn` - Clock in user
- `POST /time/clockOut` - Clock out user
- `POST /time/startBreak` - Start break
- `POST /time/endBreak` - End break
- `GET /time/getStatus` - Get current status (JSON)

### Response Format
```json
{
    "success": true,
    "message": "Clocked in successfully",
    "clock_in_time": "2024-01-15 09:00:00",
    "time_entry_id": 123
}
```

## Permission System Integration

The time tracking system integrates with the permission system:

### Required Permissions
- **Basic Usage**: All logged-in users can track their own time
- **Team View**: Requires `reports_read` permission
- **Time Reports**: Requires `reports_read` permission
- **Time Adjustments**: Requires `admin_manage` permission

### Permission Checks in Views
```php
<?php if (hasPermission('reports_read')): ?>
    <a href="/time/team">Team Time</a>
<?php endif; ?>
```

## Break Types Configuration

Default break types are automatically created:

| Type | Max Duration | Paid | Color |
|------|-------------|------|-------|
| Regular Break | 15 min | Yes | Green |
| Lunch Break | 60 min | No | Yellow |
| Meeting | Unlimited | Yes | Blue |
| Personal | 30 min | No | Red |
| Restroom | 10 min | Yes | Gray |

### Customizing Break Types
```sql
INSERT INTO BreakTypes (name, max_duration_minutes, is_paid, color_code) 
VALUES ('Training', 120, 1, '#17a2b8');
```

## Security Features

### Data Protection
- User can only access their own time data (unless manager/admin)
- Session-based authentication required
- SQL injection protection via parameterized queries

### Business Rules
- Cannot clock in if already clocked in
- Cannot start break if not clocked in
- Cannot start break if already on break
- Automatic break ending on clock out

## Troubleshooting

### Common Issues

#### Time Tracking Not Working
1. Check database connection
2. Verify TimeTracking tables exist
3. Check user permissions
4. Verify session data

#### Permission Errors
1. Ensure permissions helper is loaded
2. Check user role assignments
3. Verify permission tables exist

#### Widget Not Showing
1. Check if user is logged in
2. Verify TimeTracking model is accessible
3. Check JavaScript console for errors

### Debug Information
Enable error logging to troubleshoot:

```php
error_log('TimeTracking Debug: ' . print_r($userStatus, true));
```

## Performance Considerations

### Database Optimization
- Indexes on user_id and date fields
- Views for common queries
- Automatic cleanup of old data (recommended)

### Frontend Optimization
- AJAX calls for real-time updates
- Minimal page reloads
- Efficient JavaScript timers

## Future Enhancements

### Planned Features
- Time tracking goals and targets
- Integration with project/task assignments
- Mobile app support
- Geolocation tracking
- Overtime calculations
- Holiday and PTO integration

### Customization Options
- Configurable break rules
- Custom time entry fields
- Integration with payroll systems
- Advanced reporting templates

## File Structure

```
app/
├── controllers/
│   └── TimeTrackingController.php    # Main controller
├── models/
│   └── TimeTracking.php              # Time tracking model
├── views/
│   ├── time/
│   │   ├── dashboard.php             # Main dashboard
│   │   ├── history.php               # Time history
│   │   ├── team.php                  # Team view
│   │   └── reports.php               # Time reports
│   └── partials/
│       └── time_widget.php           # Reusable widget
├── helpers/
│   └── permissions_helper.php        # Permission functions
└── views/inc/
    └── sidebar.php                   # Updated navigation

sql/
└── create_time_tracking_tables.sql   # Database schema
```

## Support

For issues or questions:
1. Check the troubleshooting section
2. Review error logs
3. Verify database schema
4. Check permission settings

This time tracking system provides a robust foundation for employee time management with room for customization and expansion based on specific business needs. 