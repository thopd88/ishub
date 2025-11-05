# iSHub - Laravel 12 Starter with Filament & Modular Architecture

A production-ready Laravel starter kit featuring Filament Admin Panel, Inertia.js + React, modular architecture, and comprehensive authentication.

## Tech Stack

- **Backend**: Laravel 12, PHP 8.4
- **Frontend**: React 19, Inertia.js v2, Tailwind CSS v4
- **Admin Panel**: Filament v4 with Shield (Roles & Permissions)
- **Authentication**: Laravel Fortify with 2FA support
- **Testing**: Pest v4 with Browser Testing
- **Module System**: Nwidart Laravel Modules

## Quick Start

### 1. Clone & Install

```bash
# Clone the repository
git clone https://github.com/thopd88/ishub ishub
cd ishub

# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

### 2. Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Copy module status file (controls which modules appear in sidebar)
cp modules_statuses.json.example modules_statuses.json

# Generate application key
php artisan key:generate

# Create SQLite database (or configure your preferred database in .env)
touch database/database.sqlite

# Update .env with your database configuration
# For SQLite (default):
DB_CONNECTION=sqlite

# For MySQL:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ishub
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Database & Seeding

```bash
# Run migrations
php artisan migrate

# IMPORTANT: Install Filament Shield
# This creates roles, permissions, and super_admin role
php artisan shield:install

# Generate permissions for existing resources
php artisan shield:generate --all

# Create your admin user
php artisan make:filament-user

# Follow the prompts:
# Name: Your Name
# Email: admin@example.com
# Password: (your secure password)
# Super Admin: yes

# Optional: Seed sample module data
php artisan db:seed --class=Modules\\Blog\\Database\\Seeders\\BlogDatabaseSeeder
php artisan db:seed --class=Modules\\School\\Database\\Seeders\\SchoolDatabaseSeeder
```

### 4. Start Development Server

```bash
# Start both Laravel and Vite dev servers
composer run dev

# Or run them separately:
# Terminal 1:
php artisan serve

# Terminal 2:
npm run dev
```

### 5. Access the Application

**Admin Panel (Filament)**

- URL: http://localhost:8000/admin
- Login with the user you created in step 3
- Manage users, roles, and permissions

**User Dashboard**

- URL: http://localhost:8000
- Register a new account or login
- Features: Profile management, 2FA, appearance settings

**Module Examples**

- **Blog Module**: http://localhost:8000/blog
  - CRUD operations for blog posts
  - Must be authenticated to access

- **School Module**: http://localhost:8000/school (requires seeding)
  - Role-based dashboards (teacher, student, parent)
  - Assignment management and grading system
  - Demonstrates authorization and policies
  - Login with: teacher@example.com, student1@example.com, parent1@example.com (password: `password`)

## Project Structure

```
ishub/
├── app/                      # Core application code
│   ├── Filament/            # Filament admin resources
│   ├── Http/                # Controllers, Middleware, Requests
│   ├── Models/              # Eloquent models
│   └── Policies/            # Authorization policies
├── Modules/                 # Modular features (dynamic sidebar integration)
│   ├── Blog/               # Blog module example
│   │   ├── app/            # Controllers, models, policies
│   │   ├── resources/js/   # React pages
│   │   ├── routes/         # Module routes
│   │   └── tests/          # Module tests
│   └── School/             # School module (roles: teacher, student, parent)
│       ├── app/            # Controllers, models, policies
│       ├── resources/js/   # React pages (role-based)
│       ├── routes/         # Module routes
│       └── tests/          # Module tests
├── resources/
│   ├── js/                 # React components & pages
│   │   ├── pages/          # Inertia pages
│   │   ├── components/     # Reusable components
│   │   └── layouts/        # Page layouts
│   └── views/              # Blade views (minimal)
├── routes/
│   ├── web.php            # Main web routes
│   ├── api.php            # API routes
│   └── settings.php       # Settings routes
└── tests/                 # Application tests
```

## Key Features

### Authentication & Authorization

- ✅ User registration & login
- ✅ Email verification
- ✅ Password reset
- ✅ Two-factor authentication (2FA)
- ✅ Role-based access control (Shield)
- ✅ Policy-based authorization

### Admin Panel (Filament)

- ✅ User management
- ✅ Role & permission management
- ✅ Shield integration for RBAC
- ✅ Dark/light mode
- ✅ Responsive design

### User Dashboard

- ✅ Profile management
- ✅ Password change
- ✅ 2FA setup with QR codes
- ✅ Appearance settings (dark/light mode)
- ✅ Modern React UI

### Modular Architecture

- ✅ Nwidart Laravel Modules
- ✅ Dynamic sidebar navigation system
- ✅ Two example modules: Blog & School (with roles)
- ✅ Auto-loading routes & pages
- ✅ Isolated testing per module
- ✅ Easy to extend - just add JSON config

## Development

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/Auth/LoginTest.php

# Run specific module tests
php artisan test Modules/Blog/tests/Feature/PostTest.php
php artisan test Modules/School/tests/Feature/AssignmentTest.php

# Run with coverage
php artisan test --coverage

# Browser tests (Pest v4)
php artisan test tests/Browser/
```

### Code Quality

```bash
# Format PHP code
vendor/bin/pint

# Format JavaScript/React
npm run format

# Lint JavaScript
npm run lint
```

### Creating a New Module

```bash
# Create module
php artisan module:make YourModule

# Module routes, pages, and classes are auto-loaded!
# No global configuration changes needed.

# Create resources within the module
cd Modules/YourModule
php artisan module:make-controller YourController YourModule
php artisan module:make-model YourModel YourModule
php artisan module:make-migration create_your_table YourModule

# Run module migrations
php artisan migrate
```

**Add to Sidebar Navigation (Optional):**

Edit `Modules/YourModule/module.json` to add navigation metadata:

```json
{
    "name": "Your Module",
    "icon": "Package",      // Any Lucide icon name (1000+ available)
    "route": "/yourmodule",
    "priority": 30          // Lower = appears first
}
```

Enable in `modules_statuses.json`:

```json
{
    "Blog": true,
    "School": true,
    "YourModule": true  // ← Module appears in sidebar automatically!
}
```

**Dynamic System Benefits:**
- ✅ No hardcoding - modules define their own UI
- ✅ Auto-detection - enabled modules appear instantly
- ✅ 1000+ icons - all Lucide icons supported
- ✅ Easy enable/disable - just toggle JSON boolean

See `Modules/School/README.md` for detailed module documentation and dynamic system guide.

### Building for Production

```bash
# Build frontend assets
npm run build

# Optimize Laravel
php artisan optimize
php artisan route:cache
php artisan view:cache
php artisan config:cache
```

## Configuration

### Key Configuration Files

- **`config/filament-shield.php`** - Shield (RBAC) configuration
- **`config/fortify.php`** - Authentication features
- **`config/modules.php`** - Module system settings
- **`config/inertia.php`** - Inertia.js configuration
- **`vite.config.ts`** - Frontend build configuration
- **`modules_statuses.json`** - Enable/disable modules (gitignored, copy from `.example` file)

### Important Environment Variables

```env
APP_NAME="iSHub"
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=sqlite

# Mail (for password reset, verification)
MAIL_MAILER=log  # Change to smtp for production

# Filament
FILAMENT_PANEL_URL=/admin
```

## Common Tasks

### Reset Database

```bash
php artisan migrate:fresh --seed
php artisan shield:install
php artisan make:filament-user
```

### Add User to Super Admin Role

```bash
php artisan tinker
>>> $user = User::find(1);
>>> $user->assignRole('super_admin');
```

### Clear All Caches

```bash
php artisan optimize:clear
php artisan view:clear
php artisan config:clear
php artisan route:clear
npm run build
```

## Troubleshooting

### Filament Shield Issues

If you see "403 Forbidden" in Filament admin:

```bash
# Regenerate permissions
php artisan shield:generate --all

# Assign super_admin role to your user
php artisan tinker
>>> User::find(1)->assignRole('super_admin');
```

### Module Pages Not Found

If Inertia pages from modules aren't loading:

```bash
# Restart Vite dev server
# Press Ctrl+C to stop, then:
npm run dev
```

### Database Issues

```bash
# Reset and rebuild database
php artisan migrate:fresh
php artisan shield:install
php artisan make:filament-user
```

## Security

- All routes require authentication by default (except auth routes)
- Filament admin panel uses Shield for role-based access
- Policies enforce resource-level permissions
- 2FA available for enhanced security
- CSRF protection enabled
- SQL injection protection via Eloquent ORM

## Contributing

1. Create a feature branch
2. Write tests for new features
3. Run `vendor/bin/pint` before committing
4. Ensure all tests pass
5. Submit a pull request

## License

This project is open-sourced software licensed under the MIT license.

## Credits

- [Laravel](https://laravel.com)
- [Filament](https://filamentphp.com)
- [Inertia.js](https://inertiajs.com)
- [React](https://react.dev)
- [Nwidart Laravel Modules](https://github.com/nwidart/laravel-modules)

## Support

For issues and questions:

- Check module documentation:
  - `Modules/Blog/README.md` - Blog module example
  - `Modules/School/README.md` - School module with roles example & dynamic system guide
- Review test files for usage examples
- Open an issue on GitHub
