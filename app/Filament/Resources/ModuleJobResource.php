<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ModuleJobResource\Pages\ManageModuleJobs;
use App\Filament\Resources\ModuleJobResource\RelationManagers\RunsRelationManager;
use App\Models\ModuleJob;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ModuleJobResource extends Resource
{
    protected static ?string $model = ModuleJob::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static \UnitEnum|string|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Module Jobs';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('module')
                    ->label('Module')
                    ->disabled(),
                TextInput::make('name')
                    ->label('Name')
                    ->disabled(),
                TextInput::make('description')
                    ->label('Description')
                    ->maxLength(255),
                TextInput::make('cron_expression')
                    ->label('Cron Expression')
                    ->required(),
                Toggle::make('is_enabled')
                    ->label('Enabled'),
                DateTimePicker::make('not_before')
                    ->label('Not Before'),
                DateTimePicker::make('not_after')
                    ->label('Not After'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('module')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                IconColumn::make('is_enabled')
                    ->boolean()
                    ->label('Enabled'),
                TextColumn::make('cron_expression')
                    ->label('Cron')
                    ->toggleable(),
                TextColumn::make('last_run_at')
                    ->dateTime()
                    ->label('Last Run')
                    ->toggleable(),
                TextColumn::make('next_run_at')
                    ->dateTime()
                    ->label('Next Run')
                    ->toggleable(),
            ])
            ->defaultSort('module')
            ->recordUrl(null)
            ->recordActions([
                EditAction::make(),
                Action::make('runNow')
                    ->label('Run Now')
                    ->icon(Heroicon::OutlinedPlayCircle)
                    ->requiresConfirmation()
                    ->action(function (ModuleJob $record): void {
                        $now = now();

                        $run = $record->runs()->create([
                            'status' => 'running',
                            'worker' => gethostname(),
                            'started_at' => $now,
                        ]);

                        $output = null;

                        try {
                            if ($record->type === 'command' && $record->command) {
                                \Artisan::call($record->command);
                                $output = \Artisan::output();
                            } else {
                                $output = 'Unsupported job type or missing configuration.';
                            }

                            $run->update([
                                'status' => 'success',
                                'finished_at' => now(),
                                'output' => $output,
                            ]);

                            Notification::make()
                                ->title('Job ran successfully')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            $run->update([
                                'status' => 'failed',
                                'finished_at' => now(),
                                'output' => $output,
                                'exception_message' => $e->getMessage(),
                                'exception_trace' => $e->getTraceAsString(),
                            ]);

                            Notification::make()
                                ->title('Job run failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageModuleJobs::route('/'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            RunsRelationManager::class,
        ];
    }
}
