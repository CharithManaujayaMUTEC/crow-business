<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\SmsSetting;
use Illuminate\Auth\Access\HandlesAuthorization;

class SmsSettingPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SmsSetting');
    }

    public function view(AuthUser $authUser, SmsSetting $smsSetting): bool
    {
        return $authUser->can('View:SmsSetting');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SmsSetting');
    }

    public function update(AuthUser $authUser, SmsSetting $smsSetting): bool
    {
        return $authUser->can('Update:SmsSetting');
    }

    public function delete(AuthUser $authUser, SmsSetting $smsSetting): bool
    {
        return $authUser->can('Delete:SmsSetting');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:SmsSetting');
    }

    public function restore(AuthUser $authUser, SmsSetting $smsSetting): bool
    {
        return $authUser->can('Restore:SmsSetting');
    }

    public function forceDelete(AuthUser $authUser, SmsSetting $smsSetting): bool
    {
        return $authUser->can('ForceDelete:SmsSetting');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SmsSetting');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SmsSetting');
    }

    public function replicate(AuthUser $authUser, SmsSetting $smsSetting): bool
    {
        return $authUser->can('Replicate:SmsSetting');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SmsSetting');
    }

}