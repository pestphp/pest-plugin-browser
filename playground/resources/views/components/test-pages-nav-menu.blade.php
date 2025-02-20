<nav class="sm:py-12 md:w-full sm:w-2/3 container max-w-5xl py-6 mx-auto flex justify-center">
    <ul class="flex gap-4">
        <li>
            <a
                href="{{ route('home') }}"
                title="Playground home"
                class="sm:w-auto focus:outline-none w-full px-12 py-4 text-lg font-bold text-gray-900 bg-white border border-white rounded-lg"
            >
                Home
            </a>
        </li>
        @foreach ($pages as $page)
            <li>
                <a
                    href="{{ route('test-page', $page) }}"
                    class="sm:w-auto focus:outline-none w-full px-12 py-4 text-lg font-bold text-gray-900 bg-white border border-white rounded-lg"
                >
                    {{ str($page)->replace('-', ' ')->title() }}
                </a>
            </li>
        @endforeach
    </ul>
</nav>
