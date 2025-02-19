@extends('layouts.app')

@section('content')
    <div class="py-10 md:py-28 flex flex-col items-center justify-center text-center">
        <h3 class="lg:text-2xl md:text-xl px-6 mt-6 text-lg leading-tight">
            Pest is a testing framework with a focus on simplicity, <br class="sm:block hidden">meticulously designed to bring back the joy of testing in PHP.
        </h3>
        <div class="sm:flex-row sm:space-x-6 flex flex-col items-center mt-6">
            <a href="https://pestphp.com/docs/installation"
               class="sm:w-auto focus:outline-none w-full px-12 py-4 text-lg font-bold text-gray-900 bg-white border border-white rounded-lg">
                Get started
            </a>
            <a href="https://github.com/pestphp/pest" target="_blank"
               class="sm:w-auto focus:outline-none sm:mt-0 w-full px-12 py-4 mt-3 text-lg font-bold text-white bg-transparent border border-white rounded-lg">
                Source code
            </a>
        </div>
    </div>

@endsection
