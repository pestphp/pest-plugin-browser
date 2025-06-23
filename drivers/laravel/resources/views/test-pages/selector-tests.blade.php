@extends('layouts.app')

@section('content')
    <section class="sm:py-24 md:w-full sm:w-2/3 container max-w-5xl py-12 mx-auto">
        <h1 class="text-3xl font-bold mb-6">Selector Testing Page</h1>

        <!-- Test elements for getByTestId -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold my-2">Data TestID Elements</h2>
            <div data-testid="profile-section" class="p-4 border rounded">
                <p>This section has a data-testid attribute</p>
                <button data-testid="submit-button" class="bg-white text-black p-2">Submit</button>
            </div>
        </div>

        <!-- Test elements for getByRole -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold my-2">Role Elements</h2>
            <div class="flex space-x-4">
                <button role="button" aria-label="Save" class="bg-white text-black p-2">Save</button>
                <button role="button" aria-label="Cancel" class="bg-white text-black p-2">Cancel</button>
                <input role="checkbox" type="checkbox" id="remember-me" name="remember-me" aria-label="Remember Me" />
                <label for="remember-me">Remember Me</label>
                <a href="#" role="link" aria-label="Help">Help</a>
            </div>
        </div>

        <!-- Test elements for getByLabel -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold my-2">Labeled Elements</h2>
            <div class="flex flex-col space-y-2">
                <div>
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="bg-white text-black p-2" value="johndoe" />
                </div>
                <div>
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="bg-white text-black p-2" />
                </div>
            </div>
        </div>

        <!-- Test elements for getByPlaceholder -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold my-2">Placeholder Elements</h2>
            <div class="flex flex-col space-y-2">
                <input type="text" placeholder="Search..." class="bg-white text-black p-2" />
                <textarea placeholder="Enter your comments here" class="bg-white text-black p-2"></textarea>
            </div>
        </div>

        <!-- Test elements for getByText -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold my-2">Text Elements</h2>
            <div class="space-y-2">
                <p>This is a simple paragraph</p>
                <div>This is a div with text content</div>
                <span>This is a special span element</span>
                <button class="bg-white text-black p-2">Click Me Button</button>
            </div>
        </div>

        <!-- Test elements for getByAltText -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold my-2">Alt Text Elements</h2>
            <div class="flex space-x-4">
                <img src="https://pestphp.com/www/assets/logo.svg" alt="Pest Logo" width="50" height="50" />
                <img src="https://pestphp.com/www/assets/logo.svg" alt="Another Image" width="50" height="50" />
            </div>
        </div>

        <!-- Test elements for getByTitle -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold my-2">Title Attribute Elements</h2>
            <div class="flex space-x-4">
                <button title="Info Button" class="bg-white text-black p-2">Info</button>
                <a href="#" title="Help Link">Help</a>
                <div title="Important Information">Hover over me</div>
            </div>
        </div>

        <!-- Mixed test cases with nested elements -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold my-2">Complex Examples</h2>
            <div class="border rounded p-4">
                <div data-testid="user-profile">
                    <h3 class="text-xl font-bold">User Profile</h3>
                    <div class="flex space-x-4">
                        <img src="https://pestphp.com/www/assets/logo.svg" alt="Profile Picture" width="50" height="50" />
                        <div>
                            <p title="User's Name"><strong>Jane Doe</strong></p>
                            <p>Email: <span data-testid="user-email">jane.doe@example.com</span></p>
                            <button role="button" aria-label="Edit Profile" class="bg-white text-black p-1 mt-2">Edit Profile</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
