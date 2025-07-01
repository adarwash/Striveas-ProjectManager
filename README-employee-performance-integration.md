# Employee Performance Integration with Time Tracking

## Overview

This integration combines the existing Employee Management system with Time Tracking data to provide comprehensive employee performance analytics. Admins and managers can now view detailed performance metrics that include both traditional performance ratings and time-based analytics.

## Features

### 🎯 **Performance Dashboard** (`/employees/performance`)
- **Comprehensive Overview**: View all employees with integrated time tracking performance metrics
- **Real-time Status**: See current clock-in status of all employees  
- **Performance Analytics**: Combined scoring based on:
  - Traditional performance rating (1-5 scale)
  - Time tracking productivity rating (Excellent, Very Good, Good, Fair, Needs Improvement)
  - Punctuality score (percentage of on-time arrivals)
  - Attendance rate (days worked vs expected working days)
  - Consistency score (how consistent working hours are)

### 📊 **Detailed Performance Analysis** (`/employees/performance?user_id=X`)
- **Individual Employee Deep Dive**: Comprehensive analysis for specific employees
- **Time Tracking Metrics**: 
  - Total hours worked (configurable period: 7, 30, 60, 90 days)
  - Average hours per day
  - Break efficiency analysis
  - Daily time tracking history with performance indicators
- **Visual Analytics**: Progress bars, badges, and color-coded performance indicators
- **Current Status**: Real-time view of employee's current activity (clocked in, on break, clocked out)

### 📈 **Performance Metrics Calculated**

#### **Productivity Rating Algorithm**
The system calculates an overall productivity rating based on:
- **Hours per Day** (25 points): 7.5+ hours = 25pts, 6+ hours = 20pts, 4+ hours = 15pts
- **Punctuality** (25 points): 90%+ = 25pts, 80%+ = 20pts, 70%+ = 15pts  
- **Consistency** (25 points): 80%+ = 25pts, 60%+ = 20pts
- **Attendance** (25 points): 95%+ = 25pts, 85%+ = 20pts, 75%+ = 15pts

**Total Score**:
- 90+ = Excellent
- 75+ = Very Good  
- 60+ = Good
- 45+ = Fair
- <45 = Needs Improvement

#### **Key Performance Indicators**
1. **Punctuality Score**: % of days employee arrives by 9:00 AM
2. **Attendance Rate**: Days worked vs expected working days (Mon-Fri)
3. **Consistency Score**: How consistent daily working hours are (lower variance = higher score)
4. **Break Efficiency**: 
   - ≤15% of total time = Excellent
   - ≤25% of total time = Good  
   - >25% = Needs Improvement

## Technical Implementation

### Database Integration
- **No New Tables Required**: Uses existing `TimeEntries`, `TimeBreaks`, and `EmployeeManagement` tables
- **SQL Server Compatible**: All queries use SQL Server syntax (`GETDATE()`, `DATEPART()`, etc.)
- **Efficient Queries**: Optimized for performance with proper indexing

### Model Enhancements (`app/models/Employee.php`)
```php
// New methods added:
getEmployeePerformanceWithTimeTracking($userId, $days)
getAllEmployeesWithTimeTrackingPerformance($days, $sortBy)
getTimeTrackingPerformanceSummary($days)
calculateTimePerformanceMetrics($userId, $startDate, $endDate)
getDailyTimeTracking($userId, $startDate, $endDate)
```

### Controller Enhancements (`app/controllers/Employees.php`)
```php
// New methods added:
performance()                    // Main dashboard and detail views
getTimeAnalytics($userId)       // AJAX endpoint for dynamic data
exportPerformanceReport()       // CSV export functionality
updatePerformanceRating($userId) // Quick rating updates
```

### Views Created
- `app/views/employees/performance_dashboard.php` - Overview of all employees
- `app/views/employees/performance_detail.php` - Individual employee analysis

## Navigation & Access

### **Menu Links Added**
- **Admin Menu**: "Employee Performance" with graph icon
- **Employee Management Page**: "Performance Dashboard" button
- **Individual Employee Pages**: "Performance Analysis" button

### **Permission Requirements**
- Requires `employees.read` permission
- Admin role for CSV export and rating updates
- Manager/Admin roles for accessing performance data

## Usage Guide

### For Admins
1. **Access Dashboard**: Navigate to "Employee Performance" in admin menu or click "Performance Dashboard" from Employee Management
2. **Filter & Sort**: Use period filters (7, 30, 60, 90 days) and sort by various metrics
3. **Export Reports**: Click "Export Report" for CSV download with all metrics
4. **Drill Down**: Click "View Details" on any employee for comprehensive analysis

### For Managers  
1. **Team Overview**: View performance dashboard to see team productivity
2. **Individual Analysis**: Access detailed performance views for team members
3. **Performance Tracking**: Monitor punctuality, attendance, and work patterns

### **Key Performance Actions**
- **Identify Top Performers**: Sort by "Productivity Rating" to see highest performers
- **Address Attendance Issues**: Sort by "Attendance Rate" to identify attendance problems
- **Monitor Punctuality**: Sort by "Punctuality" to track late arrivals
- **Review Consistency**: Check consistency scores to identify irregular work patterns

## Dashboard Features

### **Summary Cards**
- **Active Employees**: Total employees with time tracking data
- **Total Hours**: Company-wide hours for selected period
- **Punctuality Rate**: Overall company punctuality percentage
- **Avg Hours/Entry**: Average hours per time entry

### **Performance Table Columns**
- Employee info with avatar and contact details
- Role and department information
- Performance rating with visual progress bar
- Productivity rating with color-coded badges
- Time metrics (total hours, daily average)
- Punctuality and attendance progress indicators
- Real-time status (clocked in, on break, clocked out)
- Action buttons (view details, edit, employee profile)

### **Detail View Features**
- **Performance Cards**: Large metric displays for key indicators
- **Current Status Widget**: Real-time employee status with elapsed time
- **Detailed Metrics**: Comprehensive breakdown of all performance indicators
- **Daily History Table**: Day-by-day time tracking with performance ratings

## Export Capabilities

### **CSV Report Includes**
- Employee Name, Email, Role
- Performance Rating (1-5 scale)
- Total Hours (for selected period)
- Average Hours per Day
- Punctuality Score (%)
- Attendance Rate (%)
- Consistency Score (%)
- Break Efficiency Rating
- Productivity Rating
- Tasks Completed/Pending
- Last Review Date

## Benefits for Management

1. **Data-Driven Decisions**: Objective performance metrics based on actual time data
2. **Early Problem Detection**: Identify attendance and punctuality issues quickly  
3. **Performance Trends**: Track employee performance over time
4. **Resource Planning**: Understand actual working patterns for better scheduling
5. **Fair Evaluations**: Combine subjective ratings with objective time data
6. **Productivity Insights**: Identify most productive employees and best practices

## Technical Notes

- **Performance Optimized**: Queries are optimized for large datasets
- **Real-time Data**: Current status pulls live data from time tracking system
- **Flexible Periods**: Support for 7, 30, 60, and 90-day analysis windows
- **Mobile Responsive**: Dashboard works on all device sizes
- **Error Handling**: Graceful degradation when time tracking data is unavailable

## Future Enhancements

- **Charts & Graphs**: Visual trend analysis with Chart.js integration
- **Performance Alerts**: Email notifications for attendance/performance issues
- **Goal Setting**: Set and track individual performance goals
- **Comparative Analytics**: Team and department performance comparisons
- **Integration with Tasks**: Link task completion rates with time tracking data

---

**Last Updated**: December 2024  
**Version**: 1.0  
**Compatibility**: PHP 8.1+, SQL Server 2019+ 