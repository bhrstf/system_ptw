<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Permit extends Model
{
    use HasFactory;

    // --- STATUS CONSTANTS ---
    const STATUS_PENDING   = 'pending';
    const STATUS_APPROVED  = 'approved';
    const STATUS_ACTIVE    = 'active';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CLOSED    = 'closed';
    const STATUS_REJECTED  = 'rejected';

    protected $fillable = [
        'user_id', 'permit_type', 'valid_from', 'valid_until', 'pic_lead', 'pic_batamindo',
        'applicant_name', 'company', 'email', 'phone', 'location',
        'tools_used', 'ref_doc', 'man_power', 'work_scope_general', 'work_scope_detail',
        'hazards', 'ppe', 'hazards_other', 'ppe_other', 'safety_checklists', 'additional_instructions',
        'hse_representative', 'jsa_file', 'hiradc_file', 'worker_list_file',
        'competency_cert_file', 'work_procedure_file', 'tool_cert_file',
        'agreed_to_terms', 'applicant_confirmation', 'manager_name',
        'signature_manager', 'applicant_confirm_name', 'signature_applicant',
        
        // --- TAMBAHAN UNTUK VALIDASI LAPANGAN (PJA) & PENOMORAN ---
        'pja_name', 
        'signature_pja', 
        'validated_at',
        'ptw_number',      // Kolom untuk string PTW-OHSS-xxx-2026
        'ptw_sequence',    // Kolom untuk angka urut (1, 2, 3...)
        
        'status'
    ];
    
    protected $casts = [
        'permit_type' => 'array',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'hazards' => 'array',
        'ppe' => 'array',
        'man_power' => 'integer',
        'hazards_other' => 'json',
        'ppe_other' => 'json',
        'safety_checklists' => 'array',
        'agreed_to_terms' => 'boolean',
        'applicant_confirmation' => 'boolean',
        'signature_manager' => 'string',
        'signature_applicant' => 'string',
        'validated_at' => 'datetime',
    ];

    /**
     * LOGIK OTOMATIS: Generate Nomor PTW saat status berubah jadi 'active'
     */
    protected static function booted()
    {
        static::updating(function ($permit) {
            // Cek jika status berubah jadi 'active' DAN ptw_number masih kosong
            if ($permit->isDirty('status') && $permit->status === self::STATUS_ACTIVE && is_null($permit->ptw_number)) {
                
                $year = date('Y');

                // Ambil nomor urut tertinggi di tahun berjalan
                $lastSequence = self::whereYear('created_at', $year)
                                    ->whereNotNull('ptw_sequence')
                                    ->max('ptw_sequence');

                $nextSequence = $lastSequence ? $lastSequence + 1 : 1;
                
                // Format angka jadi 3 digit (contoh: 1 -> 001)
                $formattedSeq = str_pad($nextSequence, 3, '0', STR_PAD_LEFT);

                // Set nilai ke kolom terkait
                $permit->ptw_sequence = $nextSequence;
                $permit->ptw_number = "PTW-OHSS-{$formattedSeq}-{$year}";
                
                // Set waktu validasi otomatis jika belum ada
                if (is_null($permit->validated_at)) {
                    $permit->validated_at = now();
                }
            }
        });
    }

    /**
     * Helper untuk warna badge status di UI
     */
    public function getStatusBadgeAttribute()
    {
        $colors = [
            self::STATUS_PENDING   => 'badge-warning',
            self::STATUS_APPROVED  => 'badge-info',
            self::STATUS_ACTIVE    => 'badge-primary',
            self::STATUS_COMPLETED => 'badge-info',
            self::STATUS_CLOSED    => 'badge-success',
            self::STATUS_REJECTED  => 'badge-danger',
        ];

        return $colors[$this->status] ?? 'badge-secondary';
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function audit()
    {
        return $this->hasOne(Audit::class, 'permit_id');
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS (Link URL File)
    |--------------------------------------------------------------------------
    */

    public function getJsaUrlAttribute() { return $this->jsa_file ? Storage::url($this->jsa_file) : null; }
    public function getHiradcUrlAttribute() { return $this->hiradc_file ? Storage::url($this->hiradc_file) : null; }
    public function getWorkerListUrlAttribute() { return $this->worker_list_file ? Storage::url($this->worker_list_file) : null; }
    public function getCompetencyCertUrlAttribute() { return $this->competency_cert_file ? Storage::url($this->competency_cert_file) : null; }
    public function getWorkProcedureUrlAttribute() { return $this->work_procedure_file ? Storage::url($this->work_procedure_file) : null; }
    public function getToolCertUrlAttribute() { return $this->tool_cert_file ? Storage::url($this->tool_cert_file) : null; }

    /*
    |--------------------------------------------------------------------------
    | MASTER DATA & THEMES
    |--------------------------------------------------------------------------
    */

    public static function getHazardList()
    {
        return [
            'Getaran','Kebisingan','Benda Bergerak','Biologi','Air Bertekanan Tinggi',
            'Psikologi','Beracun','Titik Buta','Longsor','Titik Jepit','Kadar Oksigen',
            'Debu','Gas Bertekanan','Radiasi','Mudah Terbakar','Licin','Suhu Panas',
            'Ergonomi','Mudah Meledak','Benda Jatuh','Pengangkatan','Benda Tajam',
            'Listrik','Angin','Korosif','Lainnya'
        ];
    }

    public static function getPpeList()
    {
        return [
            'Kepala / Wajah' => ['Safety Helmet', 'Safety Glasses', 'Safety Google', 'Face Shield', 'Kedok Las', 'Lainnya'],
            'Telinga' => ['Ear Plug', 'Ear Muff', 'Lainnya'],
            'Kaki' => ['Safety Shoes', 'Safety Boots', 'Electrical Safety Boots', 'Lainnya'],
            'Ketinggian' => ['Fullbody Harness', 'Life Line', 'SRL', 'Lainnya'],
            'Badan' => ['Uniform', 'Coverall', 'Apron', 'Reflective Vest', 'Lainnya'],
            'Tangan' => ['Cotton Gloves', 'High Impact Gloves', 'Rubber Gloves', 'Chemical Gloves', 'Lainnya'],
            'Pernafasan' => ['N95 Mask', 'Dust Mask', 'Half Face Respirator', 'Full Face Respirator', 'SCBA', 'Lainnya']
        ];
    }

    public static function getMasterChecklist()
    {
        return [
            'Hot Work' => [
                'Pencegahan minimum harus dilengkapi dan diverifikasi sesuai dengan lingkup kerja di atas. Kosongkan kotak jika tidak diperlukan.' => [
                    'Gas Mapping Plan disetujui oleh Facility Owner',
                    'Pengecekan gas awal dilakukan dan hasilnya didokumentasikan',
                    'Pengujian gas lanjutan dilakukan berdasarkan hasil pengujian gas awal',
                    'Parit dalam radius 35ft (10m) dari open flame hot work harus ditutup dengan cara plugging dan diisi air, atau cara lain yang ekuivalen',
                    'Material yang mudah terbakar dalam radius 35ft (10m) yang tidak bisa dipindahkan harus ditutup cover/shield tahan api, atau harus tetap dalam keadaan basah selama open flame hot work berlangsung',
                    'Peralatan pemadam kebakaran tersedia di lokasi kerja (seperti APAR, pelindung percikan api, sekop, persediaan air yang cukup, dsb)',
                    'Peralatan pengelasan dan peralatan listrik lain yang digunakan harus digrounding/dibumikan',
                    'Media containment (seperti fire blanket, welding screen) digunakan untuk mengontrol percikan dari api terbuka (open flame)',
                    'Fire Watch tanpa tugas lain telah ditunjuk dan akan tetap di lokasi selama 30 menit setelah pekerjaan open flame hot work selesai',
                    'Area Hot Work dibarikade dan/atau dipasangkan tanda peringatan',
                    'Isi sebelumnya dari tangki / peralatan telah diidentifikasi'
                ],
                'Non-Critical Lifting & Rigging (isi bagian ini jika dalam pekerjaan hot work diperlukan proses pengangkatan)' => [
                    'Perlukan proses pengangkatan) Sertifikasi personil dan peralatan yang dibutuhkan diverifikasi dan valid',
                    'Berat beban pada rentang batas aman peralatan bekerja (SWL)',
                    'JSA berisi langkah-langkah pengangkatan',
                    'JSA didiskusikan dengan pihak terkait dan pekerja yang terlibat sebelum pekerjaan dimulai'
                ]
            ],
            'Electrical' => [
                'Pencegahan minimum harus dilengkapi dan diverifikasi sesuai dengan lingkup kerja di atas. Kosongkan kotak jika tidak diperlukan. *' => [
                    'Diagram / rencana isolasi disediakan untuk menjelaskan seluruh titik yang akan diisolasi.',
                    'Prosedur pengisolasian peralatan digunakan untuk mengisolasi seluruh titik isolasi peralatan secara berurutan',
                    'Perangkat isolasi (seperti blind, spade, skillet, dll.) yang digunakan sesuai untuk sumber energi berbahaya.',
                    'Titik isolasi yang diidentifikasi dikunci untuk mencegah pelepasan energi berbahaya yang tidak diinginkan.',
                    'Keadaan zero energy tercapai atau sisa energi berbahaya dilepaskan',
                    'Setiap pekerja yang berkepentingan dengan sistem yang diisolasi terlindungi oleh masing-masing Individual Lock',
                    'Pengujian gas dilakukan ketika membuka peralatan, pipa, bejana (vessel), dsb. yang berisi material berbahaya (beracun atau mudah terbakar)',
                    'Persetujuan Manager dibutuhkan, jika isolasi memerlukan Positive Physical Isolation (PPI) tetapi PPI tidak dapat dilakukan'
                ],
                'Energy berbahaya yang akan diisolasi (cek semua yang diterapkan): *' => [
                    'Mekanikal',
                    'Tekanan',
                    'Kimia',
                    'Radiasi',
                    'Gravitasi',
                    'Elektrikal',
                    'Temperature',
                    'Biologi',
                    'Suara',
                    'Gerak'
                ],
            ],
            'Excavation' => [
                'Pencegahan minimum harus dilengkapi dan diverifikasi sesuai dengan lingkup kerja di atas. Kosongkan kotak jika tidak diperlukan.' => [
                    'Area tersebut diklasifikasikan dalam ruang terbatas? (jika ya, Confine Space Permit di perlukan)',
                    'Rambu dan barikade yang memadai telah disediakan',
                    'Gambar untuk fasilitas bawah tanah telah diperiksa',
                    'Banksman disediakan untuk mengontrol penggunaan peralatan bergerak untuk penggalian',
                    'Shoring diperlukan untuk melindungi keruntuhan area galian',
                    'Tanah dan material dijauhkan minimal 1 meter dari tepi galian',
                    'Akses jalan cukup dan dipastikan tidak ada permukaan licin',
                    'Pembuatan parit atau penggalian berada di jalan umum (diperlukan blok jalan)',
                    'Sistem dewatering diperlukan'
                ],
                'Jalur tersebut telah bebas dari' => [
                    'Kabel listrik',
                    'Kabel instrumen',
                    'Pipa air',
                    'Kabel telepon',
                    'Gorong-gorong'
                ]
            ],
            'Working at Height' => [
                'Pencegahan minimum harus dilengkapi dan diverifikasi sesuai dengan lingkup kerja di atas. Kosongkan kotak jika tidak diperlukan.' => [
                    'Sebagian pekerjaan dapat dikerjakan di permukaan tanah',
                    'Jarak ketinggian sudah diketahui',
                    'Area kerja sudah terbebas dari bahaya listrik dan diberi pengaman atau isolasi',
                    'Area kerja berada dipermukaan yang landai',
                    'Area kerja berada dipermukaan yang becek / basah / berlumpur telah bersihkan hingga area kerja telah aman',
                    'Rambu Keselamatan sudah terpasang',
                    'Terdapat Hard Barricade di pembatas area kerja'
                ],
                'Working at Height (Bagian 1)' => [
                    'Bekerja di Permanent Platform',
                    'Mendirikan / Memodifikasi / Membongkar scaffolding',
                    'Bekerja pada scaffolding',
                    'Bekerja di Mobile Elevating Working Platform (MEWP)',
                    'Lainnya'
                ]
            ],
            'Lifting Operation' => [
                'Pencegahan minimum harus dilengkapi dan diverifikasi sesuai dengan lingkup kerja di atas. Kosongkan kotak jika tidak diperlukan.' => [
                    'Operator crane memiliki kompetensi yang dibuktikan dengan Sertifikasi yang valid',
                    'Rute perjalanan crane sudah ditentukan dan jelas',
                    'Area pengoperasian crane ditentukan dan pondasi dudukan crane kokoh',
                    'Fitur keselamatan, tanda peringatan dan penghalang disiapkan & dipasang',
                    'Area duduk Crane menjaga jarak aman dari penggalian',
                    'Crane atau kendaraan pengangkat lainnya yang disertifikasi, diperiksa dengan kode warna',
                    'Petugas pemberi sinyal / isyarat yang kompeten dalam posisinya ditunjuk & ditempatkan',
                    'Bahaya dari pekerjaan yang bersinggungan (SIMOPS) diperhitungkan',
                    'Bahaya dari pekerjaan yang berdekatan juga dipertimbangkan'
                ],
                'Critical Lifting & Rigging (Bagian 1)' => [
                    'Blind Lift',
                    'Complicated Lift',
                    'Personnel Man Basket Lift',
                    'Bekerja di Mobile Elevating Working Platform (MEWP)',
                    'Complex Lift',
                    'Heavy Lift',
                    'Critical Lift',
                    'Lainnya'
                ]
            ],
            'Confined Spac' => [
                'Pencegahan minimum harus dilengkapi dan diverifikasi sesuai dengan lingkup kerja di atas. Kosongkan kotak jika tidak diperlukan.' => [
                    'Semua koneksi ke ruang terbatas telah diisolasi',
                    'Pekerja memiliki kompetensi memasuki ruang terbatas dengan dibuktikan dengan memiliki sertifikasi',
                    'Alat-alat pernafasan sudah diperiksa dan dinyatakan aman untuk layak pakai',
                    'Memiliki pekerja yang memiliki kompetensi pengujian gas (AGT)',
                    'Ruang terbatas memiliki tingkat oksigen yang cukup untuk bekerja',
                    'Ruang terbatas aman dari sumber bahaya dan pekerjaan lainnya yang tidak ada hubungan pekerjaan terkait.',
                    'Melakukan Uji gas sebelum memasuki ruang terbatas, dan boleh memasuki setelah melakukan flushing,blowing,dll untuk membersihkan gas berbahaya diruang terbatas.',
                    'Lampu, akses dan jalan keluar sudah diberikan rambu peringatan dan dibarikade yang cukup',
                    'Tersedia pekerja yang standby di akses ruang terbatas sebagai pengawas',
                    'Penerangan ruang terbatas sudah tersertifikasi Gasproof',
                    'Pekerja sudah menjalani tes kesehatan medis untuk ruang terbatas',
                    'Terdapat Tim Rescue ruang terbatas dan memiliki rencana untuk keadaan darurat'
                ]
            ],
            'Cold Work' => [
                'Pencegahan minimum harus dilengkapi dan diverifikasi sesuai dengan lingkup kerja di atas. Kosongkan kotak jika tidak diperlukan.' => [
                        'Critical Protection diidentifikasi dan diverifikasi Executive Terkait',
                        'Bypass jangka pendek (<72 jam)',
                        'Bypass jangka panjang (>72 jam), MOC diperlukan',
                        'Rencana durasi Bypass (Jam)'
                    ],
                'Manual Excavation, dengan kedalaman < 4ft (1.2m) ' => [
                    'Peralatan bawah tanah (contoh: pipa, kabel instrumen/listrik/fiber optic, saluran, dll.) diverifikasi dan ditandai',
                    'Pasang barikade untuk mencegah akses yang tidak diizinkan'
                ],
                'Non-Critical Lifting & Rigging' => [
                    'Sertifikasi personil dan peralatan yang dibutuhkan diverifikasi dan valid',
                    'Berat beban pada rentang batas aman peralatan bekerja (SWL)',
                    'JSA berisi langkah-langkah melakukan pengangkatan',
                    'JSA didiskusikan dengan pihak terkait dan pekerja yang terlibat sebelum pekerjaan dimulai'
                ],
                'Simple Isolation (jika lingkup kerja di atas membutuhkan simple isolation) ' => [
                    'Titik isolasi telah diidentifikasi dan dikunci / ditag',
                    'Keadaan zero energy telah tercapai'
                ]
            ]
        ];
    }

    public static function getPermitTheme($type)
    {
        $themes = [
            'Hot Work'          => ['bg' => '#FF0000', 'text' => '#FFFFFF', 'label' => 'HOT WORK'],
            'Electrical'        => ['bg' => '#FFFF00', 'text' => '#000000', 'label' => 'ELECTRICAL'],
            'Working at Height' => ['bg' => '#00CCFF', 'text' => '#000000', 'label' => 'WORKING AT HEIGHT'],
            'Excavation'        => ['bg' => '#8B7500', 'text' => '#FFFFFF', 'label' => 'EXCAVATION'],
            'Lifting Operation' => ['bg' => '#336699', 'text' => '#FFFFFF', 'label' => 'LIFTING OPERATION'],
            'Cold Work'         => ['bg' => '#0070C0', 'text' => '#FFFFFF', 'label' => 'COLD WORK'],
            'Confined Space'    => ['bg' => '#00B050', 'text' => '#FFFFFF', 'label' => 'CONFINED SPACE'],
        ];

        return $themes[$type] ?? ['bg' => '#0070C0', 'text' => '#FFFFFF', 'label' => 'GENERAL WORK'];
    }

    public static function getSafetyChecklist()
    {
        $master = self::getMasterChecklist();
        $flattened = [];
        foreach ($master as $type => $categories) {
            foreach ($categories as $subTitle => $questions) {
                foreach ($questions as $q) {
                    $flattened[$type][] = $q;
                }
            }
        }
        return $flattened;
    }
}