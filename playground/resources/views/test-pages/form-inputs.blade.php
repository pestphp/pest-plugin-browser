@extends('layouts.app')

@section('content')
    <section class="sm:py-24 md:w-full sm:w-2/3 container max-w-5xl py-12 mx-auto">
        <div class="flex flex-wrap space-x-4 space-y-4">
            <div>
                <h1 class="text-2xl font-bold my-2">Checkboxes</h1>
                <div class="flex space-x-4 mb-2">
                    <input id="default-checkbox" name="default-checkbox" type="checkbox" />
                    <label for="default-checkbox" class="">Default checkbox</label>
                </div>

                <div class="flex space-x-4 mb-2">
                    <input id="checked-checkbox" name="checked-checkbox" type="checkbox" checked />
                    <label for="checked-checkbox" class="">Checked checkbox</label>
                </div>

                <div class="flex space-x-4 mb-2">
                    <input id="unchecked-checkbox" name="unchecked-checkbox" type="checkbox" />
                    <label for="unchecked-checkbox" class="">Unchecked checkbox</label>
                </div>

                <div class="flex space-x-4 mb-2">
                    <input id="disabled-checkbox" name="disabled-checkbox" type="checkbox" disabled />
                    <label for="disabled-checkbox" class="">Disabled checkbox</label>
                </div>
            </div>

            <div>
                <h1 class="text-2xl font-bold my-2">Text Inputs</h1>
                <div class="flex space-x-4 mb-2">
                    <input id="email" name="email" type="text" class="bg-white text-black p-2" value="john.doe@pestphp.com" />
                    <label for="email">Email</label>
                </div>

                <div class="flex space-x-4 mb-2">
                    <input id="enabled-input" name="enabled-input" type="text" class="bg-white text-black p-2" placeholder="Enabled input" />
                    <label for="enabled-input">Enabled input</label>
                </div>

                <div class="flex space-x-4 mb-2">
                    <input id="disabled-input" name="disabled-input" type="text" class="bg-gray-300 text-gray-500 p-2" placeholder="Disabled input" disabled />
                    <label for="disabled-input">Disabled input</label>
                </div>

                <div class="flex space-x-4 mb-2">
                    <input id="readonly-input" name="readonly-input" type="text" class="bg-gray-100 text-black p-2" value="Readonly input" readonly />
                    <label for="readonly-input">Readonly input</label>
                </div>
            </div>

            <div>
                <h1 class="text-2xl font-bold my-2">Visibility Tests</h1>
                <div class="flex space-x-4 mb-2">
                    <input id="visible" name="visible" type="text" class="bg-white text-black p-2" placeholder="Visible input" />
                    <label for="visible">Visible input</label>
                </div>

                <div class="flex space-x-4 mb-2" style="display: none;">
                    <input id="hidden" name="hidden" type="text" class="bg-white text-black p-2" placeholder="Hidden input" />
                    <label for="hidden">Hidden input</label>
                </div>
            </div>

            <div>
                <h1 class="text-2xl font-bold my-2">Buttons</h1>
                <div class="flex space-x-4 mb-2">
                    <button id="enabled-button" name="enabled-button" type="button" class="bg-blue-500 text-white px-4 py-2 rounded">Enabled button</button>
                </div>

                <div class="flex space-x-4 mb-2">
                    <button id="disabled-button" name="disabled-button" type="button" class="bg-gray-300 text-gray-500 px-4 py-2 rounded" disabled>Disabled button</button>
                </div>
            </div>
        </div>
    </section>
@endsection
