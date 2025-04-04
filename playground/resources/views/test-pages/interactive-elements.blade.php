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
            Some (text) wi/th [some] "formatted" ch@racters.
        </div>

        <section>
            <div id="div-1" class="class-1"></div>
            <div id="div-2" class="class-1 class-2"></div>
            <div id="div-3" class="class-1 selected class-3"></div>
            <ul>
                <li class="component"></li>
                <li class="component selected"></li>
                <li class="component"></li>
            </ul>
        </section>


    </section>
@endsection
