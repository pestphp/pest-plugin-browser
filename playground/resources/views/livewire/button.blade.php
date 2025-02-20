

<div x-data="{ timer: null, progress: 0 }">
    <button @class([
            "overflow-hidden focus:outline-none w-full px-12 py-4 text-lg font-bold text-gray-900 bg-white border border-white rounded-lg",
            'relative' => $flavor === 'hold',
            'absolute top-10 right-10 w-xs' => $flavor === 'point',
        ])
        data-testId="{{ $this->flavor ?? 'default' }}-click"
        @foreach ($this->events as $event)
            x-on:{{ $event }}="$wire.handle"
        @endforeach
        @if ($flavor === 'hold')
            {{-- 700ms --}}
            @mousedown="progress = 0; timer = setInterval(() => { progress += 100/(700/100); if (progress >= 100) { clearInterval(timer); $wire.handle(); } }, 100)"
            @mouseup="clearInterval(timer); progress = 0"
            @mouseleave="clearInterval(timer); progress = 0"
        @endif
    >
        <span class="relative z-10">{{ $this->label }}</span>

        @if ($flavor === 'hold')
            <div class="absolute top-0 left-0 w-full h-full">
                <div class="h-full bg-[#ff45a6] transition-all w-0" :style="'width: ' + progress + '%'"></div>
            </div>
        @endif
    </button>
</div>
