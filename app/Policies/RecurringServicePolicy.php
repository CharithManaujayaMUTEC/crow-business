<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RecurringService;
use Illuminate\Auth\Access\HandlesAuthorization;

class RecurringServicePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RecurringService');
    }

    public function view(AuthUser $authUser, RecurringService $recurringService): bool
    {
        return $authUser->can('View:RecurringService');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RecurringService');
    }

    public function update(AuthUser $authUser, RecurringService $recurringService): bool
    {
        return $authUser->can('Update:RecurringService');
    }

    public function delete(AuthUser $authUser, RecurringService $recurringService): bool
    {
        return $authUser->can('Delete:RecurringService');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:RecurringService');
    }

    public function restore(AuthUser $authUser, RecurringService $recurringService): bool
    {
        return $authUser->can('Restore:RecurringService');
    }

    public function forceDelete(AuthUser $authUser, RecurringService $recurringService): bool
    {
        return $authUser->can('ForceDelete:RecurringService');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RecurringService');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RecurringService');
    }

    public function replicate(AuthUser $authUser, RecurringService $recurringService): bool
    {
        return $authUser->can('Replicate:RecurringService');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RecurringService');
    }

}