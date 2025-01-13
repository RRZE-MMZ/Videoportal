<?php

namespace App\Http\Controllers\Backend;

use App\Enums\ApplicationStatus;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminPortalGrantAccessRequest;
use App\Mail\AdminPortalAccessGranted;
use App\Models\Notification;
use App\Models\Presenter; //
use App\Models\User;
use Carbon\Carbon;

class AdminPortalApplicationController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(AdminPortalGrantAccessRequest $request)
    {
        // validate if the username is indeed applied for admin portal
        $validated = $request->validated();

        $appliedUser = User::search($validated['username'])->first();
        $presenter = Presenter::search($appliedUser->username)->first();

        // create or update a presenter for the applied user
        if (is_null($presenter)) {
            Presenter::create([
                'first_name' => $appliedUser->first_name,
                'last_name' => $appliedUser->last_name,
                'username' => $appliedUser->username,
                'email' => $appliedUser->email,
            ]);
        } else {
            $presenter->first_name = $appliedUser->first_name;
            $presenter->last_name = $appliedUser->last_name;
            $presenter->email = $appliedUser->email;
            $presenter->save();
        }
        // assign the user the moderator role
        $appliedUser->assignRole(Role::MODERATOR);

        // update the application status for the user
        $appliedUserSettings = $appliedUser->settings;
        $data = $appliedUserSettings->data;
        $data['admin_portal_application_status'] = ApplicationStatus::COMPLETED;
        $data['admin_portal_application_processed_at'] = Carbon::now()->format('Y-m-d H-i-s');
        $data['admin_portal_application_processed_by'] = auth()->user()->username;

        $appliedUserSettings->data = $data;
        $appliedUserSettings->save();

        // send email to user that applied for admin portal
        \Mail::to([$appliedUser])->send(new AdminPortalAccessGranted($appliedUser));

        // update admin's notification status
        auth()->user()->notifications->filter(function ($notification) use ($appliedUser) {
            return $notification->data['username_applied_for_admin_portal'] === $appliedUser->username;
        })->first()->markAsRead();

        // update all superadmins notifications to inform them that the application was processed
        Notification::where('notifiable_type', 'user')->get()->filter(function ($notification) use ($appliedUser) {
            return $notification->data['username_applied_for_admin_portal'] === $appliedUser->username;
        })->each(function ($notification) {
            $notification = Notification::find($notification->id);

            $notificationData = $notification->data;
            $notificationData['application_status'] = ApplicationStatus::COMPLETED;
            $notificationData['application_status_processed_by'] = auth()->user()->username;
            $notification->data = $notificationData;
            $notification->save();
        });

        // redirect to notifications page
        return to_route('users.edit', $appliedUser);
    }
}
