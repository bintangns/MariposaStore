@extends('layouts.app')
@section('title', 'Staff')

@section('content')
<div style="max-width:56rem;margin:0 auto;padding:3rem 1.5rem;">
    <h1 style="font-size:2.5rem;font-weight:700;color:white;margin-bottom:0.5rem;">Tim Staff</h1>
    <p style="color:#94a3b8;margin-bottom:3rem;">Orang-orang di balik Project Mariposa.</p>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1.25rem;">
        @foreach([
            ['name' => 'Binghem', 'role' => 'Owner', 'color' => '#f87171'],
            ['name' => 'Staff1',  'role' => 'Admin', 'color' => '#fb923c'],
            ['name' => 'Staff2',  'role' => 'Moderator', 'color' => '#c084fc'],
        ] as $member)
        <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:1rem;padding:1.5rem;text-align:center;">
            <div style="width:4rem;height:4rem;border-radius:0.75rem;background:rgba(255,255,255,0.05);margin:0 auto 1rem;display:flex;align-items:center;justify-content:center;font-size:1.5rem;">👤</div>
            <div style="font-weight:600;color:white;margin-bottom:0.25rem;font-family:'JetBrains Mono',monospace;">{{ $member['name'] }}</div>
            <div style="font-size:0.8rem;color:{{ $member['color'] }};">{{ $member['role'] }}</div>
        </div>
        @endforeach
    </div>
</div>
@endsection
