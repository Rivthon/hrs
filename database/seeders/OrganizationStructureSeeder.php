<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrganizationStructureSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $structure = [
            ['WAKA-1', 'Wakil Ketua I', ['BAAK' => 'BAAK', 'ICT' => 'ICT', 'OPERATOR' => 'Operator', 'PERPUSTAKAAN' => 'Perpustakaan']],
            ['PRODI-FARMASI', 'Kaprodi Farmasi', ['DOSEN-FARMASI' => 'Dosen Farmasi', 'LAB-FARMASI' => 'Laboran Farmasi']],
            ['PRODI-GIZI', 'Kaprodi Gizi', ['DOSEN-GIZI' => 'Dosen Gizi', 'LAB-GIZI' => 'Laboran Gizi']],
            ['PRODI-KEBIDANAN', 'Kaprodi Kebidanan', ['DOSEN-KEBIDANAN' => 'Dosen Kebidanan', 'LAB-KEBIDANAN' => 'Laboran Kebidanan']],
            ['WAKA-2', 'Wakil Ketua II', ['BAUK' => 'BAUK', 'SARPRAS' => 'Sarpras']],
            ['WAKA-3', 'Wakil Ketua III', ['HUMAS' => 'Humas', 'KERJASAMA' => 'Kerjasama', 'KEMAHASISWAAN' => 'Kemahasiswaan dan Alumni']],
        ];

        foreach ($structure as [$parentCode, $parentName, $children]) {
            $parent = Department::updateOrCreate(
                ['code' => $parentCode],
                ['name' => $parentName, 'type' => 'leadership', 'is_active' => true],
            );

            foreach ($children as $childCode => $childName) {
                Department::updateOrCreate(
                    ['code' => $childCode],
                    ['parent_id' => $parent->id, 'name' => $childName, 'type' => 'work_unit', 'is_active' => true],
                );
            }
        }
    }
}
