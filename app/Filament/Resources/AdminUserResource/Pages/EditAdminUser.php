<?php

namespace App\Filament\Resources\AdminUserResource\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToIndexAfterSave;

use App\Filament\Resources\AdminUserResource;
use App\Models\User;
use Filament\Resources\Pages\EditRecord;

class EditAdminUser extends EditRecord
{
    use RedirectsToIndexAfterSave;

    protected static string $resource = AdminUserResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $uid = $data['user_id'] ?? null;
        if ($uid) {
            $user = User::query()->find($uid);
            if ($user !== null) {
                $data['admin_role_ids'] = $user->adminRoles()->pluck('id')->all();
            }
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $user = User::query()->find($this->record->user_id);
        if ($user === null) {
            return;
        }
        $ids = $this->form->getState()['admin_role_ids'] ?? [];
        if (is_array($ids)) {
            $user->adminRoles()->sync($ids);
        }
    }
}
