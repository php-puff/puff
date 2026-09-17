<!doctype html>
<html lang="{{ i18n() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ lang('app') }} · {{ lang('name') }}</title>
    <style>
        :root { color-scheme: dark; font-family: ui-monospace, SFMono-Regular, Menlo, monospace; }
        body { margin: 0; background: #0b1020; color: #d8e2ff; }
        main { max-width: 760px; margin: 9vh auto; padding: 0 24px; }
        header { display: flex; align-items: center; justify-content: space-between; gap: 24px; margin-bottom: 8px; }
        header h1 { margin: 0; }
        .languages { display: flex; gap: 8px; }
        .languages a { display: inline-block; padding: 6px 10px; border: 1px solid #263454; border-radius: 6px; color: #9fb0d0; text-decoration: none; }
        .languages a:hover, .languages a.active { background: #263454; color: #fff; }
        h1 { color: #7ee787; font-size: clamp(2rem, 7vw, 4rem); margin-bottom: 8px; }
        p { color: #9fb0d0; line-height: 1.7; }
        section { margin-top: 32px; border: 1px solid #263454; border-radius: 14px; overflow: hidden; }
        section h2 { margin: 0; padding: 12px 18px; font-size: .875rem; color: #9fb0d0; background: #111a31; }
        a { display: block; padding: 14px 18px; color: #79c0ff; text-decoration: none; border-bottom: 1px solid #263454; }
        a:last-child { border-bottom: 0; }
        a:hover { background: #111a31; }
        code { color: #ffa657; }
    </style>
</head>
<body>
<main>
    <header>
        <h1>{{ lang('app') }}</h1>
        <nav class="languages" aria-label="Language">
            <a href="/?i18n=en" class="{{ i18n() === 'en' ? 'active' : '' }}">English</a>
            <a href="/?i18n=zh-CN" class="{{ i18n() === 'zh-CN' ? 'active' : '' }}">简体中文</a>
            <a href="/?i18n=zh-HK" class="{{ i18n() === 'zh-HK' ? 'active' : '' }}">繁體中文</a>
        </nav>
    </header>
    @yield('content')
</main>
</body>
</html>
