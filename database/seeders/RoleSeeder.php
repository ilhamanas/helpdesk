<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['code' => 'admin', 'name' => 'Administrator', 'description' => 'Akses penuh sistem'],
            ['code' => 'hrd', 'name' => 'HRD', 'description' => 'Approval dan assign tiket RFM'],
            ['code' => 'technician', 'name' => 'IT / Teknisi', 'description' => 'Mengerjakan tiket RFM'],
            ['code' => 'staff', 'name' => 'Karyawan', 'description' => 'Membuat tiket RFM'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['code' => $role['code']], $role);
        }
    }
}
