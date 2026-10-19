<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeBase;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Smalot\PdfParser\Parser;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $databaseConnected = false;
        $knowledges        = collect();
        $totalKnowledge    = 0;
        $searchCount       = null;
        $settings          = [];
        $availableClusters = ['Pendidikan', 'Perizinan', 'Kesehatan', 'Dinas Damkar', 'Umum', 'Pelayanan Publik', 'Pemerintahan', 'Kuliner', 'Hotel', 'Wisata', 'Direktori Kota Bandung', 'Kependudukan', '112 Kota Bandung', 'Lainnya'];

        try {
            DB::connection()->getPdo();
            $databaseConnected = true;

            $query = KnowledgeBase::query();

            // Filter klaster
            if ($request->filled('cluster') && $request->input('cluster') !== 'all') {
                $query->where('cluster', $request->input('cluster'));
            }

            // Pencarian multi-kata yang ditingkatkan
            if ($request->filled('search')) {
                $rawSearch = trim($request->input('search'));

                // Pecah query menjadi kata-kata individual (max 10 kata)
                $words = array_slice(
                    array_filter(array_map('trim', preg_split('/\s+/', $rawSearch) ?: []), fn($w) => mb_strlen($w) >= 2),
                    0, 10
                );

                if (!empty($words)) {
                    // Tentukan scope pencarian
                    $scope = $request->input('search_scope', 'all'); // all | topic | content | keywords

                    $query->where(function ($q) use ($words, $scope, $rawSearch) {
                        // Prioritas 1: Cocok persis frasa lengkap di topic (relevansi tertinggi)
                        if (in_array($scope, ['all', 'topic'])) {
                            $q->orWhere('topic', 'LIKE', "%{$rawSearch}%");
                        }

                        // Prioritas 2: Semua kata individual ada di topic
                        if (in_array($scope, ['all', 'topic'])) {
                            foreach ($words as $word) {
                                $q->orWhere('topic', 'LIKE', "%{$word}%");
                            }
                        }

                        // Prioritas 3: Cocok di keywords (JSON — cari di dalam string JSON)
                        if (in_array($scope, ['all', 'keywords'])) {
                            $q->orWhere('keywords', 'LIKE', "%{$rawSearch}%");
                            foreach ($words as $word) {
                                $q->orWhere('keywords', 'LIKE', "%{$word}%");
                            }
                        }

                        // Prioritas 4: Cocok di content (cukup satu kata saja di tiap OR)
                        if (in_array($scope, ['all', 'content'])) {
                            $q->orWhere('content', 'LIKE', "%{$rawSearch}%");
                        }
                    });

                    // Urutkan berdasarkan relevansi: topic match dulu, lalu keywords, lalu content
                    $query->orderByRaw("
                        CASE
                            WHEN topic LIKE ? THEN 0
                            WHEN topic LIKE ? THEN 1
                            WHEN keywords LIKE ? THEN 2
                            ELSE 3
                        END ASC,
                        updated_at DESC
                    ", ["%{$rawSearch}%", "%" . $words[0] . "%", "%{$rawSearch}%"]);
                }
            } else {
                $query->latest();
            }

            $totalKnowledge   = KnowledgeBase::count();
            $knowledges       = $query->paginate(10)->withQueryString();
            $searchCount      = $request->filled('search') ? $knowledges->total() : null;
            $settings         = Setting::pluck('value', 'key')->toArray();
        } catch (\Throwable $exception) {
            report($exception);
        }

        $pdfParserAvailable = class_exists(Parser::class);
        $ocrAvailable = file_exists('C:\\Program Files\\Tesseract-OCR\\tesseract.exe');
        $systemReady = $databaseConnected && $pdfParserAvailable;

        return view('admin.dashboard', compact(
            'knowledges',
            'totalKnowledge',
            'searchCount',
            'settings',
            'availableClusters',
            'databaseConnected',
            'pdfParserAvailable',
            'ocrAvailable',
            'systemReady'
        ));
    }

    public function storeKnowledge(Request $request)
    {
        if ($request->filled('source_url')) {
            $rawUrl = trim($request->input('source_url'));
            if (!str_starts_with($rawUrl, 'http://') && !str_starts_with($rawUrl, 'https://')) {
                $request->merge(['source_url' => 'https://' . $rawUrl]);
            }
        }

        $request->validate([
            'cluster'       => 'required|string|max:100',
            'topic'         => 'required|string|max:255',
            'keywords'      => 'nullable|string|max:2000',
            'source_url'    => 'nullable|url',
            'pdf_file'      => 'nullable|file|mimes:pdf,png,jpg,jpeg,webp|max:20480',
            'uploaded_file' => 'nullable|file|mimes:pdf,png,jpg,jpeg,webp|max:20480',
            'content'       => 'nullable|string',
        ]);

        $content = $request->input('content');

        // 1. Ekstraksi dari File Upload (PDF atau Gambar)
        $uploadedFile = $request->file('uploaded_file') ?? $request->file('pdf_file');
        if ($uploadedFile) {
            $extension = strtolower($uploadedFile->getClientOriginalExtension());
            $realPath  = $uploadedFile->getRealPath();

            if ($extension === 'pdf') {
                $pdfText = $this->extractTextFromPdf($realPath);
                if ($pdfText !== '') {
                    $content = $content ? $content . "\n\n[Ekstraksi File PDF Upload]:\n" . $pdfText : $pdfText;
                }
            } elseif (in_array($extension, ['png', 'jpg', 'jpeg', 'webp', 'bmp', 'tif', 'tiff'], true)) {
                $imageText = $this->extractTextFromImage($realPath);
                if ($imageText !== '') {
                    $content = $content ? $content . "\n\n[Ekstraksi Teks Gambar Upload]:\n" . $imageText : $imageText;
                }
            }
        }

        // 2. Ekstraksi dari URL Sumber (Web HTML, Direct PDF, atau Direct Image)
        // Jika user memasukkan konten manual dan TIDAK mencentang auto_scrape_url,
        // maka URL hanya disimpan sebagai tautan referensi saja tanpa di-scrape.
        $hasManualContent = !empty(trim((string) $request->input('content')));
        $shouldScrapeUrl  = $request->boolean('auto_scrape_url');

        if ($request->filled('source_url') && ($shouldScrapeUrl || !$hasManualContent)) {
            $url       = trim($request->input('source_url'));
            $urlPath   = (string) parse_url($url, PHP_URL_PATH);
            $extension = strtolower(pathinfo($urlPath, PATHINFO_EXTENSION));

            // Kasus A: URL langsung mengarah ke file PDF
            if ($extension === 'pdf') {
                $pdfText = $this->extractTextFromPdf($url);
                if ($pdfText !== '') {
                    $content = $content ? $content . "\n\n[Ekstraksi File PDF dari URL]:\n" . $pdfText : $pdfText;
                }
            }
            // Kasus B: URL langsung mengarah ke file Gambar (PNG/JPG/WEBP)
            elseif (in_array($extension, ['png', 'jpg', 'jpeg', 'webp', 'bmp', 'tif', 'tiff'], true)) {
                $imgText = $this->extractTextFromImageUrl($url);
                if ($imgText !== '') {
                    $content = $content ? $content . "\n\n[Ekstraksi Teks Gambar dari URL]:\n" . $imgText : $imgText;
                }
            }
            // Kasus C: URL Instagram
            elseif (str_contains(parse_url($url, PHP_URL_HOST) ?? '', 'instagram.com')) {
                $igText = $this->extractTextFromInstagram($url);
                if ($igText !== '') {
                    $content = $content ? $content . "\n\n[Ekstraksi Post Instagram]:\n" . $igText : $igText;
                }
            }
            // Kasus D: URL halaman Web HTML
            else {
                try {
                    $response = Http::withoutVerifying()
                        ->timeout(25)
                        ->withHeaders([
                            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
                        ])
                        ->get($url);

                    if ($response->successful()) {
                        $html = $response->body();

                        // Ekstraksi teks web dasar
                        $cleaned = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
                        $cleaned = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $cleaned);
                        $cleaned = strip_tags($cleaned);
                        $extractedWebText = trim(preg_replace('/\s+/u', ' ', html_entity_decode($cleaned, ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '');

                        if ($extractedWebText !== '') {
                            $content = $content ? $content . "\n\n" . $extractedWebText : $extractedWebText;
                        }

                        // Deteksi dokumen PDF tertaut (misal: tombol unduh brosur/kalender event)
                        if (preg_match_all('#href=["\'](https?://[^\s"\'>]+\.pdf|[^\s"\'>]+\.pdf)["\']#i', $html, $pdfMatches)) {
                            $foundPdfs = array_unique($pdfMatches[1]);
                            foreach (array_slice($foundPdfs, 0, 2) as $linkedPdf) {
                                if (!str_starts_with($linkedPdf, 'http')) {
                                    $parsedBase = parse_url($url);
                                    $baseOrigin = ($parsedBase['scheme'] ?? 'https') . '://' . ($parsedBase['host'] ?? '');
                                    $linkedPdf = $baseOrigin . (str_starts_with($linkedPdf, '/') ? '' : '/') . $linkedPdf;
                                }
                                $pdfText = $this->extractTextFromPdf($linkedPdf);
                                if ($pdfText !== '') {
                                    $content = $content ? $content . "\n\n[Dokumen PDF Terkait (" . basename($linkedPdf) . ")]:\n" . $pdfText : $pdfText;
                                }
                            }
                        }

                        // Deteksi gambar poster/galeri/flyer di halaman dan lakukan OCR
                        if (preg_match_all('#(?:href|src|data-src)=["\'](https?://[^\s"\'>]+\.(?:png|jpg|jpeg|webp)|[^\s"\'>]+\.(?:png|jpg|jpeg|webp))["\']#i', $html, $imgMatches)) {
                            $foundImgs = array_unique($imgMatches[1]);
                            $contentImages = array_filter($foundImgs, function ($img) {
                                return !preg_match('/(?:logo|icon|favicon|pixel|facebook|theme|avatar|badge|nav)/i', $img);
                            });

                            foreach (array_slice($contentImages, 0, 4) as $imgUrl) {
                                if (!str_starts_with($imgUrl, 'http')) {
                                    $parsedBase = parse_url($url);
                                    $baseOrigin = ($parsedBase['scheme'] ?? 'https') . '://' . ($parsedBase['host'] ?? '');
                                    $imgUrl = $baseOrigin . (str_starts_with($imgUrl, '/') ? '' : '/') . $imgUrl;
                                }

                                $ocrText = $this->extractTextFromImageUrl($imgUrl);
                                if ($ocrText !== '' && mb_strlen($ocrText) > 20) {
                                    $content = $content ? $content . "\n\n[Hasil OCR Gambar (" . basename($imgUrl) . ")]:\n" . $ocrText : $ocrText;
                                }
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    // Lanjutkan jika koneksi web gagal
                }
            }
        }

        $keywordsString = $request->input('keywords', '');
        if ($request->filled('source_url')) {
            $sourceTag = 'source:' . trim($request->input('source_url'));
            $keywordsString = $keywordsString ? $keywordsString . ', ' . $sourceTag : $sourceTag;
        }

        $keywordsArray = [];
        if (!empty($keywordsString)) {
            $keywordsArray = array_values(array_filter(array_map('trim', explode(',', $keywordsString))));
        }

        // Tentukan URL unik yang akan disimpan
        $rawUrl = $request->filled('source_url')
            ? trim($request->input('source_url'))
            : ('manual-' . time() . '-' . rand(100, 999));
        $saveUrl = $rawUrl;

        // Cegah error duplicate entry jika 1 URL dipakai untuk beberapa potongan topik/chunk
        if (KnowledgeBase::where('url', $saveUrl)->exists()) {
            $saveUrl = $rawUrl . '#' . \Illuminate\Support\Str::slug($request->input('topic'));
            if (KnowledgeBase::where('url', $saveUrl)->exists()) {
                $saveUrl .= '-' . rand(10, 99);
            }
        }

        KnowledgeBase::create([
            'url'         => $saveUrl,
            'cluster'     => $request->input('cluster'),
            'topic'       => $request->input('topic'),
            'content'     => $content ?: '-',
            'keywords'    => $keywordsArray,
            'description' => mb_substr($content ?: '', 0, 150),
        ]);

        return back()->with('success', 'Data Knowledge Base berhasil disimpan ke database!');
    }

    /**
     * Ekstrak teks dari file gambar lokal menggunakan Tesseract OCR
     */
    private function extractTextFromImage(string $imagePath): string
    {
        $tesseract = 'C:\\Program Files\\Tesseract-OCR\\tesseract.exe';
        if (!file_exists($tesseract) || !file_exists($imagePath)) {
            return '';
        }

        $command = sprintf('"%s" "%s" stdout 2>nul', $tesseract, $imagePath);
        $output = shell_exec($command);

        return trim((string) $output);
    }

    /**
     * Download gambar dari URL dan jalankan Tesseract OCR
     */
    private function extractTextFromImageUrl(string $imageUrl): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'img_ocr_');
        try {
            $response = Http::withoutVerifying()
                ->timeout(20)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0'])
                ->get($imageUrl);

            if (!$response->successful() || file_put_contents($tempFile, $response->body()) === false) {
                return '';
            }

            return $this->extractTextFromImage($tempFile);
        } catch (\Throwable $e) {
            return '';
        } finally {
            if ($tempFile && file_exists($tempFile)) {
                @unlink($tempFile);
            }
        }
    }

    /**
     * Ekstrak teks dari file PDF lokal atau URL PDF
     */
    private function extractTextFromPdf(string $pdfPathOrUrl): string
    {
        if (!class_exists(Parser::class)) {
            return '';
        }

        $tempFile = null;
        $filePath = $pdfPathOrUrl;

        if (filter_var($pdfPathOrUrl, FILTER_VALIDATE_URL)) {
            $tempFile = tempnam(sys_get_temp_dir(), 'pdf_dl_');
            try {
                $response = Http::withoutVerifying()->timeout(30)->get($pdfPathOrUrl);
                if (!$response->successful() || file_put_contents($tempFile, $response->body()) === false) {
                    if ($tempFile && file_exists($tempFile)) @unlink($tempFile);
                    return '';
                }
                $filePath = $tempFile;
            } catch (\Throwable $e) {
                if ($tempFile && file_exists($tempFile)) @unlink($tempFile);
                return '';
            }
        }

        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($filePath);
            return trim($pdf->getText());
        } catch (\Throwable $e) {
            return '';
        } finally {
            if ($tempFile && file_exists($tempFile)) {
                @unlink($tempFile);
            }
        }
    }

    /**
     * Ekstrak teks (caption) dari Instagram Post menggunakan Python Instaloader
     */
    private function extractTextFromInstagram(string $igUrl): string
    {
        $pythonScript = base_path('ig_scrape.py');
        if (!file_exists($pythonScript)) {
            return '';
        }

        $command = escapeshellcmd('python ' . $pythonScript . ' "' . $igUrl . '"');
        $output = shell_exec($command);
        
        if (!$output) {
            return '';
        }

        $data = json_decode(trim($output), true);
        if (isset($data['success']) && $data['success'] === true) {
            $caption = $data['data']['caption'] ?? '';
            $owner = $data['data']['owner'] ?? '';
            $date = $data['data']['date'] ?? '';
            
            $result = "Akun: @{$owner}\nTanggal: {$date}\nCaption:\n{$caption}";
            
            // Coba lakukan OCR jika ada gambar (bisa multi-page/carousel)
            $imageUrls = $data['data']['image_urls'] ?? [];
            foreach ($imageUrls as $index => $imageUrl) {
                if (!empty($imageUrl)) {
                    $ocrText = $this->extractTextFromImageUrl($imageUrl);
                    if ($ocrText !== '' && mb_strlen($ocrText) > 20) {
                        $pageNum = $index + 1;
                        $result .= "\n\n[Teks Terbaca dari Gambar Postingan Halaman {$pageNum} (OCR)]:\n" . $ocrText;
                    }
                }
            }
            
            return $result;
        }

        return '';
    }

    public function updateKnowledge(Request $request, $id)
    {
        $request->validate([
            'cluster' => 'required|string|max:100',
            'topic' => 'required|string|max:255',
            'keywords' => 'nullable|string|max:2000',
            'source_url' => 'nullable|string|max:2000',
            'content' => 'nullable|string',
        ]);

        $knowledge = KnowledgeBase::findOrFail($id);

        $keywordsString = $request->input('keywords', '');
        $keywordsArray = [];
        if (!empty($keywordsString)) {
            $keywordsArray = array_values(array_filter(array_map('trim', explode(',', $keywordsString))));
        }
        
        // Re-append source tag as a hidden keyword for backward compatibility with RAG if needed
        $sourceUrl = $request->input('source_url');
        if (!empty($sourceUrl)) {
            $keywordsArray[] = 'source:' . trim($sourceUrl);
            $knowledge->url = trim($sourceUrl); // Update url column
        }

        $knowledge->update([
            'cluster' => $request->input('cluster'),
            'topic' => $request->input('topic'),
            'content' => $request->input('content') ?: '-',
            'keywords' => $keywordsArray,
        ]);

        return back()->with('success', 'Data Knowledge Base berhasil diperbarui!');
    }

    public function destroyKnowledge($id)
    {
        $knowledge = KnowledgeBase::findOrFail($id);
        $knowledge->delete();

        return back()->with('success', 'Data Knowledge Base berhasil dihapus!');
    }

    public function updateSettings(Request $request)
    {
        $data = $request->except('_token');

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('success', 'Pengaturan AI Chat berhasil diperbarui!');
    }
}