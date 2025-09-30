@extends('layout.app')

@section('title', $page['title'] ?? 'Legal Page')

@section('content')
<div class="legal-page">
    <div class="container">
        <div class="legal-content">
            <h1>{{ $page['title'] ?? 'Legal Page' }}</h1>
            <div class="legal-text">
                {!! $page['content'] ?? '' !!}
            </div>
        </div>
    </div>
</div>

<style>
.legal-page {
    padding: 4rem 0;
    min-height: 60vh;
}

.legal-content {
    max-width: 800px;
    margin: 0 auto;
    background: white;
    padding: 3rem;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.legal-content h1 {
    color: var(--primary);
    margin-bottom: 2rem;
    font-size: 2rem;
    text-align: center;
}

.legal-text {
    line-height: 1.8;
    color: var(--slate-700);
}

.legal-text h2,
.legal-text h3,
.legal-text h4 {
    color: var(--slate-800);
    margin-top: 2rem;
    margin-bottom: 1rem;
}

.legal-text p {
    margin-bottom: 1rem;
}

.legal-text ul,
.legal-text ol {
    margin-bottom: 1rem;
    padding-left: 2rem;
}

.legal-text li {
    margin-bottom: 0.5rem;
}

@media (max-width: 768px) {
    .legal-content {
        padding: 2rem 1.5rem;
        margin: 0 1rem;
    }
    
    .legal-content h1 {
        font-size: 1.5rem;
    }
}
</style>
@endsection
