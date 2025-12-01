<?php

declare(strict_types=1);

namespace App\Contracts;

interface HasModulePermissions
{
    /**
     * Get the module name.
     */
    public function getModuleName(): string;

    /**
     * Get the module alias.
     */
    public function getModuleAlias(): string;

    /**
     * Get roles defined by this module.
     *
     * @return array<string, array{name: string, description?: string, guard_name?: string}>
     */
    public function getRoles(): array;

    /**
     * Get permissions defined by this module.
     *
     * @return array<string, array{name: string, description?: string, guard_name?: string, roles?: string[]}>
     */
    public function getPermissions(): array;
}

