<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Village;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Inisialisasi Identitas Desa
        Village::firstOrCreate([], [
            'name' => 'Desa Sukahaji',
            'district' => 'Kecamatan Patrol',
            'regency' => 'Kabupaten Indramayu',
            'province' => 'Jawa Barat',
            'postal_code' => '45258',
            'address' => 'Jl. Raya Desa Sukahaji No. 01, Sukahaji',
            'phone' => '(0234) 612345',
            'email' => 'pemdes.sukahaji@gmail.com',
            'kades_name' => 'H. Mulyadi, S.Sos',
            'kades_nip' => '197508152006041008',
            'active_tax_year' => 2026,
            'target_amount' => 175000000.00,
            'bank_account_info' => 'Bank BJB Rek. 0012345678901 a/n Kas Desa Sukahaji (PBB-P2)',
        ]);

        // 2. Inisialisasi Akun Pengguna untuk Setiap Peran
        $users = [
            [
                'name' => 'Administrator Desa',
                'username' => 'admin',
                'email' => 'admin@sukahaji.desa.id',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'dusun_name' => null,
                'phone' => '081234567890',
                'is_active' => true,
            ],
            [
                'name' => 'Ahmad Sanusi (Kadus I)',
                'username' => 'kadus1',
                'email' => 'kadus1@sukahaji.desa.id',
                'password' => Hash::make('password'),
                'role' => 'kadus',
                'dusun_name' => 'Dusun I Babakan',
                'phone' => '081234567891',
                'is_active' => true,
            ],
            [
                'name' => 'Dedi Suryana (Kolektor)',
                'username' => 'kolektor',
                'email' => 'kolektor@sukahaji.desa.id',
                'password' => Hash::make('password'),
                'role' => 'kolektor',
                'dusun_name' => null,
                'phone' => '081234567892',
                'is_active' => true,
            ],
            [
                'name' => 'Siti Rahayu (Kaur Keuangan)',
                'username' => 'bendahara',
                'email' => 'bendahara@sukahaji.desa.id',
                'password' => Hash::make('password'),
                'role' => 'bendahara',
                'dusun_name' => null,
                'phone' => '081234567893',
                'is_active' => true,
            ],
            [
                'name' => 'H. Mulyadi, S.Sos (Kuwu/Kades)',
                'username' => 'kades',
                'email' => 'kades@sukahaji.desa.id',
                'password' => Hash::make('password'),
                'role' => 'kades',
                'dusun_name' => null,
                'phone' => '081234567894',
                'is_active' => true,
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['username' => $user['username']],
                $user
            );
        }
    }
}
