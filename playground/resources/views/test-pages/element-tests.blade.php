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

        input, textarea, select, button {
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

        .counter-item, .text-item, .inner-text-item, .nth-item, .first-item, .last-item {
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
        </div>

        <div class="form-group">
            <button type="button" data-testid="click-button" onclick="incrementCounter()">Click Counter</button>
            <span data-testid="click-counter">0</span>
        </div>

        <div class="form-group">
            <input type="text" data-testid="text-input" placeholder="Text input">
            <input type="text" data-testid="prefilled-input" value="Prefilled value">
        </div>

        <div class="form-group">
            <label for="comments">Comments</label>
            <textarea id="comments" name="comments" placeholder="Enter your comments here"></textarea>
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
        <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICA8Y2lyY2xlIGN4PSI1MCIgY3k9IjUwIiByPSI0MCIgZmlsbD0iIzY2NzMiLz4KICA8dGV4dCB4PSI1MCIgeT0iNTUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNCIgZmlsbD0id2hpdGUiIHRleHQtYW5jaG9yPSJtaWRkbGUiPlVzZXI8L3RleHQ+Cjwvc3ZnPgo="
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

    <script>
        // Counter for click testing
        let clickCounter = 0;

        function incrementCounter() {
            clickCounter++;
            document.querySelector('[data-testid="click-counter"]').textContent = clickCounter;
        }

        // Add some basic interactivity for testing
        document.addEventListener('DOMContentLoaded', function() {
            // Event button click handler
            const eventButton = document.getElementById('event-button');
            const eventResult = document.getElementById('event-result');

            if (eventButton && eventResult) {
                eventButton.addEventListener('click', function() {
                    eventResult.textContent = 'Button was clicked!';
                });
            }

            // Make forms more interactive
            const usernameInput = document.getElementById('username');
            if (usernameInput) {
                usernameInput.addEventListener('input', function() {
                    console.log('Username changed to:', this.value);
                });
            }
        });
    </script>
</body>
</html>
