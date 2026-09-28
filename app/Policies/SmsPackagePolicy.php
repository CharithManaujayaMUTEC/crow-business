<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\SmsPackage;
use Illuminate\Auth\Access\HandlesAuthorization;

class SmsPackagePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SmsPackage');
    }

    public function view(AuthUser $authUser, SmsPackage $smsPackage): bool
    {
        return $authUser->can('View:SmsPackage');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SmsPackage');
    }

    public function update(AuthUser $authUser, SmsPackage $smsPackage): bool
    {
        return $authUser->can('Update:SmsPackage');
    }

    public function delete(AuthUser $authUser, SmsPackage $smsPackage): bool
    {
        return $authUser->can('Delete:SmsPackage');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:SmsPackage');
    }

    public function restore(AuthUser $authUser, SmsPackage $smsPackage): bool
    {
        return $authUser->can('Restore:SmsPackage');
    }

    public function forceDelete(AuthUser $authUser, SmsPackage $smsPackage): bool
    {
        return $authUser->can('ForceDelete:SmsPackage');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SmsPackage');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SmsPackage');
    }

    public function replicate(AuthUser $authUser, SmsPackage $smsPackage): bool
    {
        return $authUser->can('Replicate:SmsPackage');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SmsPackage');
    }

}