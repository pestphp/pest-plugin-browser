@extends('layouts.app')
<style>
    #popup-html {
        display: none;
        position: fixed;
        top: 30%;
        left: 50%;
        transform: translate(-50%, -30%);
        background: white;
        border: 1px solid #ccc;
        padding: 20px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        z-index: 1000;
    }

    #overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, 0.5);
        z-index: 999;
    }
</style>
@section('content')
<section class="sm:py-24 md:w-full sm:w-2/3 container max-w-5xl py-12 mx-auto">
    <h1 class="text-3xl font-bold mb-6">Popup Testing Page</h1>

    <!-- Test elements for popup after clicking -->
    <div class="mb-8">
        <button id="triggerBtn">Trigger Popup</button>

        <div id="overlay"></div>
        <div id="popup-html">
            <p>This is a popup message.</p>
            <button id="closeBtn">Close</button>
        </div>
    </div>
</section>
<script>
    const triggerBtn = document.getElementById('triggerBtn');
    const popup = document.getElementById('popup-html');
    const overlay = document.getElementById('overlay');
    const closeBtn = document.getElementById('closeBtn');

    function showPopup(callback) {
        overlay.style.display = 'block';
        popup.style.display = 'block';

        // Simulate "popup ready" after some visual changes
        setTimeout(() => {
            callback(); // Indicate popup is ready, now safe to perform further actions
        }, 100); // This can be 0 if popup is instant
    }

    function performClickAction() {
        console.log("Click action performed inside the popup.");
        // Simulate a click or other logic here
    }

    triggerBtn.addEventListener('click', () => {
        showPopup(() => {
            performClickAction(); // This happens after popup is shown
        });
    });

    closeBtn.addEventListener('click', () => {
        popup.style.display = 'none';
        overlay.style.display = 'none';
    });

    window.addEventListener('DOMContentLoaded', () => {
        showPopup(() => {
            console.log('Popup auto-shown on page load');
        });
    });
</script>
@endsection