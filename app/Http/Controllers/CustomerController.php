<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class CustomerController extends Controller
{
    // =========================================================================
    // 1. BAGIAN BACKEND (ADMIN) - KEMBALIKAN SEPERTI SEMULA
    // =========================================================================
    public function index()
    {
        $customer = Customer::orderBy('id', 'desc')->get();
        return view('backend.v_customer.index', [
            'judul' => 'Customer',
            'sub' => 'Halaman Customer',
            'index' => $customer
        ]);
    }

    // =========================================================================
    // 2. BAGIAN LOGIN GOOGLE (OAUTH) - KEMBALIKAN SEPERTI SEMULA
    // =========================================================================
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            $user = User::where('email', $googleUser->email)->first();

            if ($user && !in_array((string) $user->role, ['customer', '2'], true)) {
                return redirect()->route('beranda')->with('error', 'Email tersebut bukan akun customer.');
            }

            if (!$user) {
                $user = User::create([
                    'nama' => $googleUser->name,
                    'email' => $googleUser->email,
                    'role' => 'customer',
                    'status' => 1,
                    'password' => Hash::make(str()->random(40)),
                ]);
            }

            Customer::firstOrCreate(
                ['user_id' => $user->id],
                ['google_id' => $googleUser->id]
            );

            Auth::login($user);
            request()->session()->regenerate();

            return redirect()->intended(route('beranda'));
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('beranda')->with('error', 'Login Google gagal. Periksa konfigurasi Google OAuth.');
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('beranda')->with('success', 'Anda telah berhasil logout.');
    }

    public function akun($id)
    {
        abort_unless(Auth::check() && (int) Auth::id() === (int) $id, 403);

        $customer = Customer::with('user')->where('user_id', $id)->firstOrFail();

        return view('v_customer.edit', [
            'judul' => 'Akun Saya',
            'subJudul' => 'Informasi Akun Customer',
            'edit' => $customer,
        ]);
    }

    public function updateAkun(Request $request, $id)
    {
        abort_unless(Auth::check() && (int) Auth::id() === (int) $id, 403);

        $customer = Customer::with('user')->where('user_id', $id)->firstOrFail();

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $customer->user_id,
            'hp' => 'nullable|string|min:10|max:13',
            'alamat' => 'nullable|string|max:2000',
            'pos' => 'nullable|string|max:10',
            'foto' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:2048',
        ], [
            'foto.image' => 'File foto harus berupa gambar.',
            'foto.max' => 'Ukuran foto maksimal 2 MB.',
        ]);

        $userData = [
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            'hp' => $validated['hp'] ?? null,
        ];

        if ($request->hasFile('foto')) {
            $oldPhoto = $customer->user->foto;
            $filename = now()->format('YmdHis') . '_' . uniqid() . '.' . $request->file('foto')->extension();
            $request->file('foto')->storeAs('img-customer', $filename, 'public');
            $userData['foto'] = $filename;

            if ($oldPhoto) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete('img-customer/' . $oldPhoto);
            }
        }

        $customer->user->update($userData);
        $customer->update([
            'alamat' => $validated['alamat'] ?? null,
            'pos' => $validated['pos'] ?? null,
        ]);

        return redirect()->route('customer.akun', $id)->with('success', 'Data akun berhasil diperbarui.');
    }

}