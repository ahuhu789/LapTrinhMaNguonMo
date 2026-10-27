<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectUserByRole(Auth::user());
        }
        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            $user = Auth::user();

            return $this->redirectUserByRole($user)
                ->with('success', 'Đăng nhập thành công! Chào mừng ' . $user->name . ' trở lại.');
        }

        return back()->withErrors([
            'email' => 'Email hoặc mật khẩu không chính xác.',
        ])->onlyInput('email');
    }

    public function showRegisterForm()
    {
        if (Auth::check()) {
            return $this->redirectUserByRole(Auth::user());
        }
        return view('auth.register');
    }

    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'client', // Mặc định tài khoản đăng ký là Khách hàng/Client
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('talents.index')
            ->with('success', 'Đăng ký tài khoản thành công! Bạn có thể khám phá và đặt lịch Talent ngay bây giờ.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Đã đăng xuất khỏi hệ thống an toàn.');
    }

    /**
     * Chuyển hướng thông minh theo vai trò của người dùng
     */
    protected function redirectUserByRole(User $user)
    {
        return match ($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'manager' => redirect()->route('manager.dashboard'),
            'streamer' => redirect()->route('streamer.dashboard'),
            'client' => redirect()->route('talents.index'),
            default => redirect()->route('home'),
        };
    }
}
