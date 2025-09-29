@php($paperOptions = ['A4' => 'A4', 'A3' => 'A3', 'Letter' => 'Letter', 'Legal' => 'Legal'])
@php($orientationOptions = ['portrait' => 'Portrait', 'landscape' => 'Landscape'])
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Options - {{ $quiz->title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin: 0; background: #f6f7f9; color: #111827; }
        .container { max-width: 860px; margin: 32px auto; background: #fff; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.06); overflow: hidden; }
        .header { padding: 20px 24px; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: space-between; }
        .title { font-size: 18px; font-weight: 700; }
        .meta { font-size: 12px; color: #6b7280; }
        .content { padding: 24px; display: grid; gap: 20px; grid-template-columns: 1fr 1fr; }
        .field { display: flex; flex-direction: column; gap: 8px; }
        label { font-weight: 600; font-size: 13px; }
        select, input[type="checkbox"] { padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; }
        .actions { padding: 20px 24px; border-top: 1px solid #e5e7eb; display: flex; gap: 12px; justify-content: flex-end; background: #fafafa; }
        .btn { appearance: none; border: 0; padding: 10px 14px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 14px; }
        .btn-secondary { background: #eef2ff; color: #3730a3; }
        .btn-primary { background: #111827; color: #fff; }
        .hint { font-size: 12px; color: #6b7280; }
        .row-span { grid-column: span 2; }
        .card { border: 1px solid #eef2f7; border-radius: 10px; padding: 16px; }
        @media (max-width: 720px) { .content { grid-template-columns: 1fr; } .row-span { grid-column: span 1; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <div class="title">Export Options</div>
                <div class="meta">{{ $quiz->title }}</div>
            </div>
            <div class="meta">Questions: {{ $quiz->questions->count() }}</div>
        </div>
        <form method="GET" action="{{ route('quiz.export.pdf', $quiz->id) }}">
            <div class="content">
                <div class="field">
                    <label for="paper">Paper size</label>
                    <select id="paper" name="paper">
                        @php($paperDefault = getUserSettings('default_paper') ?? 'A4')
                        @foreach($paperOptions as $key => $label)
                            <option value="{{ $key }}" {{ $paperDefault === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <div class="hint">Choose standard sizes like A4, A3, Letter, Legal.</div>
                </div>
                <div class="field">
                    <label for="orientation">Orientation</label>
                    <select id="orientation" name="orientation">
                        @php($orientationDefault = getUserSettings('default_orientation') ?? 'portrait')
                        @foreach($orientationOptions as $key => $label)
                            <option value="{{ $key }}" {{ $orientationDefault === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <div class="hint">Portrait is default; Landscape fits wider content.</div>
                </div>
                <div class="field row-span card">
                    <label>Include elements</label>
                    @php($descDef = (int)(getUserSettings('include_description_default') ?? 1))
                    @php($ansDef = (int)(getUserSettings('include_answers_default') ?? 1))
                    @php($markDef = (int)(getUserSettings('mark_correct_default') ?? 1))
                    <input type="hidden" name="include_description" value="0">
                    <label><input type="checkbox" name="include_description" value="1" {{ $descDef ? 'checked' : '' }}> Include description</label>
                    <input type="hidden" name="include_answers" value="0">
                    <label><input type="checkbox" name="include_answers" value="1" {{ $ansDef ? 'checked' : '' }}> Include answers</label>
                    <input type="hidden" name="mark_correct" value="0">
                    <label><input type="checkbox" name="mark_correct" value="1" {{ $markDef ? 'checked' : '' }}> Mark correct answers</label>
                    <div class="hint">Uncheck to generate a "Question-only" sheet for students.</div>
                </div>
            </div>
            <div class="actions">
                <a class="btn btn-secondary" href="{{ url()->previous() }}">Cancel</a>
                <button type="submit" class="btn btn-primary">Export PDF</button>
            </div>
        </form>
    </div>
</body>
</html>

