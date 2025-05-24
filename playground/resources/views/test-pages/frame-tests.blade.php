@extends('layouts.app')

@section('content')
    <section class="sm:py-24 md:w-full sm:w-2/3 container max-w-5xl py-12 mx-auto">
        <h1 class="text-3xl font-bold mb-6">Frame Testing Page</h1>

        <!-- Content Testing Section -->
        <div class="mb-8" id="content-section">
            <h2 class="text-2xl font-bold my-2">Content Testing</h2>
            <div id="test-content" class="p-4 border rounded">
                <p>This is the main content for testing.</p>
                <span>Inner text content</span>
                <div class="nested">Nested content</div>
            </div>
            <div id="empty-content"></div>
            <div id="html-content"><strong>Bold text</strong> and <em>italic text</em></div>
        </div>

        <!-- Form Elements Section -->
        <div class="mb-8" id="form-section">
            <h2 class="text-2xl font-bold my-2">Form Elements</h2>
            <form id="test-form" class="space-y-4">
                <!-- Text Inputs -->
                <div class="flex flex-col space-y-2">
                    <label for="test-input">Test Input</label>
                    <input id="test-input" name="test-input" type="text" class="bg-white text-black p-2 border" placeholder="Enter text here" />
                </div>

                <div class="flex flex-col space-y-2">
                    <label for="prefilled-input">Prefilled Input</label>
                    <input id="prefilled-input" name="prefilled-input" type="text" class="bg-white text-black p-2 border" value="initial value" />
                </div>

                <div class="flex flex-col space-y-2">
                    <label for="disabled-input">Disabled Input</label>
                    <input id="disabled-input" name="disabled-input" type="text" class="bg-gray-300 text-gray-500 p-2 border" value="disabled" disabled />
                </div>

                <!-- Password Input -->
                <div class="flex flex-col space-y-2">
                    <label for="password-input">Password</label>
                    <input id="password-input" name="password-input" type="password" class="bg-white text-black p-2 border" placeholder="Enter password" />
                </div>

                <!-- Textarea -->
                <div class="flex flex-col space-y-2">
                    <label for="test-textarea">Textarea</label>
                    <textarea id="test-textarea" name="test-textarea" class="bg-white text-black p-2 border" placeholder="Enter your message">Initial textarea content</textarea>
                </div>

                <!-- Checkboxes -->
                <div class="space-y-2">
                    <h3 class="font-bold">Checkboxes</h3>
                    <div class="flex space-x-4">
                        <input id="unchecked-checkbox" name="unchecked-checkbox" type="checkbox" />
                        <label for="unchecked-checkbox">Unchecked checkbox</label>
                    </div>
                    <div class="flex space-x-4">
                        <input id="checked-checkbox" name="checked-checkbox" type="checkbox" checked />
                        <label for="checked-checkbox">Checked checkbox</label>
                    </div>
                    <div class="flex space-x-4">
                        <input id="disabled-checkbox" name="disabled-checkbox" type="checkbox" disabled />
                        <label for="disabled-checkbox">Disabled checkbox</label>
                    </div>
                </div>

                <!-- Radio Buttons -->
                <div class="space-y-2">
                    <h3 class="font-bold">Radio Buttons</h3>
                    <div class="flex space-x-4">
                        <input id="radio-option1" name="radio-group" type="radio" value="option1" />
                        <label for="radio-option1">Option 1</label>
                    </div>
                    <div class="flex space-x-4">
                        <input id="radio-option2" name="radio-group" type="radio" value="option2" checked />
                        <label for="radio-option2">Option 2 (checked)</label>
                    </div>
                    <div class="flex space-x-4">
                        <input id="radio-option3" name="radio-group" type="radio" value="option3" />
                        <label for="radio-option3">Option 3</label>
                    </div>
                </div>

                <!-- Select Elements -->
                <div class="flex flex-col space-y-2">
                    <label for="single-select">Single Select</label>
                    <select id="single-select" name="single-select" class="bg-white text-black p-2 border">
                        <option value="">Choose option</option>
                        <option value="option1">Option 1</option>
                        <option value="option2" selected>Option 2 (selected)</option>
                        <option value="option3">Option 3</option>
                    </select>
                </div>

                <div class="flex flex-col space-y-2">
                    <label for="multi-select">Multi Select</label>
                    <select id="multi-select" name="multi-select" multiple class="bg-white text-black p-2 border">
                        <option value="multi1" selected>Multi Option 1 (selected)</option>
                        <option value="multi2">Multi Option 2</option>
                        <option value="multi3" selected>Multi Option 3 (selected)</option>
                        <option value="multi4">Multi Option 4</option>
                    </select>
                </div>

                <div class="flex flex-col space-y-2">
                    <label for="test-select">Test Select</label>
                    <select id="test-select" name="test-select" class="bg-white text-black p-2 border">
                        <option value="option1">Option 1</option>
                        <option value="option2">Option 2</option>
                        <option value="option3">Option 3</option>
                    </select>
                </div>

                <!-- File Input -->
                <div class="flex flex-col space-y-2">
                    <label for="file-input">File Input</label>
                    <input id="file-input" name="file-input" type="file" class="bg-white text-black p-2 border" />
                </div>

                <!-- Buttons -->
                <div class="space-y-2">
                    <button id="enabled-button" type="button" class="bg-blue-500 text-white p-2 rounded">Enabled Button</button>
                    <button id="disabled-button" type="button" class="bg-gray-300 text-gray-500 p-2 rounded" disabled>Disabled Button</button>
                    <button id="submit-button" type="submit" class="bg-green-500 text-white p-2 rounded">Submit Button</button>
                </div>
            </form>
        </div>

        <!-- Visibility and State Testing Section -->
        <div class="mb-8" id="visibility-section">
            <h2 class="text-2xl font-bold my-2">Visibility and State Testing</h2>
            <div id="visible-element" class="p-4 border bg-green-100">Visible Element</div>
            <div id="hidden-element" class="p-4 border bg-red-100" style="display: none;">Hidden Element</div>
            <div id="opacity-hidden" class="p-4 border bg-yellow-100" style="opacity: 0;">Opacity Hidden Element</div>
            <div id="visibility-hidden" class="p-4 border bg-purple-100" style="visibility: hidden;">Visibility Hidden Element</div>
        </div>

        <!-- Interaction Testing Section -->
        <div class="mb-8" id="interaction-section">
            <h2 class="text-2xl font-bold my-2">Interaction Testing</h2>

            <!-- Focus tracking display -->
            <div id="focus-display" class="p-2 bg-blue-50 border text-sm mb-2">No element focused yet</div>

            <!-- Hover tracking display -->
            <div id="hover-display" class="p-2 bg-green-50 border text-sm mb-2">No element hovered yet</div>

            <!-- Hover Target -->
            <div id="hover-target" class="p-4 border bg-blue-100 hover:bg-blue-200 transition-colors cursor-pointer" data-hover-state="not-hovered">
                Hover over me to change color
            </div>

            <!-- Focus Target -->
            <input id="focus-target" type="text" class="bg-white text-black p-2 border focus:border-blue-500" placeholder="Focus on me" />

            <!-- Click Target -->
            <button id="click-target" type="button" class="bg-purple-500 text-white p-2 rounded mt-2" onclick="this.innerHTML = 'Clicked!'">
                Click Me
            </button>

            <!-- Drag and Drop -->
            <div class="flex space-x-4 mt-4">
                <div id="draggable" draggable="true" class="p-4 bg-yellow-300 border rounded cursor-move">
                    Draggable Element
                </div>
                <div id="droppable" class="p-4 bg-gray-200 border-2 border-dashed border-gray-400 rounded min-h-16">
                    Drop Zone
                </div>
            </div>
        </div>

        <!-- Keyboard Interaction Section -->
        <div class="mb-8" id="keyboard-section">
            <h2 class="text-2xl font-bold my-2">Keyboard Testing</h2>
            <input id="keyboard-input" type="text" class="bg-white text-black p-2 border w-full" placeholder="Type here and press keys" />
            <div id="key-display" class="mt-2 p-2 bg-gray-100 text-sm">Key events will show here</div>
        </div>

        <!-- Wait and Load State Testing Section -->
        <div class="mb-8" id="wait-section">
            <h2 class="text-2xl font-bold my-2">Wait and Load State Testing</h2>
            <button id="async-button" type="button" class="bg-orange-500 text-white p-2 rounded" onclick="simulateAsyncOperation()">
                Trigger Async Operation
            </button>
            <div id="async-result" class="mt-2 p-2 bg-gray-100" style="display: none;">
                Async operation completed!
            </div>
            <div id="loading-indicator" class="mt-2 p-2 bg-yellow-100" style="display: none;">
                Loading...
            </div>
        </div>

        <!-- URL and Navigation Testing Section -->
        <div class="mb-8" id="navigation-section">
            <h2 class="text-2xl font-bold my-2">Navigation Testing</h2>
            <a id="test-link" href="#section1" class="text-blue-500 underline">Test Link (Fragment)</a>
            <div class="mt-4" id="section1">
                <h3>Section 1</h3>
                <p>This is section 1 for fragment navigation testing.</p>
            </div>
        </div>

        <!-- Complex Content Section -->
        <div class="mb-8" id="complex-section">
            <h2 class="text-2xl font-bold my-2">Complex Content Testing</h2>
            <div id="nested-structure">
                <div class="level-1">
                    <span>Level 1 content</span>
                    <div class="level-2">
                        <span>Level 2 content</span>
                        <div class="level-3">
                            <span id="deep-text">Deep nested text</span>
                        </div>
                    </div>
                </div>
            </div>

            <div id="mixed-content">
                <p>Paragraph with <strong>bold</strong> and <em>italic</em> text.</p>
                <ul>
                    <li>List item 1</li>
                    <li>List item 2</li>
                </ul>
            </div>
        </div>
    </section>

    <script>
        // JavaScript for interactive elements
        function simulateAsyncOperation() {
            const button = document.getElementById('async-button');
            const result = document.getElementById('async-result');
            const loading = document.getElementById('loading-indicator');

            button.disabled = true;
            loading.style.display = 'block';
            result.style.display = 'none';

            setTimeout(() => {
                loading.style.display = 'none';
                result.style.display = 'block';
                button.disabled = false;
            }, 1000);
        }

        // Focus tracking
        let lastFocusedElement = '';
        function trackFocus(elementId) {
            lastFocusedElement = elementId;
            const display = document.getElementById('focus-display');
            if (display) {
                display.textContent = `Last focused: ${elementId}`;
            }
        }

        // Hover tracking
        let lastHoveredElement = '';
        function trackHover(elementId) {
            lastHoveredElement = elementId;
            const display = document.getElementById('hover-display');
            if (display) {
                display.textContent = `Last hovered: ${elementId}`;
            }
        }

        // Key press tracking
        let lastKeyPressed = '';
        function trackKeyPress(key, code) {
            lastKeyPressed = key;
            const display = document.getElementById('key-display');
            if (display) {
                display.textContent = `Key pressed: ${key} (Code: ${code})`;
            }
        }

        // Add event listeners for focus tracking
        document.getElementById('focus-target').addEventListener('focus', () => trackFocus('focus-target'));
        document.getElementById('test-input').addEventListener('focus', () => trackFocus('test-input'));
        document.getElementById('password-input').addEventListener('focus', () => trackFocus('password-input'));
        document.getElementById('test-textarea').addEventListener('focus', () => trackFocus('test-textarea'));
        document.getElementById('keyboard-input').addEventListener('focus', () => trackFocus('keyboard-input'));

        // Add event listeners for hover tracking (multiple events for compatibility)
        const hoverTarget = document.getElementById('hover-target');
        const enabledButton = document.getElementById('enabled-button');

        ['mouseenter', 'mouseover', 'pointerenter'].forEach(event => {
            hoverTarget.addEventListener(event, () => trackHover('hover-target'));
            enabledButton.addEventListener(event, () => trackHover('enabled-button'));
        });

        // Also add global hover detection for Playwright compatibility
        document.addEventListener('mousemove', function(e) {
            if (e.target.id === 'hover-target') {
                trackHover('hover-target');
            } else if (e.target.id === 'enabled-button') {
                trackHover('enabled-button');
            }
        });

        // Keyboard event listener
        document.getElementById('keyboard-input').addEventListener('keydown', function(e) {
            trackKeyPress(e.key, e.code);
        });

        // Drag and drop functionality
        document.getElementById('draggable').addEventListener('dragstart', function(e) {
            e.dataTransfer.setData('text/plain', 'dragged');
        });

        document.getElementById('droppable').addEventListener('dragover', function(e) {
            e.preventDefault();
            this.style.backgroundColor = '#e2e8f0';
        });

        document.getElementById('droppable').addEventListener('dragleave', function(e) {
            this.style.backgroundColor = '#f7fafc';
        });

        document.getElementById('droppable').addEventListener('drop', function(e) {
            e.preventDefault();
            this.style.backgroundColor = '#d4edda';
            this.textContent = 'Element dropped!';
        });
    </script>
@endsection
