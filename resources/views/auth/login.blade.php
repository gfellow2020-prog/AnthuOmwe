<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediCare Portal - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        background: '#ffffff',
                        foreground: '#252525',
                        primary: '#030213',
                        'primary-foreground': '#ffffff',
                        secondary: '#f3f3f5',
                        'secondary-foreground': '#030213',
                        muted: '#ececf0',
                        'muted-foreground': '#717182',
                        border: 'rgba(0, 0, 0, 0.1)',
                        'input-background': '#f3f3f5',
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
    </style>
</head>
<body class="h-screen w-screen overflow-hidden">
    <div class="h-full w-full flex">
        <!-- Left Side - Branding -->
        <div class="hidden lg:flex lg:w-1/2 relative bg-primary overflow-hidden">
            <div class="flex flex-col justify-between p-12 text-primary-foreground w-full">
                <div>
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center backdrop-blur-sm">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                            </svg>
                        </div>
                        <h2 class="text-xl font-medium">MediCare Portal</h2>
                    </div>
                    <h1 class="text-2xl font-medium mb-4 max-w-md">
                        Healthcare Management System
                    </h1>
                    <p class="text-white/80 max-w-md">
                        Secure access to patient records, scheduling, and administrative tools for healthcare professionals.
                    </p>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/>
                            </svg>
                        </div>
                        <span class="text-white/90">HIPAA Compliant</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/>
                            </svg>
                        </div>
                        <span class="text-white/90">256-bit Encryption</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center bg-background p-6">
            <div class="w-full max-w-md">
                <div class="mb-8">
                    <h1 class="text-2xl font-medium mb-2">Sign in to your account</h1>
                    <p class="text-muted-foreground">Enter your credentials to access the portal</p>
                </div>

                <!-- Success Message -->
                @if(session('success'))
                    <div class="mb-4 p-4 bg-neutral-100 border border-neutral-300 text-neutral-700 rounded-lg text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                <!-- Error Message -->
                @if($errors->has('login'))
                    <div class="mb-4 p-4 bg-neutral-900 border border-neutral-700 text-white rounded-lg text-sm">
                        {{ $errors->first('login') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.submit') }}" class="space-y-5">
                    @csrf

                    <div class="space-y-2">
                        <label for="email" class="block text-foreground font-medium">
                            Email Address
                        </label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            class="w-full px-4 py-3 bg-input-background rounded-lg border border-border focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all @error('email') border-neutral-500 @enderror"
                            placeholder="doctor@hospital.com"
                            required
                            autofocus
                        />
                        @error('email')
                            <p class="mt-1 text-sm text-neutral-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="password" class="block text-foreground font-medium">
                            Password
                        </label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            class="w-full px-4 py-3 bg-input-background rounded-lg border border-border focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all @error('password') border-neutral-500 @enderror"
                            placeholder="Enter your password"
                            required
                        />
                        @error('password')
                            <p class="mt-1 text-sm text-neutral-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                type="checkbox"
                                name="remember"
                                class="w-4 h-4 rounded accent-primary cursor-pointer"
                            />
                            <span class="text-foreground">Remember me</span>
                        </label>
                        <a href="#" class="text-primary hover:underline">
                            Forgot password?
                        </a>
                    </div>

                    <button
                        type="submit"
                        class="w-full py-3 bg-primary text-primary-foreground rounded-lg hover:opacity-90 transition-opacity font-medium"
                    >
                        Sign in
                    </button>
                </form>

                <div class="mt-8 pt-6 border-t border-border">
                    <p class="text-muted-foreground text-center mb-4">
                        Need access to the portal?
                    </p>
                    <button class="w-full py-3 bg-secondary text-secondary-foreground rounded-lg hover:bg-opacity-80 transition-colors font-medium">
                        Request Account
                    </button>
                </div>

                <p class="text-muted-foreground text-center mt-6 text-sm">
                    By signing in, you agree to our Terms of Service and Privacy Policy
                </p>
            </div>
        </div>
    </div>
</body>
</html>
