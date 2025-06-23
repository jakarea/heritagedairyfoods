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
            $formData = $this->form->getState();

            if (!empty($customer)) {

                $customer->address()->create([
                    'phone' => $customer->phone, 
                    'country' => $formData['country'],
                    'division_id' => $formData['division_id'],
                    'district_id' => $formData['district_id'],
                    'thana_id' => $formData['thana_id'],
                    'address_line_1' => $formData['address_line_1'],
                    'address_line_2' => $formData['address_line_2'],
                    'zip_code' => $formData['zip_code'],
                ]);

                $user = User::create([
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'password' => bcrypt($formData['password']),
                ]);

                // Update the customer with the user_id
                $customer->update(['user_id' => $user->id]);
            }
        });
    }
}
