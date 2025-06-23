<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Element Tests - Playground</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.6;
        }

        .section {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .hidden {
            display: none;
        }

        .visible {
            display: block;
        }

        input,
        textarea,
        select,
        button {
            margin: 5px;
            padding: 8px;
        }

        .form-group {
            margin: 10px 0;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .user-profile {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 8px;
        }

        .profile-picture {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
        }

        .counter-item,
        .text-item,
        .inner-text-item,
        .nth-item,
        .first-item,
        .last-item {
            padding: 10px;
            margin: 5px 0;
            background: #e9ecef;
            border-radius: 3px;
        }
    </style>
</head>

<body>
    <h1>Element Tests Playground</h1>

    <!-- Basic Visibility Section -->
    <div class="section" data-testid="profile-section">
        <h2>Profile Section</h2>
        <p>This section has a data-testid attribute and is visible by default.</p>
        <button type="button">Submit</button>
    </div>
    <div id="empty-id"></div>

    <!-- Hidden Element -->
    <div data-testid="hidden-element" class="hidden">
        This element is hidden by default
    </div>

    <!-- Form Elements Section -->
    <div class="section">
        <h2>Form Elements</h2>

        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="johndoe" />
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" value="" />
        </div>

        <div class="form-group">
            <label for="search">Search</label>
            <input type="text" id="search" name="search" placeholder="Search..." />
        </div>

        <!-- Test elements for Locator tests -->
        <div class="form-group">
            <button type="button" data-testid="enabled-button">Enabled Button</button>
            <button type="button" data-testid="disabled-button" disabled>Disabled Button</button>
        </div>

        <div class="form-group">
            <input type="checkbox" data-testid="checked-checkbox" checked> Checked Checkbox
            <input type="checkbox" data-testid="unchecked-checkbox"> Unchecked Checkbox
            <input type="checkbox" data-testid="checkbox-input"> Test Checkbox
        </div>

        <div class="form-group">
            <button type="button" data-testid="click-button" onclick="incrementCounter()">Click Counter</button>
            <span data-testid="click-counter">0</span>
        </div>

        <div class="form-group">
            <button type="button" data-testid="dblclick-button" ondblclick="incrementDoubleClickCounter()">Double Click Counter</button>
            <span data-testid="dblclick-counter">0</span>
        </div>

        <div class="form-group">
            <input type="text" data-testid="text-input" placeholder="Text input">
            <input type="text" data-testid="prefilled-input" value="Prefilled value">
        </div>

        <div class="form-group">
            <label for="comments">Comments</label>
            <textarea id="comments" name="comments" data-testid="textarea-input" placeholder="Enter your comments here"></textarea>
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="remember" /> Remember Me
            </label>
        </div>

        <div class="form-group">
            <input type="checkbox" id="disabled-checkbox" disabled /> Disabled Checkbox
        </div>

        <div class="form-group">
            <input type="text" data-testid="disabled-input" disabled placeholder="Disabled input" />
        </div>

        <div class="form-group">
            <input type="text" data-testid="readonly-input" readonly value="Readonly input" />
        </div>

        <div class="form-group">
            <label for="file-upload">File Upload</label>
            <input type="file" id="file-upload" data-testid="file-input" />
        </div>

        <div class="form-group">
            <label for="select-option">Select Option</label>
            <select id="select-option" data-testid="test-select">
                <option value="option1">Option 1</option>
                <option value="option2">Option 2</option>
                <option value="option3">Option 3</option>
            </select>
        </div>

        <div class="form-group">
            <button type="button" role="button">Save</button>
            <button type="button" role="button">Cancel</button>
        </div>
    </div>

    <!-- User Profile Section -->
    <div class="section user-profile" data-testid="user-profile">
        <h2>User Profile</h2>
        <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICA8Y2lyY2xlIGN4PSI1MCIgY3k9IjUwIiByPSI0MCIgZmlsbD0iIzY2NzMiLz4KICA8dGV4dCB4PSI1MCIgeT0iNTUiIGZvcnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNCIgZmlsbD0id2hpdGUiIHRleHQtYW5jaG9yPSJtaWRkbGUiPlVzZXI8L3RleHQ+Cjwvc3ZnPgo="
            alt="Profile Picture"
            class="profile-picture" />

        <h3 title="User's Name">Jane Doe</h3>
        <p data-testid="user-email">jane.doe@example.com</p>
        <p>This is a simple paragraph with some text content.</p>

        <button type="button" role="button">Edit Profile</button>
    </div>

    <!-- Text Content Section -->
    <div class="section">
        <h2>Text Content Examples</h2>
        <p>This is a simple paragraph</p>
        <div>This is a div with text content</div>
        <span>This is a span element</span>
    </div>

    <!-- Multiple Items for Collection Testing -->
    <div class="section" data-testid="counter-container">
        <h2>Collection Items</h2>
        <div class="counter-item">Item 1</div>
        <div class="counter-item">Item 2</div>
        <div class="counter-item">Item 3</div>
    </div>

    <!-- Multiple Items with Different Test IDs -->
    <div class="section">
        <h2>Nth Items</h2>
        <div data-testid="nth-item" class="nth-item">Item 1</div>
        <div data-testid="nth-item" class="nth-item">Item 2</div>
        <div data-testid="nth-item" class="nth-item">Item 3</div>
    </div>

    <!-- First/Last Items -->
    <div class="section">
        <h2>First/Last Items</h2>
        <div data-testid="first-item" class="first-item">Item 1</div>
        <div data-testid="first-item" class="first-item">Item 2</div>
        <div data-testid="first-item" class="first-item">Item 3</div>

        <div data-testid="last-item" class="last-item">Item 1</div>
        <div data-testid="last-item" class="last-item">Item 2</div>
        <div data-testid="last-item" class="last-item">Item 3</div>
    </div>

    <!-- Text Content Items -->
    <div class="section">
        <h2>Text Content Items</h2>
        <div data-testid="text-item" class="text-item">Text 1</div>
        <div data-testid="text-item" class="text-item">Text 2</div>
        <div data-testid="text-item" class="text-item">Text 3</div>

        <div data-testid="inner-text-item" class="inner-text-item">Inner 1</div>
        <div data-testid="inner-text-item" class="inner-text-item">Inner 2</div>
        <div data-testid="inner-text-item" class="inner-text-item">Inner 3</div>
    </div>

    <!-- Iframe for Frame Testing -->
    <div class="section">
        <h2>Frame Testing</h2>
        <iframe src="about:blank" data-testid="test-iframe" width="300" height="200"></iframe>
    </div>

    <!-- Nested Elements for querySelector Testing -->
    <div class="section" data-testid="nested-container">
        <h2>Nested Elements</h2>
        <div class="parent">
            <p class="child">Child paragraph 1</p>
            <p class="child">Child paragraph 2</p>
            <span class="child">Child span</span>
            <button class="child-button">Nested Button</button>
        </div>
    </div>

    <!-- Elements with Titles -->
    <div class="section">
        <h2>Elements with Titles</h2>
        <div title="Tooltip text">Element with tooltip</div>
        <button title="Button tooltip">Button with title</button>
    </div>

    <!-- Elements for Event Testing -->
    <div class="section">
        <h2>Event Testing</h2>
        <div id="focus-display" data-testid="focus-display" class="p-2 bg-blue-50 border text-sm mb-2">No element focused yet</div>
        <div id="hover-display" data-testid="hover-display" class="p-2 bg-green-50 border text-sm mb-2">No element hovered yet</div>
        <div id="hover-target" data-testid="hover-target" class="p-4 border bg-blue-100 hover:bg-blue-200 transition-colors cursor-pointer mb-4">
            Hover Target
        </div>
        <input id="focus-target" data-testid="focus-target" type="text" class="bg-white text-black p-2 border focus:border-blue-500 mb-4" placeholder="Focus on me" />
        <button id="event-button" data-testid="event-button">Click Me</button>
        <div id="event-result" data-testid="event-result"></div>
    </div>

    <!-- Large content for scrolling tests -->
    <div class="section" style="height: 1000px;">
        <h2>Scroll Testing</h2>
        <div data-testid="scroll-target" style="margin-top: 500px;">
            This element is far down the page for scroll testing
        </div>
    </div>

    <!-- Edge case elements that might return malformed bounding box data -->
    <div class="section">
        <h2>Edge Case Elements for Bounding Box Testing</h2>

        <!-- Elements with unusual CSS properties -->
        <div data-testid="zero-width-element" style="width: 0; height: 0; overflow: hidden;">Zero dimensions element
        </div>
        <div data-testid="negative-margin-element" style="margin: -100px; width: 50px; height: 50px;">Negative margin element
        </div>
        <div data-testid="transform-element" style="transform: scale(0); width: 100px; height: 100px;">Scaled to zero element
        </div>
        <div data-testid="position-absolute-element" style="position: absolute; left: -9999px; top: -9999px;">Off-screen absolute element
        </div>

        <!-- Elements that might cause bounding box calculation issues -->
        <div data-testid="float-element" style="float: left; width: 0.1px; height: 0.1px;">Tiny float element</div>
        <div data-testid="inline-block-element" style="display: inline-block; width: 0; height: 0;">Inline block zero size
        </div>

        <!-- SVG elements that might have edge cases -->
        <svg width="0" height="0" data-testid="zero-svg">
            <rect width="0" height="0" data-testid="zero-rect" />
        </svg>

        <!-- Table elements with edge cases -->
        <table data-testid="empty-table" style="border-collapse: collapse;">
            <tr data-testid="empty-row">
                <td data-testid="empty-cell" style="padding: 0; margin: 0; width: 0; height: 0;"></td>
            </tr>
        </table>

        <!-- Elements with CSS that might cause calculation errors -->
        <div data-testid="overflow-hidden-element" style="overflow: hidden; width: 0; height: 0;">
            <div style="width: 100px; height: 100px;">Hidden content</div>
        </div>

        <!-- Element with complex transform that might break calculations -->
        <div data-testid="complex-transform-element" style="transform: matrix(0, 0, 0, 0, 0, 0); width: 100px; height: 100px;">Matrix transform zero
        </div>

        <!-- Element with visibility hidden but dimensions -->
        <div data-testid="visibility-hidden-element" style="visibility: hidden; width: 100px; height: 100px;">Visibility hidden element
        </div>

        <!-- Element with opacity 0 -->
        <div data-testid="opacity-zero-element" style="opacity: 0; width: 100px; height: 100px;">Opacity zero element
        </div>
    </div>

    <script>
        // Counter for click testing
        let clickCounter = 0;

        function incrementCounter() {
            clickCounter++;
            document.querySelector('[data-testid="click-counter"]').textContent = clickCounter;
        }

        // Counter for double click testing
        let doubleClickCounter = 0;

        function incrementDoubleClickCounter() {
            doubleClickCounter++;
            document.querySelector('[data-testid="dblclick-counter"]').textContent = doubleClickCounter;
        }

        // Focus tracking function
        function trackFocus(elementId) {
            const focusDisplay = document.getElementById('focus-display');
            if (focusDisplay) {
                focusDisplay.textContent = 'Last focused: ' + elementId;
            }
        }

        // Hover tracking function
        function trackHover(elementId) {
            const hoverDisplay = document.getElementById('hover-display');
            if (hoverDisplay) {
                hoverDisplay.textContent = 'Last hovered: ' + elementId;
            }
        }

        // Add some basic interactivity for testing
        document.addEventListener('DOMContentLoaded', function () {
            // Event button click handler
            const eventButton = document.getElementById('event-button');
            const eventResult = document.getElementById('event-result');

            if (eventButton && eventResult) {
                eventButton.addEventListener('click', function () {
                    eventResult.textContent = 'Button was clicked!';
                });
            }

            // Add focus tracking event listeners
            document.getElementById('focus-target').addEventListener('focus', function () {
                trackFocus('focus-target');
            });
            document.getElementById('username').addEventListener('focus', function () {
                trackFocus('username');
            });
            document.getElementById('password').addEventListener('focus', function () {
                trackFocus('password');
            });
            document.getElementById('search').addEventListener('focus', function () {
                trackFocus('search');
            });
            document.getElementById('comments').addEventListener('focus', function () {
                trackFocus('comments');
            });

            // Hover tracking
            const hoverTarget = document.getElementById('hover-target');
            if (hoverTarget) {
                ['mouseenter', 'mouseover', 'pointerenter'].forEach(event => {
                    hoverTarget.addEventListener(event, function () {
                        trackHover('hover-target');
                    });
                });
            }

            // Add global hover detection for Playwright compatibility
            document.addEventListener('mousemove', function (e) {
                if (e.target.id === 'hover-target') {
                    trackHover('hover-target');
                }
            });

            // Make forms more interactive
            const usernameInput = document.getElementById('username');
            if (usernameInput) {
                usernameInput.addEventListener('input', function () {
                    console.log('Username changed to:', this.value);
                });
            }
        });
    </script>
</body>

</html>
