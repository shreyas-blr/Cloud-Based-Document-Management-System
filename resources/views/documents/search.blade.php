@extends('layout.app')
@section('title')
<title>Search Documents | DocuVault</title>
@endsection
@section('main-content')
<body class="bg-gray-100" style="background-color: #f4f6fa !important;">
  <!-- Dark Header Background -->
  <div class="position-absolute w-100" style="min-height: 280px; background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);"></div>

  <main class="main-content position-relative border-radius-lg">
    <!-- Navbar -->
    <nav class="navbar navbar-main navbar-expand-lg px-4 pt-3 shadow-none border-radius-xl" id="navbarBlur">
      <div class="container-fluid py-1 px-3 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
          <a href="{{ route('documents.index') }}" class="d-flex align-items-center text-decoration-none">
            <img src="{{ asset('images/logo.svg') }}" alt="DocuVault Logo" width="44" height="44" class="me-3 shadow-sm rounded-3">
            <div>
              <div class="d-flex align-items-center gap-2">
                <h4 class="text-white font-weight-bolder mb-0">Docu<span style="background: linear-gradient(135deg, #60a5fa, #a78bfa); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Vault</span></h4>
                <span class="badge font-weight-bold text-xxs px-2 py-1 rounded-pill" style="background: rgba(99, 102, 241, 0.3) !important; color: #c7d2fe !important; border: 1px solid rgba(165, 180, 252, 0.35);">SEARCH</span>
              </div>
              <span class="text-white opacity-7 text-xs">Full-Text Semantic Document Search</span>
            </div>
          </a>
        </div>

        <div class="d-flex align-items-center gap-2">
          <a href="{{ route('documents.index') }}" class="btn btn-sm btn-white text-primary font-weight-bold mb-0 shadow-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Dashboard
          </a>
          <a href="{{ route('documents.create') }}" class="btn btn-sm btn-outline-white text-white font-weight-bold mb-0">
            <i class="fa-solid fa-cloud-arrow-up me-1"></i> Upload
          </a>
        </div>
      </div>
    </nav>

    <!-- Search Form Card -->
    <div class="container-fluid px-4 py-4">
      <div class="row justify-content-center">
        <div class="col-lg-9">
          <div class="card shadow-sm p-4 mb-4">
            <h6 class="font-weight-bolder mb-2 text-dark">Enter keyword or phrase:</h6>
            <form action="{{ route('documents.searchHandle') }}" method="GET">
              <div class="input-group input-group-lg shadow-sm">
                <span class="input-group-text bg-white border-end-0 ps-3">
                  <i class="fa-solid fa-magnifying-glass text-primary"></i>
                </span>
                <input type="text" name="query" class="form-control border-start-0 py-3" placeholder="e.g. prescription, diabetes, santosh, layer, exam..." value="{{ request('query') }}" required autofocus>
                <button type="submit" class="btn btn-primary btn-lg px-4 mb-0">Search</button>
              </div>
            </form>

            @if ($query)
              <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                <span class="text-xs text-muted">
                  Showing results for: <strong class="text-dark">"{{ $query }}"</strong>
                </span>
                <span class="badge badge-soft-success font-weight-bold">
                  <i class="fa-solid fa-stopwatch me-1"></i> {{ number_format($timeTaken * 1000, 1) }} ms
                </span>
              </div>
            @endif

            @if ($query && !$documents->count())
              <div class="alert alert-warning text-white mt-3 mb-0" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i> No matching word or phrase found for "<strong>{{ $query }}</strong>". Try another keyword.
              </div>
            @endif
          </div>

          <!-- Result Table -->
          @if ($documents->count())
            <div class="card shadow-sm">
              <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 font-weight-bolder text-dark">
                  Matching Documents ({{ $documents->count() }})
                </h6>
              </div>
              <div class="table-responsive">
                <table class="table align-items-center mb-0">
                  <thead class="bg-light">
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-4" style="width: 35%;">Document Title</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3">Matched Snippet & Context</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($documents as $document)
                      <tr>
                        <td class="ps-4 align-top py-3">
                          <h6 class="text-sm font-weight-bold text-dark mb-0">{{ $document->title }}</h6>
                          @if (!empty($document->original_filename))
                            <span class="text-xs text-muted d-block mt-1">
                              <i class="fa-solid fa-paperclip me-1"></i> {{ $document->original_filename }}
                            </span>
                          @endif
                        </td>
                        <td class="ps-3 pe-4 align-top py-3">
                          <div class="p-2 bg-light rounded-2 border text-sm text-secondary" style="line-height: 1.6;">
                            {!! $document->highlighted_content ?? '' !!}
                          </div>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          @endif
        </div>
      </div>
    </div>
  </main>
</body>
@endsection