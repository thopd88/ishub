<?php

namespace App\Filament\Resources\ModuleJobResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;

class RunsRelationManager extends RelationManager
{
    protected static string $relationship = 'runs';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
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
                    ->limit(80)
                    ->wrap()
                    ->label('Output'),
                TextColumn::make('exception_message')
                    ->limit(80)
                    ->wrap()
                    ->label('Error'),
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
