<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    public function showRequestForm()
    {
        return view('autenticacion.email');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
        'email' => 'required|email|exists:users,email',
    ], [
        'email.required' => 'El campo Email es obligatorio.',
        'email.email' => 'Debe ingresar un formato de correo electrónico válido.',
        'email.exists' => 'No encontramos un usuario con esta dirección de correo.', // Mensaje clave
    ]);

        $token = Str::random(60);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => $token, 'created_at' => now()]
        );

        Mail::send('emails.reset-password', ['token' => $token], function ($message) use ($request) {
            $message->to($request->email)->subject('Recuperación de contraseña - Grupo Industrial ARDA S.A de C.V.');
        });

        return back()->with('mensaje', 'Te hemos enviado un enlace de recuperación.');
    }

    public function showResetForm($token)
    {
        return view('autenticacion.reset', compact('token'));
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
        'email' => 'required|email|exists:users,email',
        'password' => 'required|min:8|confirmed',
        'token' => 'required'
    ], [
        'email.required' => 'El campo Email es obligatorio.',
        'email.email' => 'El email debe ser un formato válido.',
        'password.required' => 'La nueva Contraseña es obligatoria.',
        'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        'password.confirmed' => 'La confirmación de la contraseña no coincide.', // Mensaje clave
        'token.required' => 'Error de seguridad. Por favor, vuelva a intentar desde el enlace del email.'
    ]);

        $reset = DB::table('password_reset_tokens')->where('token', $request->token)->first();

        if (!$reset || $reset->email !== $request->email) {
            return back()->withErrors(['email' => 'Token inválido o expirado.']);
        }

        User::where('email', $request->email)->update(['password' => Hash::make($request->input('password'))]);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('mensaje', 'Tu contraseña ha sido restablecida.');
    }

}