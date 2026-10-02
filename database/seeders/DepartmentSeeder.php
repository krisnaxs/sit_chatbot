<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['name' => 'Sistem Informasi', 'code' => 'SIS'],
            ['name' => 'ALBES', 'code' => 'albes'],
            ['name' => 'keuangan', 'code' => 'keu'],
            ['name' => 'HUMAS', 'code' => 'HUMAS'],
            ['name' => 'SMT', 'code' => 'smt'],
            ['name' => 'AKUNTANSI', 'code' => 'AKUNTANSI'],
            ['name' => 'EFISIENSI', 'code' => 'EFISIENSI'],
            ['name' => 'PUK', 'code' => 'ADB'],
            ['name' => 'FASAU', 'code' => 'FASAU'],
            ['name' => 'POLIKLINIK', 'code' => 'POLIKLINIK'],
            ['name' => 'PBJ01', 'code' => 'PBJ01'],
            ['name' => 'PBJ02', 'code' => 'PBJ02'],
            ['name' => 'LINGKUNGAN', 'code' => 'LINGKUNGAN'],
            ['name' => 'MSMH', 'code' => 'MSMH'],
            ['name' => 'RENDAL K3', 'code' => 'RENDAL K3'],
            ['name' => 'PP', 'code' => 'PP'],
            ['name' => 'RSO', 'code' => 'RSO'],
            ['name' => 'ANGGARAN', 'code' => 'ANGGARAN'],
            ['name' => 'SDM', 'code' => 'SDM'],
            ['name' => 'BGP', 'code' => 'BGP'],
            ['name' => 'MPBJ01', 'code' => 'MPBJ01'],
            ['name' => 'MPBJ02', 'code' => 'MPBJ02'],
            ['name' => 'SEKERTARIAT', 'code' => 'SKRT'],
            ['name' => 'MOPN17', 'code' => 'MOPN17'],
            ['name' => 'MHAR1-4', 'code' => 'MHAR1-4'],
            ['name' => 'PERPUSTAKAAN', 'code' => 'PERP'],
            ['name' => 'OUTAGE 1-4', 'code' => 'OUT1-4'],
            ['name' => 'RENDAL 1-4', 'code' => 'RDL1-4'],
            ['name' => 'MHAR5-7', 'code' => 'MHAR5-7'],
            ['name' => 'PDM', 'code' => 'PDM'],
            ['name' => 'LISTRIK 1-4', 'code' => 'LST1-4'],
            ['name' => 'BOILER 1-4', 'code' => 'BLR1-4'],
            ['name' => 'MSIK', 'code' => 'MSIK'],
            ['name' => 'TURBIN 1-4', 'code' => 'TBN1-4'],
            ['name' => 'INSTRUMEN 1-4', 'code' => 'INT1-4'],
            ['name' => 'ALATBANTU57', 'code' => 'AB57'],
            ['name' => 'LABORATORIUM', 'code' => 'LAB'],
            ['name' => 'DESAL 1-4', 'code' => 'DSL1-4'],
            ['name' => 'DESAL 5-7', 'code' => 'DSL5-7'],
            ['name' => 'ASH HANDLING 1-4', 'code' => 'ASH1-4'],
            ['name' => 'ASH HANDLING 5-7', 'code' => 'ASH5-7'],
            ['name' => 'CHCR', 'code' => 'CHC'],
            ['name' => 'TOWER G', 'code' => 'TWG'],
            ['name' => 'OUTAGE57', 'code' => 'OUT57'],
            ['name' => 'GUDANG 1-4', 'code' => 'GDG1-4'],
            ['name' => 'MPEP', 'code' => 'MPEP'],
            ['name' => 'GUDANG 5-7', 'code' => 'GDG5-7'],
            ['name' => 'MREP', 'code' => 'MREP'],
            ['name' => 'RENDAL57', 'code' => 'REND57'],
            ['name' => 'ENGINEERING', 'code' => 'ENGINEERING'],
            ['name' => 'TURBIN57', 'code' => 'TRBN57'],
            ['name' => 'BOILER57', 'code' => 'BLR57'],
            ['name' => 'LISTRIK57', 'code' => 'LSTK57'],
            ['name' => 'SMMUM', 'code' => 'SMMUM'],
            ['name' => 'INSTRUMENT57', 'code' => 'INST57'],
            ['name' => 'CCR 1-4', 'code' => 'CCR1-4'],
            ['name' => 'SMOH', 'code' => 'SMOH'],
            ['name' => 'CCR 5-7', 'code' => 'CCR5-7'],
            ['name' => 'SMEP', 'code' => 'SMEP'],
            ['name' => 'MEKANIK ASH1-7', 'code' => 'MEK.ASH'],
            ['name' => 'GM', 'code' => 'GM'],
            ['name' => 'LISTRIK ASH17', 'code' => 'LSTK ASH17'],
            ['name' => 'MKMKLS', 'code' => 'MKMKLS'],
            ['name' => 'SIPIL', 'code' => 'SIPIL'],
            ['name' => 'LINGKUNGAN', 'code' => 'LING'],
            ['name' => 'TEK IEP LIST 1-7', 'code' => 'TEK IEP LIST 1-7'],
            ['name' => 'ADM BBA', 'code' => 'ADM BBA'],
            ['name' => 'RENDAL ASH', 'code' => 'RENDAL ASH'],
            ['name' => 'ASMAN EP', 'code' => 'ASMAN EP'],
            ['name' => 'PENYALURAN ABU', 'code' => 'PENYALURAN ABU'],
            ['name' => 'ASMAN PEP', 'code' => 'ASMAN PEP'],
        ];

        foreach ($data as $d) {
            Department::updateOrCreate(['code' => $d['code']], $d);
        }

        $this->command->info(' Departments: ' . count($data));
    }
}
