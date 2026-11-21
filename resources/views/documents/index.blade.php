@extends('layout.app')
@section('title')
<title>DocuVault | Document Intelligence Hub</title>
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
          <img src="{{ asset('images/logo.svg') }}" alt="DocuVault Logo" width="44" height="44" class="me-3 shadow-sm rounded-3">
          <div>
            <div class="d-flex align-items-center gap-2">
              <h4 class="text-white font-weight-bolder mb-0">Docu<span style="background: linear-gradient(135deg, #60a5fa, #a78bfa); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Vault</span></h4>
              <span class="badge font-weight-bold text-xxs px-2 py-1 rounded-pill" style="background: rgba(99, 102, 241, 0.3) !important; color: #c7d2fe !important; border: 1px solid rgba(165, 180, 252, 0.35);">INTELLIGENCE HUB</span>
            </div>
            <span class="text-white opacity-7 text-xs">Cloud Document Intelligence & Analytics Platform</span>
          </div>
        </div>

        <div class="d-flex align-items-center gap-2">
          <a href="{{ route('documents.create') }}" class="btn btn-sm btn-white text-primary font-weight-bold mb-0 shadow-sm">
            <i class="fa-solid fa-cloud-arrow-up me-1"></i> Upload Document
          </a>
          <a href="{{ route('documents.search') }}" class="btn btn-sm btn-outline-white text-white font-weight-bold mb-0">
            <i class="fa-solid fa-magnifying-glass me-1"></i> Search
          </a>
        </div>
      </div>
    </nav>

    <div class="container-fluid px-4 py-4">
      <!-- Alerts -->
      @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show text-white shadow-sm mb-4" role="alert">
          <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
          <button type="button" class="btn-close text-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif
      @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show text-white shadow-sm mb-4" role="alert">
          <i class="fa-solid fa-circle-exclamation me-2"></i> {{ session('error') }}
          <button type="button" class="btn-close text-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif

      <!-- Metric Cards (4 Columns) -->
      <div class="row g-3 mb-4">
        <!-- Total Documents -->
        <div class="col-xl-3 col-sm-6">
          <div class="card card-hover h-100">
            <div class="card-body p-3">
              <div class="row align-items-center">
                <div class="col-8">
                  <span class="text-xs text-uppercase font-weight-bold text-muted">Total Documents</span>
                  <h3 class="font-weight-bolder mb-0 mt-1" id="statTotalDocs">{{ $documents->total() }}</h3>
                  <span class="text-xs text-success font-weight-bold">Active in Catalog</span>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-3 d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="fa-solid fa-file-lines text-white fs-4"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Total Storage Size -->
        <div class="col-xl-3 col-sm-6">
          <div class="card card-hover h-100">
            <div class="card-body p-3">
              <div class="row align-items-center">
                <div class="col-8">
                  <span class="text-xs text-uppercase font-weight-bold text-muted">Total Storage</span>
                  <h3 class="font-weight-bolder mb-0 mt-1">
                    @if ($totalSize > 1048576)
                      {{ number_format($totalSize / 1048576, 2) }} <span class="text-sm font-weight-normal">MB</span>
                    @else
                      {{ number_format($totalSize / 1024, 1) }} <span class="text-sm font-weight-normal">KB</span>
                    @endif
                  </h3>
                  <span class="text-xs text-muted">Storage Footprint</span>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-danger shadow-danger text-center rounded-3 d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="fa-solid fa-hard-drive text-white fs-4"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Categories Configured -->
        <div class="col-xl-3 col-sm-6">
          <div class="card card-hover h-100">
            <div class="card-body p-3">
              <div class="row align-items-center">
                <div class="col-8">
                  <span class="text-xs text-uppercase font-weight-bold text-muted">Active Categories</span>
                  <h3 class="font-weight-bolder mb-0 mt-1">{{ $categoriesCount ?? $categories->count() }}</h3>
                  <span class="text-xs text-info font-weight-bold">Taxonomy Trees</span>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-3 d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="fa-solid fa-tags text-white fs-4"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Retrieval & Sort Latency -->
        <div class="col-xl-3 col-sm-6">
          <div class="card card-hover h-100">
            <div class="card-body p-3">
              <div class="row align-items-center">
                <div class="col-8">
                  <span class="text-xs text-uppercase font-weight-bold text-muted">Sort Latency</span>
                  <h3 class="font-weight-bolder mb-0 mt-1">{{ number_format($timeTaken * 1000, 1) }} <span class="text-sm font-weight-normal">ms</span></h3>
                  <span class="text-xs text-success font-weight-bold"><i class="fa-solid fa-bolt me-1"></i> Ultra Fast</span>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-3 d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="fa-solid fa-stopwatch text-white fs-4"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Action Cards Row -->
      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <div class="card card-hover p-3 h-100 d-flex flex-row align-items-center justify-content-between">
            <div class="d-flex align-items-center">
              <div class="icon icon-shape bg-primary text-white rounded-circle p-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                <i class="fa-solid fa-file-arrow-up fs-5"></i>
              </div>
              <div>
                <h6 class="mb-0 font-weight-bolder">Upload Document</h6>
                <span class="text-xs text-muted">Supports PDF & DOCX with auto-classification & summarization</span>
              </div>
            </div>
            <a href="{{ route('documents.create') }}" class="btn btn-primary btn-sm mb-0 px-3">
              Upload <i class="fa-solid fa-arrow-right ms-1"></i>
            </a>
          </div>
        </div>

        <div class="col-md-6">
          <div class="card card-hover p-3 h-100">
            <form action="{{ route('documents.searchHandle') }}" method="GET" class="d-flex align-items-center gap-2 mb-0">
              <div class="input-group">
                <span class="input-group-text bg-light border-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                <input type="text" name="query" class="form-control bg-light border-0" placeholder="Full-text search inside documents (e.g. insulin, test, layer)..." required>
              </div>
              <button type="submit" class="btn btn-dark btn-sm mb-0 px-3 text-nowrap">
                Search
              </button>
            </form>
          </div>
        </div>
      </div>

      <!-- Main Document Table Card -->
      <div class="card shadow-sm mb-4">
        <!-- Interactive Toolbar Header -->
        <div class="card-header bg-white border-bottom p-4">
          <div class="row align-items-center gy-3">
            <div class="col-lg-4 col-md-5">
              <div class="d-flex align-items-center">
                <h5 class="font-weight-bolder mb-0 me-2">Document Repository</h5>
                <span class="badge bg-light text-dark font-weight-bold" id="visibleCountBadge">{{ $documents->count() }} items</span>
              </div>
            </div>

            <!-- Filters -->
            <div class="col-lg-8 col-md-7">
              <div class="d-flex flex-wrap align-items-center justify-content-md-end gap-2">
                <!-- Instant Live Filter Search -->
                <div class="input-group input-group-sm" style="max-width: 260px;">
                  <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-filter text-muted"></i></span>
                  <input type="text" id="liveTableFilter" class="form-control bg-light border-start-0" placeholder="Filter current list...">
                </div>

                <!-- Category Filter -->
                <select id="categorySelectFilter" class="form-select form-select-sm" style="max-width: 170px;">
                  <option value="">All Categories</option>
                  @foreach ($categories as $cat)
                    <option value="{{ strtolower($cat->name) }}">{{ $cat->name }}</option>
                  @endforeach
                  <option value="uncategorized">Uncategorized</option>
                </select>

                <!-- Format Filter Buttons -->
                <div class="btn-group btn-group-sm" role="group">
                  <button type="button" class="btn btn-outline-secondary active filter-type-btn" data-type="all">All</button>
                  <button type="button" class="btn btn-outline-secondary filter-type-btn" data-type="pdf">PDF</button>
                  <button type="button" class="btn btn-outline-secondary filter-type-btn" data-type="docx">DOCX</button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Table View -->
        <div class="table-responsive">
          <table class="table align-items-center mb-0" id="documentsTable">
            <thead class="bg-light">
              <tr>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-4">Document / Title</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Category</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Type</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Size</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Uploaded</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center pe-4">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($documents as $doc)
                <tr class="document-row" 
                    data-title="{{ strtolower($doc->title) }}" 
                    data-filename="{{ strtolower($doc->original_filename) }}" 
                    data-category="{{ strtolower(optional($doc->category)->name ?? 'uncategorized') }}"
                    data-type="{{ strtolower($doc->file_type) }}">
                  <!-- Title & File info -->
                  <td class="ps-4">
                    <div class="d-flex align-items-center">
                      <div class="icon icon-shape bg-light rounded-3 text-center me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        @if (strtolower($doc->file_type) === 'pdf')
                          <i class="fa-solid fa-file-pdf text-danger fs-5"></i>
                        @else
                          <i class="fa-solid fa-file-word text-primary fs-5"></i>
                        @endif
                      </div>
                      <div>
                        <h6 class="mb-0 text-sm font-weight-bold text-dark">{{ $doc->title }}</h6>
                        <span class="text-xs text-muted"><i class="fa-solid fa-paperclip me-1"></i> {{ $doc->original_filename }}</span>
                      </div>
                    </div>
                  </td>

                  <!-- Category -->
                  <td>
                    @if ($doc->category)
                      <span class="badge badge-soft-primary px-3 py-1 font-weight-bold">
                        <i class="fa-solid fa-tag me-1"></i> {{ $doc->category->name }}
                      </span>
                    @else
                      <span class="badge bg-light text-muted px-2 py-1 font-weight-normal">
                        Uncategorized
                      </span>
                    @endif
                  </td>

                  <!-- Type -->
                  <td class="text-center">
                    <span class="badge {{ strtolower($doc->file_type) === 'pdf' ? 'bg-danger' : 'bg-primary' }} text-white text-xxs px-2 py-1">
                      {{ strtoupper($doc->file_type) }}
                    </span>
                  </td>

                  <!-- Size -->
                  <td class="text-center">
                    <span class="text-xs font-weight-bold text-dark">
                      {{ number_format($doc->file_size / 1024, 1) }} KB
                    </span>
                  </td>

                  <!-- Date -->
                  <td class="text-center">
                    <span class="text-xs text-secondary">
                      {{ $doc->created_at->format('M d, Y') }}
                      <span class="d-block text-xxs opacity-7">{{ $doc->created_at->format('H:i') }}</span>
                    </span>
                  </td>

                  <!-- Action Buttons -->
                  <td class="text-center pe-4">
                    <div class="btn-group btn-group-sm">
                      <!-- AI Summary Button -->
                      <button type="button" class="btn btn-outline-warning text-dark px-2 py-1 btn-summarize" data-id="{{ $doc->id }}" title="Generate Document Summary">
                        <i class="fa-solid fa-wand-magic-sparkles text-warning me-1"></i> Summary
                      </button>

                      <!-- Quick Text Preview Modal Trigger -->
                      <button type="button" class="btn btn-outline-primary px-2 py-1 btn-preview" data-id="{{ $doc->id }}" title="Preview Extracted Text">
                        <i class="fa-solid fa-eye"></i> Text
                      </button>

                      <!-- Open/View Document File -->
                      <a href="{{ route('documents.show', $doc->id) }}" target="_blank" class="btn btn-outline-secondary px-2 py-1" title="Open Original File">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i> Open
                      </a>

                      <!-- Delete -->
                      <form action="{{ route('documents.destroy', $doc->id) }}" method="POST" onsubmit="return confirm('Delete this document?');" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger px-2 py-1" title="Delete">
                          <i class="fa-solid fa-trash"></i>
                        </button>
                      </form>
                    </div>
                  </td>
                </tr>
              @empty
                <tr id="emptyTableRow">
                  <td colspan="6" class="text-center py-5 text-muted">
                    <i class="fa-solid fa-folder-open fs-1 text-secondary mb-3 d-block opacity-5"></i>
                    <h6>No documents found in repository</h6>
                    <p class="text-xs text-muted mb-3">Upload your first PDF or Word document to get started.</p>
                    <a href="{{ route('documents.create') }}" class="btn btn-primary btn-sm">
                      <i class="fa-solid fa-cloud-arrow-up me-1"></i> Upload Now
                    </a>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <!-- Filter No-Match Notice (Hidden by default) -->
        <div id="noMatchMessage" class="p-5 text-center d-none">
          <i class="fa-solid fa-filter-circle-xmark fs-2 text-muted mb-2"></i>
          <h6 class="text-dark">No matching documents</h6>
          <p class="text-xs text-muted mb-0">Try clearing your search query or changing the filter options.</p>
        </div>

        @if ($documents->hasPages())
          <div class="card-footer bg-white py-3 d-flex justify-content-between align-items-center">
            <span class="text-xs text-muted">Showing {{ $documents->firstItem() }} to {{ $documents->lastItem() }} of {{ $documents->total() }}</span>
            {{ $documents->links() }}
          </div>
        @endif
      </div>
    </div>
  </main>

  <!-- Interactive Document Preview & AI Summary Modal -->
  <div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content shadow-lg border-0 rounded-4">
        <!-- Modal Header -->
        <div class="modal-header border-bottom pb-3">
          <div class="d-flex align-items-center">
            <div id="modalFileIcon" class="icon icon-shape bg-light rounded-3 text-center me-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
              <i class="fa-solid fa-file-pdf text-danger fs-4"></i>
            </div>
            <div>
              <h5 class="modal-title font-weight-bolder mb-0" id="modalDocTitle">Document Details</h5>
              <span class="text-xs text-muted" id="modalDocFilename">filename.pdf</span>
            </div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body p-4">
          <!-- Metadata Pills -->
          <div class="row g-2 mb-3">
            <div class="col-sm-3 col-6">
              <div class="p-2 bg-light rounded-3 text-center">
                <span class="text-xxs text-uppercase text-muted d-block font-weight-bold">Category</span>
                <span class="text-xs font-weight-bold text-primary" id="modalDocCategory">-</span>
              </div>
            </div>
            <div class="col-sm-3 col-6">
              <div class="p-2 bg-light rounded-3 text-center">
                <span class="text-xxs text-uppercase text-muted d-block font-weight-bold">File Format</span>
                <span class="text-xs font-weight-bold" id="modalDocType">PDF</span>
              </div>
            </div>
            <div class="col-sm-3 col-6">
              <div class="p-2 bg-light rounded-3 text-center">
                <span class="text-xxs text-uppercase text-muted d-block font-weight-bold">File Size</span>
                <span class="text-xs font-weight-bold" id="modalDocSize">0 KB</span>
              </div>
            </div>
            <div class="col-sm-3 col-6">
              <div class="p-2 bg-light rounded-3 text-center">
                <span class="text-xxs text-uppercase text-muted d-block font-weight-bold">Upload Date</span>
                <span class="text-xs font-weight-bold" id="modalDocDate">-</span>
              </div>
            </div>
          </div>

          <!-- Tabs Navigation -->
          <ul class="nav nav-pills nav-fill mb-3 p-1 bg-light rounded-3" id="docTabs" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active py-2 font-weight-bold text-xs rounded-3" id="summary-tab" data-bs-toggle="pill" data-bs-target="#tab-summary" type="button" role="tab">
                <i class="fa-solid fa-wand-magic-sparkles text-warning me-1"></i> Executive Summary & Key Takeaways
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link py-2 font-weight-bold text-xs rounded-3" id="text-tab" data-bs-toggle="pill" data-bs-target="#tab-text" type="button" role="tab">
                <i class="fa-solid fa-file-lines text-primary me-1"></i> Full Extracted Text
              </button>
            </li>
          </ul>

          <!-- Tab Content -->
          <div class="tab-content" id="docTabsContent">
            <!-- Summary Tab -->
            <div class="tab-pane fade show active" id="tab-summary" role="tabpanel">
              <!-- Summary Metrics Badge -->
              <div id="summaryStatsBanner" class="p-2 px-3 mb-3 rounded-3 d-flex justify-content-between align-items-center" style="background: rgba(99, 102, 241, 0.08); border: 1px solid rgba(99, 102, 241, 0.15);">
                <div class="d-flex align-items-center gap-2">
                  <span class="badge bg-success font-weight-bold" id="badgeReduction">0% Time Saved</span>
                  <span class="text-xs text-dark font-weight-bold" id="textReadingTimes">Reading time</span>
                </div>
                <span class="text-xs text-muted" id="textWordCounts">Words</span>
              </div>

              <!-- Key Takeaways Section -->
              <div class="mb-3">
                <h6 class="text-xs text-uppercase font-weight-bold text-muted mb-2">
                  <i class="fa-solid fa-list-check text-primary me-1"></i> Key Takeaways & Main Points
                </h6>
                <div id="summaryKeyPoints" class="d-flex flex-column gap-2">
                  <!-- Injected via JS -->
                </div>
              </div>

              <!-- Executive Paragraph -->
              <div class="mb-2">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <h6 class="text-xs text-uppercase font-weight-bold text-muted mb-0">
                    <i class="fa-solid fa-align-left text-primary me-1"></i> Executive Summary
                  </h6>
                  <button type="button" class="btn btn-sm btn-link text-primary p-0 mb-0" id="btnCopySummary">
                    <i class="fa-regular fa-copy me-1"></i> Copy Summary
                  </button>
                </div>
                <div class="p-3 bg-white rounded-3 border" style="font-size: 0.88rem; line-height: 1.6; color: #1e293b;" id="summaryParagraph">
                  Generating summary...
                </div>
              </div>
            </div>

            <!-- Full Text Tab -->
            <div class="tab-pane fade" id="tab-text" role="tabpanel">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="text-xs text-uppercase font-weight-bold text-muted mb-0">
                  <i class="fa-solid fa-file-lines text-primary me-1"></i> Raw Parsed Text Stream
                </h6>
                <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 mb-0" id="btnCopyText">
                  <i class="fa-regular fa-copy me-1"></i> Copy Full Text
                </button>
              </div>
              <div class="p-3 bg-light rounded-3 border" style="max-height: 280px; overflow-y: auto; font-size: 0.85rem; line-height: 1.6; color: #334155; white-space: pre-wrap;" id="modalDocContent">
                Loading content...
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer border-top d-flex justify-content-between">
          <button type="button" class="btn btn-light btn-sm mb-0" data-bs-dismiss="modal">Close</button>
          <a href="#" target="_blank" class="btn btn-primary btn-sm mb-0" id="modalViewFileBtn">
            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Open Original File
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Interactive Client-side Table Filter & Modal Logic -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const liveFilter = document.getElementById('liveTableFilter');
      const categoryFilter = document.getElementById('categorySelectFilter');
      const typeButtons = document.querySelectorAll('.filter-type-btn');
      const rows = document.querySelectorAll('.document-row');
      const noMatchMsg = document.getElementById('noMatchMessage');
      const visibleCountBadge = document.getElementById('visibleCountBadge');
      let currentTypeFilter = 'all';

      function applyFilters() {
        const query = liveFilter.value.toLowerCase().trim();
        const selectedCat = categoryFilter.value.toLowerCase();
        let visibleCount = 0;

        rows.forEach(row => {
          const title = row.getAttribute('data-title') || '';
          const filename = row.getAttribute('data-filename') || '';
          const category = row.getAttribute('data-category') || '';
          const type = row.getAttribute('data-type') || '';

          const matchesQuery = !query || title.includes(query) || filename.includes(query) || category.includes(query);
          const matchesCat = !selectedCat || category.includes(selectedCat);
          const matchesType = currentTypeFilter === 'all' || type === currentTypeFilter;

          if (matchesQuery && matchesCat && matchesType) {
            row.style.display = '';
            visibleCount++;
          } else {
            row.style.display = 'none';
          }
        });

        if (visibleCountBadge) {
          visibleCountBadge.textContent = visibleCount + ' items';
        }

        if (noMatchMsg) {
          if (visibleCount === 0 && rows.length > 0) {
            noMatchMsg.classList.remove('d-none');
          } else {
            noMatchMsg.classList.add('d-none');
          }
        }
      }

      if (liveFilter) liveFilter.addEventListener('input', applyFilters);
      if (categoryFilter) categoryFilter.addEventListener('change', applyFilters);

      typeButtons.forEach(btn => {
        btn.addEventListener('click', function() {
          typeButtons.forEach(b => b.classList.remove('active'));
          this.classList.add('active');
          currentTypeFilter = this.getAttribute('data-type');
          applyFilters();
        });
      });

      // Preview / Summary Modal Handler
      const previewModalEl = document.getElementById('previewModal');
      const previewModal = new bootstrap.Modal(previewModalEl);
      const summaryTabTrigger = document.querySelector('#summary-tab');
      const textTabTrigger = document.querySelector('#text-tab');

      function openModalForDocument(docId, defaultTab) {
        document.getElementById('modalDocTitle').textContent = 'Loading Document...';
        document.getElementById('modalDocFilename').textContent = '';
        document.getElementById('modalDocContent').textContent = 'Loading content...';
        document.getElementById('summaryParagraph').innerHTML = '<div class="text-center py-3 text-muted"><i class="fa-solid fa-spinner fa-spin me-2"></i> Analyzing text and generating summary...</div>';
        document.getElementById('summaryKeyPoints').innerHTML = '<span class="text-xs text-muted">Extracting key points...</span>';

        if (defaultTab === 'summary') {
          const tab = new bootstrap.Tab(summaryTabTrigger);
          tab.show();
        } else {
          const tab = new bootstrap.Tab(textTabTrigger);
          tab.show();
        }

        previewModal.show();

        // 1. Fetch Basic Preview & Full text
        fetch('/documents/' + docId + '/preview')
          .then(res => res.json())
          .then(data => {
            document.getElementById('modalDocTitle').textContent = data.title;
            document.getElementById('modalDocFilename').textContent = data.original_filename;
            document.getElementById('modalDocCategory').textContent = data.category;
            document.getElementById('modalDocType').textContent = data.file_type;
            document.getElementById('modalDocSize').textContent = data.file_size;
            document.getElementById('modalDocDate').textContent = data.created_at;
            document.getElementById('modalDocContent').textContent = data.content_preview;
            document.getElementById('modalViewFileBtn').setAttribute('href', data.view_url);

            const iconContainer = document.getElementById('modalFileIcon');
            if (data.file_type === 'PDF') {
              iconContainer.innerHTML = '<i class="fa-solid fa-file-pdf text-danger fs-4"></i>';
            } else {
              iconContainer.innerHTML = '<i class="fa-solid fa-file-word text-primary fs-4"></i>';
            }
          });

        // 2. Fetch AI / NLP Summary
        fetch('/documents/' + docId + '/summarize')
          .then(res => res.json())
          .then(data => {
            document.getElementById('summaryParagraph').textContent = data.summary;
            document.getElementById('badgeReduction').textContent = '⚡ ' + data.stats.reduction + '% Time Saved';
            document.getElementById('textReadingTimes').textContent = data.stats.orig_reading_time + ' read ➔ ' + data.stats.summary_reading_time + ' summary';
            document.getElementById('textWordCounts').textContent = data.stats.original_words.toLocaleString() + ' words ➔ ' + data.stats.summary_words + ' words';

            const keyPointsContainer = document.getElementById('summaryKeyPoints');
            keyPointsContainer.innerHTML = '';
            if (data.key_points && data.key_points.length > 0) {
              data.key_points.forEach(point => {
                const item = document.createElement('div');
                item.className = 'p-2 px-3 bg-white border rounded-2 d-flex align-items-start text-xs text-dark';
                item.innerHTML = '<i class="fa-solid fa-circle-check text-success mt-1 me-2 flex-shrink-0"></i> <span>' + point + '</span>';
                keyPointsContainer.appendChild(item);
              });
            } else {
              keyPointsContainer.innerHTML = '<span class="text-xs text-muted">No distinct bullet points extracted.</span>';
            }
          })
          .catch(() => {
            document.getElementById('summaryParagraph').textContent = 'Could not generate summary for this document.';
          });
      }

      // Hook up Summarize button
      document.querySelectorAll('.btn-summarize').forEach(btn => {
        btn.addEventListener('click', function() {
          openModalForDocument(this.getAttribute('data-id'), 'summary');
        });
      });

      // Hook up Preview button
      document.querySelectorAll('.btn-preview').forEach(btn => {
        btn.addEventListener('click', function() {
          openModalForDocument(this.getAttribute('data-id'), 'text');
        });
      });

      // Copy Full Text
      const btnCopy = document.getElementById('btnCopyText');
      if (btnCopy) {
        btnCopy.addEventListener('click', function() {
          const text = document.getElementById('modalDocContent').innerText;
          navigator.clipboard.writeText(text).then(() => {
            const originalHtml = btnCopy.innerHTML;
            btnCopy.innerHTML = '<i class="fa-solid fa-check text-success me-1"></i> Copied!';
            setTimeout(() => { btnCopy.innerHTML = originalHtml; }, 2000);
          });
        });
      }

      // Copy Summary
      const btnCopySum = document.getElementById('btnCopySummary');
      if (btnCopySum) {
        btnCopySum.addEventListener('click', function() {
          const text = document.getElementById('summaryParagraph').innerText;
          navigator.clipboard.writeText(text).then(() => {
            const originalHtml = btnCopySum.innerHTML;
            btnCopySum.innerHTML = '<i class="fa-solid fa-check text-success me-1"></i> Copied!';
            setTimeout(() => { btnCopySum.innerHTML = originalHtml; }, 2000);
          });
        });
      }
    });
  </script>
</body>
@endsection