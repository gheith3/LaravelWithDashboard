<?php

namespace App\Filament\Resources\Users\Pages;

use App\Enums\AppUserRole;
use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['email_verified_at'])) {
            unset($data['email_verified_at']);
        }

        // Set account_id from authenticated user
        if (! auth()->user()->hasRole(AppUserRole::SuperAdmin->value)) {
            $data['account_id'] = auth()->user()?->account_id;
        }

        return $data;
    }
}
