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
        'ptw_number', 
        'ptw_sequence',
        
        // --- TAMBAHAN FIELD BARU (HASIL MIGRASI) ---
        'rencana_durasi_bypass_jam',
        'jumlah_titik_isolasi',
        'penjelasan_zero_energy',
        'check_content_identified',
        'check_isolation_diagram',
        'check_zero_energy_achieved',
        
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
        
        // --- CASTING FIELD BARU ---
        'rencana_durasi_bypass_jam' => 'integer',
        'jumlah_titik_isolasi' => 'integer',
        'check_content_identified' => 'boolean',
        'check_isolation_diagram' => 'boolean',
        'check_zero_energy_achieved' => 'boolean',
    ];

    /**
     * LOGIK OTOMATIS: Generate Nomor PTW saat status berubah jadi 'active'
     */
    protected static function booted()
    {
        static::updating(function ($permit) {
            // Deteksi jika status berubah aktif DAN nomornya kosong atau salah format (tidak diawali PTW-OHSS-)
            if ($permit->isDirty('status') && $permit->status === self::STATUS_ACTIVE && (empty($permit->ptw_number) || !str_starts_with($permit->ptw_number, 'PTW-OHSS-'))) {
                
                $year = date('Y');

                // 1. Ambil nilai angka urutan (ptw_sequence) tertinggi di tahun galian ini
                $maxSequence = self::whereYear('created_at', $year)->max('ptw_sequence');

                // 2. Jika kolom ptw_sequence ternyata kosong atau bernilai 0 karena data lama hancur,
                // kita bongkar string ptw_number terakhir secara paksa sebagai backup cadangan keselamatan
                if (empty($maxSequence) || $maxSequence == 0) {
                    $lastByNumber = self::whereYear('created_at', $year)
                                        ->whereNotNull('ptw_number')
                                        ->orderBy('id', 'desc')
                                        ->first();

                    if ($lastByNumber) {
                        // Ambil angka dari format string (baik dari PTW-00008 maupun PTW-OHSS-003)
                        preg_match_all('!\d+!', $lastByNumber->ptw_number, $matches);
                        $foundNumbers = $matches[0] ?? [];
                        // Ambil nomor urutnya (biasanya kelompok angka pertama atau sebelum tahun)
                        $maxSequence = isset($foundNumbers[0]) ? (int)$foundNumbers[0] : 0;
                    }
                }

                // 3. Tentukan nomor urut berikutnya (pasti melanjutkan urutan terbesar di database)
                $nextSequence = $maxSequence ? $maxSequence + 1 : 1;
                
                $formattedSeq = str_pad($nextSequence, 3, '0', STR_PAD_LEFT);

                $permit->ptw_sequence = $nextSequence;
                $permit->ptw_number = "PTW-OHSS-{$formattedSeq}-{$year}";
                
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
                    ['text' => 'Gas Mapping Plan disetujui oleh Facility Owner'],
                    ['text' => 'Pengecekan gas awal dilakukan dan hasilnya didokumentasikan'],
                    ['text' => 'Pengujian gas lanjutan dilakukan berdasarkan hasil pengujian gas awal'],
                    ['text' => 'Parit dalam radius 35ft (10m) dari open flame hot work harus ditutup dengan cara plugging dan diisi air, atau cara lain yang ekuivalen'],
                    ['text' => 'Material yang mudah terbakar dalam radius 35ft (10m) yang tidak bisa dipindahkan harus ditutup cover/shield tahan api, atau harus tetap dalam keadaan basah selama open flame hot work berlangsung'],
                    ['text' => 'Peralatan pemadam kebakaran tersedia di lokasi kerja (seperti APAR, pelindung percikan api, sekop, persediaan air yang cukup, dsb)'],
                    ['text' => 'Peralatan pengelasan dan peralatan listrik lain yang digunakan harus digrounding/dibumikan'],
                    ['text' => 'Media containment (seperti fire blanket, welding screen) digunakan untuk mengontrol percikan dari api terbuka (open flame)'],
                    ['text' => 'Fire Watch tanpa tugas lain telah ditunjuk dan akan tetap di lokasi selama 30 menit setelah pekerjaan open flame hot work selesai'],
                    ['text' => 'Area Hot Work dibarikade dan/atau dipasangkan tanda peringatan'],
                    [
                        'text' => 'Isi sebelumnya dari tangki / peralatan telah diidentifikasi',
                        'input_tambahan' => [
                            'type' => 'text',
                            'label' => 'Jelaskan *',
                            'name' => 'Jelaskan'
                        ]
                    ]
                ],
                'Non-Critical Lifting & Rigging (isi bagian ini jika dalam pekerjaan hot work diperlukan proses pengangkatan)' => [
                    ['text' => 'Perlukan proses pengangkatan) Sertifikasi personil dan peralatan yang dibutuhkan diverifikasi dan valid'],
                    ['text' => 'Berat beban pada rentang batas aman peralatan bekerja (SWL)'],
                    ['text' => 'JSA berisi langkah-langkah pengangkatan'],
                    ['text' => 'JSA didiskusikan dengan pihak terkait dan pekerja yang terlibat sebelum pekerjaan dimulai']
                ]
            ],
            'Electrical' => [
                'Pencegahan minimum harus dilengkapi dan diverifikasi sesuai dengan lingkup kerja di atas. Kosongkan kotak jika tidak diperlukan. *' => [
                    [
                        'text' => 'Diagram / rencana isolasi disediakan untuk menjelaskan seluruh titik yang akan diisolasi.',
                        'input_tambahan' => [
                            'type' => 'text',
                            'label' => 'Jumlah titik isolasi yang diidentifikasi untuk dikunci: *',
                            'name' => 'jumlah_titik_isolasi'
                        ]
                    ],
                    ['text' => 'Prosedur pengisolasian peralatan digunakan untuk mengisolasi seluruh titik isolasi peralatan secara berurutan'],
                    ['text' => 'Perangkat isolasi (seperti blind, spade, skillet, dll.) yang digunakan sesuai untuk sumber energi berbahaya.'],
                    ['text' => 'Titik isolasi yang diidentifikasi dikunci untuk mencegah pelepasan energi berbahaya yang tidak diinginkan.'],
                    [
                        'text' => 'Keadaan zero energy tercapai atau sisa energi berbahaya dilepaskan',
                        'input_tambahan' => [
                            'type' => 'textarea',
                            'label' => 'Jelaskan bagaimana: *',
                            'name' => 'penjelasan_zero_energy'
                        ]
                    ],
                    ['text' => 'Setiap pekerja yang berkepentingan dengan sistem yang diisolasi terlindungi oleh masing-masing Individual Lock'],
                    ['text' => 'Pengujian gas dilakukan ketika membuka peralatan, pipa, bejana (vessel), dsb. yang berisi material berbahaya (beracun atau mudah terbakar)'],
                    ['text' => 'Persetujuan Manager dibutuhkan, jika isolasi memerlukan Positive Physical Isolation (PPI) tetapi PPI tidak dapat dilakukan']
                ],
                'Energy berbahaya yang akan diisolasi (cek semua yang diterapkan): *' => [
                    ['text' => 'Mekanikal'], ['text' => 'Tekanan'], ['text' => 'Kimia'], ['text' => 'Radiasi'], ['text' => 'Gravitasi'],
                    ['text' => 'Elektrikal'], ['text' => 'Temperature'], ['text' => 'Biologi'], ['text' => 'Suara'], ['text' => 'Gerak']
                ],
            ],
            'Excavation' => [
                'Pencegahan minimum harus dilengkapi dan diverifikasi sesuai dengan lingkup kerja di atas. Kosongkan kotak jika tidak diperlukan.' => [
                    ['text' => 'Area tersebut diklasifikasikan dalam ruang terbatas? (jika ya, Confine Space Permit di perlukan)'],
                    ['text' => 'Rambu dan barikade yang memadai telah disediakan'],
                    ['text' => 'Gambar untuk fasilitas bawah tanah telah diperiksa'],
                    ['text' => 'Banksman disediakan untuk mengontrol penggunaan peralatan bergerak untuk penggalian'],
                    ['text' => 'Shoring diperlukan untuk melindungi keruntuhan area galian'],
                    ['text' => 'Tanah dan material dijauhkan minimal 1 meter dari tepi galian'],
                    ['text' => 'Akses jalan cukup dan dipastikan tidak ada permukaan licin'],
                    ['text' => 'Pembuatan parit atau penggalian berada di jalan umum (diperlukan blok jalan)'],
                    ['text' => 'Sistem dewatering diperlukan']
                ],
                'Jalur tersebut telah bebas dari' => [
                    ['text' => 'Kabel listrik'], ['text' => 'Kabel instrumen'], ['text' => 'Pipa air'], ['text' => 'Kabel telepon'], ['text' => 'Gorong-gorong']
                ]
            ],
            'Working at Height' => [
                'Pencegahan minimum harus dilengkapi dan diverifikasi sesuai dengan lingkup kerja di atas. Kosongkan kotak jika tidak diperlukan.' => [
                    ['text' => 'Sebagian pekerjaan dapat dikerjakan di permukaan tanah'],
                    ['text' => 'JSA jarak ketinggian sudah diketahui'],
                    ['text' => 'Area kerja sudah terbebas dari bahaya listrik dan diberi pengaman atau isolasi'],
                    ['text' => 'Area kerja berada dipermukaan yang landai'],
                    ['text' => 'Area kerja berada dipermukaan yang becek / basah / berlumpur telah bersihkan hingga area kerja telah aman'],
                    ['text' => 'Rambu Keselamatan sudah terpasang'],
                    ['text' => 'Terdapat Hard Barricade di pembatas area kerja']
                ],
                'Working at Height (Bagian 1)' => [
                    ['text' => 'Bekerja di Permanent Platform'],
                    ['text' => 'Mendirikan / Memodifikasi / Membongkar scaffolding'],
                    ['text' => 'Bekerja pada scaffolding'],
                    ['text' => 'Bekerja di Mobile Elevating Working Platform (MEWP)'],
                    ['text' => 'Lainnya']
                ],
                'Working at Height (Bagian 2) *' => [
                    ['text' => 'Sertifikasi personil dan peralatan yang dibutuhkan diverifikasi dan valid'],
                    ['text' => 'Peralatan pelindung jatuh diinspeksi dan/atau disertifikasi'],
                    ['text' => 'Fall Arrest dipakai'],
                    ['text' => 'Fall Restraint dipakai'],
                    ['text' => 'Rescue Plan tersedia dan dipahami oleh pekerja diketinggian yang menggunakan fall arrest system']
                ]
            ],
            'Lifting Operation' => [
                'Pencegahan minimum harus dilengkapi dan diverifikasi sesuai dengan lingkup kerja di atas. Kosongkan kotak jika tidak diperlukan.' => [
                    ['text' => 'Operator crane memiliki kompetensi yang dibuktikan dengan Sertifikasi yang valid'],
                    ['text' => 'Route perjalanan crane sudah ditentukan dan jelas'],
                    ['text' => 'Area peng pengoperasian crane ditentukan dan pondasi dudukan crane kokoh'],
                    ['text' => 'Fitur keselamatan, tanda peringatan dan penghalang disiapkan & dipasang'],
                    ['text' => 'Area duduk Crane menjaga jarak aman dari penggalian'],
                    ['text' => 'Crane atau kendaraan pengangkat lainnya yang disertifikasi, diperiksa dengan kode warna'],
                    ['text' => 'Petugas pemberi sinyal / isyarat yang kompeten dalam posisinya ditunjuk & ditempatkan'],
                    ['text' => 'Bahaya dari pekerjaan yang bersinggungan (SIMOPS) diperhitungkan'],
                    ['text' => 'Bahaya dari pekerjaan yang berdekatan juga dipertimbangkan']
                ],
                'Critical Lifting & Rigging (Bagian 1)' => [
                    ['text' => 'Blind Lift'],
                    ['text' => 'Complicated Lift'],
                    ['text' => 'Personnel Man Basket Lift'],
                    ['text' => 'Bekerja di Mobile Elevating Working Platform (MEWP)'],
                    ['text' => 'Complex Lift'],
                    ['text' => 'Heavy Lift'],
                    ['text' => 'Critical Lift'],
                    ['text' => 'Lainnya']
                ],
                'Critical Lifting & Rigging (Bagian 2) *' => [
                    ['text' => 'Lift Plan tertulis dibuat, direview, disetujui, didiskusikan dan dipahami oleh pihak terkait sebelum pekerjaan dimulai'],
                    ['text' => 'Sertifikasi personil dan peralatan yang dibutuhkan diverifikasi dan valid']
                ]
            ],
            'Confined Space' => [
                'Pencegahan minimum harus dilengkapi dan diverifikasi sesuai dengan lingkup kerja di atas. Kosongkan kotak jika tidak diperlukan.' => [
                    ['text' => 'Semua koneksi ke ruang terbatas telah diisolasi'],
                    ['text' => 'Pekerja memiliki kompetensi memasuki ruang terbatas dengan dibuktikan dengan memiliki sertifikasi'],
                    ['text' => 'Alat-alat pernafasan sudah diperiksa dan dinyatakan aman untuk layak pakai'],
                    ['text' => 'Memiliki pekerja yang memiliki kompetensi pengujian gas (AGT)'],
                    ['text' => 'Ruang terbatas memiliki tingkat oksigen yang cukup untuk bekerja'],
                    ['text' => 'Ruang terbatas aman dari sumber bahaya dan pekerjaan lainnya yang tidak ada hubungan pekerjaan terkait.'],
                    ['text' => 'Melakukan Uji gas sebelum memasuki ruang terbatas, dan boleh memasuki setelah melakukan flushing,blowing,dll untuk membersihkan gas berbahaya diruang terbatas.'],
                    ['text' => 'Lampu, akses dan jalan keluar sudah diberikan rambu peringatan dan dibarikade yang cukup'],
                    ['text' => 'Tersedia pekerja yang standby di akses ruang terbatas sebagai pengawas'],
                    ['text' => 'Penerangan ruang terbatas sudah tersertifikasi Gasproof'],
                    ['text' => 'Pekerja sudah menjalani tes kesehatan medis untuk ruang terbatas'],
                    ['text' => 'Terdapat Tim Rescue ruang terbatas dan memiliki rencana untuk keadaan darurat']
                ]
            ],
            'Cold Work' => [
                'Pencegahan minimum harus dilengkapi dan diverifikasi sesuai dengan lingkup kerja di atas. Kosongkan kotak jika tidak diperlukan.' => [
                    ['text' => 'Critical Protection diidentifikasi dan diverifikasi Executive Terkait'],
                    ['text' => 'Bypass jangka pendek (<72 jam)'],
                    ['text' => 'Bypass jangka panjang (>72 jam), MOC diperlukan'],
                    [
                        'text' => 'Rencana durasi Bypass (Jam)',
                        'input_tambahan' => [
                            'type' => 'text',
                            'label' => 'Rencana durasi Bypass: _____ jam *',
                            'name' => 'rencana_durasi_bypass_jam'
                        ]
                    ]
                ],
                'Manual Excavation, dengan kedalaman < 4ft (1.2m) ' => [
                    ['text' => 'Peralatan bawah tanah (contoh: pipa, kabel instrumen/listrik/fiber optic, saluran, dll.) diverifikasi dan ditandai'],
                    ['text' => 'Pasang barikade untuk mencegah akses yang tidak diizinkan']
                ],
                'Non-Critical Lifting & Rigging' => [
                    ['text' => 'Sertifikasi personil dan peralatan yang dibutuhkan diverifikasi dan valid'],
                    ['text' => 'Berat beban pada rentang batas aman peralatan bekerja (SWL)'],
                    ['text' => 'JSA berisi langkah-langkah melakukan pengangkatan'],
                    ['text' => 'JSA didiskusikan dengan pihak terkait dan pekerja yang terlibat sebelum pekerjaan dimulai']
                ],
                'Simple Isolation (jika lingkup kerja di atas membutuhkan simple isolation) ' => [
                    ['text' => 'Titik isolasi telah diidentifikasi dan dikunci / ditag'],
                    ['text' => 'Keadaan zero energy telah tercapai']
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