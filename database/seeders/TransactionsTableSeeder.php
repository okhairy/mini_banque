<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transaction;
use App\Models\User;
use Faker\Factory as Faker;

class TransactionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        // Récupérer les utilisateurs existants
        $users = User::all();

        for ($i = 0; $i < 20; $i++) {
            // Générer un faux type de transaction (Dépôt, Retrait, ou Transfert)
            $type = $faker->randomElement(['Dépôt', 'Retrait', 'Transfert']);

            // Générer le destinataire seulement si c'est un Transfert
            $destinataire = $type == 'Transfert' ? $faker->name : null;

            Transaction::create([
                'user_id' => $faker->randomElement($users)->id, // Lier à un utilisateur aléatoire
                'type' => $type,
                'destinataire' => $destinataire,
                'montant' => $faker->randomFloat(2, 100, 10000), // Montant aléatoire entre 100 et 10000
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
