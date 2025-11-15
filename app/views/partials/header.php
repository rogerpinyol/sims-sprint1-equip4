<!doctype html>
<html lang="ca">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>EcoMotion</title>
        <!-- Using Tailwind CDN with project palette (no local build required) -->
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            brand: '#DE541E',
                            'brand-variant': '#F37A3D',
                            'accent-success': '#78866B',
                            surface: '#C2B098',
                            'surface-subtle': '#A6B093',
                            neutral: { 900: '#333333' }
                        }
                    }
                }
            }
        </script>
        <link rel="stylesheet" href="/css/app.css">
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
<?php // header partial - session is started in index.php to avoid headers-sent issues ?>