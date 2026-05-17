<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Spatie\ScheduleMonitor\Models\MonitoredScheduledTask;
use Illuminate\Auth\Access\HandlesAuthorization;

class MonitoredScheduledTaskPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MonitoredScheduledTask');
    }

    public function view(AuthUser $authUser, MonitoredScheduledTask $monitoredScheduledTask): bool
    {
        return $authUser->can('View:MonitoredScheduledTask');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MonitoredScheduledTask');
    }

    public function update(AuthUser $authUser, MonitoredScheduledTask $monitoredScheduledTask): bool
    {
        return $authUser->can('Update:MonitoredScheduledTask');
    }

    public function delete(AuthUser $authUser, MonitoredScheduledTask $monitoredScheduledTask): bool
    {
        return $authUser->can('Delete:MonitoredScheduledTask');
    }

    public function restore(AuthUser $authUser, MonitoredScheduledTask $monitoredScheduledTask): bool
    {
        return $authUser->can('Restore:MonitoredScheduledTask');
    }

    public function forceDelete(AuthUser $authUser, MonitoredScheduledTask $monitoredScheduledTask): bool
    {
        return $authUser->can('ForceDelete:MonitoredScheduledTask');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MonitoredScheduledTask');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MonitoredScheduledTask');
    }

    public function replicate(AuthUser $authUser, MonitoredScheduledTask $monitoredScheduledTask): bool
    {
        return $authUser->can('Replicate:MonitoredScheduledTask');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MonitoredScheduledTask');
    }

}