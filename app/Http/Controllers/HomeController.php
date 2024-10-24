<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); // Utiliser l'authentification pour sécuriser les actions
    }

    public function index()
    {
        $userId = auth()->id();
        $transactions = Transaction::with('user')->where('user_id', $userId)->orderBy('created_at', 'desc')->paginate(10);
        $totalDepot = Transaction::where('user_id', $userId)->where('type', 'Dépôt')->sum('montant');
        $totalRetrait = Transaction::where('user_id', $userId)->where('type', 'Envoi')->sum('montant');
        $balance = auth()->user()->balance;

        // Générer le QR code pour le compte de l'utilisateur
        $accountNumber = auth()->user()->account_number; // Assurez-vous que le champ account_number existe
        $qrCodeUrl = QrCode::format('png')->size(300)->generate($accountNumber); // Générer le QR code

        return view('home', compact('transactions', 'totalDepot', 'totalRetrait', 'balance', 'qrCodeUrl')); // Passer la variable à la vue
    }

    public function transfer(Request $request)
    {
        // Validation des données du formulaire
        $request->validate([
            'account_number' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'password' => 'required|string',
        ], [
            'account_number.required' => 'Le numéro de compte est obligatoire.',
            'amount.required' => 'Le montant est obligatoire.',
            'amount.numeric' => 'Le montant doit être un nombre.',
            'password.required' => 'Le mot de passe est obligatoire.',
        ]);
    
        $user = auth()->user();
    
        // Vérification du mot de passe de l'utilisateur
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Le mot de passe est incorrect.']);
        }
    
        $amount = $request->amount;
        $fee = $amount * 0.02; // Frais de 2%
    
        // Vérification du solde, y compris les frais
        if ($user->balance < ($amount + $fee)) {
            return back()->withErrors(['amount' => 'Solde insuffisant pour effectuer le transfert, frais inclus.']);
        }
    
        // Vérifier que le compte destinataire existe
        $destinataire = User::where('account_number', $request->account_number)->first();
        if (!$destinataire) {
            return back()->withErrors(['account_number' => 'Compte destinataire introuvable.']);
        }
    
        // Enregistrer les transactions dans une transaction DB
        try {
            \DB::transaction(function () use ($user, $destinataire, $amount, $fee) {
                // Mettre à jour les soldes
                $user->balance -= ($amount + $fee);
                $user->save();

                $destinataire->balance += $amount;
                $destinataire->save();

                // Enregistrer la transaction pour l'expéditeur
                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'Envoi',
                    'destinataire' => $destinataire->account_number,
                    'montant' => $amount,
                    'frais' => $fee,
                    'sender_name' => $user->name,
                ]);

                // Enregistrer la transaction pour le destinataire
                Transaction::create([
                    'user_id' => $destinataire->id,
                    'type' => 'Réception',
                    'destinataire' => $user->account_number,
                    'montant' => $amount,
                    'sender_name' => $user->name,
                ]);
                
            });

            return back()->with('success', 'Transfert effectué avec succès.');
        } catch (\Exception $e) {
            return back()->withErrors(['transaction' => 'Une erreur est survenue lors du transfert.']);
        }
    }

    protected function getUserIdByAccountNumber($account_number)
    {
        $user = User::where('account_number', $account_number)->first();
        if (!$user) {
            throw new \Exception('Compte destinataire introuvable.');
        }

        return $user->id;
    }
}
