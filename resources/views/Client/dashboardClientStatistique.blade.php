<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Client</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.min.js"
            integrity="sha512-L0Shl7nXXzIlBSUUPpxrokqq4ojqgZFQczTYlGjzONGTDAcLremjwaWv5A+EDLnxhQzY5xUZPWLOLqYRkY0Cbw=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">

<div class="flex flex-col">
    @include('menu.menuClient')

    <div class="flex-1 md:ml-24 p-6 parentStat">
        <!-- divStat1 -->
        <div class="divStat1 card w-full flex items-center justify-center mx-auto p-6 bg-white rounded-lg border shadow-md">
            <form id="yearWeekForm" action="{{ route('dashboardClientStatistique') }}" method="get"
                  class="flex items-center justify-center w-full">
                <!-- Conteneur des champs année et semaine sur la même ligne avec ESPACE -->
                <div class="flex items-center space-x-52">
                    <!-- Bloc de sélection de l'année -->
                    <div class="text-center">
                        <label for="yearSelect" class="block text-2xl font-bold text-gray-700">Sélectionnez
                            l'année</label>
                        <select name="year" id="yearSelect" class="custom-select w-32 text-center"
                                onchange="updateWeekToCurrent()">
                            @foreach($years as $year)
                                <option value="{{ $year }}"
                                        @if($year == $selectedYear) selected @endif>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Bloc de sélection de la semaine -->
                    <div>
                        <div class="text-center">
                            <label for="weekSelect" class="block text-2xl font-bold text-gray-700">Sélectionnez la
                                semaine</label>
                        </div>
                        <div class="flex items-center justify-center">
                            <input type="hidden" name="week" id="weekInput" value="{{ $selectedWeek }}">
                            <button type="button" onclick="changeWeek(-1)"
                                    class="bg-transparent hover:bg-gray-400 text-red-600 font-bold py-2 px-4 rounded-l">
                                &lt;
                            </button>
                            <span id="weekDisplay" class="bg-transparent text-red-600 font-bold py-2 px-4">
                        {{ $selectedWeek ? $selectedWeek : date('W') }}
                    </span>
                            <button type="button" onclick="changeWeek(1)"
                                    class="bg-transparent hover:bg-gray-400 text-red-600 font-bold py-2 px-4 rounded-r">
                                &gt;
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- divStat2-->
        <!-- Compteur de nombre de vues total -->
        <div class="divStat2 card w-full p-6 bg-white rounded-lg border shadow-md">
            <div class="mb-4">
                <p class="text-center font-bold text-2xl">Nombre de vues</p>
                <p class="text-center text-xl">Global</p>
            </div>
            <div class="flex flex-grow justify-center items-center">
                <h1 class="text-7xl font-bold text-red-900">{{ $totalViewsCard }}</h1>
            </div>
        </div>
        <!-- divStat3-->
        <!-- Compteur de nombre de vues total semaine -->
        <div class="divStat3 card w-full p-6 bg-white rounded-lg border shadow-md">
            <div class="mb-4">
                <p class="text-center font-bold text-2xl">Nombre de vues</p>
                <p class="text-center text-xl">Semaine</p>
            </div>
            <div class="flex flex-grow justify-center items-center">
                @if($selectedWeek)
                    <h1 class="text-7xl font-bold text-red-900">{{ $weeklyViews[$selectedWeek] ?? 0 }}</h1>
                @else
                    @foreach($weeklyViews as $week => $count)
                        <p>Semaine {{ $week }} : {{ $count }} vues</p>
                    @endforeach
                @endif
            </div>
        </div>

        <!-- divStat4-->
        <!-- Graph -->
        <div class="divStat4 card w-full p-6 bg-white rounded-lg border shadow-md justify-center items-center mx-auto">
            <!-- titre du graph-->
            <div class="mb-4">
                <p class="text-center font-bold text-2xl">Nombres de vues</p>
                <p class="text-center text-xl">Par employes</p>
            </div>

            @if(empty($employerData['datasets'][0]['data']))
                <p>Aucune donnée disponible pour le graphique.</p>
            @else
                <div class="chart-container">
                    <canvas id="yearChart" class="chart-canvas"></canvas>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const employerData = @json($employerData);
                        const ctxYear = document.getElementById('yearChart').getContext('2d');

                        // employer chart
                        let employe = new Chart(ctxYear, {
                            type: 'pie',
                            data: employerData,
                            options: {
                                scales: {
                                    x: {
                                        display: false
                                    },
                                    y: {
                                        display: false
                                    }
                                }
                            }
                        });
                    });
                </script>
            @endif
        </div>
        <!-- divStat5-->
        <!-- Graph nombre de vue par mois -->
        <div class="divStat5 card w-full p-6 bg-white rounded-lg border shadow-md">
            <div class="mb-4">
                <p class="text-center font-bold text-2xl">Nombres de vues</p>
                <p class="text-center text-xl">Par mois</p>
            </div>

            <!-- Débogage des données -->

            <!-- Vérification des données -->
            @if(empty($monthlyData['datasets'][0]['data']))
                <p>Aucune donnée disponible pour le graphique.</p>
            @else
                <div class="chart-container">
                    <canvas id="monthChart" class="chart-canvas"></canvas>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const $monthlyData = @json($monthlyData);
                        const ctxMonth = document.getElementById('monthChart').getContext('2d');

                        // Création du graphique
                        let month = new Chart(ctxMonth, {
                            type: 'bar',
                            data: $monthlyData,
                            options: {
                                scales: {
                                    x: {
                                        display: true
                                    },
                                    y: {
                                        display: true
                                    }
                                }
                            }
                        });
                    });
                </script>
            @endif
        </div>
    </div>
</div>
</body>

<script>
    function changeWeek(direction) {
        let weekInput = document.getElementById('weekInput');
        let weekDisplay = document.getElementById('weekDisplay');
        let currentWeek = parseInt(weekInput.value) - 1 || new Date().getWeek() - 1;
        let newWeek = currentWeek + direction;

        if (newWeek >= 0 && newWeek < 52) {
            weekInput.value = newWeek + 1;
            weekDisplay.innerText = newWeek + 1;
            document.getElementById('yearWeekForm').submit();
        }
    }

    function updateWeekToCurrent() {
        let weekInput = document.getElementById('weekInput');
        let weekDisplay = document.getElementById('weekDisplay');
        let currentWeek = new Date().getWeek() - 1;

        weekInput.value = currentWeek + 1;
        weekDisplay.innerText = currentWeek + 1;
        document.getElementById('yearWeekForm').submit();
    }

    // Function to get the current week number
    Date.prototype.getWeek = function () {
        var onejan = new Date(this.getFullYear(), 0, 1);
        return Math.ceil((((this - onejan) / 86400000) + onejan.getDay() + 1) / 7);
    };
</script>

</html>
