<?php

namespace App\Http\Controllers;
use App\Services\DocumentSummarizer;
use League\Flysystem\FilesystemException;
use PhpOffice\PhpWord\IOFactory;
use Smalot\PdfParser\Parser as PdfParser;
use PhpOffice\PhpWord\IOFactory as WordReader;
use App\Models\Document;
use App\Jobs\ProcessDocumentJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Smalot\PdfParser\Parser;

class DocumentController extends Controller
{
    /**
     * Display a listing of the documents.
     */
   public function index()
{
    $startTime = microtime(true);

    $documents = Document::with('category')->orderBy('created_at', 'desc')->paginate(50);
    $timeTaken = microtime(true) - $startTime;
    $totalSize = Document::sum('file_size');
    $categoriesCount = \App\Models\Category::count();
    $categories = \App\Models\Category::orderBy('name')->get();

    return view('documents.index', compact('documents', 'timeTaken', 'totalSize', 'categoriesCount', 'categories'));
}

    /**
     * View or download the physical document file.
     */
    public function show(Document $document)
    {
        $path = storage_path('app/documents/' . $document->file_path);
        if (file_exists($path)) {
            $mime = ($document->file_type === 'pdf') ? 'application/pdf' : 'application/octet-stream';
            return response()->file($path, [
                'Content-Type' => $mime,
                'Content-Disposition' => 'inline; filename="' . $document->original_filename . '"'
            ]);
        }
        return back()->with('error', 'File not found on server.');
    }

    /**
     * Return JSON document metadata and extracted text preview for modal.
     */
    public function preview(Document $document)
    {
        return response()->json([
            'id' => $document->id,
            'title' => $document->title,
            'original_filename' => $document->original_filename,
            'file_type' => strtoupper($document->file_type),
            'file_size' => number_format($document->file_size / 1024, 2) . ' KB',
            'category' => optional($document->category)->name ?? 'Uncategorized',
            'created_at' => $document->created_at->format('M d, Y H:i'),
            'content_preview' => !empty($document->content_preview) ? $document->content_preview : 'No extracted text available for this document.',
            'view_url' => route('documents.show', $document->id),
        ]);
    }


    
    /**
     * Generate or fetch an intelligent summary of the document.
     */
    public function summarize(Document $document, DocumentSummarizer $summarizer)
    {
        // 1. If summary is already cached in database, return it
        if (!empty($document->summary) && !empty($document->summary_points)) {
            $keyPoints = json_decode($document->summary_points, true) ?: [];
            $wordCount = str_word_count($document->content_preview ?? '');
            $summaryWords = str_word_count($document->summary);
            $reduction = max(0, round((($wordCount - $summaryWords) / max(1, $wordCount)) * 100));

            return response()->json([
                'id' => $document->id,
                'title' => $document->title,
                'original_filename' => $document->original_filename,
                'file_type' => strtoupper($document->file_type),
                'summary' => $document->summary,
                'key_points' => $keyPoints,
                'stats' => [
                    'original_words' => $wordCount,
                    'summary_words' => $summaryWords,
                    'reduction' => $reduction,
                    'orig_reading_time' => ceil($wordCount / 200) . ' min',
                    'summary_reading_time' => ceil($summaryWords / 200) . ' min',
                ],
            ]);
        }

        // 2. Retrieve document text
        $text = $document->content_preview ?? '';
        if (empty($text) && $document->file_path) {
            $path = storage_path('app/documents/' . $document->file_path);
            if (file_exists($path)) {
                $ext = strtolower($document->file_type);
                if ($ext === 'pdf') {
                    try {
                        $parser = new Parser();
                        $text = $parser->parseFile($path)->getText();
                    } catch (\Exception $e) {}
                } elseif ($ext === 'docx') {
                    try {
                        $phpWord = IOFactory::load($path);
                        $t = '';
                        foreach ($phpWord->getSections() as $section) {
                            foreach ($section->getElements() as $el) {
                                if (method_exists($el, 'getText')) $t .= $el->getText() . ' ';
                            }
                        }
                        $text = $t;
                    } catch (\Exception $e) {}
                }
            }
        }

        // 3. Run summarization
        $result = $summarizer->summarize($text, 5);

        // 4. Cache in database
        $document->update([
            'summary' => $result['summary'],
            'summary_points' => json_encode($result['key_points']),
        ]);

        return response()->json([
            'id' => $document->id,
            'title' => $document->title,
            'original_filename' => $document->original_filename,
            'file_type' => strtoupper($document->file_type),
            'summary' => $result['summary'],
            'key_points' => $result['key_points'],
            'stats' => $result['stats'],
        ]);
    }

    public function create()
    {
        return view('documents.create');
    }
private function extractTitleFromContent($file, $extension)
{
    if ($extension === 'pdf') {
        try {
            $parser = new PdfParser();
            $pdf = $parser->parseFile($file->getPathname());
            $text = $pdf->getText();
            $lines = explode("\n", trim($text));
            return $lines[0] ?? 'Untitled PDF';
        } catch (\Exception $e) {
            return 'Untitled PDF';
        }
    }

    if ($extension === 'docx') {
        try {
            $phpWord = WordReader::load($file->getPathname(), 'Word2007');
            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    if (method_exists($element, 'getText')) {
                        $text = trim($element->getText());
                        if (!empty($text)) {
                            return $text;
                        }
                    }
                }
            }
            return 'Untitled Word';
        } catch (\Exception $e) {
            return 'Untitled Word';
        }
    }

    return 'Untitled';
}
    /**
     * Store a newly created document in storage.
     */
  public function store(Request $request)
{
    $start = microtime(true);
    // 1. Validate file (PDF / DOCX up to 50MB)
    $request->validate([
        'document' => 'required|file|max:51200',
    ], [
        'document.required' => 'Please select a document to upload.',
        'document.file' => 'The selected file could not be uploaded.',
        'document.max' => 'The document must not exceed 50MB.',
    ]);

    // 2. Capture file and metadata
    $file = $request->file('document');
    $originalFilename = $file->getClientOriginalName();
    $fileSize = $file->getSize();
    $extension = strtolower($file->getClientOriginalExtension());

    if (!in_array($extension, ['pdf', 'docx', 'doc'])) {
        return back()->withErrors(['document' => 'Invalid format! Only PDF and DOCX files are supported.']);
    }

    // 3. توليد اسم فريد للملف (UUID) بدون إضافة مجلد 'documents' يدويًا
    $uniqueFilename = Str::uuid() . '.' . $extension;
    $localPath = $uniqueFilename; // سيتم تخزينه داخل مجلد 'documents' تلقائيًا عبر disk

    // 4. تخزين الملف فعليًا في storage/app/public/documents
    Storage::disk('dropbox')->put('documents/' . $uniqueFilename, file_get_contents($file));

    // 5. استخراج عنوان المستند من المحتوى
    $title = $this->extractTitleFromContent($file, $extension);


    // 6. إنشاء سجل في قاعدة البيانات
    $document = Document::create([
        'title' => $title,
        'original_filename' => $originalFilename,
        'file_path' => $localPath, // بدون تكرار 'documents/'
        'file_type' => $extension,
        'file_size' => $fileSize,
        'category_id' => $request->input('category_id'), // قد يتم تحديثها لاحقًا من Job
    ]);

    // 7. إرسال الملف للمعالجة في الخلفية عبر الـ Job
    ProcessDocumentJob::dispatch($document);
     $end = microtime(true);
      $duration = number_format($end - $start, 3); // الزمن المستغرق بالثواني
    // 8. إرجاع رسالة نجاح للمستخدم
 return redirect()->route('documents.create')->with([
        'success' => 'Document uploaded successfully.',
        'duration' => "Upload, classification and saving took {$duration} seconds.",
    ]);}




  


public function search(Request $request)
{
    $query = trim($request->input('query', ''));
    $startTime = microtime(true);
    $results = [];

    if ($query !== '') {
        $lowerQuery = strtolower($query);
        $documents = Document::all();

        foreach ($documents as $doc) {
            $content = $doc->content_preview ?? '';

            // If content preview was not cached, try to extract it from the local storage file
            if (empty($content) && $doc->file_path) {
                $localPath = storage_path('app/documents/' . $doc->file_path);
                if (file_exists($localPath)) {
                    $ext = strtolower(pathinfo($localPath, PATHINFO_EXTENSION));
                    if ($ext === 'pdf') {
                        try {
                            $parser = new Parser();
                            $pdf = $parser->parseFile($localPath);
                            $content = $pdf->getText();
                            $doc->update(['content_preview' => $content]);
                        } catch (\Exception $e) {}
                    } elseif ($ext === 'docx') {
                        try {
                            $phpWord = IOFactory::load($localPath);
                            $text = '';
                            foreach ($phpWord->getSections() as $section) {
                                foreach ($section->getElements() as $element) {
                                    if (method_exists($element, 'getText')) {
                                        $text .= $element->getText() . ' ';
                                    }
                                }
                            }
                            $content = $text;
                            $doc->update(['content_preview' => $content]);
                        } catch (\Exception $e) {}
                    }
                }
            }

            $searchableText = strtolower($content);
            $titleLower = strtolower($doc->title . ' ' . $doc->original_filename);

            $pos = stripos($searchableText, $lowerQuery);
            if ($pos !== false) {
                $contextRadius = 60;
                $start = max(0, $pos - $contextRadius);
                $length = strlen($lowerQuery) + 2 * $contextRadius;
                $snippet = substr($content, $start, $length);
                $highlighted = str_ireplace($query, '<mark style="background-color: #ffeb3b; color: #000; font-weight: bold; padding: 2px 4px; border-radius: 2px;">' . e($query) . '</mark>', e($snippet));

                $results[] = (object) [
                    'title' => $doc->title,
                    'original_filename' => $doc->original_filename,
                    'highlighted_content' => '...' . $highlighted . '...',
                ];
            } elseif (stripos($titleLower, $lowerQuery) !== false) {
                $highlightedTitle = str_ireplace($query, '<mark style="background-color: #ffeb3b; color: #000; font-weight: bold; padding: 2px 4px; border-radius: 2px;">' . e($query) . '</mark>', e($doc->title));
                $results[] = (object) [
                    'title' => $doc->title,
                    'original_filename' => $doc->original_filename,
                    'highlighted_content' => 'Matched in document title: ' . $highlightedTitle . ' (' . e($doc->original_filename) . ')',
                ];
            }
        }
    }

    $timeTaken = microtime(true) - $startTime;

    return view('documents.search', [
        'documents' => collect($results),
        'query' => $query,
        'timeTaken' => $timeTaken,
    ]);
}
public function searchView()
{
    return view('documents.search', [
        'documents' => collect(), // قائمة فاضية بالبداية
        'query' => '',
        'timeTaken' => 0
    ]);
}

    /**
     * Remove the specified document from storage.
     */
    public function destroy(Document $document)
    {
        try {
            if ($document->file_path) {
                Storage::disk('dropbox')->delete('documents/' . $document->file_path);
            }
            $document->delete();
            return redirect()->route('documents.index')->with('success', 'Document deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('documents.index')->with('error', 'Failed to delete document.');
        }
    }
}
