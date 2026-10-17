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

        // 3. Inisialisasi Data Contoh Wajib Pajak, Objek Pajak (NOP), dan SPPT
        $sampleData = [
            [
                'wp_name' => 'H. Sukarna Wiraatmadja',
                'nik' => '3212051203650001',
                'phone' => '081321456780',
                'address' => 'Jl. Balai Desa Blok A No. 12',
                'rt' => '01', 'rw' => '01', 'dusun' => 'Dusun I Babakan',
                'nop' => '321205000100100010',
                'op_address' => 'Blok Karang Tengah',
                'status' => 'normal',
                'pbb_terutang' => 125000,
            ],
            [
                'wp_name' => 'Ibu Hj. Aminah',
                'nik' => '3212055507720003',
                'phone' => '081223344556',
                'address' => 'Gang Melati No. 4',
                'rt' => '02', 'rw' => '01', 'dusun' => 'Dusun I Babakan',
                'nop' => '321205000100100020',
                'op_address' => 'Blok Sawah Lor',
                'status' => 'normal',
                'pbb_terutang' => 85000,
            ],
            [
                'wp_name' => 'Bapak Warno Supriatna',
                'nik' => '3212051908800002',
                'phone' => '085712349988',
                'address' => 'Jl. Patrol Raya No. 45',
                'rt' => '01', 'rw' => '02', 'dusun' => 'Dusun II Sukajaya',
                'nop' => '321205000100200010',
                'op_address' => 'Blok Peundeuy',
                'status' => 'normal',
                'pbb_terutang' => 210000,
            ],
            [
                'wp_name' => 'Keluarga Bpk. Sulaeman (Alm)',
                'nik' => null,
                'phone' => null,
                'address' => 'Blok Sukamaju Kulon',
                'rt' => '03', 'rw' => '02', 'dusun' => 'Dusun II Sukajaya',
                'nop' => '321205000100200020',
                'op_address' => 'Blok Sukamaju Kulon',
                'status' => 'to', // Tanpa Objek / Pemilik Belum Jelas
                'pbb_terutang' => 65000,
            ],
            [
                'wp_name' => 'Drs. Tatang Kusnadi',
                'nik' => '3212051011680005',
                'phone' => '081987654321',
                'address' => 'Jl. Irigasi Barat No. 8',
                'rt' => '02', 'rw' => '03', 'dusun' => 'Dusun III Mekarsari',
                'nop' => '321205000100300010',
                'op_address' => 'Blok Sawah Bengkok',
                'status' => 'normal',
                'pbb_terutang' => 340000,
            ],
        ];

        foreach ($sampleData as $item) {
            $taxpayer = \App\Models\Taxpayer::firstOrCreate(
                ['name' => $item['wp_name']],
                [
                    'nik' => $item['nik'],
                    'phone' => $item['phone'],
                    'address' => $item['address'],
                    'rt' => $item['rt'],
                    'rw' => $item['rw'],
                    'dusun' => $item['dusun'],
                    'village_name' => 'Desa Sukahaji',
                ]
            );

            $taxObject = \App\Models\TaxObject::firstOrCreate(
                ['nop' => $item['nop']],
                [
                    'taxpayer_id' => $taxpayer->id,
                    'address' => $item['op_address'],
                    'rt' => $item['rt'],
                    'rw' => $item['rw'],
                    'dusun' => $item['dusun'],
                    'status' => $item['status'],
                ]
            );

            \App\Models\Sppt::updateOrCreate(
                [
                    'tax_object_id' => $taxObject->id,
                    'tax_year' => 2026,
                ],
                [
                    'pbb_terutang' => $item['pbb_terutang'],
                    'total_bill' => $item['pbb_terutang'],
                    'status' => 'unpaid',
                    'due_date' => '2026-09-30',
                ]
            );
        }
    }
}
