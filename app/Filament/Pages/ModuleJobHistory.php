<?php

namespace App\Filament\Pages;

use App\Models\ModuleJobRun;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ModuleJobHistory extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static \UnitEnum|string|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Job Run History';

    protected static ?string $title = 'Job Run History';

    protected string $view = 'filament.pages.module-job-history';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ModuleJobRun::query()->with('job')
            )
            ->columns([
                TextColumn::make('job.module')
                    ->label('Module')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('job.name')
                    ->label('Job')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('worker')
                    ->sortable(),
                TextColumn::make('started_at')
                    ->dateTime()
                    ->label('Started')
                    ->sortable(),
                TextColumn::make('finished_at')
                    ->dateTime()
                    ->label('Finished')
                    ->sortable(),
                TextColumn::make('output')
                    ->label('Output')
                    ->limit(80)
                    ->wrap()
                    ->toggleable(),
                TextColumn::make('exception_message')
                    ->label('Error')
                    ->limit(80)
                    ->wrap()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'success' => 'Success',
                        'failed' => 'Failed',
                        'running' => 'Running',
                    ]),
                Filter::make('recent')
                    ->label('Last 24 hours')
                    ->query(fn ($query) => $query->where('created_at', '>=', now()->subDay())),
            ])
            ->defaultSort('created_at', 'desc');
    }
}


