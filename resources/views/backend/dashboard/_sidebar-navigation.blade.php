<a
        href="{{route('dashboard')}}"
        class="flex items-left items-center text-white {{ setActiveLink(route('dashboard')) }}   hover:opacity-100 hover:mx-2
    hover:rounded-sm py-4 pl-6 my-4 nav-item space-x-2 "
>
    <div>
        <x-heroicon-c-home class="w-4" />
    </div>
    <div>
        {{ __('common.menu.dashboard') }}
    </div>
</a>
<div class="flex items-left py-4 pl-4 italic font-extrabold text-lg">
    {{ __('common.menu.main objects') }}
</div>
<a
        href="{{route('series.index')}}"
        class="flex items-left items-center text-white hover:opacity-100 hover:mx-2 hover:rounded-sm py-4
        pl-6 nav-item {{ setActiveLink(route('series.index')) }} space-x-2"
>
    <div>
        <x-heroicon-c-square-3-stack-3d class="w-4" />
    </div>
    <div>
        {{ __('common.menu.series') }}
    </div>
</a>
<a
        href="{{route('clips.index')}}"
        class="flex items-left items-center text-white hover:opacity-100 hover:mx-2 hover:rounded-sm py-4
        pl-6 nav-item {{ setActiveLink(route('clips.index')) }} space-x-2"
>
    <div>
        <x-heroicon-c-video-camera class="w-4" />
    </div>
    <div>
        {{ trans_choice('common.menu.clip', 2)}}
    </div>
</a>
<a
        href="{{route('podcasts.index')}}"
        class="flex items-left items-center text-white hover:opacity-100 hover:mx-2 hover:rounded-sm py-4
        pl-6 nav-item {{ setActiveLink(route('podcasts.index')) }} space-x-2"
>
    <div>
        <x-iconoir-podcast class="w-4" />
    </div>
    <div>
        {{ trans_choice('common.menu.podcast', 2)}}
    </div>
</a>
<a
        href="{{route('channels.index')}}"
        class="flex items-left items-center text-white hover:opacity-100 hover:mx-2 hover:rounded-sm py-4
        pl-6 nav-item {{ setActiveLink(route('channels.index')) }} space-x-2"
>
    <div>
        <x-heroicon-c-megaphone class="w-4" />
    </div>
    <div>
        {{ trans_choice('common.menu.channel', 2) }}
    </div>
</a>
<div class="flex items-left py-4 pl-4 italic font-extrabold text-lg">
    {{ __('common.menu.portal resources') }}
</div>
<a
        href="{{route('images.index')}}"
        class="flex items-left items-center text-white hover:opacity-100 hover:mx-2 hover:rounded-sm py-4
        pl-6 nav-item {{ setActiveLink(route('images.index')) }} space-x-2"
>
    <div>
        <x-heroicon-c-photo class="w-4" />
    </div>
    <div>
        {{ trans_choice('common.menu.image', 2) }}
    </div>
</a>

@can('administrate-assistant-pages')
    <a
            href="{{ route('presenters.index') }}"
            class="flex items-left items-center text-white hover:opacity-100 hover:mx-2 hover:rounded-sm py-4
        pl-6 nav-item  {{ setActiveLink(route('presenters.index')) }} space-x-2"
    >
        <div>
            <x-heroicon-m-users class="w-4" />
        </div>
        <div>{{ trans_choice('common.menu.presenter', 2) }}</div>

    </a>
    <a
            href="{{ route('activities.index') }}"
            class="flex items-left items-center text-white hover:opacity-100 hover:mx-2 hover:rounded-sm py-4
        pl-6 nav-item {{ setActiveLink(route('activities.index')) }} space-x-2"
    >
        <div>
            <x-heroicon-c-server-stack class="w-4" />
        </div>
        <div>
            {{ trans_choice('common.menu.activity', 2)  }}
        </div>
    </a>
    <a
            href="{{ route('livestreams.index') }}"
            class="flex items-left items-center text-white hover:opacity-100 hover:mx-2 hover:rounded-sm py-4
        pl-6 nav-item {{ setActiveLink(route('livestreams.index')) }} space-x-2"
    >
        <div>
            <x-heroicon-c-play-circle class="w-4" />
        </div>
        <div>
            Livestreams
        </div>
    </a>
    <a
            href="{{ route('devices.index') }}"
            class="flex items-left items-center text-white hover:opacity-100 hover:mx-2 hover:rounded-sm py-4
        pl-6 nav-item  {{ setActiveLink(route('devices.index')) }} space-x-2"
    >
        <div>
            <x-heroicon-c-device-phone-mobile class="w-4" />
        </div>
        <div>
            {{ trans_choice('common.menu.device', 2) }}
        </div>

    </a>
@endcan

@can('administrate-admin-portal-pages')
    <a
            href="{{ route('articles.index') }}"
            class="flex items-left items-center text-white hover:opacity-100 hover:mx-2 hover:rounded-sm py-4
        pl-6 nav-item  {{ setActiveLink(route('articles.index')) }} space-x-2"
    >
        <div>
            <x-heroicon-c-pencil class="w-4" />
        </div>
        <div>
            {{ trans_choice('common.menu.article', 2) }}
        </div>

    </a>

    <a
            href="{{ route('collections.index') }}"
            class="flex items-left items-center text-white hover:opacity-100 hover:mx-2 hover:rounded-sm py-4
        pl-6 nav-item {{ setActiveLink(route('collections.index')) }} space-x-2"
    >
        <div>
            <x-heroicon-c-circle-stack class="w-4" />
        </div>
        <div>
            {{ trans_choice('common.menu.collection', 2) }}
        </div>
    </a>
    <div class="flex items-left py-4 pl-4 italic font-extrabold text-lg w-full pr-2">
        {{ __('common.menu.portal administration') }}
    </div>
    <a
            href="{{ route('users.index') }}"
            class="flex items-left items-center text-white hover:opacity-100 hover:mx-2 hover:rounded-sm py-4
        pl-6 nav-item {{ setActiveLink(route('users.index')) }} space-x-2"
    >
        <div>
            <x-heroicon-m-users class="w-4" />
        </div>
        <div>
            {{ trans_choice('common.menu.user', 2) }}
        </div>
    </a>
@endcan
@can('administrate-superadmin-portal-pages')

    <a
            href="{{route('systems.status')}}"
            class="flex items-left items-center text-white hover:opacity-100 hover:mx-2 hover:rounded-sm py-4
        pl-6 nav-item  {{ setActiveLink(route('systems.status')) }} space-x-2"
    >
        <div>
            <x-heroicon-c-cog-8-tooth class="w-4" />
        </div>
        <div>
            {{ __('common.menu.portal settings') }}
        </div>
    </a>
@endcan
