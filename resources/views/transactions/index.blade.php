@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Transactions</h1>

    <!-- Affichage des messages de succès -->
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Affichage des totaux -->
    <div class="row">
        <div class="col-md-6">
            <h3>Total Dépôts : {{ number_format($totalDepot, 2) }}F</h3>
        </div>
        <div class="col-md-6">
            <h3>Total Retraits : {{ number_format($totalRetrait, 2) }}F</h3>
        </div>
    </div>

    <!-- Formulaire pour ajouter une nouvelle transaction -->
    <form action="{{ route('transactions.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="type">Type</label>
            <select name="type" id="type" class="form-control" required>
                <option value="Dépôt">Dépôt</option>
                <option value="Envoi">Envoi</option>
            </select>
        </div>
        <div class="form-group">
            <label for="destinataire">Destinataire (si applicable)</label>
            <input type="text" name="destinataire" id="destinataire" class="form-control">
        </div>
        <div class="form-group">
            <label for="montant">Montant</label>
            <input type="number" name="montant" id="montant" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Ajouter Transaction</button>
    </form>

    <h2>Liste des Transactions</h2>
    <ul class="list-group mt-3">
        @foreach($transactions as $transaction)
            <li class="list-group-item d-flex justify-content-between align-items-center">
                {{ $transaction->type }}{{ $transaction->destinataire ? ' à ' . $transaction->destinataire : '' }}
                <span>{{ $transaction->created_at->format('d M Y à H:i') }}</span>
                <span style="color: {{ $transaction->type == 'Dépôt' ? 'blue' : 'red' }};">{{ ($transaction->type == 'Dépôt' ? '' : '-') . number_format($transaction->montant, 2) . 'F' }}</span>
            </li>
        @endforeach
    </ul>
</div>
@endsection
