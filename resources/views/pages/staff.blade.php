@extends('layouts.app')
@section('title', 'Staff')

@section('content')
<div style="max-width:56rem;margin:0 auto;padding:3rem 1.5rem;">
    <h1 style="font-size:2.5rem;font-weight:700;color:white;margin-bottom:0.5rem;">Tim Staff</h1>
    <p style="color:#94a3b8;margin-bottom:3rem;">Orang-orang di balik Project Mariposa.</p>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1.25rem;">
        @foreach([
            ['name' => 'Binghem', 'role' => 'Owner', 'color' => '#f87171'],
        ] as $member)
        <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:1rem;padding:1.5rem;text-align:center;">
            <img src="https://mc-heads.net/avatar/{{ urlencode($member['name']) }}/64" alt="{{ $member['name'] }}"
                style="width:4rem;height:4rem;border-radius:0.75rem;margin:0 auto 1rem;display:block;image-rendering:pixelated;">
            <div style="font-weight:600;color:white;margin-bottom:0.25rem;font-family:'JetBrains Mono',monospace;">{{ $member['name'] }}</div>
            <div style="font-size:0.8rem;color:{{ $member['color'] }};">{{ $member['role'] }}</div>
        </div>
        @endforeach
    </div>
</div>
@endsection
