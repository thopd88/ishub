# Blog Module

A sample Laravel module demonstrating modular architecture with React/Inertia.js frontend.

## Features

- ✅ Full CRUD for blog posts
- ✅ React + Inertia.js pages
- ✅ Form validation with Laravel Form Requests
- ✅ Factory & Seeder for test data
- ✅ Comprehensive test suite (Feature + Unit)
- ✅ Authentication & authorization

## Quick Start

### 1. Run Migrations

```bash
php artisan migrate
```

### 2. Seed Sample Data (Optional)

```bash
php artisan db:seed --class=Modules\\Blog\\Database\\Seeders\\BlogDatabaseSeeder
```

### 3. Start Dev Server

```bash
composer run dev
```

### 4. Visit the Blog

Navigate to: `http://localhost:8000/blog`

## Structure

```
Blog/
├── app/
│   ├── Http/
│   │   ├── Controllers/PostController.php
│   │   └── Requests/
│   │       ├── StorePostRequest.php
│   │       └── UpdatePostRequest.php
│   ├── Models/Post.php
│   └── Providers/BlogServiceProvider.php
├── database/
│   ├── factories/PostFactory.php
│   ├── migrations/2025_11_05_032103_create_posts_table.php
│   └── seeders/BlogDatabaseSeeder.php
├── resources/js/pages/
│   ├── index.tsx    # List posts
│   ├── create.tsx   # Create post
│   ├── edit.tsx     # Edit post
│   └── show.tsx     # View post
├── routes/
│   ├── web.php      # Blog routes
│   └── api.php      # API routes (empty)
└── tests/Feature/PostTest.php
```

## Routes

| Method | URI                 | Name         | Action           |
| ------ | ------------------- | ------------ | ---------------- |
| GET    | `/blog`             | blog.index   | List all posts   |
| GET    | `/blog/create`      | blog.create  | Show create form |
| POST   | `/blog`             | blog.store   | Store new post   |
| GET    | `/blog/{post}`      | blog.show    | Show single post |
| GET    | `/blog/{post}/edit` | blog.edit    | Show edit form   |
| PUT    | `/blog/{post}`      | blog.update  | Update post      |
| DELETE | `/blog/{post}`      | blog.destroy | Delete post      |

## Testing

```bash
# Run all Blog tests
php artisan test Modules/Blog/tests/Feature/PostTest.php

# Run specific test
php artisan test --filter=test_can_create_post
```

## Creating a New Module

This module serves as a template. To create your own:

```bash
# 1. Create module
php artisan module:make YourModule

# 2. The main config files are already set up to auto-load modules:
#    - routes/web.php (auto-loads all module web routes)
#    - bootstrap/app.php (auto-loads all module API routes)
#    - composer.json (auto-loads Modules namespace)
#    - resources/js/app.tsx (auto-loads module pages)

# 3. Run migrations
php artisan migrate

# 4. That's it! Your module is ready.
```

## Key Files Modified for Module Support

These files were updated to support the modular architecture:

- **`composer.json`** - Added `Modules\` namespace autoloading
- **`routes/web.php`** - Dynamic module route loading
- **`bootstrap/app.php`** - Dynamic API route loading
- **`resources/js/app.tsx`** - Custom page resolver for module pages
- **`resources/js/ssr.tsx`** - SSR support for module pages
- **`vite.config.ts`** - Path alias for `@` imports

## Notes

- All module pages are auto-discovered by Vite
- Restart dev server after creating new module pages
- Module routes are automatically registered
- Tests use `RefreshDatabase` and run module migrations in `setUp()`

## Dynamic Module System

This module is part of a **dynamic module system** that automatically displays modules in the sidebar navigation.

### Module Configuration

The `module.json` file controls how this module appears in the sidebar:

```json
{
    "name": "Blog",
    "icon": "Newspaper",
    "route": "/blog",
    "priority": 10
}
```

**Key Fields:**
- `name` - Display name in sidebar
- `icon` - Any Lucide icon name (1000+ available at https://lucide.dev/icons)
- `route` - URL path for the module
- `priority` - Sort order (lower numbers appear first)

### Enable/Disable

Control module visibility in `modules_statuses.json` (project root):

```json
{
    "Blog": true,  // Set to false to hide from sidebar
    "School": true
}
```

**Note:** `modules_statuses.json` is gitignored. Copy from the example file:

```bash
cp modules_statuses.json.example modules_statuses.json
```

### How It Works

1. **Backend:** `ModuleService` reads enabled modules from `modules_statuses.json`
2. **Middleware:** Shares module data with all Inertia pages
3. **Frontend:** Sidebar dynamically renders modules with icons
4. **Icons:** Automatically resolved from `lucide-react` package

**No code changes needed** - just update JSON files and refresh the page!
