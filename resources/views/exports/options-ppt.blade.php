<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>PPT Export Options</title>
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
</head>
<body class="container py-4">
    <h3 class="mb-3">PPT Export Options</h3>
    <form action="{{ route('quiz.export.ppt', $quiz->id) }}" method="GET" target="_blank">
        <div class="form-check mb-2">
            <input class="form-check-input" type="hidden" name="include_title" value="0">
            <input class="form-check-input" type="checkbox" id="include_title" name="include_title" value="1" checked>
            <label class="form-check-label" for="include_title">Include title slide</label>
        </div>
        <div class="form-check mb-2">
            <input class="form-check-input" type="hidden" name="include_answers" value="0">
            <input class="form-check-input" type="checkbox" id="include_answers" name="include_answers" value="1" checked>
            <label class="form-check-label" for="include_answers">Include answers</label>
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="hidden" name="mark_correct" value="0">
            <input class="form-check-input" type="checkbox" id="mark_correct" name="mark_correct" value="1" checked>
            <label class="form-check-label" for="mark_correct">Mark correct answers</label>
        </div>
        <button class="btn btn-primary" type="submit">Export PPTX</button>
        <a class="btn btn-secondary" href="{{ url()->previous() }}">Cancel</a>
    </form>
</body>
</html>


