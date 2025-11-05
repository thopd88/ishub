# School Module - Quick Setup Guide

## ✅ Module Created Successfully!

The School module has been created with complete role-based functionality for **Teachers**, **Students**, and **Parents**.

## 🎯 What's Included

### Core Components
- ✅ **3 Models**: Assignment, Submission, with relationships
- ✅ **3 Controllers**: DashboardController, AssignmentController, SubmissionController
- ✅ **4 Form Requests**: Validation for creating/updating assignments and grading submissions
- ✅ **2 Policies**: Authorization for assignments and submissions
- ✅ **2 Factories**: For testing with sample data
- ✅ **3 Migrations**: Database structure for assignments, submissions, and student-parent relationships
- ✅ **1 Seeder**: Creates sample users, roles, assignments, and submissions
- ✅ **3 Test Files**: Comprehensive Pest tests for assignments, submissions, and dashboards

### Database Tables
- `assignments` - Teacher-created assignments
- `submissions` - Student submissions with grades and feedback
- `student_parent_relationships` - Links parents to their children

### User Roles
The seeder creates the following test accounts (all use password: `password`):

| Role | Email | Can Do |
|------|-------|--------|
| Teacher | teacher@example.com | Create assignments, grade submissions |
| Student 1 | student1@example.com | Submit assignments, view grades |
| Student 2 | student2@example.com | Submit assignments, view grades |
| Parent 1 | parent1@example.com | Monitor Student 1's progress |
| Parent 2 | parent2@example.com | Monitor Student 2's progress |

## 🚀 Getting Started

### 1. Database is Ready
The migrations have been run and sample data has been seeded.

### 2. Test the Routes
All routes are prefixed with `/school`:

```
GET  /school                          # Role-specific dashboard
GET  /school/assignments               # List assignments
POST /school/assignments               # Create assignment (teacher only)
GET  /school/assignments/{id}          # View assignment
GET  /school/assignments/{id}/edit     # Edit assignment (teacher only)
POST /school/assignments/{id}/submissions # Submit work (student only)
POST /school/submissions/{id}/grade    # Grade submission (teacher only)
```

### 3. Frontend Pages Needed
The controllers return Inertia views. Create these React components:

#### Teacher Views
- `resources/js/pages/School/Teacher/Dashboard.tsx`
- `resources/js/pages/School/Assignment/Index.tsx`
- `resources/js/pages/School/Assignment/Create.tsx`
- `resources/js/pages/School/Assignment/Edit.tsx`
- `resources/js/pages/School/Assignment/Show.tsx`

#### Student Views
- `resources/js/pages/School/Student/Dashboard.tsx`

#### Parent Views
- `resources/js/pages/School/Parent/Dashboard.tsx`

#### Shared Views
- `resources/js/pages/School/Submission/Show.tsx`

### 4. Run Tests
```bash
php artisan test Modules/School/tests
```

## 📊 Example Data Created

### Assignments
1. **Introduction to Laravel** - Due in 7 days, 100 points
2. **Database Design Project** - Due in 14 days, 150 points

### Submissions
- Student 1 submitted Assignment 1 (graded: 95/100) ✅
- Student 2 submitted Assignment 1 (pending grading) ⏳
- Assignment 2 has no submissions yet 📝

## 🔐 Authorization Flow

### Teachers Can:
- Create, edit, and delete their own assignments
- View all submissions for their assignments
- Grade student submissions
- View assignment statistics

### Students Can:
- View all assignments
- Submit work for assignments
- Update their submissions before grading
- View their grades and feedback

### Parents Can:
- View their children's assignments
- Monitor submission status
- View grades and teacher feedback

## 🧪 Testing

The module includes comprehensive tests:

```bash
# Run all School module tests
php artisan test Modules/School/tests

# Run specific test files
php artisan test Modules/School/tests/Feature/AssignmentTest.php
php artisan test Modules/School/tests/Feature/SubmissionTest.php
php artisan test Modules/School/tests/Feature/DashboardTest.php
```

## 📚 API Examples

### Create an Assignment (Teacher)
```php
use Modules\School\Models\Assignment;

Assignment::create([
    'teacher_id' => auth()->id(),
    'title' => 'Final Project',
    'description' => 'Build a full-stack application',
    'due_date' => now()->addDays(30),
    'max_points' => 200,
]);
```

### Submit Work (Student)
```php
use Modules\School\Models\Submission;

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

### Grade a Submission (Teacher)
```php
$submission->update([
    'grade' => 95,
    'feedback' => 'Excellent work! Your implementation is clean and well-documented.',
]);
```

## 🎨 Next Steps

1. **Create Frontend Pages**: Build the Inertia React components listed above
2. **Add Navigation**: Link to `/school` in your main navigation
3. **Customize Styling**: Adapt the views to match your app's design
4. **Extend Features**: Consider adding:
   - File attachments for submissions
   - Assignment categories/subjects
   - Comments on assignments
   - Email notifications
   - Grade statistics
   - Course management

## 📖 Full Documentation

See `README.md` for complete documentation including:
- Detailed model relationships
- All available routes
- Policy rules
- Validation rules
- Architecture notes
- Extension examples

## 🐛 Troubleshooting

### Routes not working?
Make sure the module is enabled:
```bash
# Check modules_statuses.json - School should be true
cat modules_statuses.json
```

### Tests failing?
Run migrations:
```bash
php artisan migrate --path=Modules/School/database/migrations
```

### Autoload issues?
Regenerate autoload files:
```bash
composer dump-autoload
```

## 🎉 You're All Set!

The School module is fully functional on the backend. Just add the frontend views and you'll have a complete role-based school management system!

