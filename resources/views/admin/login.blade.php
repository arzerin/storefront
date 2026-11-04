<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in · Ayra Admin</title>
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
</head>
<body class="admin-body admin-login">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4 p-sm-5">
            <div class="text-center mb-4"><span class="brand-mark mb-3">A</span><h1 class="h3 fw-bold mb-1">Ayra Admin</h1><p class="text-muted mb-0">Sign in to manage your catalogue.</p></div>
            <form method="post" action="{{ route('admin.login.submit') }}">
                @csrf
                <div class="mb-3"><label for="email" class="form-label">Email address</label><input id="email" name="email" type="email" autocomplete="username" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autofocus>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="mb-4"><label for="password" class="form-label">Password</label><input id="password" name="password" type="password" autocomplete="current-password" class="form-control @error('password') is-invalid @enderror" required>@error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <button type="submit" class="btn btn-primary w-100 py-2">Sign in <i class="bi bi-arrow-right ms-1"></i></button>
            </form>
            <div class="text-center mt-4"><a class="text-muted small" href="{{ route('home') }}">← Return to storefront</a></div>
        </div>
    </div>
</body>
</html>
