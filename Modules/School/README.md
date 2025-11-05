# School Module

A comprehensive Laravel module demonstrating role-based access control with three distinct user roles: **Teacher**, **Student**, and **Parent**.

## Overview

This module provides a complete school management system where:
- **Teachers** can create assignments and grade student submissions
- **Students** can view assignments, submit work, and check their grades
- **Parents** can monitor their children's progress and submissions

## Features

### Teacher Role
- Create, edit, and delete assignments
- View all assignments and their submissions
- Grade student submissions with feedback
- Dashboard showing assignment statistics

### Student Role
- View all available assignments
- Submit work for assignments
- View grades and teacher feedback
- Dashboard showing pending and completed assignments

### Parent Role
- View their children's assignments
- Monitor submission status and grades
- Dashboard showing all children's academic progress

## Installation

### 1. Run Migrations

```bash
php artisan migrate
```

This will create the following tables:
- `assignments` - Stores assignment information
- `submissions` - Stores student submissions and grades
- `student_parent_relationships` - Links students with their parents

### 2. Seed Sample Data

```bash
php artisan module:seed School
```

This creates:
- 1 Teacher (teacher@example.com)
- 2 Students (student1@example.com, student2@example.com)
- 2 Parents (parent1@example.com, parent2@example.com)
- 2 Sample assignments
- 3 Sample submissions (with various states)

All accounts use the password: `password`

## Database Structure

### Assignments Table
- `id` - Primary key
- `teacher_id` - Foreign key to users table
- `title` - Assignment title
- `description` - Full assignment description
- `due_date` - Submission deadline
- `max_points` - Maximum achievable points

### Submissions Table
- `id` - Primary key
- `assignment_id` - Foreign key to assignments table
- `student_id` - Foreign key to users table
- `content` - Student's submission text
- `grade` - Points awarded (nullable until graded)
- `feedback` - Teacher's feedback (nullable)
- `submitted_at` - Submission timestamp

### Student-Parent Relationships Table
- `id` - Primary key
- `student_id` - Foreign key to users table
- `parent_id` - Foreign key to users table
- Unique constraint on (student_id, parent_id)

## Models

### Assignment
```php
namespace Modules\School\Models;

// Relationships
$assignment->teacher() // BelongsTo User
$assignment->submissions() // HasMany Submission
```

### Submission
```php
namespace Modules\School\Models;

// Relationships
$submission->assignment() // BelongsTo Assignment
$submission->student() // BelongsTo User
```

## Routes

All routes are prefixed with `/school` and require authentication:

```php
// Dashboard
GET /school

// Assignments
GET    /school/assignments           # List all assignments
GET    /school/assignments/create    # Create assignment form (teacher only)
POST   /school/assignments           # Store new assignment (teacher only)
GET    /school/assignments/{id}      # View assignment details
GET    /school/assignments/{id}/edit # Edit assignment form (teacher only)
PUT    /school/assignments/{id}      # Update assignment (teacher only)
DELETE /school/assignments/{id}      # Delete assignment (teacher only)

// Submissions
POST /school/assignments/{id}/submissions # Submit assignment work (student only)
GET  /school/submissions/{id}             # View submission details
POST /school/submissions/{id}/grade       # Grade submission (teacher only)
```

## Controllers

### DashboardController
- `index()` - Routes users to role-specific dashboard views
- `teacherDashboard()` - Shows teacher's assignments and statistics
- `studentDashboard()` - Shows student's assignments and completion status
- `parentDashboard()` - Shows parent's children and their progress

### AssignmentController
- Standard CRUD operations with authorization
- Teachers can only edit/delete their own assignments

### SubmissionController
- `store()` - Students submit their work
- `show()` - View submission details (with proper authorization)
- `grade()` - Teachers grade submissions

## Policies

### AssignmentPolicy
- `viewAny()` - All school roles can view assignments
- `create()` - Only teachers can create assignments
- `update()` - Teachers can only update their own assignments
- `delete()` - Teachers can only delete their own assignments

### SubmissionPolicy
- `view()` - Students view their own, teachers view their assignments', parents view their children's
- `grade()` - Only the assignment's teacher can grade submissions

## Form Requests

### StoreAssignmentRequest
```php
// Validation Rules
'title' => 'required|string|max:255'
'description' => 'required|string'
'due_date' => 'required|date|after:now'
'max_points' => 'required|integer|min:1|max:1000'
```

### UpdateAssignmentRequest
Similar to StoreAssignmentRequest but without the "after:now" constraint on due_date.

### StoreSubmissionRequest
```php
// Validation Rules
'content' => 'required|string|min:10'
```

### GradeSubmissionRequest
```php
// Validation Rules
'grade' => 'required|integer|min:0|max:{assignment->max_points}'
'feedback' => 'nullable|string'
```

## User Model Extensions

The User model has been extended with relationships for the school module:

```php
// Get a parent's children
$user->children() // BelongsToMany User

// Get a student's parents
$user->parents() // BelongsToMany User

// Get a student's submissions
$user->submissions() // HasMany Submission

// Get a teacher's assignments
$user->assignmentsAsTeacher() // HasMany Assignment
```

## Testing

Run the module tests:

```bash
php artisan test --filter School
```

Tests cover:
- Role-based authorization
- Assignment CRUD operations
- Submission creation and grading
- Policy enforcement
- Validation rules

## Frontend (To Be Implemented)

The module includes controller actions that render Inertia views. The following pages need to be created:

### Teacher Pages
- `School/Teacher/Dashboard.tsx` - Teacher dashboard
- `School/Assignment/Index.tsx` - List assignments
- `School/Assignment/Create.tsx` - Create assignment form
- `School/Assignment/Edit.tsx` - Edit assignment form
- `School/Assignment/Show.tsx` - View assignment with submissions

### Student Pages
- `School/Student/Dashboard.tsx` - Student dashboard
- `School/Assignment/Index.tsx` - View available assignments
- `School/Assignment/Show.tsx` - Submit work for assignment

### Parent Pages
- `School/Parent/Dashboard.tsx` - Parent dashboard with children's progress

### Shared Pages
- `School/Submission/Show.tsx` - View submission details

## Usage Examples

### Creating an Assignment (Teacher)
```php
$assignment = Assignment::create([
    'teacher_id' => auth()->id(),
    'title' => 'Final Project',
    'description' => 'Build a full-stack application',
    'due_date' => now()->addDays(30),
    'max_points' => 200,
]);
```

### Submitting Work (Student)
```php
Submission::updateOrCreate(
    [
        'assignment_id' => $assignment->id,
        'student_id' => auth()->id(),
    ],
    [
        'content' => 'My submission content...',
        'submitted_at' => now(),
    ]
);
```

### Grading a Submission (Teacher)
```php
$submission->update([
    'grade' => 95,
    'feedback' => 'Excellent work!',
]);
```

### Linking a Parent to a Student
```php
DB::table('student_parent_relationships')->insert([
    'student_id' => $student->id,
    'parent_id' => $parent->id,
]);
```

## Configuration

The module uses the default Laravel configuration. Ensure the following are properly configured:

1. **Spatie Permissions** - Used for role management
2. **Laravel Fortify** - Handles authentication
3. **Inertia.js** - For rendering views

## Architecture Notes

- **Modular Design**: Self-contained with its own models, migrations, controllers, and routes
- **Role-Based Access**: Uses Spatie Laravel Permission for flexible role management
- **Policy-Driven Authorization**: All sensitive operations check user permissions
- **Factory Support**: Includes factories for easy testing and seeding
- **Relationship Integrity**: Foreign key constraints ensure data consistency
- **Reusable Components**: Can be easily adapted for other educational systems

## Extending the Module

### Adding a New Role (e.g., Administrator)

1. Create the role in the seeder
2. Update policies to include new role permissions
3. Add role-specific dashboard method in DashboardController
4. Create corresponding Inertia views

### Adding More Features

Consider adding:
- Assignment categories/subjects
- File attachments for submissions
- Comments on assignments
- Notification system
- Grade statistics and analytics
- Course management
- Attendance tracking

## Dynamic Module System

This module is part of a **dynamic module system** that automatically detects and displays modules in the sidebar.

### How Modules Appear in Navigation

**No hardcoding required!** The system automatically:
1. Reads `modules_statuses.json` to find enabled modules
2. Loads each module's `module.json` for metadata (name, icon, route)
3. Displays modules in the sidebar sorted by priority

### Module Configuration

Edit `module.json` to control how this module appears:

```json
{
    "name": "School",           // ← Display name in sidebar
    "icon": "GraduationCap",    // ← Any Lucide icon (1000+ available)
    "route": "/school",         // ← URL path
    "priority": 20              // ← Sort order (lower = first)
}
```

**All 1000+ Lucide icons supported!** Browse: https://lucide.dev/icons

### Enable/Disable Module

Control visibility in `modules_statuses.json` at the project root:

```json
{
    "School": true   // ← Set to false to hide from sidebar
}
```

**Important:** `modules_statuses.json` is gitignored. Copy from the example file first:

```bash
cp modules_statuses.json.example modules_statuses.json
```

This allows each environment to enable different modules without affecting git.

**No code changes needed** - just update JSON and refresh!

### Creating Your Own Module

1. **Generate:** `php artisan module:make MyModule`
2. **Configure:** Add icon, route, priority to `module.json`
3. **Enable:** Set `"MyModule": true` in `modules_statuses.json`
4. **Done!** Module appears in sidebar automatically

### Technical Details

- **Backend:** `ModuleService` (`app/Services/ModuleService.php`) manages module discovery
- **Frontend:** Sidebar dynamically renders from shared Inertia data
- **Icons:** Automatically resolved from `lucide-react` package
- **Type-safe:** Full TypeScript support via `SharedData` interface

## License

This module is part of the ISHub project and follows the same license.

