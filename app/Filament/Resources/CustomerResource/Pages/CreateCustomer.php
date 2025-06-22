<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateCustomer extends CreateRecord
{
    use \App\Traits\RedirectIndex;

    protected static string $resource = CustomerResource::class;

    protected function afterCreate(): void
    {
        DB::transaction(function () {
            $customer = $this->record;
            $data = $this->form->getState();

            if (!empty($customer)) {

                $password = '1234567890';

                if (!empty($data['password'])) {
                    $password = $data['password'];
                }

                $user = User::create([
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'password' => bcrypt($password),
                ]);
            }
        });
    }
}
