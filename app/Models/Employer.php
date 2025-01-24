<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;
use App\Models\Compte;
use App\Models\Carte;
use App\Models\Logs;

class Employer extends Model
{
    protected $table = 'employer';
    protected $primaryKey = 'idEmp';
    public $timestamps = false;
    public $incrementing = true;

    public function carte(): BelongsTo
    {
        return $this->belongsTo(Carte::class, 'idCarte');
    }

    public function vues(): HasMany
    {
        return $this->hasMany(Vue::class, 'idEmp');
    }

    public static function QrCodeEmploye($id, $entreprise, $idEmp)
    {
        $url = "https://chart.io/qr?size=300&dark=000000&light=FFFFFF&text=127.0.0.1:9000/Templates?CompteEmp=". $id ."x".$idEmp;

        $ch = curl_init();

        // Configurer les options cURL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification

        // Exécuter la requête cURL et obtenir le contenu
        $content = curl_exec($ch);

        if (curl_errno($ch)) {
            Log::error('Erreur cURL : ' . curl_error($ch));
            curl_close($ch);
            return null;
        } else {
            // Fermer la session cURL
            curl_close($ch);

            // Chemin où enregistrer le fichier SVG
            $directoryPath = public_path("entreprises/{$id}_{$entreprise}/QR_Codes");
            $svgFilePath = "{$directoryPath}/QR_Code_{$idEmp}.svg";

            // Créer le répertoire s'il n'existe pas
            if (!file_exists($directoryPath)) {
                mkdir($directoryPath, 0777, true);
            }

            // Enregistrer le contenu dans un fichier SVG
            file_put_contents($svgFilePath, $content);

            Log::info("QR Code généré et enregistré à : $svgFilePath");

            return $svgFilePath;
        }
    }
}
