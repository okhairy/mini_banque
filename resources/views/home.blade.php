@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">

<!-- Carte principale -->
<div class="card" style="border-radius: 15px;">
    <!-- En-tête avec l'icône des paramètres et le solde -->
    <div class="card-header text-center" style="background-color: #1C627B; color: white; padding: 50px; border-radius: 15px 15px 0 0; position: relative;">
        <button id="settingsButton" style="background: none; border: none; color: white; position: absolute; top: 15px; left: 15px;">
            <i class="fa fa-cog" style="font-size: 24px;"></i>
        </button>

        <h2>
            <span id="solde">{{ number_format($balance) }} F</span>
            <button id="toggleSolde" style="background: none; border: none; color: white;">
                <i id="soldeIcon" class="fa fa-eye"></i>
            </button>
        </h2>

        <!-- Conteneur pour le QR Code -->
        <div class="text-center" style="padding: 20px;">
            <div style="background-color: #40AEC9; 
                        border-radius: 15px; 
                        padding: 30px; 
                        display: inline-block; width: 400px;">
                <img src="data:image/png;base64,{{ base64_encode($qrCodeUrl) }}" alt="QR Code" style="border-radius: 10px; width: 200px; height: 100px; border: 10px solid #FFFFFF;" />
            </div>
        </div>
    </div>
</div>

<!-- Carte avec icônes d'actions -->
<div class="d-flex justify-content-center" style="position: relative; margin-top: -50px; z-index: 1;">
    <div class="card text-center border rounded" style="background-color: #f2f2f2; padding: 15px; border-color: #40AEC9; width: 600px; height: auto; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);">
        <div class="row">
            <div class="col">
                <i class="fa fa-exchange-alt" id="showTransferModal" style="font-size: 24px; cursor: pointer;"></i>
            </div>
            <div class="col">
                <i class="fa fa-history" style="font-size: 24px;"></i>
            </div>
        </div>
    </div>
</div>

<!-- Historique des transactions -->
<div class="card" style="padding: 20px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2); margin-top: 12px;">
    <div class="card-body">
        <ul class="list-group list-group-flush">
        @foreach($transactions as $transaction)
            <li class="list-group-item d-flex justify-content-between align-items-center transaction-item" 
                data-toggle="modal" 
                data-target="#transactionDetailsModal" 
                data-type="{{ $transaction->type }}" 
                data-user="{{ $transaction->user ? e($transaction->user->name) : 'Inconnu' }}"
                data-destinataire="{{ $transaction->destinataire ? e($transaction->destinataire) : 'Inconnu' }}"
                data-montant="{{ number_format($transaction->montant, 2) }}"
                data-date="{{ $transaction->created_at->format('d M Y à H:i') }}"
                data-status="{{ $transaction->status }}">
                <div class="d-flex flex-column">
                    <span>
                        <strong style="color: blue;">{{ $transaction->type }}</strong>
                        @if($transaction->status !== 'canceled')
                            @if($transaction->type === 'Transfert')
                                de {{ $transaction->user ? e($transaction->user->name) : 'Inconnu' }}
                            @elseif($transaction->type === 'Réception')
                                à {{ $transaction->destinataire ? e($transaction->destinataire) : 'Inconnu' }}
                            @endif
                        @endif
                    </span>
                    <small class="text-muted">{{ $transaction->created_at->format('d M Y à H:i') }}</small>
                </div>
                <span style="color: {{ in_array($transaction->type, ['Dépôt', 'Réception']) ? 'blue' : 'red' }};">
                    {{ in_array($transaction->type, ['Dépôt', 'Réception']) ? number_format($transaction->montant, 2) . ' F' : '-' . number_format($transaction->montant, 2) . ' F' }}
                </span>
            </li>
        @endforeach
        </ul>
    </div>
</div>

<!-- Modal pour les détails de la transaction -->
<div class="modal fade" id="transactionDetailsModal" tabindex="-1" role="dialog" aria-labelledby="transactionDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 10px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);">
            <div class="modal-header" style="background-color: #f5f5f5; border-bottom: 1px solid #ddd; border-top-left-radius: 10px; border-top-right-radius: 10px;">
                <h5 class="modal-title" id="transactionDetailsModalLabel" style="font-weight: 600; color: #333;">Détails de la transaction</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="background: transparent; border: none;">
                    <span aria-hidden="true" style="font-size: 1.5rem; color: #555;">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 20px;">
                <p><strong>Type de transaction:</strong> <span id="modalTransactionType" style="color: #007bff;"></span></p>
                <p><strong>Utilisateur:</strong> <span id="modalTransactionUser" style="color: #007bff;"></span></p>
                <p><strong>Destinataire:</strong> <span id="modalTransactionDestinataire" style="color: #007bff;"></span></p>
                <p><strong>Montant:</strong> <span id="modalTransactionMontant" style="color: #28a745;"></span></p>
                <p><strong>Date:</strong> <span id="modalTransactionDate" style="color: #6c757d;"></span></p>
                <p><strong>Status:</strong> <span id="modalTransactionStatus" style="font-weight: 500; color: #333;"></span></p>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #ddd;">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: 5px; background-color: #6c757d; border: none;">Fermer</button>
            </div>
        </div>
    </div>
</div>


<!-- Inclusion des bibliothèques JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<!-- Script pour gérer les détails des transactions -->
<script>
    $(document).ready(function() {
        // Afficher le modal de transfert lorsque l'utilisateur clique sur l'icône échange
        $('#showTransferModal').click(function() {
            $('#transferModal').modal('show');
        });

        // Afficher la modale d'erreur si des erreurs existent
        @if ($errors->any())
            $('#errorModal').modal('show');
        @endif

        // Afficher ou masquer le solde
        $('#toggleSolde').click(function() {
            let icon = $('#soldeIcon');
            let solde = $('#solde');
            if (icon.hasClass('fa-eye')) {
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
                solde.text('*******');
            } else {
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
                solde.text('{{ number_format($balance) }} F');
            }
        });

        // Afficher les détails de la transaction dans le modal
        $('.transaction-item').click(function() {
            $('#modalTransactionType').text($(this).data('type'));
            $('#modalTransactionUser').text($(this).data('user'));
            $('#modalTransactionDestinataire').text($(this).data('destinataire'));
            $('#modalTransactionMontant').text($(this).data('montant') + ' F');
            $('#modalTransactionDate').text($(this).data('date'));
            $('#modalTransactionStatus').text($(this).data('status'));
        });
    });
</script>
@endsection
