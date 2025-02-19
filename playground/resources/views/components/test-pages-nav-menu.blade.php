<div class="flex justify-center">
    <ul class="flex gap-4">
        <li><a href="{{ route('home') }}">Home</a></li>
        @foreach($pages as $page)
            <li>
                <a href="{{ route('test-page', $page) }}">
                    {{ str($page)->replace('-', ' ')->title() }}
                </a>
            </li>
        @endforeach
    </ul>
</div>
