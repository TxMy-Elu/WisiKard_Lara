<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Client</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .custom-width {
            width: 100%; /* Adjusted to be responsive */
        }

        .search-icon {
            position: absolute;
            left: 0.5rem;
            top: 50%;
            transform: translateY(-50%);
        }

        .box-shadow {
            box-shadow: 2px 2px 2px rgba(0, 0, 0, 1);
        }

        @media (max-width: 768px) {
            .navbar {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                z-index: 1000;
            }

            .content {
                margin-top: 60px; /* Adjust based on the height of the navbar */
            }
        }
    </style>
</head>
<body class="align-items-center bg-gray-100 w-100">

<div class="flex flex-col md:flex-row">
    @include('menuClient')
</div>

</body>
</html>