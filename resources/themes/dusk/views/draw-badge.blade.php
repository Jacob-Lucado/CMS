<x-app-layout>
    @push('title', __('Badge Generator'))

    <script>
    const translations = {
        buy_confirmation: @json(__('badge_purchase_confirmation', ['cost' => $cost, 'currency' => $currencyType])),
        purchase_success: @json(__('badge_purchase_success', ['currency' => ucfirst($currencyType)])),
        purchase_error_insufficient: @json(__('badge_purchase_error_insufficient', ['currency' => $currencyType])),
        purchase_error_general: @json(__('badge_purchase_error_general')),
        missing_fields: @json(__('Please fill in the badge name, description, and draw something on the canvas.')),
        invalid_content: @json(__('Badge name and description cannot contain URLs.')),
        invalid_file_type: @json(__('Only PNG and GIF files are allowed.'))
    };
    </script>

    <div class="col-span-12 flex flex-col lg:grid grid-cols-4 gap-4" x-data="badgeDrawer({ cost: {{ $cost }}, currencyType: '{{ $currencyType }}' })">
        <x-content.content-card icon="hotel-icon" classes="border dark:border-gray-900 col-span-3">
            <x-slot:title>
                {{ __('Badge Drawer') }}
            </x-slot:title>

            <x-slot:under-title>
                {{ __('Draw your very own badge') }}
            </x-slot:under-title>

            <p class="mb-3 px-2 text-sm dark:text-gray-200"><a href="{{ route('help-center.badges.index') }}" class="font-semibold underline">{{ __('See badges other players drew') }}</a></p>

            <div class="px-2 text-sm dark:text-gray-200">
                @if ($folderError)
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <strong class="font-bold">{{ __('Error:') }}</strong>
                        <span class="block sm:inline">{{ $errorMessage }}</span>
                    </div>
                @endif
                <div class="flex flex-col gap-6">
                    <div class="flex flex-col gap-2">
                        <div class="flex flex-col md:flex-row flex-wrap gap-4 justify-between">
                            <button @click="toggleCopyMode" :aria-pressed="copyMode" aria-label="{{ __('Toggle copy color mode') }}" class="flex items-center justify-center font-medium gap-2 flex-1 border-2 border-black rounded badge-drawer-button text-black dark:text-white">
                                {{ __('Copy color:') }}
                                <i class="fa-solid fa-eye-dropper"></i>
                            </button>
                            <button @click="toggleEraseMode" :aria-pressed="eraseMode" aria-label="{{ __('Toggle erase mode') }}" class="flex items-center justify-center font-medium gap-2 flex-1 border-2 border-black rounded badge-drawer-button text-black dark:text-white">
                                {{ __('Erase mode:') }}
                                <i class="fa-solid fa-eraser"></i>
                            </button>
                            <button  @click="$refs.fileInput.click()" aria-label="{{ __('Import a picture or animated GIF for your badge') }}" class="flex items-center justify-center font-medium gap-2 flex-1 border-2 border-black rounded badge-drawer-button text-black dark:text-white">
                                {{ __('Import PNG or GIF:') }}
                                <i class="fa-solid fa-file-import"></i>
                            </button>
                            <button  @click="showGrid = !showGrid" :aria-pressed="showGrid" aria-label="{{ __('Toggle grid visibility') }}" class="flex items-center justify-center font-medium gap-2 flex-1 border-2 border-black rounded badge-drawer-button text-black dark:text-white">
                                {{ __('Show Grid:') }}    
                                <i class="fa-solid fa-table-cells"></i>
                            </button>
                        </div>
                    </div>
                    <input type="file" accept="image/png,image/gif" x-ref="fileInput" style="display:none;" @change="importImage($event)">
                    <div class="flex flex-col md:flex-row gap-4 md:gap-8 items-start">
                        <div class="checkerboard w-full max-w-[640px] mx-auto aspect-square relative">
                            <div id="guide" x-ref="guide"></div>
                            <canvas width="40" height="40" x-ref="canvas" class="w-full h-full border border-gray-300 dark:border-gray-700" style="image-rendering: pixelated; background: transparent;" role="img" aria-label="{{ __('Badge drawing area') }}"></canvas>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2" aria-label="{{ __('Animation frames') }}">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="font-semibold dark:text-gray-100">
                                {{ __('Frames') }} (<span x-text="frames.length"></span>/<span x-text="maxFrames"></span>)
                            </p>
                            <button type="button" @click="addFrame(false)" :disabled="frames.length >= maxFrames" aria-label="{{ __('Add an empty frame') }}" class="flex items-center gap-2 border-2 border-black rounded badge-drawer-button px-3 py-1 text-black dark:text-white disabled:opacity-40">
                                <i class="fa-solid fa-plus"></i> {{ __('Add') }}
                            </button>
                            <button type="button" @click="addFrame(true)" :disabled="frames.length >= maxFrames" aria-label="{{ __('Copy this frame') }}" class="flex items-center gap-2 border-2 border-black rounded badge-drawer-button px-3 py-1 text-black dark:text-white disabled:opacity-40">
                                <i class="fa-solid fa-copy"></i> {{ __('Copy') }}
                            </button>
                            <button type="button" @click="moveFrame(-1)" :disabled="current === 0" aria-label="{{ __('Move frame earlier') }}" class="border-2 border-black rounded badge-drawer-button px-3 py-1 text-black dark:text-white disabled:opacity-40">
                                <i class="fa-solid fa-arrow-left"></i>
                            </button>
                            <button type="button" @click="moveFrame(1)" :disabled="current === frames.length - 1" aria-label="{{ __('Move frame later') }}" class="border-2 border-black rounded badge-drawer-button px-3 py-1 text-black dark:text-white disabled:opacity-40">
                                <i class="fa-solid fa-arrow-right"></i>
                            </button>
                            <button type="button" @click="removeFrame()" :disabled="frames.length <= 1" aria-label="{{ __('Delete this frame') }}" class="flex items-center gap-2 border-2 border-red-500 rounded px-3 py-1 text-red-600 dark:text-red-400 disabled:opacity-40">
                                <i class="fa-solid fa-trash"></i> {{ __('Delete') }}
                            </button>
                            <button type="button" @click="togglePlay()" :disabled="frames.length < 2" :aria-pressed="playing" aria-label="{{ __('Play the animation in the preview') }}" class="flex items-center gap-2 border-2 border-black rounded badge-drawer-button px-3 py-1 text-black dark:text-white disabled:opacity-40">
                                <i class="fa-solid" :class="playing ? 'fa-stop' : 'fa-play'"></i>
                                <span x-text="playing ? @js(__('Stop')) : @js(__('Play'))"></span>
                            </button>
                        </div>
                        <div class="flex gap-2 overflow-x-auto pb-1">
                            <template x-for="(frame, index) in frames" :key="index">
                                <button type="button" @click="selectFrame(index)" :aria-pressed="index === current" :aria-label="@js(__('Frame')) + ' ' + (index + 1)" class="relative shrink-0 w-14 h-14 rounded border-2 overflow-hidden bg-white dark:bg-gray-800" :class="index === current ? 'border-[#eeb425] ring-2 ring-[#eeb425]' : 'border-gray-300 dark:border-gray-600'">
                                    <img x-show="frame.thumb" :src="frame.thumb" alt="" class="w-full h-full" style="image-rendering: pixelated;">
                                    <span class="absolute bottom-0 right-0 bg-black/70 text-white text-xs px-1" x-text="index + 1"></span>
                                </button>
                            </template>
                        </div>
                        <div class="flex items-center gap-2" x-show="frames.length > 1">
                            <label for="frameDelay" class="font-semibold dark:text-gray-100">{{ __('Frame time (ms):') }}</label>
                            <input type="number" id="frameDelay" x-model.number="frames[current].delay" @change="frames[current].delay = clampDelay(frames[current].delay)" :min="minDelay" :max="maxDelay" step="10" class="w-24 focus:ring-0 border-4 border-gray-200 rounded dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 focus:border-[#eeb425]">
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('Slowest 2000, fastest 50. Badges loop forever.') }}</span>
                        </div>
                    </div>
                    <div class="flex flex-col md:flex-row flex-wrap gap-4 justify-between">
                        <div class="flex flex-col gap-2 flex-1">
                            <p class="font-semibold mb-1 dark:text-gray-100">{{ __('Choose Color') }}</p>
                            <div class="flex items-center gap-2 w-full">
                                <div @click="$refs.colorInput.click()" class="w-12 h-12 !p-0 border-2 border-black rounded badge-drawer-button cursor-pointer flex items-center justify-center relative">
                                    <i class="fa-solid fa-fill-drip fa-lg text-black"></i>
                                    <input type="color" id="colorInput" x-ref="colorInput" x-model="color" class="absolute opacity-0 w-full h-full cursor-pointer top-0 left-0" @input="color = $event.target.value" >
                                </div>
                                <div :style="'background-color: ' + color" class="!w-full !max-w-none !h-12 badge-drawer-palette"></div>
                            </div>
                            
                            <div class="flex flex-col gap-2 flex-1">
                                <p class="font-semibold mb-1 dark:text-gray-100">{{ __('Recent color') }}</p>
                                <div class="flex items-center flex-wrap gap-x-2 mx-auto w-full gap-y-2">
                                    <template x-for="col in recentColors" :key="col">
                                        <div @click="color = col; eraseMode = false" :style="'background-color: ' + col" class="badge-drawer-palette"></div>
                                    </template>
                                    <template x-for="i in (12 - recentColors.length)" :key="'empty-' + i">
                                        <div class="badge-drawer-palette"></div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col gap-2 flex-1">
                            <p class="font-semibold mb-1 dark:text-gray-100">{{ __('Palette') }}</p>
                            <div class="flex items-center flex-wrap gap-x-2 mx-auto w-full gap-y-2">
                                <template x-for="col in colors">
                                    <div @click="color = col; eraseMode = false" :style="'background-color: ' + col" class="badge-drawer-palette"></div>
                                </template>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col md:flex-row gap-4 justify-between">
                        <button type="button" @click="clearBoard" class="w-full rounded bg-red-600 hover:bg-red-700 text-white p-2 border-2 border-red-500 transition ease-in-out duration-150 font-semibold">{{ __('Clear All') }}</button>
                        <button type="button" @click="generateCanvas('download')" class="w-full rounded bg-[#eeb425] text-white p-2 border-2 border-yellow-400 transition ease-in-out duration-200 hover:bg-[#d49f1c] font-semibold"> {{ __('Download badge') }} </button>
                    </div>
                </div>
            </div>
        </x-content.content-card>
        <x-content.content-card icon="hotel-icon" classes="border dark:border-gray-900 col-span-1 h-max">
            <x-slot:title>
                {{ __('Badge Drawer Details') }}
            </x-slot:title>

            <x-slot:under-title>
                {{ __('My Badge Details') }}
            </x-slot:under-title>

            <div class="px-2 text-sm">
                <div class="flex flex-col gap-3">
                    <h3 class="font-semibold dark:text-gray-100">{{ __('Preview') }}</h3>
                    <div id="avatarbox" class="mx-auto">
                        <div class="username"
                            style="font-size: 12px;margin-top: 13px;margin-left: 30px;color: #FFF;">
                            {{ auth()->user()->username}}
                        </div>
                        <div class="avatara"
                            style="float: left;background: url('{{ setting('avatar_imager') }}{{ auth()->user()->look}}&direction=4&head_direction=3') no-repeat;width: 60px;height: 120px;margin-left: 15px;margin-top: 10px;">
                        </div>
                        <div class="preview" style='float: left;margin-left: 15px;margin-top: 7px;'>
                            <canvas width="40" height="40" x-ref="previewCanvas" style="image-rendering: pixelated; background: transparent; role="img" aria-label="{{ __('Badge preview') }}""></canvas>
                        </div>
                    </div>
                    <div class="">
                        <label for="badgeName" class="font-semibold dark:text-gray-100">{{ __('Badge Name:') }}</label>
                        <input type="text" id="badgeName" x-model="badgeName" maxlength="24" class="mt-1 focus:ring-0 border-4 border-gray-200 rounded dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 focus:border-[#eeb425] w-full">
                    </div>
                    <div class="mt-2">
                        <label for="badgeDescription" class="font-semibold mb-4 dark:text-gray-100">{{ __('Badge Description:') }}</label>
                        <input type="text" id="badgeDescription" x-model="badgeDescription" maxlength="255" class="mt-1 focus:ring-0 border-4 border-gray-200 rounded dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 focus:border-[#eeb425] w-full">
                    </div>
                    <button type="button" x-text="buttonText" @click="buyBadge" class="w-full rounded text-white p-2 border-2 border-green-500 transition ease-in-out duration-150 font-semibold bg-green-600 hover:bg-green-700" :class="isValid ? 'cursor-pointer' : 'cursor-not-allowed'" :disabled="!isValid || {{ $folderError ? 'true' : 'false' }}"></button>
                </div>
            </div>
        </x-content.content-card>
    </div>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <script src="{{ asset('assets/js/gif/gif.js') }}"></script>
    <script src="{{ asset('assets/js/gif/gif-reader.js') }}"></script>

    @include('badge.drawer-script')

    <style>
        #canvas {
            cursor: pointer;
        }
        #guide {
            display: block;
            pointer-events: none;
            position: absolute;
            top: 0;
            left: 0;
            background-repeat: repeat;
        }
        .checkerboard {
        }
        .dark .checkerboard {
        }
        input[type="color"] {
            position: absolute;
            top: auto;
            left: auto;
            bottom: 0;
            right: 0;
            transform: translateY(100%);
        }
        #avatarbox {
            background: url('/assets/images/badgecreator/avatarbox.png');
            width: 199px;
            height: 180px;
            float: right;
        }
        .tint-red {
            filter: invert(21%) sepia(87%) saturate(4855%) hue-rotate(346deg) brightness(91%) contrast(92%);
        }
    </style>
</x-app-layout>