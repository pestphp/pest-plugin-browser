@extends('layouts.app')

@section('content')
    <section class="sm:py-24 md:w-full sm:w-2/3 container max-w-5xl py-12 mx-auto">
        <div class="flex space-x-4">
            <div>
                <h1 class="text-2xl font-bold my-2">Checkboxes</h1>
                <div class="flex space-x-4">
                    <input id="default-checkbox" name="default-checkbox" type="checkbox" />
                    <label for="default-checkbox" class="">Default checkbox</label>
                </div>

                <div class="flex space-x-4">
                    <input id="checked-checkbox" name="checked-checkbox" type="checkbox" checked />
                    <label for="checked-checkbox" class="">Checked checkbox</label>
                </div>
            </div>
            <div>
                <h1 class="text-2xl font-bold my-2">E-mail</h1>
                <div class="flex space-x-4">
                    <input id="email" name="email" type="text" class="bg-white text-black p-2" value="john.doe@pestphp.com" />
                </div>        
            </div>
        </div>
    </section>
@endsection
