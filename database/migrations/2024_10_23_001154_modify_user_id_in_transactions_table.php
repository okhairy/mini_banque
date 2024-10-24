<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyUserIdInTransactionsTable extends Migration
{
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Supposons que tu veuilles changer le type de la colonne user_id ou ajouter une contrainte
            $table->unsignedBigInteger('user_id')->change(); // Par exemple, changer en unsignedBigInteger si ce n'était pas le cas

            // Si tu veux ajouter une contrainte de clé étrangère ou modifier une contrainte existante
            $table->dropForeign(['user_id']); // Supprimer la contrainte existante
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); // Réajouter la contrainte
        });
    }

    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Rétablir l'ancienne définition si nécessaire
            $table->unsignedInteger('user_id')->change(); // Modifier selon la définition précédente
            $table->dropForeign(['user_id']);
        });
    }
}
