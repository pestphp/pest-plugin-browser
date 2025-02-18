@extends('layouts.app')

@section('content')
    <section class="sm:py-24 md:w-full sm:w-2/3 container max-w-5xl py-12 text-center mx-auto">

        <h1
            x-init="setTimeout(() => $el.innerHTML = 'I appear after 2 seconds', 2000)"
        >

        </h1>


    </section>
@endsection
