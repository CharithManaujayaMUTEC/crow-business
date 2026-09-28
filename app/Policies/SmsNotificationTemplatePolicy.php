<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\SmsNotificationTemplate;
use Illuminate\Auth\Access\HandlesAuthorization;

class SmsNotificationTemplatePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SmsNotificationTemplate');
    }

    public function view(AuthUser $authUser, SmsNotificationTemplate $smsNotificationTemplate): bool
    {
        return $authUser->can('View:SmsNotificationTemplate');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SmsNotificationTemplate');
    }

    public function update(AuthUser $authUser, SmsNotificationTemplate $smsNotificationTemplate): bool
    {
        return $authUser->can('Update:SmsNotificationTemplate');
    }

    public function delete(AuthUser $authUser, SmsNotificationTemplate $smsNotificationTemplate): bool
    {
        return $authUser->can('Delete:SmsNotificationTemplate');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:SmsNotificationTemplate');
    }

    public function restore(AuthUser $authUser, SmsNotificationTemplate $smsNotificationTemplate): bool
    {
        return $authUser->can('Restore:SmsNotificationTemplate');
    }

    public function forceDelete(AuthUser $authUser, SmsNotificationTemplate $smsNotificationTemplate): bool
    {
        return $authUser->can('ForceDelete:SmsNotificationTemplate');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SmsNotificationTemplate');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SmsNotificationTemplate');
    }

    public function replicate(AuthUser $authUser, SmsNotificationTemplate $smsNotificationTemplate): bool
    {
        return $authUser->can('Replicate:SmsNotificationTemplate');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SmsNotificationTemplate');
    }

}