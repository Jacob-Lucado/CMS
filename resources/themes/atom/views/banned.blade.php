<x-app-layout>
    @push('title', __('Banned'))
    <div class="col-span-12 flex justify-center dark:text-gray-100">
        <div class="space-y-4 lg:w-1/2">
            <div class="w-full rounded-md bg-red-500 p-2 text-center text-white">
                {{ __('It seems like you are banned off :hotel', ['hotel' => setting('hotel_name')]) }}
            </div>

            <div class="rounded-md p-2 shadow-sm bg-white dark:bg-gray-800">
                <div class="flex justify-between">
                    <div class="flex flex-col px-1">
                        <div class="max-w-[380px]">
                            <p>
                                <strong>{{ __('Ban type:') }}</strong> {{ $ban->type }}
                            </p>

                            <p>
                                <strong>{{ __('Ban reason:') }}</strong> {{ $ban->ban_reason }}
                            </p>

                            <p>
                                <strong>{{ __('Ban expiration:') }}</strong>
                                {{ $ban->ban_expire === null ? __('Never') : date('Y/m/d', $ban->ban_expire) }}
                            </p>
                        </div>

                        <div class="mt-4 max-w-[380px]">
                            <p class="mb-4">
                                {{ __('If you believe this is a mistake, please reach out to one of our staff members through our Discord server!') }}
                            </p>

                            <a href="{{ setting('discord_invitation_link') }}" target="_blank">
                                <x-form.primary-button>
                                    {{ __('Join discord') }}
                                </x-form.primary-button>
                            </a>
                        </div>

                        <div class="mt-6 max-w-[380px]">
                            <h3 class="mb-2 font-semibold">{{ __('Appeal your ban') }}</h3>

                            @if (session('status'))
                                <p class="mb-2 rounded bg-green-100 p-2 text-sm text-green-800">{{ session('status') }}</p>
                            @endif

                            @if ($pending)
                                <p class="text-sm">{{ __('Your appeal is with the team. We will get back to you.') }}</p>
                            @else
                                <form method="POST" action="{{ route('banned.appeal') }}" class="space-y-2">
                                    @csrf
                                    @guest
                                        <input type="text" name="username" value="{{ old('username') }}" maxlength="64" placeholder="{{ __('Your username') }}" required class="w-full rounded border-2 border-gray-300 dark:bg-gray-700">
                                    @endguest
                                    <textarea name="message" rows="4" minlength="20" maxlength="2000" required placeholder="{{ __('Tell us what happened and why the ban should be lifted.') }}" class="w-full rounded border-2 border-gray-300 dark:bg-gray-700">{{ old('message') }}</textarea>
                                    @foreach ($errors->all() as $error)
                                        <p class="text-sm text-red-600">{{ $error }}</p>
                                    @endforeach
                                    <x-form.primary-button>{{ __('Send appeal') }}</x-form.primary-button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <div class="mr-8 hidden items-center lg:flex">
                        <img src="{{ asset('assets/images/angry_frank.png') }}" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
