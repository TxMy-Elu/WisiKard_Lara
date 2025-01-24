<?php
// app/Http/Controllers/Connexion.php

namespace App\Http\Controllers;

use App\Models\Carte;
use App\Models\Compte;
use App\Models\Custom_Link;
use App\Models\Employer;
use App\Models\Rediriger;
use App\Models\Social;
use App\Models\Template;
use App\Models\Vue;
use Illuminate\Http\Request;


class Templates extends Controller
{
    public function afficherTemplates(Request $request)
    {
        // Vérifier si "CompteEmp" est présent dans la requête
        $CompteEmp = $request->query('CompteEmp');
        $idCarte = null;
        $idEmp = null;
        $employe = null;

        if ($CompteEmp) {
            // Si CompteEmp est présent, le split au niveau de la virgule
            [$idCompte, $idEmp] = explode('x', $CompteEmp);

            // Convertir en entier pour s'assurer qu'on travaille avec des ID valides
            $idCompte = (int)$idCompte;
            $idEmp = (int)$idEmp;

            // Récupérer les infos de la carte de visite
            $carte = Carte::where('idCompte', $idCompte)->first();

            //idCarte
            $idCarte = $carte->idCarte ?? null;

            // Récupérer les infos de l'employé en fonction de l'idCarte et idEmp
            $employe = Employer::where('idCarte', $carte->idCarte)->where('idEmp', $idEmp)->first();

            //idTemplate
            $idTemplate = $carte->idTemplate ?? null;


        } else {
            // Sinon, récupérer l'idCompte
            $idCompte = $request->query('idCompte');

            // Récupérer d'abord l'idTemplate
            $idTemplate = Carte::where('idCompte', $idCompte)->value('idTemplate');

            // Prend toutes les infos de la carte
            $carte = Carte::where('idCompte', $idCompte)->first();
            $idCarte = $carte->idCarte ?? null;
        }

        // Si $idCarte est toujours null, on ne peut rien afficher
        if (!$idCarte) {
            return response()->json(['message' => 'idCarte non trouvé.'], 404);
        }

        // Récupération des données du compte, template, etc.
        $compte = isset($idCompte) ? Compte::find($idCompte) : null;
        $lien = Rediriger::where('idCarte', $idCarte)->get(); // Tous les liens associés à une carte
        $custom = Custom_Link::where('idCarte', $idCarte)->where('activer', 1)->get(); // Liens personnalisés activés (custom_link)
        $vue = Vue::where('idCarte', $idCarte)->get(); // Toutes les vues d'une carte
        $template = isset($idTemplate) ? Template::find($idTemplate) : null;

        // Récupérer les réseaux sociaux
        $logoSocial = Social::all()->map(function ($item) {
            return [
                'id' => $item->idSocial,
                'logo' => $item->lienLogo,
                'nom' => $item->nom,
            ];
        });

        // Récupérer les liens activés pour une carte spécifique
        $social = Rediriger::where('idCarte', $idCarte)
            ->where('activer', 1)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->idSocial,
                    'lien' => $item->lien,
                    'activer' => $item->activer,
                ];
            });

        // Fusionner les collections de réseaux sociaux actifs avec leurs logos
        $mergedSocial = $social->map(function ($item) use ($logoSocial) {
            $socialItem = $logoSocial->firstWhere('id', $item['id']);
            return [
                'lien' => $item['lien'],
                'logo' => $socialItem ? $socialItem['logo'] : null,
                'nom' => $socialItem ? $socialItem['nom'] : null,
            ];
        });

        // Définir les fonctions spécifiques
        $fonctions = [
            ['nom' => 'nopub'],
            ['nom' => 'embedyoutube', 'option' => $idCarte ? Carte::find($idCarte)->lienCommande : null]
        ];

        // Renvoyer la bonne vue selon le template
        switch ($idTemplate ?? null) {
            case 1:
                return view('Templates.oxygen', compact('carte', 'compte', 'social', 'vue', 'template', 'logoSocial', 'custom', 'employe', 'fonctions', 'lien', 'mergedSocial'));
            case 2:
                return view('Templates.fraise', compact('carte', 'compte', 'social', 'vue', 'template', 'logoSocial', 'custom', 'employe', 'fonctions'));
            case 3:
                return view('Templates.peche', compact('carte', 'compte', 'social', 'vue', 'template', 'logoSocial', 'custom', 'employe', 'fonctions'));
            default:
                // Si aucun template trouvé, retourner un message JSON ou une vue vide.
                return response()->json([
                    'message' => 'Aucun template trouvé',
                    'data' => compact('idCarte', 'employe', 'compte')
                ], 404);
        }
    }


    public function iframePomme()
    {
        return view('Templates.Iframe.pomme');
    }

    public function iframeFraise()
    {
        return view('Templates.Iframe.fraise');
    }

    public function iframePeche()
    {
        return view('Templates.Iframe.peche');
    }

}