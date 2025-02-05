<?php

namespace App\Http\Controllers;

use App\Models\Compte;
use App\Models\Logs;
use App\Models\Vue;
use App\Models\Carte;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DashboardAdmin extends Controller
{
    protected $compte;
    protected $vue;
    protected $carte;
    protected $message;

    /**
     * DashboardAdmin constructor.
     *
     * @param Compte $compte
     * @param Vue $vue
     * @param Carte $carte
     * @param Message $message
     */
    public function __construct(Compte $compte, Vue $vue, Carte $carte, Message $message)
    {
        $this->compte = $compte;
        $this->vue = $vue;
        $this->carte = $carte;
        $this->message = $message;
    }

    /**
     * Affiche le tableau de bord administrateur.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function afficherDashboardAdmin(Request $request)
    {
        $search = $request->input('search');
        $entreprises = $this->carte->join('compte', 'carte.idCompte', '=', 'compte.idCompte')
            ->when($search, function ($query, $search) {
                return $query->where('carte.nomEntreprise', 'like', "%{$search}%")
                    ->orWhere('compte.email', 'like', "%{$search}%");
            })
            ->select('carte.*', 'compte.email as compte_email', 'compte.role as compte_role')
            ->get();

        foreach ($entreprises as $entreprise) {
            $entreprise->formattedTel = $this->formatPhoneNumber($entreprise->tel);
        }

        $message = $this->message->where('afficher', true)->orderBy('id', 'desc')->first();
        $messageContent = $message ? $message->message : 'Aucun message disponible';

        return view('Admin.dashboardAdmin', compact('entreprises', 'search', 'messageContent'));
    }

    /**
     * Affiche le formulaire de modification du mot de passe.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function showModifyPasswordForm($id)
    {
        $compte = Compte::find($id);
        if (!$compte) {
            abort(404, 'Compte non trouvé');
        }
        return view('Formulaire.formulaireModifMDP', compact('compte'));
    }

    /**
     * Met à jour le mot de passe de l'utilisateur.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateMDP(Request $request, $id)
    {
        $idCompte = session('connexion');
        $emailUtilisateur = Compte::find($idCompte)->email; // Récupérer l'email de l'utilisateur connecté
        if ($request->isMethod('post')) {
            $messagesErreur = [];
            $validationFormulaire = true;

            if ($request->input('mdp1') != $request->input('mdp2')) {
                $messagesErreur[] = "Les deux mots de passe saisis ne sont pas identiques";
                $validationFormulaire = false;
                Logs::ecrireLog($emailUtilisateur, "Erreur mdp non identiques : Modif MDP");
            }

            if (preg_match("/^(?=.*?[a-z])(?=.*?[A-Z])(?=.*?[0-9])(?=.*?[!@#%^&*()\$_+÷%§€\-=\[\]{}|;':\",.\/<>?~`]).{12,}$/", $request->input('mdp1')) === 0) {
                $messagesErreur[] = "Le mot de passe doit contenir au minimum 12 caractères comportant au moins une minuscule, une majuscule, un chiffre et un caractère spécial.";
                $validationFormulaire = false;
                Logs::ecrireLog($emailUtilisateur, "Erreur pregmatch : Modif MDP");
            }

            if ($validationFormulaire) {
                $motDePasseHashe = password_hash($request->input('mdp1'), PASSWORD_BCRYPT);
                $compte = Compte::find($id);
                if ($compte) {
                    $compte->password = $motDePasseHashe;
                    $compte->save();
                    Logs::ecrireLog($compte->email, "Modification du mot de passe");
                    return redirect()->route('dashboardAdmin')->with('success', 'Mot de passe modifié !');
                } else {
                    return redirect()->back()->with('error', 'Compte non trouvé');
                    Logs::ecrireLog($emailUtilisateur, "Erreur Compte non trouvé : Modif MDP");
                }
            } else {
                return redirect()->back()->with('error', implode('<br>', $messagesErreur));
            }
        }
        return view('Formulaire.formulaireModifMDP');
    }

    /**
     * Affiche les statistiques du tableau de bord administrateur.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function statistique(Request $request)
    {
        $year = $request->query('year', date('Y'));
        $month = $request->query('month', null);

        $yearlyViews = $this->vue->selectRaw('MONTH(date) as month, COUNT(*) as count')
            ->whereYear('date', $year)
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $yearlyData = [
            'labels' => ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
            'datasets' => [
                [
                    'label' => 'Nombre de vue par mois',
                    'backgroundColor' => 'rgba(153, 27, 27, 0.2)',
                    'borderColor' => 'rgba(153, 27, 27, 1)',
                    'borderWidth' => 1,
                    'data' => array_values(array_replace(array_fill(1, 12, 0), $yearlyViews)),
                ],
            ],
        ];

        $totalViews = $this->vue->whereYear('date', $year)->count();
        $totalEntreprise = $this->carte->count();

        $years = range(date('Y'), date('Y') - 10);
        $selectedYear = $year;

        return view('Admin.dashboardAdminStatistique', compact('yearlyData', 'years', 'selectedYear', 'month', 'totalViews', 'totalEntreprise'));
    }

    /**
     * Formate un numéro de téléphone.
     *
     * @param string $phoneNumber
     * @return string
     */
    private function formatPhoneNumber($phoneNumber)
    {
        return preg_replace("/(\d{2})(?=\d)/", "$1.", $phoneNumber);
    }

    /**
     * Ajoute un nouveau message.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function ajoutMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $this->message->create([
            'message' => $request->input('message'),
            'afficher' => true,
        ]);

        return redirect()->route('dashboardAdminMessage');
    }

    /**
     * Active ou désactive l'affichage d'un message.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleMessage($id)
    {
        $message = $this->message->find($id);
        if ($message) {
            $message->afficher = !$message->afficher;
            $message->save();
            Log::info('Message ' . $message->id . ' toggled');
        }

        Log::info('Message ' . $message->id . ' not found');

        return redirect()->route('dashboardAdminMessage');
    }

    /**
     * Modifie un message existant.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function modifierMessage(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $message = $this->message->findOrFail($id);
        $message->message = $request->input('message');
        $message->save();

        Log::info('Message ' . $message->id . ' updated');
        Logs::ecrireLog($request->session()->get('email'), 'Modification du message');
        return redirect()->route('dashboardAdminMessage')->with('success', 'Message mis à jour avec succès.');
    }

    /**
     * Supprime un message.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function SupprimerMessage(Request $request, $id)
    {
        $message = $this->message->findOrFail($id);
        $message->delete();

        Log::info('Message ' . $message->id . ' supprimer');
        Logs::ecrireLog($request->session()->get('email'), 'Suppression du message');
        return redirect()->route('dashboardAdminMessage')->with('success', 'Message supprimé avec succès.');
    }

    /**
     * Affiche tous les messages.
     *
     * @return \Illuminate\View\View
     */
    public function afficherAllMessage()
    {
        $messages = $this->message->all();
        return view('Admin.dashboardAdminMessage', compact('messages'));
    }

    /**
     * Rafraîchit le QR Code d'une entreprise.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function refreshQrCode($id)
    {
        $compte = $this->compte->find($id);
        if ($compte) {
            $carte = $this->carte->where('idCompte', $compte->idCompte)->first();
            if ($carte) {
                $compte->QrCode($compte->idCompte, $carte->nomEntreprise);

                $carte->lienQr = "/entreprises/{$compte->idCompte}_{$carte->nomEntreprise}/QR_Codes/QR_Code.svg";
                $carte->save();
                Log::info('QR Code for ' . $carte->nomEntreprise . ' refreshed');
                Logs::ecrireLog($compte->email, 'Rafraîchissement du QR Code');
            }
        }

        return redirect()->route('dashboardAdmin');
    }
}
