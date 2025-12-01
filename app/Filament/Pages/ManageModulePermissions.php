<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Services\ModulePermissionService;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ManageModulePermissions extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.manage-module-permissions';

    protected static ?string $title = 'Module Permissions';

    protected static ?string $navigationLabel = 'Module Permissions';

    public ?array $data = [];

    public static function getNavigationGroup(): ?string
    {
        return 'Settings';
    }

    protected ModulePermissionService $modulePermissionService;

    public function boot(ModulePermissionService $modulePermissionService): void
    {
        $this->modulePermissionService = $modulePermissionService;
    }

    public function mount(): void
    {
        $this->loadPermissions();
    }

    protected function loadPermissions(): void
    {
        $roles = Role::all();
        $formData = [
            'roles' => [],
        ];

        foreach ($roles as $role) {
            // Ensure we get fresh permissions from database
            $role->load('permissions');
            $rolePermissions = $role->permissions->pluck('name')->toArray();
            $formData['roles'][$role->name] = array_values($rolePermissions);
        }

        // Set the form data directly on the data property since we use statePath('data')
        $this->data = $formData;

        // Also fill the form to ensure it's properly initialized
        $this->form->fill($formData);
    }

    public function form(Schema $schema): Schema
    {
        $roles = Role::all();
        $modulePermissions = $this->modulePermissionService->getAllModulePermissions();

        // Get all permissions from database (including non-module permissions)
        $allPermissions = Permission::all()->pluck('name', 'name')->toArray();

        // Build permission options with descriptions
        $permissionOptions = [];
        $descriptions = [];

        $activeModule = $this->data['filter_module'] ?? null;

        if ($activeModule && $activeModule !== '') {
            // Filter by module - only show module permissions for the selected module
            foreach ($modulePermissions as $permName => $permData) {
                $module = $permData['module'] ?? 'Other';
                if ($module === $activeModule) {
                    $permissionOptions[$permName] = $permData['description'] ?? $permName;
                    $descriptions[$permName] = 'Module: '.$module;
                }
            }
        } else {
            // Show all permissions - include both module and non-module permissions
            foreach ($allPermissions as $permName) {
                if (isset($modulePermissions[$permName])) {
                    // It's a module permission
                    $permData = $modulePermissions[$permName];
                    $permissionOptions[$permName] = $permData['description'] ?? $permName;
                    $descriptions[$permName] = 'Module: '.($permData['module'] ?? 'Other');
                } else {
                    // It's a non-module permission (system permission)
                    $permissionOptions[$permName] = $permName;
                    $descriptions[$permName] = 'System Permission';
                }
            }
        }

        // Add tab for each role
        $tabs = [];
        foreach ($roles as $role) {
            $tabs[] = Tab::make($role->name)
                ->label(ucfirst(str_replace('_', ' ', $role->name)))
                ->schema([
                    CheckboxList::make("roles.{$role->name}")
                        ->label('Permissions')
                        ->options($permissionOptions)
                        ->columns(2)
                        ->searchable()
                        ->bulkToggleable()
                        ->selectAllAction(
                            fn (Action $action) => $action
                                ->label('Select All')
                                ->visible()
                        )
                        ->deselectAllAction(
                            fn (Action $action) => $action
                                ->label('Deselect All')
                                ->visible()
                        )
                        ->descriptions($descriptions)
                        ->dehydrated(),
                ]);
        }

        return $schema
            ->components([
                Select::make('filter_module')
                    ->label('Filter by Module')
                    ->options($this->getModuleOptions())
                    ->placeholder('All Modules')
                    ->live(),
                Tabs::make('role_permissions')
                    ->tabs($tabs)
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        try {
            DB::beginTransaction();

            $data = $this->form->getState();
            $roles = Role::all()->keyBy('name');

            if (isset($data['roles'])) {
                foreach ($data['roles'] as $roleName => $permissionNames) {
                    $role = $roles->get($roleName);
                    if (! $role) {
                        continue;
                    }

                    if (! is_array($permissionNames)) {
                        $permissionNames = [];
                    }

                    // If filtering is active, we need to preserve permissions outside the filter
                    $allPermissionsToSync = $permissionNames;

                    $activeModule = $data['filter_module'] ?? null;

                    if ($activeModule && $activeModule !== '') {
                        // Filter is active - preserve existing permissions that are not in the filtered view
                        $modulePermissions = $this->modulePermissionService->getAllModulePermissions();
                        $filteredPermissionNames = [];

                        foreach ($modulePermissions as $permName => $permData) {
                            $module = $permData['module'] ?? 'Other';
                            if ($module === $activeModule) {
                                $filteredPermissionNames[] = $permName;
                            }
                        }

                        // Get current role permissions from database
                        $role->load('permissions');
                        $existingPermissionNames = $role->permissions->pluck('name')->toArray();

                        // Keep permissions that are NOT in the filtered list
                        $preservedPermissions = array_diff($existingPermissionNames, $filteredPermissionNames);

                        // Merge preserved permissions with new selections
                        $allPermissionsToSync = array_merge($preservedPermissions, $permissionNames);
                        $allPermissionsToSync = array_unique($allPermissionsToSync);
                    }

                    $permissions = Permission::whereIn('name', $allPermissionsToSync)->get();
                    $role->syncPermissions($permissions);
                }
            }

            DB::commit();

            Notification::make()
                ->title('Permissions saved successfully')
                ->success()
                ->send();

            // Don't reload - keep the current form state since it matches what was saved
            // The data is already in the database and the form already reflects the saved state
        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->title('Error saving permissions')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Permissions')
                ->action('save')
                ->color('primary'),
            Action::make('register')
                ->label('Register Module Permissions')
                ->action('registerPermissions')
                ->color('gray'),
        ];
    }

    public function registerPermissions(): void
    {
        try {
            $this->modulePermissionService->registerModuleRoles();
            $this->modulePermissionService->registerModulePermissions();

            $this->loadPermissions();

            Notification::make()
                ->title('Module permissions registered successfully')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error registering permissions')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function getModuleOptions(): array
    {
        $modulePermissions = $this->modulePermissionService->getAllModulePermissions();

        return collect($modulePermissions)
            ->map(fn ($permData) => $permData['module'] ?? 'Other')
            ->unique()
            ->sort()
            ->mapWithKeys(fn ($module) => [$module => $module])
            ->toArray();
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('ViewAny:Role') ?? false;
    }
}
