<?php

namespace App\Filament\Resources\ModuleJobResource\Pages;

use App\Filament\Resources\ModuleJobResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;

class ManageModuleJobs extends ManageRecords
{
    protected static string $resource = ModuleJobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sync')
                ->label('Sync Module Jobs')
                ->action('syncModuleJobs'),
        ];
    }

    public function syncModuleJobs(): void
    {
        app(\App\Services\ModuleJobService::class)->syncModuleJobs();

        Notification::make()
            ->title('Module jobs synced successfully')
            ->success()
            ->send();
    }
}
