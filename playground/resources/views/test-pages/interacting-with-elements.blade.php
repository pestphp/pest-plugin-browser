@extends('layouts.app')

@section('content')
    <section class="sm:py-24 md:w-full sm:w-2/3 container max-w-5xl py-12 text-center mx-auto">
        <h2 class="md:text-4xl text-3xl font-bold mt-14 mb-5">Interacting With Elements</h2>
        <h3 class="md:text-3xl text-2xl font-bold text-center mt-10 mb-4">Using the Mouse</h3>

        <div class="my-10 grid grid-cols-3 gap-4">
            <a
                href="https://pestphp.com/docs/installation" class="focus:outline-none w-full px-12 py-4 text-lg font-bold text-gray-900 bg-white border border-white rounded-lg flex items-center justify-center"
                target="_blank"
            >
                Link in a new tab
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 ml-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 13v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                    <polyline points="15 3 21 3 21 9"></polyline>
                    <line x1="10" y1="14" x2="21" y2="3"></line>
                </svg>
            </a>


            <livewire:button />

            <livewire:button flavor="double" />

            <livewire:button flavor="right" />

            <livewire:button flavor="control" />

            <livewire:button flavor="hold" />
        </div>
    </section>
@endsection
