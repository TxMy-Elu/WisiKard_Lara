<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Client Employe</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <style>
        .qr-code-container {
            width: 100px; /* Taille fixe pour le conteneur du QR code */
            height: 100px;
            background-color: #f1f1f1; /* Couleur de fond pour le conteneur */
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden; /* Couper le contenu qui dépasse */
            border-radius: 8px; /* Coins arrondis */
        }

        .qr-code-container img {
            max-width: 100%; /* Assure que l'image s'adapte au conteneur */
            max-height: 100%;
        }
    </style>
</head>
<body class="align-items-center bg-gray-100 w-100">

<div class="flex flex-col md:flex-row">

    @include('menu.menuClient')
    <div class="flex-1 md:ml-24 content">

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                 role="alert">
                <strong class="font-bold">Succès!</strong>
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Erreur!</strong>
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        @if(isset($error))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Erreur!</strong>
                <span class="block sm:inline">{{ $error }}</span>
            </div>
        @endif

        <div class="min-h-screen p-4">
            <div class="flex flex-col md:flex-row justify-between items-center pb-4">
                <form method="GET" action="{{ route('dashboardClientEmploye') }}"
                      class="flex items-center relative w-full md:w-64 mb-4 md:mb-0">
                    <div class="search-icon pl-2">
                        <svg class="w-6 h-6 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                             xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search..."
                           class="p-2 pl-10 border border-gray-900 rounded-lg text-sm flex-grow">
                </form>

                <div class="flex items-center w-full md:w-auto">
                    <a href="{{ route('afficherFormInsEmploye') }}"
                       class="w-full md:w-auto px-4 py-2 border border-gray-900 rounded-lg text-sm flex items-center justify-center hover:bg-gray-900 hover:text-white">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                             xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 4v16m8-8H4"></path>
                        </svg>
                        Ajouter un employe
                    </a>
                </div>
            </div>


            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($employes as $employe)
                    <div class="custom-width bg-white rounded-lg shadow-lg p-4 flex flex-col">
                        <div class="flex justify-between">
                            <div class="flex flex-col">
                                <div class="mb-4">
                                    <p class="text-xl font-semibold">{{ $employe->nom }}</p>
                                </div>
                                <div class="mb-4">
                                    <p class="text-lg text-gray-600">{{ $employe->prenom }}</p>
                                </div>
                                <div class="mb-4">
                                    <p class="text-lg text-gray-500">{{ $employe->fonction }}</p>
                                </div>
                                <div class="mb-4">
                                    <p class="text-sm text-gray-500">{{ $employe->telephone }}</p>
                                </div>
                                <div class="mb-4">
                                    <p class="text-sm text-gray-500">{{ $employe->mail }}</p>
                                </div>
                            </div>
                            <div class="flex justify-center mb-4">
                                <div class="qr-code-container">
                                    <img src="{{ asset("entreprises/{$employe->carte->idCompte}_{$employe->carte->nomEntreprise}/QR_Codes/QR_Code_{$employe->idEmp}.svg") }}"
                                         alt="QR Code" class="max-w-full max-h-full">
                                </div>
                                <div class="flex justify-end ">
                                    <a href="{{ route('refreshQrCodeEmp', ['id' => $employe->carte->idCompte, 'empId' => $employe->idEmp]) }} " class="ml-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                             viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="2"
                                             stroke-linecap="round" stroke-linejoin="round"
                                             class="feather feather-repeat mr-4">
                                            <polyline points="17 1 21 5 17 9"></polyline>
                                            <path d="M3 11V9a4 4 0 0 1 4-4h14"></path>
                                            <polyline points="7 23 3 19 7 15"></polyline>
                                            <path d="M21 13v2a4 4 0 0 1-4 4H3"></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-row-reverse mt-auto pt-4">
                            <form action="{{ route('employe.destroy', $employe->idEmp) }}" method="POST"
                                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet employé ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-full">Supprimer
                                </button>
                            </form>
                            <a href="{{ route('employe.edit', $employe->idEmp) }}" class="bg-indigo-500 text-white px-4 py-2 rounded-full mr-2">Modifier</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

</body>
</html>
