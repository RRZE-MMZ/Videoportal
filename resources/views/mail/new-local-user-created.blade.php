<x-mail::message>

# Hi {{$user->getFullNameAttribute()}},

Welcome aboard! Your journey to amazing video content starts now. Please read the following carefully:

You can log in with the following username:

## {{ $user->username}}

📺 Explore Videos: Discover the latest and greatest in our library.

👉 Need help? Please read the [FAQ Page]({{route('frontend.faq')}}).

🔒 **Reset Password:** It is important to reset your password, click below:

@component('mail::button', ['url' => $url])
Reset Password
@endcomponent

Thanks,<br>
{{ config('app.name') }}

---
If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web
browser: {{ $url }}
</x-mail::message>