<?php

namespace App\Http\Controllers\Backend;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\OpencastService;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class UsersController extends Controller
{
    public function __construct(private OpencastService $opencastService) {}

    /**
     * Render datatables Livewire component
     */
    public function index(): View
    {
        return view('backend.users.index', [
            'users' => User::paginate(10),
        ]);
    }

    /**
     * Store a user in database
     *
     * @throws Exception
     */
    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();
        $secret = random_int(20, 30);
        $validated['password'] = Hash::make((string) $secret);
        $validated['login_type'] = 'local';
        $user = User::create($validated);

        Password::sendResetLink(['email' => $user->email]);

        return to_route('users.index');
    }

    /**
     * Create form for a user
     */
    public function create(): View
    {
        return view('backend.users.create');
    }

    /**
     * Edit form for a user
     */
    public function edit(User $user): View
    {
        return view('backend.users.edit', compact('user'));
    }

    /**
     * Updates a single user in database
     */
    public function update(User $user, UpdateUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user->update(Arr::except($validated, ['roles']));

        $roles = collect($validated['roles']);

        $user->assignRoles($roles);

        if ($roles->contains(fn ($value) => $value == Role::ASSISTANT->value)) {
            $this->opencastService->createUser($user);
        }

        session()->flash('flashMessage', "{$user->getFullNameAttribute()} ".__FUNCTION__.'d successfully');

        return to_route('users.edit', $user);
    }

    /**
     * Deletes a single user
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->login_type === 'local') {
            $user->delete();
        } else {
            session()->flash('errorMessage', 'You cannot delete this user');
            abort(403);
        }

        return to_route('users.index');
    }
}
