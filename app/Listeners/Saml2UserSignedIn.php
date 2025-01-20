<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Slides\Saml2\Events\SignedIn;

class Saml2UserSignedIn
{
    public function __construct() {}

    public function handle(SignedIn $event): void
    {
        // your own code preventing reuse of a $messageId to stop replay attacks
        $samlUser = $event->getSaml2User();

        $samlUser = [
            'id' => $samlUser->getUserId(),
            'attributes' => $samlUser->getAttributes(),
            'sessionIndex' => $samlUser->getSessionIndex(),
            'nameId' => $samlUser->getNameId(),
        ];
        Log::info($samlUser);
        // check if email already exists and fetch user
        $user = User::firstOrCreate(
            [
                'username' => $samlUser['attributes']['urn:mace:dir:attribute-def:uid'][0],
            ],
            [
                'username' => $samlUser['attributes']['urn:mace:dir:attribute-def:uid'][0],
                'email' => $samlUser['attributes']['urn:mace:dir:attribute-def:mail'][0],
                'password' => Hash::make(bcrypt(str()->random(40))),
                'first_name' => str($samlUser['attributes']['urn:mace:dir:attribute-def:displayName'][0])->before(' '),
                'last_name' => str($samlUser['attributes']['urn:mace:dir:attribute-def:displayName'][0])->after(' '),
                'login_type' => 'sso',
            ]
        );
        // Login a user
        $userSettings = $this->checkUserSettings($user);
        $lang = $userSettings->data['language'];
        $user->logged_in_at = Carbon::now();
        $user->save();
        Auth::login($user);
        session()->put('locale', $lang);
        session()->put('url.intended', session('url.intended'));
    }

    private function checkUserSettings(User $user): mixed
    {
        if (is_null($user->settings)) {
            $user->settings()->create([
                'name' => $user->username,
                'data' => config('settings.user'), ]);
        }
        $user->refresh(); // refresh the user otherwise the settings value would be null

        return $user->settings;
    }
}
