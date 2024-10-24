<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index()
    {
        // Récupérer toutes les transactions
        $transactions = Transaction::orderBy('created_at', 'desc')->get();

        // Calculer les totaux
        $totalDepot = Transaction::where('type', 'Dépôt')->sum('montant');
        $totalRetrait = Transaction::where('type', 'Envoi')->sum('montant');

        return view('transactions.index', compact('transactions', 'totalDepot', 'totalRetrait'));
    }

    public function store(Request $request)
    {
        // Valider et stocker une nouvelle transaction
        $request->validate([
            'type' => 'required|string',
            'destinataire' => 'nullable|string',
            'montant' => 'required|numeric',
        ]);

        Transaction::create($request->all());

        return redirect()->route('transactions.index')->with('success', 'Transaction ajoutée avec succès.');
    }
}
