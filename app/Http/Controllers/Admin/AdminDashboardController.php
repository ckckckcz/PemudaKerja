<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\BimbinganMagang;
use App\Models\LowonganMagang;
use App\Models\HasilRekomendasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalDosen = Dosen::count();
        $totalMahasiswa = Mahasiswa::count();
        $totalLowongan = LowonganMagang::where('status_lowongan', 'open')->count();
        
        $totalMahasiswaMagang = BimbinganMagang::distinct('id_mahasiswa')
            ->where('status_bimbingan', 'aktif')
            ->count('id_mahasiswa');
        
        $rasio = $totalDosen > 0 ? round($totalMahasiswaMagang / $totalDosen, 2) : 0;
        
        $pertumbuhanMagang = $this->hitungPertumbuhanMagang();
        
        $statistikMagang = $this->getStatistikMagang();
        
        $distribusiBeban = $this->getDistribusiBebanDosen();
        
        // $efektivitasRekomendasi = $this->getEfektivitasRekomendasi();
        
        $trenIndustri = $this->getTrenIndustri();
        
        $distribusiFakultas = $this->getDistribusiFakultas();
        
        $chartData = $this->getChartData();

        return view('admin.dashboard', compact(
            'totalDosen',
            'totalMahasiswa', 
            'totalLowongan',
            'totalMahasiswaMagang',
            'rasio',
            'pertumbuhanMagang',
            'statistikMagang',
            'distribusiBeban',
            // 'efektivitasRekomendasi',
            'trenIndustri',
            'distribusiFakultas',
            'chartData'
        ));
    }
    
    private function hitungPertumbuhanMagang()
    {
        return 12;
    }
    
    private function getStatistikMagang()
    {
        $sudahMagang = BimbinganMagang::where('status_bimbingan', 'selesai')->distinct('id_mahasiswa')->count();
        $sedangMagang = BimbinganMagang::where('status_bimbingan', 'aktif')->distinct('id_mahasiswa')->count();
        $totalMahasiswa = Mahasiswa::count();
        $belumMulai = $totalMahasiswa - $sudahMagang - $sedangMagang;
        
        return [
            'sudah_magang' => $sudahMagang,
            'sedang_mencari' => $sedangMagang,
            'belum_mulai' => $belumMulai,
            'persentase_sudah' => $totalMahasiswa > 0 ? round(($sudahMagang / $totalMahasiswa) * 100, 1) : 0,
            'persentase_mencari' => $totalMahasiswa > 0 ? round(($sedangMagang / $totalMahasiswa) * 100, 1) : 0,
            'persentase_belum' => $totalMahasiswa > 0 ? round(($belumMulai / $totalMahasiswa) * 100, 1) : 0,
        ];
    }
    
    private function getDistribusiBebanDosen()
    {
        try {
            $optimal = Dosen::whereHas('bimbinganMagang', function($query) {
                $query->selectRaw('count(*) as jumlah')
                      ->groupBy('id_dosen')
                      ->havingRaw('count(*) <= 5');
            })->count();
            
            return [
                'optimal' => $optimal,
                'normal' => 28, // Contoh
                'overload' => 8, // Contoh
            ];
        } catch (\Exception $e) {
            return [
                'optimal' => 0,
                'normal' => 0,
                'overload' => 0,
            ];
        }
    }
    
    // private function getEfektivitasRekomendasi()
    // {
    //     try {
    //         $totalRekomendasi = HasilRekomendasi::count();
    //         $rekomendasiDiterima = HasilRekomendasi::where('status_rekomendasi', 'diterima')->count();
            
    //         return [
    //             'total' => $totalRekomendasi,
    //             'diterima' => $rekomendasiDiterima,
    //             'tingkat_keberhasilan' => $totalRekomendasi > 0 ? round(($rekomendasiDiterima / $totalRekomendasi) * 100, 1) : 0,
    //             'akurasi_matching' => 92.3, // Contoh
    //         ];
    //     } catch (\Exception $e) {
    //         return [
    //             'total' => 0,
    //             'diterima' => 0,
    //             'tingkat_keberhasilan' => 0,
    //             'akurasi_matching' => 0,
    //         ];
    //     }
    // }
    
    private function getTrenIndustri()
    {
        return [
            ['nama' => 'Teknologi Informasi', 'persentase' => 34.2, 'color' => '#3B82F6'],
            ['nama' => 'Keuangan & Perbankan', 'persentase' => 22.8, 'color' => '#10B981'],
            ['nama' => 'Manufaktur', 'persentase' => 18.5, 'color' => '#F59E0B'],
            ['nama' => 'Kesehatan', 'persentase' => 12.1, 'color' => '#8B5CF6'],
            ['nama' => 'Lainnya', 'persentase' => 12.4, 'color' => '#EF4444'],
        ];
    }
    
    private function getDistribusiFakultas()
    {
        return [
            [
                'nama' => 'Teknik',
                'jumlah_dosen' => 18,
                'jumlah_mahasiswa' => 142,
                'rasio' => 7.9,
                'persentase' => 37.5,
                'color' => '#3B82F6'
            ],
            [
                'nama' => 'Ekonomi',
                'jumlah_dosen' => 15,
                'jumlah_mahasiswa' => 98,
                'rasio' => 6.5,
                'persentase' => 31.3,
                'color' => '#10B981'
            ],
            [
                'nama' => 'Sains & Teknologi',
                'jumlah_dosen' => 15,
                'jumlah_mahasiswa' => 102,
                'rasio' => 6.8,
                'persentase' => 31.2,
                'color' => '#F59E0B'
            ],
        ];
    }
    
    private function getChartData()
    {
        // Data untuk chart - bisa disesuaikan dengan kebutuhan
        return [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            'datasets' => [
                [
                    'label' => 'Aplikasi Magang',
                    'data' => [120, 140, 160, 180, 200, 220],
                    'borderColor' => '#3B82F6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)'
                ]
            ]
        ];
    }
}
