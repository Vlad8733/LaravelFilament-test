<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListTickets extends ListRecords
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Tickets'),

            'open' => Tab::make('Open')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'open'))
                ->badge(fn () => \App\Models\Ticket::where('status', 'open')->count())
                ->badgeColor('warning'),

            'in_progress' => Tab::make('In Progress')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'in_progress'))
                ->badge(fn () => \App\Models\Ticket::where('status', 'in_progress')->count())
                ->badgeColor('info'),

            'resolved' => Tab::make('Resolved')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'resolved'))
                ->badge(fn () => \App\Models\Ticket::where('status', 'resolved')->count())
                ->badgeColor('success'),

            'my_tickets' => Tab::make('Assigned to Me')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('assigned_to', auth()->id()))
                ->badge(fn () => \App\Models\Ticket::where('assigned_to', auth()->id())->count())
                ->badgeColor('primary'),
        ];
    }
}
