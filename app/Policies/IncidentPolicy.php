<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Incident;
use App\Models\User;

/**
 * Every access decision about an incident lives here.
 *
 * Before this existed the rules were scattered: some controllers scoped a query by
 * user_id, some checked nothing at all. That is what produced the two escalations —
 * any signed-in citizen could post a "Staff Response" on any incident and read any
 * other citizen's thread, and any officer could act on an incident assigned to someone
 * else simply by changing the id in the URL.
 */
class IncidentPolicy
{
    /**
     * Staff who oversee the whole queue.
     */
    private function isAdmin(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    /**
     * Responders who act on incidents assigned to them.
     */
    private function isResponder(User $user): bool
    {
        return $user->isResponder();
    }

    private function isAssignedTo(User $user, Incident $incident): bool
    {
        return $incident->assigned_to !== null
            && $incident->assigned_to === $user->id;
    }

    private function isReporter(User $user, Incident $incident): bool
    {
        return $incident->user_id !== null
            && $incident->user_id === $user->id;
    }

    /**
     * Read an incident and its follow-up thread.
     *
     * The reporter, the assigned responder, and admins — nobody else. Follow-up threads
     * carry the reporter's own account of an environmental crime, so an unrelated
     * citizen enumerating ids must not reach them.
     */
    public function view(User $user, Incident $incident): bool
    {
        return $this->isAdmin($user)
            || $this->isReporter($user, $incident)
            || $this->isAssignedTo($user, $incident);
    }

    /**
     * Add a follow-up to the thread.
     */
    public function addFollowup(User $user, Incident $incident): bool
    {
        return $this->view($user, $incident);
    }

    /**
     * Post a reply marked "Staff Response".
     *
     * Restricted to admins and the assigned responder: the label carries authority, so
     * a citizen must not be able to author one — least of all on someone else's report.
     */
    public function respond(User $user, Incident $incident): bool
    {
        return $this->isAdmin($user) || $this->isAssignedTo($user, $incident);
    }

    /**
     * Assign, resolve, or reject. Queue-level decisions belong to admins.
     */
    public function manage(User $user, Incident $incident): bool
    {
        return $this->isAdmin($user);
    }

    /**
     * Take an incident into action and attach documentation.
     */
    public function takeAction(User $user, Incident $incident): bool
    {
        return $this->isResponder($user) && $this->isAssignedTo($user, $incident);
    }

    /**
     * View the attached evidence files.
     */
    public function viewMedia(User $user, Incident $incident): bool
    {
        return $this->view($user, $incident);
    }
}
