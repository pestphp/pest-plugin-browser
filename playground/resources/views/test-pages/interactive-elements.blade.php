@extends('layouts.app')

@section('content')
    <section class="sm:py-24 md:w-full sm:w-2/3 container max-w-5xl py-12 text-center mx-auto">

        <h1
            x-init="setTimeout(() => $el.innerHTML = 'I appear after 2 seconds', 2000)"
        ></h1>

        <div class="h-12"></div>

        <div
            id="i-have-data-testid"
            data-testid="i-exist-to-be-seen"
        >
            I'm an element with a data-attribute.
        </div>

        <div
            id="invisible-element"
            class="hidden"
        >
            I'm an invisible element.
        </div>

        <div id="text-with-special-chars">
            Some (text) with some "formatted" characters.
        </div>

    </section>
@endsection
