<div x-data="{ timer: null, progress: 0 }">
    <button
        data-testId="{{ $this->flavor ?? 'default' }}-click"
        class="relative overflow-hidden focus:outline-none w-full px-12 py-4 text-lg font-bold text-gray-900 bg-white border border-white rounded-lg"
        @foreach ($this->events as $event)
            x-on:{{ $event }}="$wire.handle"
        @endforeach
        @if ($flavor === 'hold')
            @mousedown="progress = 0; timer = setInterval(() => { progress += 10; if (progress >= 100) { clearInterval(timer); $wire.handle(); } }, 100)"
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
