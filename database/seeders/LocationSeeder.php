<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['building' => 'ADB', 'floor' => '1', 'room' => 'SIS', 'division' => 'Sistem Informasi', 'full_name' => 'ADB - SIS'],
            ['building' => 'ALBES', 'floor' => '1', 'room' => 'ALBES', 'division' => 'ALBES', 'full_name' => 'ALBES - 1 - ALBES'],
            ['building' => 'ADB', 'floor' => '1', 'room' => 'KEUANGAN', 'division' => 'keuangan', 'full_name' => 'ADB - 1 - KEUANGAN'],
            ['building' => 'ADB', 'floor' => '1', 'room' => 'HUMAS', 'division' => 'HUMAS', 'full_name' => 'ADB - 1 - HUMAS'],
            ['building' => 'ADB', 'floor' => '2', 'room' => 'SMT', 'division' => 'SMT', 'full_name' => 'ADB - SMT'],
            ['building' => 'ADB', 'floor' => '1', 'room' => 'AKUNTANSI', 'division' => 'AKUNTANSI', 'full_name' => 'ADB - 1 - AKUNTANSI'],
            ['building' => 'ADB', 'floor' => '2', 'room' => 'EFISIENSI', 'division' => 'EFISIENSI', 'full_name' => 'ADB - EFISIENSI'],
            ['building' => 'ADB', 'floor' => '2', 'room' => 'PUK', 'division' => 'PUK', 'full_name' => 'ADB - PUK'],
            ['building' => 'ADB', 'floor' => '1', 'room' => 'FASAU', 'division' => 'FASAU', 'full_name' => 'ADB - 1 - FASAU'],
            ['building' => 'ADB', 'floor' => '1', 'room' => 'POLIKLINIK', 'division' => 'POLIKLINIK', 'full_name' => 'ADB - 1 - POLIKLINIK'],
            ['building' => 'ADB', 'floor' => '1', 'room' => 'PBJ01', 'division' => 'PBJ01', 'full_name' => 'ADB - 1 - PBJ01'],
            ['building' => 'ADB', 'floor' => '1', 'room' => 'PBJ02', 'division' => 'PBJ02', 'full_name' => 'ADB - 1 - PBJ02'],
            ['building' => 'ADB', 'floor' => '1', 'room' => 'LINGKUNGAN', 'division' => 'LINGKUNGAN', 'full_name' => 'ADB - 1 - LINGKUNGAN'],
            ['building' => 'ADB', 'floor' => '2', 'room' => 'ENGEENERING', 'division' => 'ENGENERING', 'full_name' => 'ADB - 2 - ENGEENERING'],
            ['building' => 'ADB', 'floor' => '1', 'room' => 'RENDAL K3', 'division' => 'RENDAL K3', 'full_name' => 'ADB - 1 - RENDAL K3'],
            ['building' => 'ADB', 'floor' => '1', 'room' => 'PP', 'division' => 'PP', 'full_name' => 'ADB - 1 - PP'],
            ['building' => 'ADB', 'floor' => '2', 'room' => 'RSO', 'division' => 'RSO', 'full_name' => 'ADB - 2 - RSO'],
            ['building' => 'ADB', 'floor' => '1', 'room' => 'ANGGARAN', 'division' => 'ANGGARAN', 'full_name' => 'ADB - 1 - ANGGARAN'],
            ['building' => 'ADB', 'floor' => '2', 'room' => 'SDM', 'division' => 'SDM', 'full_name' => 'ADB - 2 - SDM'],
            ['building' => 'ADB', 'floor' => '2', 'room' => 'MPBJ01', 'division' => 'MPBJ01', 'full_name' => 'ADB - 2 - MPBJ01'],
            ['building' => 'ADB', 'floor' => '2', 'room' => 'BGP', 'division' => 'BGP', 'full_name' => 'ADB - 2 - BGP'],
            ['building' => 'ADB', 'floor' => '2', 'room' => 'MPBJ02', 'division' => 'PBJ02', 'full_name' => 'ADB - 2 - MPBJ02'],
            ['building' => 'ADB', 'floor' => '2', 'room' => 'SEKERTARIAT', 'division' => 'SEKERTARIAT', 'full_name' => 'ADB - 2 - SEKERTARIAT'],
            ['building' => 'ADB', 'floor' => '2', 'room' => 'MOPN17', 'division' => 'MOPN17', 'full_name' => 'ADB - 2 - MOPN17'],
            ['building' => 'ADB', 'floor' => '2', 'room' => 'MHAR1-4', 'division' => 'MHAR1-4', 'full_name' => 'ADB - 2 - MHAR1-4'],
            ['building' => 'ADB', 'floor' => '2', 'room' => 'PERPUSTAKAAN', 'division' => 'PERPUSTAKAAN', 'full_name' => 'ADB - 2 - PERPUSTAKAAN'],
            ['building' => 'ADB', 'floor' => '2', 'room' => 'MHAR5-7', 'division' => 'MHAR5-7', 'full_name' => 'ADB - 2 - MHAR5-7'],
            ['building' => 'HAR57', 'floor' => '1', 'room' => 'ALATBANTU57', 'division' => 'ALATBANTU57', 'full_name' => 'HAR57 - 1 - ALATBANTU57'],
            ['building' => 'ADB', 'floor' => '2', 'room' => 'MSIK', 'division' => 'MSIK', 'full_name' => 'ADB - 2 - MSIK'],
            ['building' => 'HAR57', 'floor' => '2', 'room' => 'OUTAGE57', 'division' => 'OUTAGE57', 'full_name' => 'HAR57 - 2 - OUTAGE57'],
            ['building' => 'ADB', 'floor' => '2', 'room' => 'MPEP', 'division' => 'MPEP', 'full_name' => 'ADB - 2 - MPEP'],
            ['building' => 'CENTRAL', 'floor' => '2', 'room' => 'OUTAGE 1-4', 'division' => 'OUTAGE 1-4', 'full_name' => 'CENTRAL - 2 - OUTAGE 1-4'],
            ['building' => 'ADB', 'floor' => '2', 'room' => 'MREP', 'division' => 'MREP', 'full_name' => 'ADB - 2 - MREP'],
            ['building' => 'CENTRAL', 'floor' => '2', 'room' => 'PDM', 'division' => 'PDM', 'full_name' => 'CENTRAL - 2 - PDM'],
            ['building' => 'HAR57', 'floor' => '2', 'room' => 'RENDAL57', 'division' => 'RENDAL57', 'full_name' => 'HAR57 - 2 - RENDAL57'],
            ['building' => 'CENTRAL', 'floor' => '2', 'room' => 'RENDAL 1-4', 'division' => 'RENDAL 1-4', 'full_name' => 'CENTRAL - 2 - RENDAL 1-4'],
            ['building' => 'CENTRAL', 'floor' => '3', 'room' => 'LISTRIK 1-4', 'division' => 'LISTRIK 1-4', 'full_name' => 'CENTRAL - 3 - LISTRIK 1-4'],
            ['building' => 'CENTRAL', 'floor' => '3', 'room' => 'BOILER 1-4', 'division' => 'BOILER 1-4', 'full_name' => 'CENTRAL - 3 - BOILER 1-4'],
            ['building' => 'CENTRAL', 'floor' => '3', 'room' => 'TURBIN 1-4', 'division' => 'TURBIN 1-4', 'full_name' => 'CENTRAL - 3 - TURBIN 1-4'],
            ['building' => 'CENTRAL', 'floor' => '4', 'room' => 'INSTRUMEN 1-4', 'division' => 'INSTRUMEN 1-4', 'full_name' => 'CENTRAL - 4 - INSTRUMEN 1-4'],
            ['building' => 'CENTRAL', 'floor' => '4', 'room' => 'LABORATORIUM', 'division' => 'LABORATORIUM', 'full_name' => 'CENTRAL - 4 - LABORATORIUM'],
            ['building' => 'DESAL 1-4', 'floor' => '1', 'room' => 'DESAL 1-4', 'division' => 'DESAL 1-4', 'full_name' => 'DESAL 1-4 - 1 - DESAL 1-4'],
            ['building' => 'HAR57', 'floor' => '3', 'room' => 'TURBIN57', 'division' => 'TURBIN57', 'full_name' => 'HAR57 - 3 - TURBIN57'],
            ['building' => 'DESAL 5-7', 'floor' => '1', 'room' => 'DESAL 5-7', 'division' => 'DESAL 5-7', 'full_name' => 'DESAL 5-7 - 1 - DESAL 5-7'],
            ['building' => 'ASH HANDLING 1-4', 'floor' => '1', 'room' => 'ASH HANDLING 1-4', 'division' => 'ASH HANDLING 1-4', 'full_name' => 'ASH HANDLING 1-4 - 1 - ASH HANDLING 1-4'],
            ['building' => 'ASH HANDLING 5-7', 'floor' => '1', 'room' => 'ASH HANDLING 5-7', 'division' => 'ASH HANDLING 5-7', 'full_name' => 'ASH HANDLING 5-7 - 1 - ASH HANDLING 5-7'],
            ['building' => 'ADB', 'floor' => '2', 'room' => 'MSMH', 'division' => 'MSMH', 'full_name' => 'ADB - 2 - MSMH'],
            ['building' => 'CHCR', 'floor' => '1', 'room' => 'CHCR', 'division' => 'CHCR', 'full_name' => 'CHCR - 1 - CHCR'],
            ['building' => 'HAR57', 'floor' => '3', 'room' => 'BOILER57', 'division' => 'BOILER57', 'full_name' => 'HAR57 - 3 - BOILER57'],
            ['building' => 'TOWER G', 'floor' => '1', 'room' => 'TOWER G', 'division' => 'TOWER G', 'full_name' => 'TOWER G - 1 - TOWER G'],
            ['building' => 'GUDANG 5-7', 'floor' => '1', 'room' => 'GUDANG 5-7', 'division' => 'GUDANG 5-7', 'full_name' => 'GUDANG 5-7 - 1 - GUDANG 5-7'],
            ['building' => 'GUDANG 1-4', 'floor' => '1', 'room' => 'GUDANG 1-4', 'division' => 'GUDANG 1-4', 'full_name' => 'GUDANG 1-4 - 1 - GUDANG 1-4'],
            ['building' => 'HAR57', 'floor' => '4', 'room' => 'LISTRIK57', 'division' => 'LISTRIK57', 'full_name' => 'HAR57 - 4 - LISTRIK57'],
            ['building' => 'ADB', 'floor' => '2', 'room' => 'SMMUM', 'division' => 'SMMUM', 'full_name' => 'ADB - 2 - SMMUM'],
            ['building' => 'HAR57', 'floor' => '4', 'room' => 'INSTRUMENT57', 'division' => 'INSTRUMENT57', 'full_name' => 'HAR57 - 4 - INSTRUMENT57'],
            ['building' => 'ADB', 'floor' => '2', 'room' => 'SMOH', 'division' => 'SMOH', 'full_name' => 'ADB - 2 - SMOH'],
            ['building' => 'ADB', 'floor' => '2', 'room' => 'SMEP', 'division' => 'SMEP', 'full_name' => 'ADB - 2 - SMEP'],
            ['building' => 'CCR 1-4', 'floor' => '1', 'room' => 'CONTROL ROOM 1-4', 'division' => 'CCR 1-4', 'full_name' => 'CCR 1-4 - 1 - CONTROL ROOM 1-4'],
            ['building' => 'CCR 5-7', 'floor' => '1', 'room' => 'CONTROL ROOM 5-7', 'division' => 'CCR 5-7', 'full_name' => 'CCR 5-7 - 1 - CONTROL ROOM 5-7'],
            ['building' => 'ADB', 'floor' => '2', 'room' => 'GM', 'division' => 'GM', 'full_name' => 'ADB - 2 - GM'],
            ['building' => 'JBIC', 'floor' => '1', 'room' => 'MEKANIK ASH17', 'division' => 'MEKANIK ASH1-7', 'full_name' => 'JBIC - 1 - MEKANIK ASH17'],
            ['building' => 'JBIC', 'floor' => '1', 'room' => 'LISTRIK ASH17', 'division' => 'LISTRIK ASH17', 'full_name' => 'JBIC - 1 - LISTRIK ASH17'],
            ['building' => 'ADB', 'floor' => '2', 'room' => 'MKMKLS', 'division' => 'MKMKLS', 'full_name' => 'ADB - 2 - MKMKLS'],
            ['building' => 'BATUBARA', 'floor' => '1', 'room' => 'SIPIL', 'division' => 'SIPIL', 'full_name' => 'BATUBARA - 1 - SIPIL'],
            ['building' => 'BATUBARA', 'floor' => '1', 'room' => 'LINGKUNGAN', 'division' => 'LINGKUNGAN', 'full_name' => 'BATUBARA - 1 - LINGKUNGAN'],
            ['building' => 'BBA', 'floor' => '1', 'room' => 'TEK IEP LIST 1-7', 'division' => 'TEK IEP LIST 1-7', 'full_name' => 'BBA - 1 - TEK IEP LIST 1-7'],
            ['building' => 'BBA', 'floor' => '1', 'room' => 'ADM BBA', 'division' => 'ADM BBA', 'full_name' => 'BBA - 1 - ADM BBA'],
            ['building' => 'BBA', 'floor' => '1', 'room' => 'RENDAL ASH', 'division' => 'RENDAL ASH', 'full_name' => 'BBA - 1 - RENDAL ASH'],
            ['building' => 'BBA', 'floor' => '1', 'room' => 'ASMAN EP', 'division' => 'ASMAN EP', 'full_name' => 'BBA - 1 - ASMAN EP'],
            ['building' => 'BBA', 'floor' => '1', 'room' => 'PENYALURAN ABU', 'division' => 'PENYALURAN ABU', 'full_name' => 'BBA - 1 - PENYALURAN ABU'],
            ['building' => 'BBA', 'floor' => '1', 'room' => 'ASMAN PEP', 'division' => 'ASMAN PEP', 'full_name' => 'BBA - 1 - ASMAN PEP'],
        ];

        foreach ($data as $d) {
            Location::updateOrCreate(
                ['building' => $d['building'], 'floor' => $d['floor'], 'room' => $d['room']],
                $d
            );
        }

        $this->command->info(' Locations: ' . count($data));
    }
}
