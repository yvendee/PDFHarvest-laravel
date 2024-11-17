<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    // Display the login form
    public function showLoginForm()
    {
        return view('login.login-page');
    }

    // Handle the login form submission
    public function login(Request $request)
    {
        // Hardcoded credentials
        $hardcodedUsername = 'searchmaid';
        $hardcodedPassword = 'maidasia';

        // Validate the form input
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $username = $request->input('username');
        $password = $request->input('password');

        // Check credentials
        if ($username === $hardcodedUsername && $password === $hardcodedPassword) {
            // Authentication passed
            $request->session()->put('user', $username); // Store user in session
            return redirect('/'); // Redirect to home or intended page
        }

        // Authentication failed
        return redirect()->back()->withErrors(['Invalid credentials'])->withInput();
    }

    // Handle user logout
    public function logout(Request $request)
    {
        $request->session()->forget('user'); // Remove user from session
        return redirect('/login'); // Redirect to login page
    }
}
