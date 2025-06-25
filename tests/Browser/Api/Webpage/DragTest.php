<?php

declare(strict_types=1);

it('may drag an element to another element', function (): void {
    Route::get('/', fn (): string => '
        <style>
            #source, #target {
                width: 100px;
                height: 100px;
                padding: 10px;
                margin: 10px;
                display: inline-block;
            }
            #source { background-color: #f00; }
            #target { background-color: #0f0; }
        </style>
        <div id="source" draggable="true">Drag me</div>
        <div id="target">Drop here</div>
        <div id="result"></div>
        <script>
            const source = document.getElementById("source");
            const target = document.getElementById("target");
            const result = document.getElementById("result");

            source.addEventListener("dragstart", function(e) {
                e.dataTransfer.setData("text/plain", "dragged");
            });

            target.addEventListener("dragover", function(e) {
                e.preventDefault();
            });

            target.addEventListener("drop", function(e) {
                e.preventDefault();
                const data = e.dataTransfer.getData("text/plain");
                if (data === "dragged") {
                    result.textContent = "Element was dragged and dropped";
                }
            });
        </script>
    ');

    $page = visit('/');

    $page->drag('#source', '#target');

    expect($page->text('#result'))->toBe('Element was dragged and dropped');
});

it('may drag an element to another element using element names', function (): void {
    Route::get('/', fn (): string => '
        <style>
            .draggable, .droppable {
                width: 100px;
                height: 100px;
                padding: 10px;
                margin: 10px;
                display: inline-block;
            }
            .draggable { background-color: #f00; }
            .droppable { background-color: #0f0; }
        </style>
        <div class="draggable" id="source" name="source" draggable="true">Drag me</div>
        <div class="droppable" id="target" name="target">Drop here</div>
        <div id="result"></div>
        <script>
            const source = document.getElementById("source");
            const target = document.getElementById("target");
            const result = document.getElementById("result");

            source.addEventListener("dragstart", function(e) {
                e.dataTransfer.setData("text/plain", "dragged");
            });

            target.addEventListener("dragover", function(e) {
                e.preventDefault();
            });

            target.addEventListener("drop", function(e) {
                e.preventDefault();
                const data = e.dataTransfer.getData("text/plain");
                if (data === "dragged") {
                    result.textContent = "Element was dragged and dropped";
                }
            });
        </script>
    ');

    $page = visit('/');

    $page->drag('source', 'target');

    expect($page->text('#result'))->toBe('Element was dragged and dropped');
});
