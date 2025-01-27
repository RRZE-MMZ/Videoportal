@use(App\Enums\Role)

<aside class="w-full sm:w-64" aria-label="Sidebar">
    <div class="overflow-y-auto rounded-sm bg-gray-50 dark:bg-sky-950 text-dark dark:text-gray-200 px-4 sm:px-3 py-4">
        <ul class="space-y-2 list-none">
            <li>
                <a href="{{ route('frontend.userSettings.edit') }}"
                   class="flex items-center p-2 text-base font-normal text-gray-900 rounded-lg
                          dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700">
                    <x-heroicon-o-adjustments-horizontal class="shrink-0 w-5 sm:w-6 h-5 sm:h-6 text-gray-500
                          transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" />
                    <span class="ml-2 sm:ml-3 flex-1 whitespace-nowrap">
                        {{ __('myPortal._sidebar_menu.Portal settings') }}
                    </span>
                </a>
            </li>
            <li>
                <a href="{{ route('frontend.user.subscriptions') }}"
                   class="flex items-center p-2 text-base font-normal text-gray-900 rounded-lg
                          dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700">
                    <x-heroicon-o-book-open class="w-5 sm:w-6 h-5 sm:h-6 text-gray-500 transition duration-75
                          dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" />
                    <span class="ml-2 sm:ml-3">
                        {{ __('myPortal._sidebar_menu.Series subscriptions') }}
                    </span>
                </a>
            </li>
            <li>
                <a href="{{ route('frontend.user.comments') }}"
                   class="flex items-center p-2 text-base font-normal text-gray-900 rounded-lg
                          dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700">
                    <x-heroicon-o-chat-bubble-oval-left class="shrink-0 w-5 sm:w-6 h-5 sm:h-6 text-gray-500
                          transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" />
                    <span class="ml-2 sm:ml-3 flex-1 whitespace-nowrap">
                        {{ __('myPortal._sidebar_menu.Comments') }}
                    </span>
                    <span class="inline-flex justify-center items-center p-2 ml-2 sm:ml-3 w-3 h-3 text-xs sm:text-sm
                          font-medium text-blue-600 bg-blue-200 rounded-full dark:bg-blue-900 dark:text-blue-200">
                        {{ auth()->user()->comments()->where('type', 'frontend')->count() }}
                    </span>
                </a>
            </li>

            @if(auth()->user()->hasRole(Role::MEMBER) && auth()->user()->roles->containsOneItem())
                @if(isset($settings['admin_portal_application_status']))
                    <li>
                        <a href="{{ route('frontend.user.applications') }}"
                           class="flex items-center p-2 text-base font-normal text-gray-900 rounded-lg
                                  dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700">
                            <x-heroicon-o-exclamation-circle class="w-5 sm:w-6 h-5 sm:h-6 text-gray-500 transition duration-75
                                  dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" />
                            <span class="ml-2 sm:ml-3">
                                {{ __('myPortal._sidebar_menu.Application status') }}
                            </span>
                        </a>
                    </li>
                @else
                    <li>
                        <a href="{{ route('frontend.admin.portal.use.terms') }}"
                           class="flex items-center p-2 text-base font-normal text-gray-900 rounded-lg
                                  dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700">
                            <x-heroicon-o-exclamation-circle class="w-5 sm:w-6 h-5 sm:h-6 text-gray-500 transition duration-75
                                  dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" />
                            <span class="ml-2 sm:ml-3">
                                {{ __('myPortal._sidebar_menu.Apply for admin portal') }}
                            </span>
                        </a>
                    </li>
                @endif
            @endif
        </ul>
    </div>
</aside>
