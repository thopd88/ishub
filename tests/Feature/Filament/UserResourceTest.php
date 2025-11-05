<?php

use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('can render the page', function () {
    Livewire::test(ManageUsers::class)
        ->assertSuccessful();
});

it('can list users', function () {
    $users = User::factory()->count(10)->create();

    Livewire::test(ManageUsers::class)
        ->assertCanSeeTableRecords($users);
});

it('can create a user', function () {
    $newData = User::factory()->make();

    Livewire::test(ManageUsers::class)
        ->callAction('create', data: [
            'name' => $newData->name,
            'email' => $newData->email,
            'password' => 'password',
        ])
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas(User::class, [
        'name' => $newData->name,
        'email' => $newData->email,
    ]);
});

it('can edit a user', function () {
    $user = User::factory()->create();
    $newData = User::factory()->make();

    Livewire::test(ManageUsers::class)
        ->callTableAction('edit', $user, data: [
            'name' => $newData->name,
            'email' => $newData->email,
        ])
        ->assertHasNoTableActionErrors();

    expect($user->refresh())
        ->name->toBe($newData->name)
        ->email->toBe($newData->email);
});

it('can delete a user', function () {
    $user = User::factory()->create();

    Livewire::test(ManageUsers::class)
        ->callTableAction('delete', $user);

    $this->assertModelMissing($user);
});

it('can assign roles to user', function () {
    $role = Role::create(['name' => 'Admin']);
    $newData = User::factory()->make();

    Livewire::test(ManageUsers::class)
        ->callAction('create', data: [
            'name' => $newData->name,
            'email' => $newData->email,
            'password' => 'password',
            'roles' => [$role->id],
        ])
        ->assertHasNoActionErrors();

    $user = User::where('email', $newData->email)->first();
    expect($user->hasRole('Admin'))->toBeTrue();
});
