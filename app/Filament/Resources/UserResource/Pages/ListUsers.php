<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords; 
use Filament\Resources\Pages\ListRecords\Tab;
use Spatie\Permission\Models\Role;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        // Initialize tabs array with the "All" tab
        $tabs = [
            'All' => Tab::make()
                ->badge(User::count()),
        ];

        // Fetch all roles from the database
        $roles = Role::all();

        // Create a tab for each role
        foreach ($roles as $role) {
            $tabs[$role->name] = Tab::make()
                ->label($role->name) // Optional: Customize label if needed (e.g., ucfirst($role->name))
                ->modifyQueryUsing(fn($query) => $query->whereHas('roles', fn($query) => $query->where('name', $role->name)))
                ->badge(User::query()->whereHas('roles', fn($query) => $query->where('name', $role->name))->count());
        }

        return $tabs;
    }
}
