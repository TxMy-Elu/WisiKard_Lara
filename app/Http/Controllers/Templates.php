<?php
// app/Http/Controllers/Connexion.php

namespace App\Http\Controllers;

use App\Models\Carte;
use App\Models\Compte;
use App\Models\Rediriger;
use App\Models\Social;
use App\Models\Template;
use App\Models\Vue;
use Illuminate\Http\Request;


class Templates extends Controller
{
    public function afficherTemplates(Request $request)
    {

        $idCompte = $request->query('idCompte');

        $idTemplate = Carte::where('idCompte', $idCompte)->value('idTemplate');

        // Prend tout les infos de la carte et les envoie à la vue
        $carte = Carte::find($idCompte);
        $compte = Compte::find($idCompte);
        $logoSocial = Social::all();
        $social = Rediriger::where('idCarte', $carte->idCarte)
            ->where('activer', 1)
            ->get();
        $vue = Vue::where('idCarte', $carte->idCarte)->get();
        $template = Template::where('idTemplate', $idTemplate)->get();


        switch ($idTemplate) {
            case 1:
                return view('templates.pomme', compact('carte', 'compte', 'social', 'vue', 'template', 'logoSocial'));
            case 2:
                return view('templates.fraise', compact('carte', 'compte', 'social', 'vue', 'template', 'logoSocial'));
            case 3:
                return view('templates.peche', compact('carte', 'compte', 'social', 'vue', 'template', 'logoSocial'));
            default:
                return abort(404, 'Template not found');
        }
    }

}