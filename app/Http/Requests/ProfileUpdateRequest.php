<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Tentukan apakah user diperbolehkan membuat request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi yang berlaku sesuai dengan form di Blade.
     */
    public function rules(): array
    {
        return [
            // Nama Lengkap
            'name' => ['required', 'string', 'max:255'],
            
            // Email (Unik kecuali untuk diri sendiri)
            'email' => [
                'required', 
                'string', 
                'lowercase', 
                'email', 
                'max:255', 
                Rule::unique(User::class)->ignore($this->user()->id)
            ],

            // Nama Perusahaan (Harus sama dengan name="company_name" di Blade)
            'company' => ['nullable', 'string', 'max:255'],

            // Nomor Telepon (Harus sama dengan name="phone" di Blade)
            'phone' => ['nullable', 'string', 'max:20'],

            // Tanggal Lahir (Jika kamu ada input birth_date)
            'birth_date' => ['nullable', 'date'], 

            // Alamat / Departemen (Harus sama dengan name="address" di Blade)
            'address' => ['nullable', 'string', 'max:500'], 
            
            // Validasi Foto Profil
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'], 
        ];
    }

    /**
     * Pesan error kustom (Opsional, biar lebih cakep kalau ada salah input)
     */
    public function messages(): array
    {
        return [
            'photo.max' => 'Ukuran foto jangan lebih dari 2MB ya sayang.',
            'photo.image' => 'File harus berupa gambar.',
            'email.unique' => 'Email ini sudah dipakai orang lain nih.',
        ];
    }
}