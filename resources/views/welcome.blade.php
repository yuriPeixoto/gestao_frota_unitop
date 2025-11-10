<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="icon" href="{{ asset('images/logoM.svg') }}" type="image/svg+xml">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full" style="background-size: cover; background-position: center;">
    <video autoplay loop muted style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;">
        <source src="{{ asset('images/truck.mp4') }}" type="video/mp4">
    </video>
    <div class="min-h-screen flex items-center justify-center p-4">
        <!-- Card de Login -->
        <div class="w-full max-w-md">
            <div class="glass-effect rounded-3xl p-8">
                <!-- Logo -->
                <div class="flex justify-center mb-6">
                    <img class="h-32 w-auto" src="{{ asset('images/logo.png') }}" alt="Logo">
                </div>

                <!-- Título -->
                <h2 class="text-2xl font-semibold text-center text-gray-800 mb-2">Login</h2>
                <p class="text-center text-gray-600 text-sm mb-8">Entre com seus dados de acesso</p>

                <!-- Formulário -->
                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <!-- Email -->
                    <div>
                        <input id="email" name="email" type="email" required placeholder="Email"
                            class="input-glass w-full px-4 py-3 rounded-xl text-gray-700 placeholder-gray-500
                            focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Senha -->
                    <div>
                        <input id="password" name="password" type="password" required placeholder="Senha"
                            class="input-glass w-full px-4 py-3 rounded-xl text-gray-700 placeholder-gray-500
                            focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Filial -->
                    {{-- <div>
                        <select id="branch_id" name="branch_id" required disabled class="input-glass w-full px-4 py-3 rounded-xl text-gray-700
                            focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Selecione a Filial</option>
                        </select>
                    </div> --}}

                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center">
                            <input type="checkbox" name="remember" id="remember_me"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 rounded">
                            <label for="remember_me" class="ml-2 text-gray-700">Lembrar-me</label>

                            <head>
                                <meta charset="utf-8">
                                <meta name="viewport" content="width=device-width, initial-scale=1">

                                <title>{{ config('app.name', 'Laravel') }}</title>

                                <!-- Fonts -->
                                <link rel="preconnect" href="https://fonts.bunny.net">
                                <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap"
                                    rel="stylesheet" />

                                <!-- Styles / Scripts -->
                                @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
                                    @vite(['resources/css/app.css', 'resources/js/app.js'])
                                @else
                                    <style>
                                        /* ! tailwindcss v3.4.1 | MIT License | https://tailwindcss.com */
                                        *,
                                        ::after,
                                        ::before {
                                            box-sizing: border-box;
                                            border-width: 0;
                                            border-style: solid;
                                            border-color: #e5e7eb
                                        }

                                        ::after,
                                        ::before {
                                            --tw-content: ''
                                        }

                                        :host,
                                        html {
                                            line-height: 1.5;
                                            -webkit-text-size-adjust: 100%;
                                            -moz-tab-size: 4;
                                            tab-size: 4;
                                            font-family: Figtree, ui-sans-serif, system-ui, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji;
                                            font-feature-settings: normal;
                                            font-variation-settings: normal;
                                            -webkit-tap-highlight-color: transparent
                                        }

                                        body {
                                            margin: 0;
                                            line-height: inherit
                                        }

                                        hr {
                                            height: 0;
                                            color: inherit;
                                            border-top-width: 1px
                                        }

                                        abbr:where([title]) {
                                            -webkit-text-decoration: underline dotted;
                                            text-decoration: underline dotted
                                        }

                                        h1,
                                        h2,
                                        h3,
                                        h4,
                                        h5,
                                        h6 {
                                            font-size: inherit;
                                            font-weight: inherit
                                        }

                                        a {
                                            color: inherit;
                                            text-decoration: inherit
                                        }

                                        b,
                                        strong {
                                            font-weight: bolder
                                        }

                                        code,
                                        kbd,
                                        pre,
                                        samp {
                                            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
                                            font-feature-settings: normal;
                                            font-variation-settings: normal;
                                            font-size: 1em
                                        }

                                        small {
                                            font-size: 80%
                                        }

                                        sub,
                                        sup {
                                            font-size: 75%;
                                            line-height: 0;
                                            position: relative;
                                            vertical-align: baseline
                                        }

                                        sub {
                                            bottom: -.25em
                                        }

                                        sup {
                                            top: -.5em
                                        }

                                        table {
                                            text-indent: 0;
                                            border-color: inherit;
                                            border-collapse: collapse
                                        }

                                        button,
                                        input,
                                        optgroup,
                                        select,
                                        textarea {
                                            font-family: inherit;
                                            font-feature-settings: inherit;
                                            font-variation-settings: inherit;
                                            font-size: 100%;
                                            font-weight: inherit;
                                            line-height: inherit;
                                            color: inherit;
                                            margin: 0;
                                            padding: 0
                                        }

                                        button,
                                        select {
                                            text-transform: none
                                        }

                                        [type=button],
                                        [type=reset],
                                        [type=submit],
                                        button {
                                            -webkit-appearance: button;
                                            background-color: transparent;
                                            background-image: none
                                        }

                                        :-moz-focusring {
                                            outline: auto
                                        }

                                        :-moz-ui-invalid {
                                            box-shadow: none
                                        }

                                        progress {
                                            vertical-align: baseline
                                        }

                                        ::-webkit-inner-spin-button,
                                        ::-webkit-outer-spin-button {
                                            height: auto
                                        }

                                        [type=search] {
                                            -webkit-appearance: textfield;
                                            outline-offset: -2px
                                        }

                                        ::-webkit-search-decoration {
                                            -webkit-appearance: none
                                        }

                                        ::-webkit-file-upload-button {
                                            -webkit-appearance: button;
                                            font: inherit
                                        }

                                        summary {
                                            display: list-item
                                        }

                                        blockquote,
                                        dd,
                                        dl,
                                        figure,
                                        h1,
                                        h2,
                                        h3,
                                        h4,
                                        h5,
                                        h6,
                                        hr,
                                        p,
                                        pre {
                                            margin: 0
                                        }

                                        fieldset {
                                            margin: 0;
                                            padding: 0
                                        }

                                        legend {
                                            padding: 0
                                        }

                                        menu,
                                        ol,
                                        ul {
                                            list-style: none;
                                            margin: 0;
                                            padding: 0
                                        }

                                        dialog {
                                            padding: 0
                                        }

                                        textarea {
                                            resize: vertical
                                        }

                                        input::placeholder,
                                        textarea::placeholder {
                                            opacity: 1;
                                            color: #9ca3af
                                        }

                                        [role=button],
                                        button {
                                            cursor: pointer
                                        }

                                        :disabled {
                                            cursor: default
                                        }

                                        audio,
                                        canvas,
                                        embed,
                                        iframe,
                                        img,
                                        object,
                                        svg,
                                        video {
                                            display: block;
                                            vertical-align: middle
                                        }

                                        img,
                                        video {
                                            max-width: 100%;
                                            height: auto
                                        }

                                        [hidden] {
                                            display: none
                                        }

                                        *,
                                        ::before,
                                        ::after {
                                            --tw-border-spacing-x: 0;
                                            --tw-border-spacing-y: 0;
                                            --tw-translate-x: 0;
                                            --tw-translate-y: 0;
                                            --tw-rotate: 0;
                                            --tw-skew-x: 0;
                                            --tw-skew-y: 0;
                                            --tw-scale-x: 1;
                                            --tw-scale-y: 1;
                                            --tw-pan-x: ;
                                            --tw-pan-y: ;
                                            --tw-pinch-zoom: ;
                                            --tw-scroll-snap-strictness: proximity;
                                            --tw-gradient-from-position: ;
                                            --tw-gradient-via-position: ;
                                            --tw-gradient-to-position: ;
                                            --tw-ordinal: ;
                                            --tw-slashed-zero: ;
                                            --tw-numeric-figure: ;
                                            --tw-numeric-spacing: ;
                                            --tw-numeric-fraction: ;
                                            --tw-ring-inset: ;
                                            --tw-ring-offset-width: 0px;
                                            --tw-ring-offset-color: #fff;
                                            --tw-ring-color: rgb(59 130 246 / 0.5);
                                            --tw-ring-offset-shadow: 0 0 #0000;
                                            --tw-ring-shadow: 0 0 #0000;
                                            --tw-shadow: 0 0 #0000;
                                            --tw-shadow-colored: 0 0 #0000;
                                            --tw-blur: ;
                                            --tw-brightness: ;
                                            --tw-contrast: ;
                                            --tw-grayscale: ;
                                            --tw-hue-rotate: ;
                                            --tw-invert: ;
                                            --tw-saturate: ;
                                            --tw-sepia: ;
                                            --tw-drop-shadow: ;
                                            --tw-backdrop-blur: ;
                                            --tw-backdrop-brightness: ;
                                            --tw-backdrop-contrast: ;
                                            --tw-backdrop-grayscale: ;
                                            --tw-backdrop-hue-rotate: ;
                                            --tw-backdrop-invert: ;
                                            --tw-backdrop-opacity: ;
                                            --tw-backdrop-saturate: ;
                                            --tw-backdrop-sepia:
                                        }

                                        ::backdrop {
                                            --tw-border-spacing-x: 0;
                                            --tw-border-spacing-y: 0;
                                            --tw-translate-x: 0;
                                            --tw-translate-y: 0;
                                            --tw-rotate: 0;
                                            --tw-skew-x: 0;
                                            --tw-skew-y: 0;
                                            --tw-scale-x: 1;
                                            --tw-scale-y: 1;
                                            --tw-pan-x: ;
                                            --tw-pan-y: ;
                                            --tw-pinch-zoom: ;
                                            --tw-scroll-snap-strictness: proximity;
                                            --tw-gradient-from-position: ;
                                            --tw-gradient-via-position: ;
                                            --tw-gradient-to-position: ;
                                            --tw-ordinal: ;
                                            --tw-slashed-zero: ;
                                            --tw-numeric-figure: ;
                                            --tw-numeric-spacing: ;
                                            --tw-numeric-fraction: ;
                                            --tw-ring-inset: ;
                                            --tw-ring-offset-width: 0px;
                                            --tw-ring-offset-color: #fff;
                                            --tw-ring-color: rgb(59 130 246 / 0.5);
                                            --tw-ring-offset-shadow: 0 0 #0000;
                                            --tw-ring-shadow: 0 0 #0000;
                                            --tw-shadow: 0 0 #0000;
                                            --tw-shadow-colored: 0 0 #0000;
                                            --tw-blur: ;
                                            --tw-brightness: ;
                                            --tw-contrast: ;
                                            --tw-grayscale: ;
                                            --tw-hue-rotate: ;
                                            --tw-invert: ;
                                            --tw-saturate: ;
                                            --tw-sepia: ;
                                            --tw-drop-shadow: ;
                                            --tw-backdrop-blur: ;
                                            --tw-backdrop-brightness: ;
                                            --tw-backdrop-contrast: ;
                                            --tw-backdrop-grayscale: ;
                                            --tw-backdrop-hue-rotate: ;
                                            --tw-backdrop-invert: ;
                                            --tw-backdrop-opacity: ;
                                            --tw-backdrop-saturate: ;
                                            --tw-backdrop-sepia:
                                        }

                                        .absolute {
                                            position: absolute
                                        }

                                        .relative {
                                            position: relative
                                        }

                                        .-left-20 {
                                            left: -5rem
                                        }

                                        .top-0 {
                                            top: 0px
                                        }

                                        .-bottom-16 {
                                            bottom: -4rem
                                        }

                                        .-left-16 {
                                            left: -4rem
                                        }

                                        .-mx-3 {
                                            margin-left: -0.75rem;
                                            margin-right: -0.75rem
                                        }

                                        .mt-4 {
                                            margin-top: 1rem
                                        }

                                        .mt-6 {
                                            margin-top: 1.5rem
                                        }

                                        .flex {
                                            display: flex
                                        }

                                        .grid {
                                            display: grid
                                        }

                                        .hidden {
                                            display: none
                                        }

                                        .aspect-video {
                                            aspect-ratio: 16 / 9
                                        }

                                        .size-12 {
                                            width: 3rem;
                                            height: 3rem
                                        }

                                        .size-5 {
                                            width: 1.25rem;
                                            height: 1.25rem
                                        }

                                        .size-6 {
                                            width: 1.5rem;
                                            height: 1.5rem
                                        }

                                        .h-12 {
                                            height: 3rem
                                        }

                                        .h-40 {
                                            height: 10rem
                                        }

                                        .h-full {
                                            height: 100%
                                        }

                                        .min-h-screen {
                                            min-height: 100vh
                                        }

                                        .w-full {
                                            width: 100%
                                        }

                                        .w-\[calc\(100\%\+8rem\)\] {
                                            width: calc(100% + 8rem)
                                        }

                                        .w-auto {
                                            width: auto
                                        }

                                        .max-w-\[877px\] {
                                            max-width: 877px
                                        }

                                        .max-w-2xl {
                                            max-width: 42rem
                                        }

                                        .flex-1 {
                                            flex: 1 1 0%
                                        }

                                        .shrink-0 {
                                            flex-shrink: 0
                                        }

                                        .grid-cols-2 {
                                            grid-template-columns: repeat(2, minmax(0, 1fr))
                                        }

                                        .flex-col {
                                            flex-direction: column
                                        }

                                        .items-start {
                                            align-items: flex-start
                                        }

                                        .items-center {
                                            align-items: center
                                        }

                                        .items-stretch {
                                            align-items: stretch
                                        }

                                        .justify-end {
                                            justify-content: flex-end
                                        }

                                        .justify-center {
                                            justify-content: center
                                        }

                                        .gap-2 {
                                            gap: 0.5rem
                                        }

                                        .gap-4 {
                                            gap: 1rem
                                        }

                                        .gap-6 {
                                            gap: 1.5rem
                                        }

                                        .self-center {
                                            align-self: center
                                        }

                                        .overflow-hidden {
                                            overflow: hidden
                                        }

                                        .rounded-\[10px\] {
                                            border-radius: 10px
                                        }

                                        .rounded-full {
                                            border-radius: 9999px
                                        }

                                        .rounded-lg {
                                            border-radius: 0.5rem
                                        }

                                        .rounded-md {
                                            border-radius: 0.375rem
                                        }

                                        .rounded-sm {
                                            border-radius: 0.125rem
                                        }

                                        .bg-\[\#FF2D20\]\/10 {
                                            background-color: rgb(255 45 32 / 0.1)
                                        }

                                        .bg-white {
                                            --tw-bg-opacity: 1;
                                            background-color: rgb(255 255 255 / var(--tw-bg-opacity))
                                        }

                                        .bg-gradient-to-b {
                                            background-image: linear-gradient(to bottom, var(--tw-gradient-stops))
                                        }

                                        .from-transparent {
                                            --tw-gradient-from: transparent var(--tw-gradient-from-position);
                                            --tw-gradient-to: rgb(0 0 0 / 0) var(--tw-gradient-to-position);
                                            --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to)
                                        }

                                        .via-white {
                                            --tw-gradient-to: rgb(255 255 255 / 0) var(--tw-gradient-to-position);
                                            --tw-gradient-stops: var(--tw-gradient-from), #fff var(--tw-gradient-via-position), var(--tw-gradient-to)
                                        }

                                        .to-white {
                                            --tw-gradient-to: #fff var(--tw-gradient-to-position)
                                        }

                                        .stroke-\[\#FF2D20\] {
                                            stroke: #FF2D20
                                        }

                                        .object-cover {
                                            object-fit: cover
                                        }

                                        .object-top {
                                            object-position: top
                                        }

                                        .p-6 {
                                            padding: 1.5rem
                                        }

                                        .px-6 {
                                            padding-left: 1.5rem;
                                            padding-right: 1.5rem
                                        }

                                        .py-10 {
                                            padding-top: 2.5rem;
                                            padding-bottom: 2.5rem
                                        }

                                        .px-3 {
                                            padding-left: 0.75rem;
                                            padding-right: 0.75rem
                                        }

                                        .py-16 {
                                            padding-top: 4rem;
                                            padding-bottom: 4rem
                                        }

                                        .py-2 {
                                            padding-top: 0.5rem;
                                            padding-bottom: 0.5rem
                                        }

                                        .pt-3 {
                                            padding-top: 0.75rem
                                        }

                                        .text-center {
                                            text-align: center
                                        }

                                        .font-sans {
                                            font-family: Figtree, ui-sans-serif, system-ui, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji
                                        }

                                        .text-sm {
                                            font-size: 0.875rem;
                                            line-height: 1.25rem
                                        }

                                        .text-sm\/relaxed {
                                            font-size: 0.875rem;
                                            line-height: 1.625
                                        }

                                        .text-xl {
                                            font-size: 1.25rem;
                                            line-height: 1.75rem
                                        }

                                        .font-semibold {
                                            font-weight: 600
                                        }

                                        .text-black {
                                            --tw-text-opacity: 1;
                                            color: rgb(0 0 0 / var(--tw-text-opacity))
                                        }

                                        .text-white {
                                            --tw-text-opacity: 1;
                                            color: rgb(255 255 255 / var(--tw-text-opacity))
                                        }

                                        .underline {
                                            -webkit-text-decoration-line: underline;
                                            text-decoration-line: underline
                                        }

                                        .antialiased {
                                            -webkit-font-smoothing: antialiased;
                                            -moz-osx-font-smoothing: grayscale
                                        }

                                        .shadow-\[0px_14px_34px_0px_rgba\(0\2c 0\2c 0\2c 0\.08\)\] {
                                            --tw-shadow: 0px 14px 34px 0px rgba(0, 0, 0, 0.08);
                                            --tw-shadow-colored: 0px 14px 34px 0px var(--tw-shadow-color);
                                            box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow)
                                        }

                                        .ring-1 {
                                            --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
                                            --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(1px + var(--tw-ring-offset-width)) var(--tw-ring-color);
                                            box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000)
                                        }

                                        .ring-transparent {
                                            --tw-ring-color: transparent
                                        }

                                        .ring-white\/\[0\.05\] {
                                            --tw-ring-color: rgb(255 255 255 / 0.05)
                                        }

                                        .drop-shadow-\[0px_4px_34px_rgba\(0\2c 0\2c 0\2c 0\.06\)\] {
                                            --tw-drop-shadow: drop-shadow(0px 4px 34px rgba(0, 0, 0, 0.06));
                                            filter: var(--tw-blur) var(--tw-brightness) var(--tw-contrast) var(--tw-grayscale) var(--tw-hue-rotate) var(--tw-invert) var(--tw-saturate) var(--tw-sepia) var(--tw-drop-shadow)
                                        }

                                        .drop-shadow-\[0px_4px_34px_rgba\(0\2c 0\2c 0\2c 0\.25\)\] {
                                            --tw-drop-shadow: drop-shadow(0px 4px 34px rgba(0, 0, 0, 0.25));
                                            filter: var(--tw-blur) var(--tw-brightness) var(--tw-contrast) var(--tw-grayscale) var(--tw-hue-rotate) var(--tw-invert) var(--tw-saturate) var(--tw-sepia) var(--tw-drop-shadow)
                                        }

                                        .transition {
                                            transition-property: color, background-color, border-color, fill, stroke, opacity, box-shadow, transform, filter, -webkit-text-decoration-color, -webkit-backdrop-filter;
                                            transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
                                            transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter, -webkit-text-decoration-color, -webkit-backdrop-filter;
                                            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
                                            transition-duration: 150ms
                                        }

                                        .duration-300 {
                                            transition-duration: 300ms
                                        }

                                        .selection\:bg-\[\#FF2D20\] *::selection {
                                            --tw-bg-opacity: 1;
                                            background-color: rgb(255 45 32 / var(--tw-bg-opacity))
                                        }

                                        .selection\:text-white *::selection {
                                            --tw-text-opacity: 1;
                                            color: rgb(255 255 255 / var(--tw-text-opacity))
                                        }

                                        .selection\:bg-\[\#FF2D20\]::selection {
                                            --tw-bg-opacity: 1;
                                            background-color: rgb(255 45 32 / var(--tw-bg-opacity))
                                        }

                                        .selection\:text-white::selection {
                                            --tw-text-opacity: 1;
                                            color: rgb(255 255 255 / var(--tw-text-opacity))
                                        }

                                        .hover\:text-black:hover {
                                            --tw-text-opacity: 1;
                                            color: rgb(0 0 0 / var(--tw-text-opacity))
                                        }

                                        .hover\:text-black\/70:hover {
                                            color: rgb(0 0 0 / 0.7)
                                        }

                                        .hover\:ring-black\/20:hover {
                                            --tw-ring-color: rgb(0 0 0 / 0.2)
                                        }

                                        .focus\:outline-none:focus {
                                            outline: 2px solid transparent;
                                            outline-offset: 2px
                                        }

                                        .focus-visible\:ring-1:focus-visible {
                                            --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
                                            --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(1px + var(--tw-ring-offset-width)) var(--tw-ring-color);
                                            box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000)
                                        }

                                        .focus-visible\:ring-\[\#FF2D20\]:focus-visible {
                                            --tw-ring-opacity: 1;
                                            --tw-ring-color: rgb(255 45 32 / var(--tw-ring-opacity))
                                        }

                                        @media (min-width: 640px) {
                                            .sm\:size-16 {
                                                width: 4rem;
                                                height: 4rem
                                            }

                                            .sm\:size-6 {
                                                width: 1.5rem;
                                                height: 1.5rem
                                            }

                                            .sm\:pt-5 {
                                                padding-top: 1.25rem
                                            }
                                        }

                                        @media (min-width: 768px) {
                                            .md\:row-span-3 {
                                                grid-row: span 3 / span 3
                                            }
                                        }

                                        @media (min-width: 1024px) {
                                            .lg\:col-start-2 {
                                                grid-column-start: 2
                                            }

                                            .lg\:h-16 {
                                                height: 4rem
                                            }

                                            .lg\:max-w-7xl {
                                                max-width: 80rem
                                            }

                                            .lg\:grid-cols-3 {
                                                grid-template-columns: repeat(3, minmax(0, 1fr))
                                            }

                                            .lg\:grid-cols-2 {
                                                grid-template-columns: repeat(2, minmax(0, 1fr))
                                            }

                                            .lg\:flex-col {
                                                flex-direction: column
                                            }

                                            .lg\:items-end {
                                                align-items: flex-end
                                            }

                                            .lg\:justify-center {
                                                justify-content: center
                                            }

                                            .lg\:gap-8 {
                                                gap: 2rem
                                            }

                                            .lg\:p-10 {
                                                padding: 2.5rem
                                            }

                                            .lg\:pb-10 {
                                                padding-bottom: 2.5rem
                                            }

                                            .lg\:pt-0 {
                                                padding-top: 0px
                                            }

                                            .lg\:text-\[\#FF2D20\] {
                                                --tw-text-opacity: 1;
                                                color: rgb(255 45 32 / var(--tw-text-opacity))
                                            }
                                        }

                                        @media (prefers-color-scheme: dark) {
                                            .dark\:block {
                                                display: block
                                            }

                                            .dark\:hidden {
                                                display: none
                                            }

                                            .dark\:bg-black {
                                                --tw-bg-opacity: 1;
                                                background-color: rgb(0 0 0 / var(--tw-bg-opacity))
                                            }

                                            .dark\:bg-zinc-900 {
                                                --tw-bg-opacity: 1;
                                                background-color: rgb(24 24 27 / var(--tw-bg-opacity))
                                            }

                                            .dark\:via-zinc-900 {
                                                --tw-gradient-to: rgb(24 24 27 / 0) var(--tw-gradient-to-position);
                                                --tw-gradient-stops: var(--tw-gradient-from), #18181b var(--tw-gradient-via-position), var(--tw-gradient-to)
                                            }

                                            .dark\:to-zinc-900 {
                                                --tw-gradient-to: #18181b var(--tw-gradient-to-position)
                                            }

                                            .dark\:text-white\/50 {
                                                color: rgb(255 255 255 / 0.5)
                                            }

                                            .dark\:text-white {
                                                --tw-text-opacity: 1;
                                                color: rgb(255 255 255 / var(--tw-text-opacity))
                                            }

                                            .dark\:text-white\/70 {
                                                color: rgb(255 255 255 / 0.7)
                                            }

                                            .dark\:ring-zinc-800 {
                                                --tw-ring-opacity: 1;
                                                --tw-ring-color: rgb(39 39 42 / var(--tw-ring-opacity))
                                            }

                                            .dark\:hover\:text-white:hover {
                                                --tw-text-opacity: 1;
                                                color: rgb(255 255 255 / var(--tw-text-opacity))
                                            }

                                            .dark\:hover\:text-white\/70:hover {
                                                color: rgb(255 255 255 / 0.7)
                                            }

                                            .dark\:hover\:text-white\/80:hover {
                                                color: rgb(255 255 255 / 0.8)
                                            }

                                            .dark\:hover\:ring-zinc-700:hover {
                                                --tw-ring-opacity: 1;
                                                --tw-ring-color: rgb(63 63 70 / var(--tw-ring-opacity))
                                            }

                                            .dark\:focus-visible\:ring-\[\#FF2D20\]:focus-visible {
                                                --tw-ring-opacity: 1;
                                                --tw-ring-color: rgb(255 45 32 / var(--tw-ring-opacity))
                                            }

                                            .dark\:focus-visible\:ring-white:focus-visible {
                                                --tw-ring-opacity: 1;
                                                --tw-ring-color: rgb(255 255 255 / var(--tw-ring-opacity))
                                            }
                                        }
                                    </style>
                                @endif
                            </head>

                            <body class="font-sans antialiased dark:bg-black dark:text-white/50 min-h-screen">
                                <div
                                    class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50 min-h-screen flex flex-col">
                                    <!-- Header Fixo -->
                                    <header class="fixed top-0 w-full bg-slate-700 text-white dark:bg-black z-50">
                                        <div class="max-w-7xl mx-auto px-6">
                                            <div class="grid grid-cols-2 items-center gap-2 py-4 lg:grid-cols-3">
                                                <div class="flex lg:justify-center lg:col-start-2">
                                                    <img src="{{ Vite::asset('resources/images/logoM.svg') }}"
                                                        alt="Logo Unitop"
                                                        class="w-auto max-w-[157px] h-10 sm:h-14 transition-all dark:brightness-100" />
                                                </div>
                                                @if (Route::has('login'))
                                                    <nav class="-mx-3 flex flex-1 justify-end">
                                                        @auth
                                                            <a href="{{ route('admin.dashboard') }}"
                                                                class="rounded-md px-3 py-2 text-white ring-1 ring-transparent transition hover:text-white/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white">
                                                                Dashboard
                                                            </a>
                                                        @else
                                                            <a href="{{ route('login') }}"
                                                                class="rounded-md px-3 py-2 text-white ring-1 ring-transparent transition hover:text-white/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white">
                                                                Log in
                                                            </a>
                                                            @if (Route::has('register'))
                                                                <a href="{{ route('register') }}"
                                                                    class="rounded-md px-3 py-2 text-white ring-1 ring-transparent transition hover:text-white/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white">
                                                                    Register
                                                                </a>
                                                            @endif
                                                        @endauth
                                                    </nav>
                                                @endif
                                            </div>

                                            <button type="submit"
                                                class="w-full bg-blue-600 text-white rounded-xl py-3 px-4 hover:bg-blue-700
                        focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
                        transition-colors duration-200">
                                                Entrar
                                            </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // document.getElementById('email').addEventListener('input', async function() {
        //     const email = this.value;
        //     const branchSelect = document.getElementById('branch_id');

        //     branchSelect.innerHTML = '<option value="">Selecione a Filial</option>';
        //     branchSelect.disabled = true;

        //     if (email) {
        //         try {
        //             const response = await fetch(`/get-user-branch?email=${encodeURIComponent(email)}`);
        //             const data = await response.json();

        //             if (data.branch) {
        //                 const option = new Option(data.branch.name, data.branch.id);
        //                 branchSelect.add(option);
        //                 branchSelect.disabled = false;
        //                 branchSelect.value = data.branch.id;
        //             }
        //         } catch (error) {
        //             console.error('Erro ao carregar filial:', error);
        //         }
        //     }
        // });
    </script>
</body>

</html>
